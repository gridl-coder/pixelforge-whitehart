<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_server_load_panel_metabox');
add_action('wp_ajax_pixelforge_get_server_load_data', __NAMESPACE__ . '\\ajax_get_server_load_data');


function register_server_load_panel_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }

    // Enqueue Chart.js and our custom script
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_localize_script('chart-js', 'serverLoadAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('pixelforge_server_load_panel_nonce'),
    ]);

    $cmb_server = \new_cmb2_box([
        'id'           => 'pixelforge_server_load_panel',
        'title'        => esc_html__('Server Load Panel', 'pixelforge'),
        'object_types' => ['options-page'],
        'option_key'   => 'pixelforge_server_load',
        'icon_url'     => 'dashicons-performance',
        'menu_title'   => esc_html__('Server Load', 'pixelforge'),
        'position'     => 81,
    ]);

    // Section for Server Load
    $cmb_server->add_field([
        'name' => esc_html__('Server Metrics', 'pixelforge'),
        'id'   => 'server_load_title',
        'type' => 'title',
        'desc' => esc_html__('Live server performance metrics, updated every 5 seconds.', 'pixelforge'),
    ]);

    $cpu_load = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 'Not available';
    $cmb_server->add_field([
        'name'    => esc_html__('CPU Load (1 min avg)', 'pixelforge'),
        'id'      => 'cpu_load',
        'type'    => 'text',
        'default' => $cpu_load,
        'attributes' => ['readonly' => 'readonly', 'id' => 'cpu_load_field'],
    ]);

    $mem_usage = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
    $cmb_server->add_field([
        'name'    => esc_html__('PHP Memory Usage', 'pixelforge'),
        'id'      => 'memory_usage',
        'type'    => 'text',
        'default' => $mem_usage,
        'attributes' => ['readonly' => 'readonly', 'id' => 'memory_usage_field'],
    ]);

    // Section for Bot Spam
    $cmb_server->add_field([
        'name' => esc_html__('Comment Spam', 'pixelforge'),
        'id'   => 'spam_detection_title',
        'type' => 'title',
        'desc' => esc_html__('Monitor for comment spam activity.', 'pixelforge'),
    ]);

    $cmb_server->add_field([
        'name' => esc_html__('Comment Statistics Chart', 'pixelforge'),
        'id'   => 'comment_chart',
        'type' => 'title',
        'desc' => '<canvas id="commentStatsChart" width="400" height="200"></canvas>',
    ]);

    // Get recent comments
    $recent_comments = get_comments(['number' => 10, 'status' => 'all']);
    $recent_comments_display = '';
    foreach ($recent_comments as $comment) {
        $recent_comments_display .= sprintf(
            "Author: %s\nEmail: %s\nStatus: %s\n\n",
            $comment->comment_author,
            $comment->comment_author_email,
            wp_get_comment_status($comment)
        );
    }

    $cmb_server->add_field([
        'name' => esc_html__('Recent Comments', 'pixelforge'),
        'id'   => 'recent_comments',
        'type' => 'textarea',
        'default' => $recent_comments_display,
        'attributes' => ['readonly' => 'readonly', 'rows' => 10],
    ]);

    // Section for Error Logs
    $cmb_server->add_field([
        'name' => esc_html__('Server Error Logs', 'pixelforge'),
        'id'   => 'error_logs_title',
        'type' => 'title',
        'desc' => esc_html__('View recent entries from the server error log.', 'pixelforge'),
    ]);

    $log_file = WP_CONTENT_DIR . '/debug.log';
    $log_contents = file_exists($log_file) ? implode('', array_slice(file($log_file), -50)) : esc_html__('No debug log file found.', 'pixelforge');

    $cmb_server->add_field([
        'name' => esc_html__('Recent Errors', 'pixelforge'),
        'id'   => 'error_log_contents',
        'type' => 'textarea',
        'default' => $log_contents,
        'attributes' => ['readonly' => 'readonly', 'rows' => 15],
    ]);

    add_action('admin_footer', __NAMESPACE__ . '\\server_load_panel_javascript');
}

function server_load_panel_javascript()
{
    $comment_counts = wp_count_comments();
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('commentStatsChart').getContext('2d');
            var commentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Pending', 'Approved', 'Spam'],
                    datasets: [{
                        label: 'Comment Counts',
                        data: [
                            <?php echo $comment_counts->pending; ?>,
                            <?php echo $comment_counts->approved; ?>,
                            <?php echo $comment_counts->spam; ?>
                        ],
                        backgroundColor: ['rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                        borderColor: ['rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true } } }
            });

            function updatePanelData() {
                fetch(serverLoadAjax.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 'action': 'pixelforge_get_server_load_data', 'security': serverLoadAjax.nonce })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        const data = response.data;
                        document.getElementById('cpu_load_field').value = data.cpu_load;
                        document.getElementById('memory_usage_field').value = data.memory_usage;
                        document.getElementById('recent_comments').value = data.recent_comments;
                        document.getElementById('error_log_contents').value = data.error_log;

                        commentChart.data.datasets[0].data = [
                            data.comment_counts.pending,
                            data.comment_counts.approved,
                            data.comment_counts.spam
                        ];
                        commentChart.update();
                    }
                });
            }

            setInterval(updatePanelData, 5000);
        });
    </script>
    <?php
}

function ajax_get_server_load_data()
{
    check_ajax_referer('pixelforge_server_load_panel_nonce', 'security');

    $comment_counts = wp_count_comments();
    $recent_comments = get_comments(['number' => 10, 'status' => 'all']);
    $recent_comments_display = '';
    foreach ($recent_comments as $comment) {
        $recent_comments_display .= sprintf("Author: %s\nEmail: %s\nStatus: %s\n\n", $comment->comment_author, $comment->comment_author_email, wp_get_comment_status($comment));
    }

    $log_file = WP_CONTENT_DIR . '/debug.log';
    $log_contents = file_exists($log_file) ? implode('', array_slice(file($log_file), -50)) : esc_html__('No debug log file found.', 'pixelforge');

    wp_send_json_success([
        'cpu_load' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 'Not available',
        'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        'comment_counts' => ['pending' => $comment_counts->pending, 'approved' => $comment_counts->approved, 'spam' => $comment_counts->spam],
        'recent_comments' => $recent_comments_display,
        'error_log' => $log_contents,
    ]);
}
