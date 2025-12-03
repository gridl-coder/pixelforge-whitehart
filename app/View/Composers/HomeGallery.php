<?php

namespace PixelForge\View\Composers;

use Roots\Acorn\View\Composer;
use function __;
use function esc_url_raw;
use function filter_var;
use function get_post_meta;
use function get_the_ID;
use function get_theme_file_uri;
use function maybe_unserialize;
use function sanitize_text_field;
use function wp_get_attachment_image_url;

class HomeGallery extends Composer
{
    private const DEFAULT_ALT = 'White Hart gallery image';

    protected static $views = [
        'front-page.home-gallery',
    ];

    public function with(): array
    {
        $postId = (int) get_the_ID();

        return [
            'homeGallery' => [
                'subtitle' => __('A glimpse around the Hart', 'pixelforge'),
                'title' => __('Gallery', 'pixelforge'),
                'images' => $this->galleryImages($postId),
            ],
        ];
    }

    private function galleryImages(int $postId): array
    {
        if ($postId) {
            $rawImages = maybe_unserialize(get_post_meta($postId, 'home_gallery_images', true));

            if (is_array($rawImages)) {
                $images = array_filter(array_map(fn ($image) => $this->normalizeImage($image), $rawImages));

                if (! empty($images)) {
                    return array_values($images);
                }
            }
        }

        return $this->fallbackImages();
    }

    private function normalizeImage($value): ?array
    {
        $defaultAlt = __(self::DEFAULT_ALT, 'pixelforge');

        if (is_array($value)) {
            $url = $value['url'] ?? $value['image'] ?? null;
            $attachmentId = $value['id'] ?? $value['ID'] ?? null;
            $alt = isset($value['alt']) ? sanitize_text_field($value['alt']) : ($value['title'] ?? $defaultAlt);

            if (! $url && $attachmentId) {
                $url = wp_get_attachment_image_url((int) $attachmentId, 'large');
            }

            if ($url) {
                return [
                    'url' => esc_url_raw($url),
                    'alt' => $alt ?: $defaultAlt,
                    'caption' => isset($value['caption']) ? sanitize_text_field($value['caption']) : '',
                ];
            }
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, 'large');

            if ($url) {
                return [
                    'url' => esc_url_raw($url),
                    'alt' => $defaultAlt,
                    'caption' => '',
                ];
            }
        }

        if (is_string($value) && $value !== '') {
            $url = esc_url_raw($value);

            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return [
                    'url' => $url,
                    'alt' => $defaultAlt,
                    'caption' => '',
                ];
            }
        }

        return null;
    }

    private function fallbackImages(): array
    {
        $defaults = [
            [
                'path' => 'resources/images/hart-main-1.png',
                'alt' => __('Exterior of The White Hart', 'pixelforge'),
            ],
            [
                'path' => 'resources/images/hart--lamb-shank.png',
                'alt' => __('Lamb shank plated meal', 'pixelforge'),
            ],
            [
                'path' => 'resources/images/large-breakfast.jpg',
                'alt' => __('Large breakfast plate', 'pixelforge'),
            ],
            [
                'path' => 'resources/images/events/1-11-25-saturday-night-party.jpg',
                'alt' => __('Saturday night party poster', 'pixelforge'),
            ],
            [
                'path' => 'resources/images/events/26-10-matt-bowen.jpg',
                'alt' => __('Matt Bowen event poster', 'pixelforge'),
            ],
            [
                'path' => 'resources/images/events/31-10-halloween.jpg',
                'alt' => __('Halloween event poster', 'pixelforge'),
            ],
        ];

        return array_map(function (array $image): array {
            return [
                'url' => esc_url_raw(get_theme_file_uri($image['path'])),
                'alt' => $image['alt'],
                'caption' => '',
            ];
        }, $defaults);
    }
}
