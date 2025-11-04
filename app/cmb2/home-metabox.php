<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\register_home_metabox');

function register_home_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }
    /*
        $cmb_home = \new_cmb2_box([
            'id'           => 'pixelforge_home_metabox',
            'title'        => esc_html__('Home Metabox', 'cmb2'),
            'object_types' => ['page'],
            'show_on_cb'   => __NAMESPACE__ . '\show_if_front_page',
        ]);

        $cmb_home->add_field([
            'name'       => esc_html__('Test Text', 'cmb2'),
            'desc'       => esc_html__('field description (optional)', 'cmb2'),
            'id'         => 'pixelforge_demo_text',
            'type'       => 'text',
            'show_on_cb' => __NAMESPACE__ . '\hide_if_no_cats',
        ]);

        $cmb_home->add_field([
            'name' => esc_html__('Website URL', 'cmb2'),
            'desc' => esc_html__('field description (optional)', 'cmb2'),
            'id'   => 'pixelforge_demo_url',
            'type' => 'text_url',
        ]);

        $cmb_home->add_field([
            'name' => esc_html__('Test Text Email', 'cmb2'),
            'desc' => esc_html__('field description (optional)', 'cmb2'),
            'id'   => 'pixelforge_demo_email',
            'type' => 'text_email',
        ]);

        $cmb_home->add_field([
            'name' => esc_html__('Test Money', 'cmb2'),
            'desc' => esc_html__('field description (optional)', 'cmb2'),
            'id'   => 'pixelforge_demo_textmoney',
            'type' => 'text_money',
        ]);

        $cmb_home->add_field([
            'name'    => esc_html__('Test Color Picker', 'cmb2'),
            'desc'    => esc_html__('field description (optional)', 'cmb2'),
            'id'      => 'pixelforge_demo_colorpicker',
            'type'    => 'colorpicker',
            'default' => '#ffffff',
        ]);

        $cmb_home->add_field([
            'name' => esc_html__('Test Text Area', 'cmb2'),
            'desc' => esc_html__('field description (optional)', 'cmb2'),
            'id'   => 'pixelforge_demo_textarea',
            'type' => 'textarea',
        ]);

        $cmb_home->add_field([
            'name'    => esc_html__('Test wysiwyg', 'cmb2'),
            'desc'    => esc_html__('field description (optional)', 'cmb2'),
            'id'      => 'pixelforge_demo_wysiwyg',
            'type'    => 'wysiwyg',
            'options' => [
                'textarea_rows' => 5,
            ],
        ]);

        $cmb_home->add_field([
            'name' => esc_html__('Test Image', 'cmb2'),
            'desc' => esc_html__('Upload an image or enter a URL.', 'cmb2'),
            'id'   => 'pixelforge_demo_image',
            'type' => 'file',
        ]);
    */
}
