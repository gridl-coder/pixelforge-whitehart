<?php

namespace PixelForge;

add_action('init', __NAMESPACE__ . '\\disable_emojis');
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\remove_unused_scripts', 100);
add_filter('wp_resource_hints', __NAMESPACE__ . '\\add_performance_hints', 10, 2);
add_filter('wp_editor_set_quality', __NAMESPACE__ . '\\set_editor_image_quality');
add_filter('jpeg_quality', __NAMESPACE__ . '\\set_editor_image_quality');
add_filter('image_editor_output_format', __NAMESPACE__ . '\\force_modern_image_formats');
add_filter('upload_mimes', __NAMESPACE__ . '\\allow_additional_image_mimes');
add_filter('script_loader_tag', __NAMESPACE__ . '\\defer_theme_scripts', 10, 3);

function disable_emojis(): void
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}

function remove_unused_scripts(): void
{
    wp_dequeue_script('wp-embed');
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

    if (strpos($tag, ' defer') === false && preg_match('/<script\b[^>]*src="[^"]+"[^>]*><\\/script>/', $tag)) {
        $tag = str_replace('<script ', '<script defer ', $tag);
    }

    return $tag;
}
