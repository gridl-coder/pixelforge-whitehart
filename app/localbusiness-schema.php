<?php

/**
 * Structured data output for the White Hart Bodmin website.
 */

namespace PixelForge;

use function apply_filters;
use function get_bloginfo;
use function get_home_url;
use function PixelForge\CMB2\get_theme_option;
use function wp_json_encode;

// Hook into wp_head early so the JSON-LD appears near other meta tags.
add_action('wp_head', __NAMESPACE__ . '\output_localbusiness_schema', 2);

/**
 * Output LocalBusiness structured data in JSON-LD format.
 */
function output_localbusiness_schema(): void
{
    // Only run on the front end.
    if (is_admin()) {
        return;
    }

    $logo_id = get_theme_option('pf_logo', '');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BarOrPub',
        '@id' => esc_url(get_home_url(null, '/bodmin')),
        'name' => get_bloginfo('name', 'display'),
        'url' => esc_url(get_home_url(null, '/bodmin')),
        'telephone' => get_theme_option('pf_phone_number', '07922 214361'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => get_theme_option('pf_street_address', 'Pool Street'),
            'addressLocality' => get_theme_option('pf_address_locality', 'Bodmin'),
            'postalCode' => get_theme_option('pf_postal_code', 'PL31 2HA'),
            'addressCountry' => get_theme_option('pf_address_country', 'GB'),
        ],
        'image' => [
            'https://theh.art/bodmin/wp-content/uploads/sites/2/2025/12/bar-view-1.webp',
            'https://theh.art/bodmin/wp-content/uploads/sites/2/2025/12/function-room-1.webp',
            'https://theh.art/bodmin/wp-content/uploads/sites/2/2025/12/event-6.webp'
        ],
        'priceRange' => '££',
        'servesCuisine' => ['Pub Food', 'British'],
    ];

    if ($logo_url) {
        $schema['image'] = esc_url($logo_url);
    }

    /**
     * Filter the LocalBusiness schema data.
     */
    $schema = apply_filters('pixelforge_localbusiness_schema', $schema);

    if (empty($schema) || !is_array($schema)) {
        return;
    }

    $json = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (!$json) {
        return;
    }

    echo "\n    <!-- LocalBusiness schema -->\n    <script type=\"application/ld+json\">{$json}</script>\n";
}
