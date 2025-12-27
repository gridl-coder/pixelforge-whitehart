<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\register_home_metabox');
add_action('cmb2_after_form', __NAMESPACE__ . '\enable_home_group_sorting', 10, 2);

function register_home_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }

    $show_on_front = static function ($cmb): bool {
        $frontPageId = (int) get_option('page_on_front');

        if (! $frontPageId) {
            return false;
        }

        return (int) $cmb->object_id === $frontPageId;
    };

    // 1. Home Hero Content
    $cmb_home = new_cmb2_box([
        'id' => 'home_register_metabox',
        'title' => esc_html__('Home Hero Content', 'pixelforge'),
        'object_types' => ['page'],
        'show_on_cb' => $show_on_front,
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

    $cmb_home->add_field([
        'name' => esc_html__('Guest Food Image', 'pixelforge'),
        'desc' => esc_html__('Upload the graphic for the guest food popup.', 'pixelforge'),
        'id' => 'home_guestpopup_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => __('Add Image', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'medium',
    ]);

    // 2. Amenities
    $cmb_amenities = new_cmb2_box([
        'id' => 'home_amenities_metabox',
        'title' => esc_html__('Amenities', 'pixelforge'),
        'object_types' => ['page'],
        'show_on_cb' => $show_on_front,
    ]);

    $amenities_group = $cmb_amenities->add_field([
        'id' => 'home_amenities',
        'type' => 'group',
        'repeatable' => true,
        'options' => [
            'group_title' => esc_html__('Amenity {#}', 'pixelforge'),
            'add_button' => esc_html__('Add Amenity', 'pixelforge'),
            'remove_button' => esc_html__('Remove Amenity', 'pixelforge'),
            'closed' => true,
            'sortable' => true,
        ],
    ]);

    $cmb_amenities->add_group_field($amenities_group, [
        'name' => esc_html__('Title', 'pixelforge'),
        'id' => 'title',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_amenities->add_group_field($amenities_group, [
        'name' => esc_html__('Icon', 'pixelforge'),
        'desc' => esc_html__('Enter the filename of the SVG icon (e.g., "guitars").', 'pixelforge'),
        'id' => 'icon',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_amenities->add_group_field($amenities_group, [
        'name' => esc_html__('Description', 'pixelforge'),
        'id' => 'description',
        'type' => 'textarea_small',
    ]);

    $cmb_amenities->add_group_field($amenities_group, [
        'name' => esc_html__('Image 1', 'pixelforge'),
        'id' => 'image_1',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => esc_html__('Add Image', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_amenities->add_group_field($amenities_group, [
        'name' => esc_html__('Image 2', 'pixelforge'),
        'id' => 'image_2',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => esc_html__('Add Image', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'medium',
    ]);

    // 3. Food Banner Images
    $cmb_food = new_cmb2_box([
        'id' => 'home_food_banner_metabox',
        'title' => esc_html__('Food Banner Images', 'pixelforge'),
        'object_types' => ['page'],
        'show_on_cb' => $show_on_front,
    ]);

    $food_banner_slider_group = $cmb_food->add_field([
        'id' => 'home_food_banner_images',
        'type' => 'group',
        'repeatable' => true,
        'options' => [
            'group_title' => esc_html__('Food Banner Image {#}', 'pixelforge'),
            'add_button' => esc_html__('Add Food Banner Image', 'pixelforge'),
            'remove_button' => esc_html__('Remove Image', 'pixelforge'),
            'closed' => true,
            'sortable' => true,
        ],
    ]);

    $cmb_food->add_group_field($food_banner_slider_group, [
        'name' => esc_html__('Image', 'pixelforge'),
        'id' => 'image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => esc_html__('Add Image', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_food->add_group_field($food_banner_slider_group, [
        'name' => esc_html__('Alt text', 'pixelforge'),
        'id' => 'alt',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_food->add_group_field($food_banner_slider_group, [
        'name' => esc_html__('Caption', 'pixelforge'),
        'id' => 'caption',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    // 4. Gallery Images
    $cmb_gallery = new_cmb2_box([
        'id' => 'home_gallery_metabox',
        'title' => esc_html__('Gallery Images', 'pixelforge'),
        'object_types' => ['page'],
        'show_on_cb' => $show_on_front,
    ]);

    $gallery_group_id = $cmb_gallery->add_field([
        'id' => 'home_gallery_images',
        'type' => 'group',
        'repeatable' => true,
        'options' => [
            'group_title' => esc_html__('Gallery Image {#}', 'pixelforge'),
            'add_button' => esc_html__('Add Gallery Image', 'pixelforge'),
            'remove_button' => esc_html__('Remove Image', 'pixelforge'),
            'closed' => true,
            'sortable' => true,
        ],
    ]);

    $cmb_gallery->add_group_field($gallery_group_id, [
        'name' => esc_html__('Image', 'pixelforge'),
        'id' => 'image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'text' => [
            'add_upload_file_text' => esc_html__('Add Image', 'pixelforge'),
        ],
        'query_args' => [
            'type' => [
                'image/gif',
                'image/jpg',
                'image/png',
                'image/jpeg',
            ],
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_gallery->add_group_field($gallery_group_id, [
        'name' => esc_html__('Alt text', 'pixelforge'),
        'id' => 'alt',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_gallery->add_group_field($gallery_group_id, [
        'name' => esc_html__('Caption', 'pixelforge'),
        'id' => 'caption',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);
}

function enable_home_group_sorting($postId, $cmb): void
{
    unset($postId);

    $allowed_metaboxes = [
        'home_amenities_metabox',
        'home_food_banner_metabox',
        'home_gallery_metabox'
    ];

    if (!isset($cmb->cmb_id) || !in_array($cmb->cmb_id, $allowed_metaboxes, true)) {
        return;
    }

    if (wp_script_is('pixelforge-home-gallery-sortable', 'registered')) {
        wp_enqueue_script('pixelforge-home-gallery-sortable');
        return;
    }

    wp_register_script(
        'pixelforge-home-gallery-sortable',
        false,
        ['jquery', 'jquery-ui-sortable'],
        null,
        true
    );

    $script = <<<'JS'
        (function($) {
          const sortableSelector = '#home_amenities_metabox .cmb-group-list, #home_food_banner_metabox .cmb-group-list, #home_gallery_metabox .cmb-group-list';

          const makeSortable = () => {
            const $list = $(sortableSelector);

            if (!$list.length || !$list.sortable) {
              return;
            }

            $list.sortable({
              handle: '.cmb-group-title, .cmbhandle',
              items: '> .cmb-repeatable-grouping',
              placeholder: 'cmb-row cmb-repeatable-grouping cmb-group-placeholder',
            });
          };

          $(document).on('cmb2_add_row', makeSortable);
          $(document).ready(makeSortable);
        })(jQuery);
    JS;

    wp_add_inline_script('pixelforge-home-gallery-sortable', $script);
    wp_enqueue_script('pixelforge-home-gallery-sortable');
}
