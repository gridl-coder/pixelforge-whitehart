<?php

namespace PixelForge\CMB2;

use PixelForge\PostTypes\Events;
use function get_option;

add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_events_metabox');

function register_events_metabox(): void
{
    if (! function_exists('new_cmb2_box')) {
        return;
    }

    $eventsBox = new_cmb2_box([
        'id' => 'events_register_metabox',
        'title' => esc_html__('Event Details', 'pixelforge'),
        'object_types' => [Events::KEY],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    $eventsBox->add_field([
        'name' => esc_html__('Event Date', 'pixelforge'),
        'desc' => esc_html__('Choose the date the event takes place.', 'pixelforge'),
        'id' => 'events_date',
        'type' => 'text_date_timestamp',
        'date_format' => 'Y-m-d',
        'sanitization_cb' => static function ($timestamp) {
            return $timestamp ? absint($timestamp) : null;
        },
    ]);

    $eventsBox->add_field([
        'name' => esc_html__('Event Link', 'pixelforge'),
        'desc' => esc_html__('Optional URL for more information or ticket sales.', 'pixelforge'),
        'id' => 'events_url',
        'type' => 'text_url',
        'sanitization_cb' => 'esc_url_raw',
        'escape_cb' => 'esc_url',
    ]);
}
