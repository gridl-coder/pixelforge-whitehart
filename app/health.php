<?php

namespace PixelForge;

add_filter('site_status_tests', __NAMESPACE__ . '\\register_site_health_tests');

function register_site_health_tests(array $tests): array
{
    $tests['direct']['pixelforge_imagick'] = [
        'label' => __('Image processing module availability', 'pixelforge'),
        'test' => __NAMESPACE__ . '\\site_health_imagick_test',
    ];

    $tests['direct']['pixelforge_object_cache'] = [
        'label' => __('Persistent object cache support', 'pixelforge'),
        'test' => __NAMESPACE__ . '\\site_health_object_cache_test',
    ];

    $tests['direct']['pixelforge_page_cache'] = [
        'label' => __('Page cache headers', 'pixelforge'),
        'test' => __NAMESPACE__ . '\\site_health_page_cache_test',
    ];

    return $tests;
}

function site_health_imagick_test(): array
{
    $imagickAvailable = extension_loaded('imagick') && class_exists('Imagick');

    if ($imagickAvailable) {
        return [
            'label' => __('Imagick is available for image processing', 'pixelforge'),
            'status' => 'good',
            'badge' => [
                'label' => __('Performance', 'pixelforge'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('The Imagick PHP extension is installed and can be used for optimized image generation.', 'pixelforge')
            ),
            'test' => 'pixelforge_imagick',
        ];
    }

    return [
        'label' => __('Imagick is not available for image processing', 'pixelforge'),
        'status' => 'recommended',
        'badge' => [
            'label' => __('Performance', 'pixelforge'),
            'color' => 'orange',
        ],
        'description' => sprintf(
            '<p>%s</p>',
            __('Install or enable the Imagick PHP extension so WordPress can create faster, higher-quality thumbnails.', 'pixelforge')
        ),
        'actions' => sprintf(
            '<p><a href="%s" target="_blank" rel="noreferrer noopener">%s</a></p>',
            esc_url('https://wordpress.org/hosting/requirements/#php-extensions'),
            __('Review the recommended PHP extensions', 'pixelforge')
        ),
        'test' => 'pixelforge_imagick',
    ];
}

function site_health_object_cache_test(): array
{
    $objectCacheEnabled = wp_using_ext_object_cache();
    $apcuSupported = function_exists('apcu_fetch') && ini_get('apc.enabled');

    if ($objectCacheEnabled) {
        return [
            'label' => __('A persistent object cache is enabled', 'pixelforge'),
            'status' => 'good',
            'badge' => [
                'label' => __('Performance', 'pixelforge'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('WordPress is using a persistent object cache to speed up database access.', 'pixelforge')
            ),
            'test' => 'pixelforge_object_cache',
        ];
    }

    $description = __('Enable a persistent object cache to reduce database load and improve response times.', 'pixelforge');

    if ($apcuSupported) {
        $description .= ' ' . __('Your host supports APCu, so you can install a caching plugin that uses it.', 'pixelforge');
    }

    return [
        'label' => __('Persistent object cache is not in use', 'pixelforge'),
        'status' => 'recommended',
        'badge' => [
            'label' => __('Performance', 'pixelforge'),
            'color' => 'orange',
        ],
        'description' => sprintf('<p>%s</p>', esc_html($description)),
        'actions' => sprintf(
            '<p><a href="%s" target="_blank" rel="noreferrer noopener">%s</a></p>',
            esc_url('https://wordpress.org/documentation/article/optimization/#persistent-object-cache'),
            __('Learn how to enable a persistent object cache', 'pixelforge')
        ),
        'test' => 'pixelforge_object_cache',
    ];
}

function site_health_page_cache_test(): array
{
    $request = wp_remote_get(home_url('/'), [
        'timeout' => 5,
        'user-agent' => 'PixelForge site health cache test',
    ]);

    if (is_wp_error($request)) {
        return [
            'label' => __('Page cache headers could not be detected', 'pixelforge'),
            'status' => 'recommended',
            'badge' => [
                'label' => __('Performance', 'pixelforge'),
                'color' => 'orange',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('We could not perform a loopback request to verify caching headers. Ensure loopback requests are allowed and retry.', 'pixelforge')
            ),
            'test' => 'pixelforge_page_cache',
        ];
    }

    $responseHeaders = wp_remote_retrieve_headers($request);
    $headers = array_change_key_case(
        is_object($responseHeaders) && method_exists($responseHeaders, 'getAll')
            ? $responseHeaders->getAll()
            : (array) $responseHeaders,
        CASE_LOWER
    );
    $detectedHeaders = array_intersect(array_keys($headers), [
        'cache-control',
        'expires',
        'age',
        'last-modified',
        'etag',
        'x-cache-enabled',
        'x-cache-disabled',
        'x-srcache-store-status',
        'x-srcache-fetch-status',
    ]);

    $pageCacheEnabled = ! empty($detectedHeaders) || (defined('WP_CACHE') && WP_CACHE);

    if ($pageCacheEnabled) {
        return [
            'label' => __('Caching headers detected on the homepage', 'pixelforge'),
            'status' => 'good',
            'badge' => [
                'label' => __('Performance', 'pixelforge'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('Responses from the homepage include cache headers, which should improve load times for visitors.', 'pixelforge')
            ),
            'test' => 'pixelforge_page_cache',
        ];
    }

    return [
        'label' => __('Page cache headers were not detected', 'pixelforge'),
        'status' => 'recommended',
        'badge' => [
            'label' => __('Performance', 'pixelforge'),
            'color' => 'orange',
        ],
        'description' => sprintf(
            '<p>%s</p>',
            __('Install or configure a page caching plugin so responses include cache headers like Cache-Control or Expires.', 'pixelforge')
        ),
        'actions' => sprintf(
            '<p><a href="%s" target="_blank" rel="noreferrer noopener">%s</a></p>',
            esc_url('https://wordpress.org/documentation/article/optimization/#page-cache'),
            __('Read more about enabling page caching', 'pixelforge')
        ),
        'test' => 'pixelforge_page_cache',
    ];
}
