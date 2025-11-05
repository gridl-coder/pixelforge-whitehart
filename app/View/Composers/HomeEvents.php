<?php

namespace PixelForge\View\Composers;

use DateTimeImmutable;
use DateTimeInterface;
use Roots\Acorn\View\Composer;
use WP_Post;
use function current_time;
use function esc_url_raw;
use function filter_var;
use function get_option;
use function get_permalink;
use function get_post_meta;
use function get_post_thumbnail_id;
use function get_post_type_archive_link;
use function get_posts;
use function get_the_post_thumbnail_url;
use function get_the_title;
use function home_url;
use function wp_get_attachment_image_url;
use function wp_timezone;
use PixelForge\PostTypes\Events;

class HomeEvents extends Composer
{
    protected static $views = [
        'front-page.home-events',
    ];

    public function with(): array
    {
        $events = $this->upcomingEvents(4);
        $highlight = array_shift($events);

        return [
            'highlightEvent' => $highlight,
            'upcomingEvents' => $events,
            'eventsArchiveUrl' => get_post_type_archive_link(Events::KEY) ?: home_url('/' . Events::SLUG . '/'),
        ];
    }

    private function upcomingEvents(int $limit): array
    {
        $query = get_posts([
            'post_type' => Events::KEY,
            'posts_per_page' => $limit,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_key' => 'events_date',
            'meta_type' => 'NUMERIC',
            'meta_query' => [[
                'key' => 'events_date',
                'value' => current_time('timestamp'),
                'compare' => '>=',
                'type' => 'NUMERIC',
            ]],
            'no_found_rows' => true,
            'suppress_filters' => false,
        ]);

        return array_map([$this, 'mapEvent'], $query);
    }

    private function mapEvent(WP_Post $post): array
    {
        $timestamp = (int) get_post_meta($post->ID, 'events_date', true);
        $date = $timestamp > 0 ? (new DateTimeImmutable('@' . $timestamp))->setTimezone(wp_timezone()) : null;
        $externalUrl = $this->sanitizeUrl(get_post_meta($post->ID, 'events_url', true));
        $imageUrl = $this->resolveImageUrl($post->ID);

        return [
            'id' => $post->ID,
            'title' => get_the_title($post),
            'permalink' => get_permalink($post),
            'externalUrl' => $externalUrl,
            'date' => $date,
            'formattedDate' => $date instanceof DateTimeInterface ? $date->format(get_option('date_format')) : null,
            'image' => $imageUrl,
        ];
    }

    private function resolveImageUrl(int $postId): ?array
    {
        $thumbId = get_post_thumbnail_id($postId);
        $url = $thumbId ? wp_get_attachment_image_url($thumbId, 'large') : get_the_post_thumbnail_url($postId, 'large');

        if (! $url) {
            return null;
        }

        return [
            'url' => $url,
            'alt' => get_the_title($postId),
        ];
    }

    private function sanitizeUrl($value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $url = esc_url_raw($value);

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
