<?php

/**
 * Theme setup.
 */

namespace PixelForge;

use Illuminate\Support\Facades\Vite;

/**
 * Disable the block editor in favor of the classic editor experience.
 */
add_filter('use_block_editor_for_post_type', function ($useBlockEditor, $postType) {
    return false;
}, 100, 2);

add_filter('use_block_editor_for_post', '__return_false', 100);
add_filter('use_widgets_block_editor', '__return_false');
add_filter('gutenberg_use_widgets_block_editor', '__return_false');

add_filter('show_admin_bar', '__return_false');


/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.scss');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'pixelforge'),
        'mastnav' => __('Masthead Navigation', 'pixelforge'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'pixelforge'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'pixelforge'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Ensure required pages exist and configure the front page settings on theme activation.
 */
add_action('after_switch_theme', function () {
    $pages = [
        'home' => [
            'post_title' => __('Home', 'pixelforge'),
            'post_name' => 'home',
        ],
        'about' => [
            'post_title' => __('About Us', 'pixelforge'),
            'post_name' => 'about-us',
        ],
        'news' => [
            'post_title' => __('News', 'pixelforge'),
            'post_name' => 'news',
        ],
        'contact' => [
            'post_title' => __('Contact', 'pixelforge'),
            'post_name' => 'contact',
        ],
    ];

    $createdPages = [];

    $samplePage = get_page_by_path('sample-page');

    if (! $samplePage instanceof \WP_Post) {
        $samplePage = get_page_by_title('Sample Page');
    }

    if ($samplePage instanceof \WP_Post) {
        wp_delete_post($samplePage->ID, true);
    }

    foreach ($pages as $key => $page) {
        $existingPage = get_page_by_path($page['post_name']);

        if (! $existingPage instanceof \WP_Post) {
            $existingPage = get_page_by_title($page['post_title']);
        }

        if ($existingPage instanceof \WP_Post) {
            $createdPages[$key] = $existingPage->ID;

            continue;
        }

        $pageId = wp_insert_post([
            'post_title' => $page['post_title'],
            'post_name' => sanitize_title($page['post_name']),
            'post_status' => 'publish',
            'post_type' => 'page',
        ], true);

        if (! is_wp_error($pageId)) {
            $createdPages[$key] = $pageId;
        }
    }

    if (isset($createdPages['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $createdPages['home']);
    }

    if (isset($createdPages['news'])) {
        update_option('page_for_posts', $createdPages['news']);
    }

    $menu = wp_get_nav_menu_object('mastnav');

    if (! $menu) {
        $menuId = wp_create_nav_menu('mastnav');

        if (is_wp_error($menuId)) {
            return;
        }

        $menu = wp_get_nav_menu_object($menuId);
    }

    if (! $menu instanceof \WP_Term) {
        return;
    }

    $menuId = (int) $menu->term_id;

    $menuItems = wp_get_nav_menu_items($menuId);
    $existingMenuObjectIds = [];

    if (is_array($menuItems)) {
        foreach ($menuItems as $item) {
            if ($item instanceof \WP_Post) {
                $existingMenuObjectIds[] = (int) $item->object_id;
            }
        }
    }

    foreach (['home', 'about', 'news', 'contact'] as $key) {
        if (! isset($createdPages[$key])) {
            continue;
        }

        $pageId = (int) $createdPages[$key];

        if (in_array($pageId, $existingMenuObjectIds, true)) {
            continue;
        }

        wp_update_nav_menu_item($menuId, 0, [
            'menu-item-title' => get_the_title($pageId),
            'menu-item-object' => 'page',
            'menu-item-object-id' => $pageId,
            'menu-item-status' => 'publish',
            'menu-item-type' => 'post_type',
        ]);
    }

    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['mastnav'] = $menuId;

    set_theme_mod('nav_menu_locations', $locations);
});
