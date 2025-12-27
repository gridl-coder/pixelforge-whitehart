<?php

namespace PixelForge;

/**
 * Disable emojis.
 */
add_action('init', __NAMESPACE__ . '\\disable_emojis');

/**
 * Remove unused scripts.
 */
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\remove_unused_scripts', 100);

/**
 * Clean up wp_head.
 */
add_action('init', __NAMESPACE__ . '\\cleanup_head');

/**
 * Remove version strings.
 */
add_filter('style_loader_src', __NAMESPACE__ . '\\remove_version_strings', 10, 2);
add_filter('script_loader_src', __NAMESPACE__ . '\\remove_version_strings', 10, 2);

/**
 * Disable XML-RPC.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Disable self-pingbacks.
 */
add_action('pre_ping', __NAMESPACE__ . '\\disable_self_pingbacks');

/**
 * Limit Heartbeat API.
 */
add_filter('heartbeat_settings', __NAMESPACE__ . '\\limit_heartbeat');

/**
 * Performance hints.
 */
add_filter('wp_resource_hints', __NAMESPACE__ . '\\add_performance_hints', 10, 2);

/**
 * Image optimization.
 */
add_filter('wp_editor_set_quality', __NAMESPACE__ . '\\set_editor_image_quality');
add_filter('jpeg_quality', __NAMESPACE__ . '\\set_editor_image_quality');
add_filter('image_editor_output_format', __NAMESPACE__ . '\\force_modern_image_formats');
add_filter('upload_mimes', __NAMESPACE__ . '\\allow_additional_image_mimes');

/**
 * Defer scripts.
 */
add_filter('script_loader_tag', __NAMESPACE__ . '\\defer_theme_scripts', 10, 3);

/**
 * Cache headers.
 */
add_filter('wp_headers', __NAMESPACE__ . '\\add_client_cache_headers');

function disable_emojis(): void
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', __NAMESPACE__ . '\\disable_emojis_tinymce');
    add_filter('wp_resource_hints', __NAMESPACE__ . '\\remove_emoji_dns_prefetch', 10, 2);
}

function disable_emojis_tinymce(array $plugins): array
{
    if (is_array($plugins)) {
        return array_diff($plugins, ['wpemoji']);
    }
    return [];
}

function remove_emoji_dns_prefetch(array $urls, string $relation_type): array
{
    if ('dns-prefetch' === $relation_type) {
        $emoji_svg_url = 'https://s.w.org/images/core/emoji/2/svg/';
        $urls = array_diff($urls, [$emoji_svg_url]);
    }
    return $urls;
}

function cleanup_head(): void
{
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'start_post_rel_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
}

function remove_unused_scripts(): void
{
    if (!is_admin()) {
        wp_dequeue_script('wp-embed');
    }
}

function remove_version_strings(string $src): string
{
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

function disable_self_pingbacks(array &$links): void
{
    $home = get_option('home');
    foreach ($links as $l => $link) {
        if (0 === strpos($link, $home)) {
            unset($links[$l]);
        }
    }
}

function limit_heartbeat(array $settings): array
{
    $settings['interval'] = 60;
    return $settings;
}

function add_performance_hints(array $hints, string $relation): array
{
    if ($relation === 'preconnect') {
        $hints = array_filter(
            $hints,
            static fn($hint) => ! in_array($hint, ['https://fonts.gstatic.com', 'https://fonts.googleapis.com'], true)
        );
    }

    return array_values(array_unique($hints));
}

function set_editor_image_quality(): int
{
    return 82;
}

function force_modern_image_formats(array $formats): array
{
    if (function_exists('wp_image_editor_supports') && ! wp_image_editor_supports(['mime_type' => 'image/webp'])) {
        return $formats;
    }

    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png'] = 'image/webp';

    return $formats;
}

function allow_additional_image_mimes(array $mimeTypes): array
{
    $mimeTypes['webp'] = 'image/webp';

    return $mimeTypes;
}

function defer_theme_scripts(string $tag, string $handle, string $src): string
{
    if (is_admin()) {
        return $tag;
    }

    $excludedHandles = [
        'jquery-core',
        'jquery-migrate',
    ];

    if (in_array($handle, $excludedHandles, true)) {
        return $tag;
    }

    if (strpos($tag, ' defer') === false && strpos($tag, ' src=') !== false) {
        return str_replace('<script ', '<script defer ', $tag);
    }

    return $tag;
}

function add_client_cache_headers(array $headers): array
{
    if (is_user_logged_in() || is_admin() || is_search() || is_404() || is_feed() || is_preview()) {
        return $headers;
    }

    $cacheableStatuses = [200, 203, 206];

    if (isset($GLOBALS['wp']->query_vars['error']) || (isset($headers['Status']) && ! in_array((int) $headers['Status'], $cacheableStatuses, true))) {
        return $headers;
    }

    $maxAge = 600;

    if (! isset($headers['Cache-Control'])) {
        $headers['Cache-Control'] = sprintf('public, max-age=%1$d, s-maxage=%1$d', $maxAge);
    }

    if (! isset($headers['Expires'])) {
        $headers['Expires'] = gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT';
    }

    if (! isset($headers['Last-Modified'])) {
        $lastModified = get_lastpostmodified('GMT') ?: gmdate('Y-m-d H:i:s');
        $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', strtotime($lastModified)) . ' GMT';
    }

    if (! isset($headers['ETag'])) {
        $etagSeed = home_url('/') . '|' . ($headers['Last-Modified'] ?? '') . '|' . wp_cache_get_last_changed('posts');
        $headers['ETag'] = '"' . md5($etagSeed) . '"';
    }

    return $headers;
}
