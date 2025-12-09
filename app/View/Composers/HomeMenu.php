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

class HomeMenu extends Composer
{
    private const DEFAULT_ALT = 'White Hart food banner image';

    protected static $views = [
        'front-page.home-menu',
    ];

    public function with(): array
    {
        $postId = (int) get_the_ID();

        return [
            'guestPopup' => $this->imageField($postId, 'home_guestpopup_image', __('Guest food events at The White Hart', 'pixelforge')),
            'alesImage' => [
                'url' => esc_url_raw(get_theme_file_uri('resources/images/new-ales.jpg')),
                'alt' => __('New ales available at The White Hart', 'pixelforge'),
            ],
            'serviceTimes' => $this->serviceTimes(),
            'foodBannerSlider' => $this->foodBannerSlider($postId),
        ];
    }

    private function foodBannerSlider(int $postId): array
    {
        if ($postId) {
            $rawSlides = maybe_unserialize(get_post_meta($postId, 'home_food_banner_images', true));

            if (is_array($rawSlides)) {
                $slides = array_filter(array_map(fn ($slide) => $this->normalizeSlide($slide), $rawSlides));

                if (! empty($slides)) {
                    return array_values($slides);
                }
            }
        }

        return [];
    }

    private function normalizeSlide($value): ?array
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

    private function imageField(int $postId, string $metaKey, string $defaultAlt): ?array
    {
        if (! $postId) {
            return null;
        }

        $value = get_post_meta($postId, $metaKey, true);
        $url = $this->normalizeImage($value);

        if (! $url) {
            return null;
        }

        return [
            'url' => $url,
            'alt' => $defaultAlt,
        ];
    }

    private function normalizeImage($value): ?string
    {
        if (is_array($value)) {
            $value = $value['url'] ?? $value['menu_image'] ?? $value['image'] ?? $value['ID'] ?? $value['id'] ?? null;
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, 'large');

            if ($url) {
                return $url;
            }
        }

        if (is_string($value)) {
            $url = esc_url_raw($value);

            return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
        }

        return null;
    }

    private function serviceTimes(): array
    {
        return [
            [
                'label' => __('White Hart Breakfast', 'pixelforge'),
                'hours' => __('Mon / Tue / Wed / Thur: 09:00 - 13:00', 'pixelforge'),
            ],
            [
                'label' => __('GRIDL Breakfast Takeover', 'pixelforge'),
                'hours' => __('Fri / Sat: 09:00 - 14:00', 'pixelforge'),
            ],
            [
                'label' => __('Sunday Lunch', 'pixelforge'),
                'hours' => __('Sun: 12:00 - 15:00', 'pixelforge'),
            ],
            [
                'label' => __('Guest Food Evening', 'pixelforge'),
                'hours' => __('Tues: 17:00 - 20:00', 'pixelforge'),
            ],
        ];
    }
}
