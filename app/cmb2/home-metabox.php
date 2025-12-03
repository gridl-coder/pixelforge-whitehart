<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\register_home_metabox');
add_action('cmb2_after_form', __NAMESPACE__ . '\enable_home_gallery_sorting', 10, 2);

function register_home_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }
    $cmb_home = new_cmb2_box([
        'id' => 'home_register_metabox',
        'title' => esc_html__('Home Hero Content', 'pixelforge'),
        'object_types' => ['page'],
        'show_on_cb' => static function ($cmb): bool {
            $frontPageId = (int) get_option('page_on_front');

            if (! $frontPageId) {
                return false;
            }

            return (int) $cmb->object_id === $frontPageId;
        },
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

    $gallery_group_id = $cmb_home->add_field([
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

    $cmb_home->add_group_field($gallery_group_id, [
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

    $cmb_home->add_group_field($gallery_group_id, [
        'name' => esc_html__('Alt text', 'pixelforge'),
        'id' => 'alt',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_home->add_group_field($gallery_group_id, [
        'name' => esc_html__('Caption', 'pixelforge'),
        'id' => 'caption',
        'type' => 'text',
        'sanitization_cb' => 'sanitize_text_field',
    ]);
}

function enable_home_gallery_sorting($postId, $cmb): void
{
    unset($postId);

    if (!isset($cmb->cmb_id) || $cmb->cmb_id !== 'home_register_metabox') {
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
          const sortableSelector = '#home_register_metabox .cmb-group-list';

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
