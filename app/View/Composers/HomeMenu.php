<?php

namespace PixelForge\View\Composers;

use Roots\Acorn\View\Composer;
use function __;
use function esc_url_raw;
use function filter_var;
use function get_post_meta;
use function get_the_ID;
use function get_theme_file_uri;
use function wp_get_attachment_image_url;

class HomeMenu extends Composer
{
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
        ];
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
