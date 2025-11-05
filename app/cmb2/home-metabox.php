<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\register_home_metabox');

function register_home_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }
    $cmb_home = new_cmb2_box([
        'id' => 'home_register_metabox',
        'title' => esc_html__('Home Hero Content', 'pixelforge'),
        'object_types' => ['page'],
        'show_on' => ['key' => 'front-page', 'value' => 'true'],
    ]);

    $cmb_home->add_field([
        'name' => esc_html__('Location', 'pixelforge'),
        'desc' => esc_html__('Display the town or area beneath the site title.', 'pixelforge'),
        'id' => 'home_location',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_home->add_field([
        'name' => esc_html__('Header Image', 'pixelforge'),
        'desc' => esc_html__('Upload the background image shown behind the hero content.', 'pixelforge'),
        'id' => 'home_header_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => __('Add File', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'large',
    ]);
}
