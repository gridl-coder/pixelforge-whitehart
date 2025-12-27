<?php
/**
 * Bootstraps the White Hart child theme so Blade views resolve from the child resources folder
 * while continuing to use the parent framework for booking and data providers.
 */
add_action('after_setup_theme', function () {
    if (! function_exists('app')) {
        return;
    }

    $config = app('config');

    if (! $config) {
        return;
    }

    $viewPaths = $config->get('view.paths', []);
    array_unshift($viewPaths, get_stylesheet_directory() . '/resources/views');

    $config->set('view.paths', array_values(array_unique($viewPaths)));

    $config->set('view.compiled', get_stylesheet_directory() . '/storage/framework/views');
}, 20);
