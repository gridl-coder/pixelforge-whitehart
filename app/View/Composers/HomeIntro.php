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
use function get_the_title;
use function is_front_page;
use function sprintf;
use function wp_get_attachment_image_url;

class HomeIntro extends Composer
{
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
                'features' => $this->features(),
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
                return [
                    'url' => $url,
                    'alt' => $alt,
                ];
            }
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, 'full');

            if ($url) {
                return [
                    'url' => $url,
                    'alt' => $alt,
                ];
            }
        }

        if (is_string($value) && $value !== '') {
            return [
                'url' => $value,
                'alt' => $alt,
            ];
        }

        return null;
    }

    private function features(): array
    {
        return [
            ['icon' => 'fa-solid fa-guitars', 'label' => __('Live Music', 'pixelforge')],
            ['icon' => 'fa-solid fa-tv-retro', 'label' => __('Live Sports', 'pixelforge')],
            ['icon' => 'fa-solid fa-pool-8-ball', 'label' => __('Pool Table', 'pixelforge')],
            ['icon' => 'fa-solid fa-bullseye-arrow', 'label' => __('Dart Board', 'pixelforge')],
            ['icon' => 'fa-solid fa-family', 'label' => __('Family Friendly', 'pixelforge')],
            ['icon' => 'fa-solid fa-dog-leashed', 'label' => __('Dog Friendly', 'pixelforge')],
            ['icon' => 'fa-solid fa-square-parking', 'label' => __('Parking', 'pixelforge')],
            ['icon' => 'fa-solid fa-burger-soda', 'label' => __('Pub Food', 'pixelforge')],
            ['icon' => 'fa-solid fa-sun-cloud', 'label' => __('Beer Garden', 'pixelforge')],
        ];
    }
}
