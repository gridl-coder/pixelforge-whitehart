<?php

/**
 * Structured data output for the White Hart Bodmin website.
 *
 * This module registers a hook on `wp_head` that prints a JSON‑LD
 * representation of the business using the Schema.org `LocalBusiness`
 * vocabulary. Search engines use this data to understand the business
 * and to enable rich results for local establishments. Including
 * structured data can improve SEO by explicitly communicating the
 * address and contact details of your pub to crawlers. For more
 * guidance on required and recommended fields see Google’s Local
 * Business structured data documentation【725761757124204†L855-L904】.
 *
 * The values in this script are intentionally conservative to avoid
 * misrepresenting your business. You should review and update the
 * fields (such as telephone and priceRange) to match the latest
 * information for your pub. If you have multiple locations or more
 * detailed opening hours, consider generating this data dynamically
 * using theme options or custom fields.
 */

namespace PixelForge;

use function apply_filters;
use function esc_attr;
use function esc_js;
use function esc_url;
use function get_bloginfo;
use function get_home_url;
use function wp_json_encode;

// Hook into wp_head early so the JSON‑LD appears near other meta tags.
add_action('wp_head', __NAMESPACE__ . '\output_localbusiness_schema', 2);

/**
 * Output LocalBusiness structured data in JSON‑LD format.
 *
 * This function assembles a minimal LocalBusiness object and prints
 * it inside a `<script type="application/ld+json">` tag. If you
 * wish to customise the values, you can filter the array via the
 * `pixelforge_localbusiness_schema` filter. Returning an empty array
 * will suppress output entirely.
 */
function output_localbusiness_schema(): void
{
    // Only run on the front end.
    if (is_admin()) {
        return;
    }

    // Build the base schema array. Use `@context` and `@type` keys as
    // required by the JSON‑LD specification.
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'LocalBusiness',
        '@id'      => esc_url(get_home_url(null, '/bodmin')),
        'name'     => get_bloginfo('name', 'display'),
        'url'      => esc_url(get_home_url(null, '/bodmin')),
        // Provide a telephone number for direct contact. Update this
        // value to reflect the pub’s current phone number.
        'telephone' => '07922 214361',
        // Street‑level address details. Adjust these fields to match
        // the official postal address of the White Hart Bodmin.
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'Pool Street',
            'addressLocality' => 'Bodmin',
            'postalCode'      => 'PL31 2HA',
            'addressCountry'  => 'GB',
        ],
        // An indicative price range expressed in pound signs. Update
        // this to reflect your actual pricing tier (e.g. £, ££).
        'priceRange' => '££',
        // The cuisine served by the business. You may list multiple
        // values for more specific cuisines.
        'servesCuisine' => ['Pub Food', 'British'],
    ];

    /**
     * Filter the LocalBusiness schema data.
     *
     * Developers can override or extend the schema by hooking into
     * this filter. Returning an empty array will prevent any schema
     * output.
     *
     * @param array $schema The schema.org data.
     */
    $schema = apply_filters('pixelforge_localbusiness_schema', $schema);

    if (empty($schema) || ! is_array($schema)) {
        return;
    }

    // Encode the PHP array into JSON while preserving slashes and
    // avoiding escaping of Unicode characters. Escaping ensures that
    // the script is safe to output in an HTML context.
    $json = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (! $json) {
        return;
    }

    echo "\n    <!-- LocalBusiness schema -->\n    <script type=\"application/ld+json\">" . esc_js($json) . "</script>\n";
}
