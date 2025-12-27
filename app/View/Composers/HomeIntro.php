<?php

namespace PixelForge\View\Composers;

use Roots\Acorn\View\Composer;
use function __;
use function apply_filters;
use function get_bloginfo;
use function get_option;
use function get_post_field;
use function get_post_meta;
use function get_queried_object_id;
use function get_theme_file_uri;
use function get_the_title;
use function is_front_page;
use function sprintf;
use function wp_get_attachment_image_url;
use function esc_url;
use function wp_get_attachment_metadata;
use function wp_get_attachment_image_srcset;
use function wp_get_attachment_image_sizes;

/**
 * View composer for the home introduction section.
 *
 * This class builds the data array used by the front‑page.home‑intro
 * Blade template. In addition to returning the hero image URL and
 * alternate text, it now also attempts to provide width and height
 * attributes for the image. Providing explicit dimensions helps the
 * browser reserve the correct amount of space while the image loads,
 * reducing cumulative layout shift (CLS) as recommended by the
 * PageSpeed Insights report.
 */
class HomeIntro extends Composer
{
    private const FEATURES = [
        ['icon' => 'guitars', 'label' => 'Live Music'],
        ['icon' => 'tv-retro', 'label' => 'Live Sports'],
        ['icon' => 'game-board', 'label' => 'Pool Table'],
        ['icon' => 'bullseye-arrow', 'label' => 'Dart Board'],
        ['icon' => 'family', 'label' => 'Family Friendly'],
        ['icon' => 'dog-leashed', 'label' => 'Dog Friendly'],
        ['icon' => 'square-parking', 'label' => 'Parking'],
        ['icon' => 'burger-soda', 'label' => 'Pub Food'],
        ['icon' => 'sun-cloud', 'label' => 'Beer Garden'],
    ];

    protected static $views = [
        'front-page.home-intro',
    ];

    public function with(): array
    {
        $postId = $this->resolveFrontPageId();

        $location = get_post_meta($postId, 'home_location', true);
        $headerImage = $this->resolveMediaUrl(get_post_meta($postId, 'home_header_image', true));
        $content = $postId ? apply_filters('the_content', get_post_field('post_content', $postId)) : '';

        return [
            'homeIntro' => [
                'title' => $postId ? get_the_title($postId) : get_bloginfo('name'),
                'location' => is_string($location) ? trim($location) : '',
                'headerImage' => $headerImage,
                'content' => $content,
                'features' => $this->features($postId),
            ],
        ];
    }

    private function resolveFrontPageId(): int
    {
        if (is_front_page()) {
            return get_queried_object_id() ?: (int) get_option('page_on_front');
        }

        return (int) get_option('page_on_front');
    }

    /**
     * Resolve a WordPress media field into a structured array containing the
     * URL, alt text and (where possible) the image’s intrinsic dimensions.
     *
     * @param mixed $value A WP media field value (array, numeric ID or URL).
     * @return array|null An associative array with keys: url, alt, width, height, srcset, sizes.
     */
    private function resolveMediaUrl($value): ?array
    {
        $alt = sprintf(__('%s hero image', 'pixelforge'), get_bloginfo('name'));

        if (is_array($value)) {
            $attachmentId = $value['ID'] ?? $value['id'] ?? null;
            $url = $value['url'] ?? null;

            if (! $url && $attachmentId) {
                $url = wp_get_attachment_image_url((int) $attachmentId, 'full');
            }

            if ($url) {
                // Attempt to extract width and height. Prefer attachment metadata
                // for performance, falling back to getimagesize if necessary.
                $width = null;
                $height = null;
                $srcset = null;
                $sizesAttr = null;
                if ($attachmentId) {
                    $metadata = wp_get_attachment_metadata((int) $attachmentId);
                    if (is_array($metadata) && ! empty($metadata['width']) && ! empty($metadata['height'])) {
                        $width = (int) $metadata['width'];
                        $height = (int) $metadata['height'];
                    }
                    // Build responsive image attributes for modern browsers.
                    $srcset = wp_get_attachment_image_srcset((int) $attachmentId, 'full') ?: null;
                    $sizesAttr = wp_get_attachment_image_sizes((int) $attachmentId, 'full') ?: null;
                }
                // Fallback: try to read dimensions directly from the image URL if we still lack dimensions.
                if ($url && ($width === null || $height === null)) {
                    $size = @getimagesize($url);
                    if (is_array($size)) {
                        $width = $size[0] ?? $width;
                        $height = $size[1] ?? $height;
                    }
                }
                return [
                    'url' => $url,
                    'alt' => $alt,
                    'width' => $width,
                    'height' => $height,
                    'srcset' => $srcset,
                    'sizes' => $sizesAttr,
                ];
            }
        }

        if (is_numeric($value)) {
            $attachmentId = (int) $value;
            $url = wp_get_attachment_image_url($attachmentId, 'full');

            if ($url) {
                $width = null;
                $height = null;
                $srcset = null;
                $sizesAttr = null;
                $metadata = wp_get_attachment_metadata($attachmentId);
                if (is_array($metadata) && ! empty($metadata['width']) && ! empty($metadata['height'])) {
                    $width = (int) $metadata['width'];
                    $height = (int) $metadata['height'];
                }
                // Build responsive image attributes.
                $srcset = wp_get_attachment_image_srcset($attachmentId, 'full') ?: null;
                $sizesAttr = wp_get_attachment_image_sizes($attachmentId, 'full') ?: null;
                if ($url && ($width === null || $height === null)) {
                    $size = @getimagesize($url);
                    if (is_array($size)) {
                        $width = $size[0] ?? $width;
                        $height = $size[1] ?? $height;
                    }
                }
                return [
                    'url' => $url,
                    'alt' => $alt,
                    'width' => $width,
                    'height' => $height,
                    'srcset' => $srcset,
                    'sizes' => $sizesAttr,
                ];
            }
        }

        if (is_string($value) && $value !== '') {
            // For custom URLs, attempt to derive the image size to provide
            // explicit dimensions and reduce layout shift. We cannot generate
            // responsive srcset attributes for arbitrary URLs.
            $width = null;
            $height = null;
            $size = @getimagesize($value);
            if (is_array($size)) {
                $width = $size[0] ?? null;
                $height = $size[1] ?? null;
            }
            return [
                'url' => $value,
                'alt' => $alt,
                'width' => $width,
                'height' => $height,
                'srcset' => null,
                'sizes' => null,
            ];
        }

        return null;
    }

    private function features(int $postId): array
    {
        $amenities = get_post_meta($postId, 'home_amenities', true);

        if (is_array($amenities) && !empty($amenities)) {
            return array_map(function ($amenity) {
                $icon = $amenity['icon'] ?? '';
                $label = $amenity['title'] ?? '';
                $description = $amenity['description'] ?? '';
                $image1 = $this->resolveMediaUrl($amenity['image_1_id'] ?? $amenity['image_1'] ?? null);
                $image2 = $this->resolveMediaUrl($amenity['image_2_id'] ?? $amenity['image_2'] ?? null);

                return [
                    'icon' => $icon,
                    'label' => $label,
                    'path' => esc_url(get_theme_file_uri("resources/icons/{$icon}.svg")),
                    'description' => $description,
                    'image1' => $image1,
                    'image2' => $image2,
                ];
            }, $amenities);
        }

        return array_map(function (array $feature): array {
            $icon = $feature['icon'];

            return [
                'icon' => $icon,
                'label' => __($feature['label'], 'pixelforge'),
                'path' => esc_url(get_theme_file_uri("resources/icons/{$icon}.svg")),
                'description' => '',
                'image1' => null,
                'image2' => null,
            ];
        }, self::FEATURES);
    }
}
