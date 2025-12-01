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

    $image_group_id = $cmb_home->add_field(array(
        'id' => 'home_menu_carousel',
        'type' => 'group',
        'repeatable' => true,
        'options' => array(
            'group_title' => 'Image {#}',
            'add_button' => 'Add Another Image',
            'remove_button' => 'Remove Image',
            'closed' => true,  // Repeater fields closed by default - neat & compact.
            'sortable' => true,  // Allow changing the order of repeated groups.
        ),
    ));
    $cmb_home->add_group_field($image_group_id, array(
        'name' => 'Image Title',
        'desc' => 'Enter the image title.',
        'id' => 'menu_image_title',
        'type' => 'text',
    ));
    $cmb_home->add_group_field($image_group_id, array(
        'name' => 'Image File',
        'desc' => 'Upload an image',
        'id' => 'menu_image',
        'type' => 'file',
        // Optional:
        'options' => array(
            'url' => false, // Hide the text input for the url
        ),
        'text' => array(
            'add_upload_file_text' => 'Add Image' // Change upload button text. Default: "Add or Upload File"
        ),
        'query_args' => array(
            'type' => array(
                'image/gif',
                'image/jpeg',
                'image/png',
            ),
        ),
        'preview_size' => 'medium',
    ));
}
