<?php

namespace PixelForge\PostTypes;

use function apply_filters;
use function register_post_type;

final class BookingSection
{
    public const KEY = 'booking_section';
    public const SLUG = 'booking-section';

    public static function register(): void
    {
        $args = [
            'labels' => self::labels(),
            'description' => __('Sections for organising tables (e.g., Bar, Stage, Function room).', 'pixelforge'),
            'public' => true,
            'hierarchical' => false,
            'has_archive' => false,
            'show_in_rest' => true,
            'rest_base' => self::SLUG,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-category',
            'supports' => ['title'],
            'rewrite' => [
                'slug' => self::SLUG,
                'with_front' => false,
            ],
            'query_var' => self::SLUG,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'map_meta_cap' => true,
            'capability_type' => 'post',
        ];

        register_post_type(self::KEY, apply_filters('pixelforge/booking_sections/post_type_args', $args));
    }

    private static function labels(): array
    {
        $singular = __('Table Section', 'pixelforge');
        $plural = __('Table Sections', 'pixelforge');

        return [
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'name_admin_bar' => $plural,
            'add_new' => __('Add New', 'pixelforge'),
            'add_new_item' => sprintf(__('Add New %s', 'pixelforge'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'pixelforge'), $singular),
            'new_item' => sprintf(__('New %s', 'pixelforge'), $singular),
            'view_item' => sprintf(__('View %s', 'pixelforge'), $singular),
            'view_items' => sprintf(__('View %s', 'pixelforge'), $plural),
            'search_items' => sprintf(__('Search %s', 'pixelforge'), $plural),
            'not_found' => sprintf(__('No %s found.', 'pixelforge'), strtolower($plural)),
            'not_found_in_trash' => sprintf(__('No %s found in Trash.', 'pixelforge'), strtolower($plural)),
            'all_items' => sprintf(__('All %s', 'pixelforge'), $plural),
            'archives' => sprintf(__('%s Archives', 'pixelforge'), $singular),
            'attributes' => sprintf(__('%s Attributes', 'pixelforge'), $singular),
            'insert_into_item' => sprintf(__('Insert into %s', 'pixelforge'), strtolower($singular)),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', 'pixelforge'), strtolower($singular)),
            'featured_image' => __('Featured Image', 'pixelforge'),
            'set_featured_image' => __('Set featured image', 'pixelforge'),
            'remove_featured_image' => __('Remove featured image', 'pixelforge'),
            'use_featured_image' => __('Use as featured image', 'pixelforge'),
            'items_list' => sprintf(__('%s list', 'pixelforge'), $plural),
            'items_list_navigation' => sprintf(__('%s list navigation', 'pixelforge'), $plural),
            'filter_items_list' => sprintf(__('Filter %s list', 'pixelforge'), strtolower($plural)),
            'parent_item_colon' => sprintf(__('Parent %s:', 'pixelforge'), $singular),
        ];
    }
}
