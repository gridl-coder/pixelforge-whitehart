<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\register_home_metabox');

function register_home_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }
    $cmb_home = new_cmb2_box(array(
        'id' => 'home_register_metabox',
        'title' => esc_html__('Events Meta', 'cmb2'),
        'object_types' => array('page'), // Post type
        'show_on' => array('key' => 'front-page', 'value' => 'true'),
    ));

    $cmb_home->add_field(array(
        'name' => esc_html__('Location', 'cmb2'),
        'desc' => esc_html__('Insert the town of the pub', 'cmb2'),
        'id' => 'home_location',
        'type' => 'text',
        //'date_format' => 'd/n/Y',
    ));

    $cmb_home->add_field(array(
        'name' => esc_html__('Header Image', 'cmb2'),
        'desc' => esc_html__('Upload the header image', 'cmb2'),
        'id' => 'home_header_image',
        'type' => 'file',
        // Optional:
        'options' => array(
            'url' => false, // Hide the text input for the url
        ),
        'text' => array(
            'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
        ),
        // query_args are passed to wp.media's library query.
        'query_args' => array(
            //'type' => 'application/pdf', // Make library only display PDFs.
            // Or only allow gif, jpg, or png images
            'type' => array(
                'image/gif',
                'image/jpg',
                'image/png',
            ),
        ),
        'preview_size' => 'large', // Image size to use when previewing in the admin.
    ));
}
