<?php

use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'pixelforge'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        PixelForge\Providers\ThemeServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect([
    'setup',
    'filters',
    'cmb2/bootstrap',
    'cmb2/helpers',
    'cmb2/home-metabox',
    'cmb2/theme-options',
    'cmb2/seo-metabox',
    'performance',
    'seo',
])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'pixelforge'), $file)
            );
        }
    });


// Register Events Post Type
function events()
{

    $labels = array(
        'name' => _x('Events', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Event', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('Events', 'text_domain'),
        'name_admin_bar' => __('Events', 'text_domain'),
        'archives' => __('Event Archives', 'text_domain'),
        'attributes' => __('Event Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Event:', 'text_domain'),
        'all_items' => __('All Events', 'text_domain'),
        'add_new_item' => __('Add New Event', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Event', 'text_domain'),
        'edit_item' => __('Edit Event', 'text_domain'),
        'update_item' => __('Update Event', 'text_domain'),
        'view_item' => __('View Event', 'text_domain'),
        'view_items' => __('View Events', 'text_domain'),
        'search_items' => __('Search Event', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
        'featured_image' => __('Featured Image', 'text_domain'),
        'set_featured_image' => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image' => __('Use as featured image', 'text_domain'),
        'insert_into_item' => __('Insert into Event', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this Event', 'text_domain'),
        'items_list' => __('Events list', 'text_domain'),
        'items_list_navigation' => __('Events list navigation', 'text_domain'),
        'filter_items_list' => __('Filter iClients list', 'text_domain'),
    );
    $rewrite = array(
        'slug' => 'events',
        'with_front' => true,
        'pages' => true,
        'feeds' => true,
    );
    $args = array(
        'label' => __('Event', 'text_domain'),
        'description' => __('Post type for Events', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies' => array('category', 'post_tag'),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-admin-generic',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => 'events',
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'query_var' => 'events',
        'rewrite' => $rewrite,
        'capability_type' => 'page',
        'show_in_rest' => true,
        'rest_base' => 'events',
    );
    register_post_type('events', $args);

}

add_action('init', 'events', 0);



add_action( 'cmb2_admin_init', 'events_register_metabox' );
/**
 * Hook in and add a demo metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
 */
function events_register_metabox()
{
    $events_meta = new_cmb2_box(array(
        'id' => 'events_register_metabox',
        'title' => esc_html__('Events Meta', 'cmb2'),
        'object_types' => array('events'), // Post type
    ));

    $events_meta->add_field(array(
        'name' => esc_html__('Event Date', 'cmb2'),
        'desc' => esc_html__('Insert the date of the event here', 'cmb2'),
        'id' => 'events_date',
        'type' => 'text_date_timestamp',
        //'date_format' => 'd/n/Y',
    ));

    $events_meta->add_field(array(
        'name' => esc_html__('Event URL', 'cmb2'),
        'desc' => esc_html__('Insert the url of the event here', 'cmb2'),
        'id' => 'events_url',
        'type' => 'text_url',
    ));
}
