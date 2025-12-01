<?php

namespace PixelForge\CMB2;

use PixelForge\PostTypes\BookingMenu;
use PixelForge\PostTypes\BookingSection;
use PixelForge\PostTypes\BookingTable;
use PixelForge\PostTypes\TableBooking;

add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_booking_metaboxes');

function register_booking_metaboxes(): void
{
    if (! function_exists('new_cmb2_box')) {
        return;
    }

    $sectionBox = new_cmb2_box([
        'id' => 'booking_section_metabox',
        'title' => esc_html__('Booking Section Details', 'pixelforge'),
        'object_types' => [BookingSection::KEY],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    $sectionBox->add_field([
        'name' => esc_html__('Notes', 'pixelforge'),
        'desc' => esc_html__('Internal notes to describe the section (e.g., accessibility or ambience details).', 'pixelforge'),
        'id' => 'booking_section_notes',
        'type' => 'textarea_small',
    ]);

    $tableBox = new_cmb2_box([
        'id' => 'booking_table_metabox',
        'title' => esc_html__('Table Details', 'pixelforge'),
        'object_types' => [BookingTable::KEY],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    $tableBox->add_field([
        'name' => esc_html__('Section', 'pixelforge'),
        'desc' => esc_html__('Select the section this table belongs to.', 'pixelforge'),
        'id' => 'booking_table_section',
        'type' => 'select',
        'options_cb' => __NAMESPACE__ . '\\get_section_options',
    ]);

    $tableBox->add_field([
        'name' => esc_html__('Seats', 'pixelforge'),
        'desc' => esc_html__('Number of seats available at this table.', 'pixelforge'),
        'id' => 'booking_table_seats',
        'type' => 'text_small',
        'attributes' => [
            'type' => 'number',
            'min' => 1,
            'step' => 1,
            'pattern' => '\\d*',
        ],
        'sanitization_cb' => 'absint',
        'escape_cb' => 'absint',
    ]);

    $tableBox->add_field([
        'name' => esc_html__('Table Notes', 'pixelforge'),
        'desc' => esc_html__('Optional notes to help staff match guests to the right spot.', 'pixelforge'),
        'id' => 'booking_table_notes',
        'type' => 'textarea_small',
    ]);

    $menuBox = new_cmb2_box([
        'id' => 'booking_menu_metabox',
        'title' => esc_html__('Menu Availability', 'pixelforge'),
        'object_types' => [BookingMenu::KEY],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    $menuBox->add_field([
        'name' => esc_html__('Start Time', 'pixelforge'),
        'desc' => esc_html__('First bookable time for this menu (24-hour format).', 'pixelforge'),
        'id' => 'booking_menu_start_time',
        'type' => 'text_time',
        'time_format' => 'H:i',
    ]);

    $menuBox->add_field([
        'name' => esc_html__('End Time', 'pixelforge'),
        'desc' => esc_html__('Last time bookings can start for this menu (24-hour format).', 'pixelforge'),
        'id' => 'booking_menu_end_time',
        'type' => 'text_time',
        'time_format' => 'H:i',
    ]);

    $menuBox->add_field([
        'name' => esc_html__('Available Days', 'pixelforge'),
        'desc' => esc_html__('Choose which days this menu can be booked. Leave empty to allow every day.', 'pixelforge'),
        'id' => 'booking_menu_days',
        'type' => 'multicheck',
        'options' => get_day_options(),
    ]);

    $bookingBox = new_cmb2_box([
        'id' => 'table_booking_metabox',
        'title' => esc_html__('Booking Details', 'pixelforge'),
        'object_types' => [TableBooking::KEY],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Customer Name', 'pixelforge'),
        'id' => 'table_booking_customer_name',
        'type' => 'text',
        'attributes' => ['readonly' => 'readonly'],
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Customer Email', 'pixelforge'),
        'id' => 'table_booking_customer_email',
        'type' => 'text_email',
        'attributes' => ['readonly' => 'readonly'],
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Customer Phone', 'pixelforge'),
        'id' => 'table_booking_customer_phone',
        'type' => 'text_medium',
        'attributes' => ['readonly' => 'readonly'],
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Party Size', 'pixelforge'),
        'id' => 'table_booking_party_size',
        'type' => 'text_small',
        'attributes' => ['readonly' => 'readonly'],
        'escape_cb' => 'absint',
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Menu', 'pixelforge'),
        'id' => 'table_booking_menu_id',
        'type' => 'select',
        'options_cb' => __NAMESPACE__ . '\\get_menu_options',
        'attributes' => ['disabled' => 'disabled'],
        'escape_cb' => __NAMESPACE__ . '\\format_post_label',
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Section', 'pixelforge'),
        'id' => 'table_booking_section_id',
        'type' => 'select',
        'options_cb' => __NAMESPACE__ . '\\get_section_options',
        'attributes' => ['disabled' => 'disabled'],
        'escape_cb' => __NAMESPACE__ . '\\format_post_label',
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Table', 'pixelforge'),
        'id' => 'table_booking_table_id',
        'type' => 'select',
        'options_cb' => __NAMESPACE__ . '\\get_table_options',
        'attributes' => ['disabled' => 'disabled'],
        'escape_cb' => __NAMESPACE__ . '\\format_post_label',
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Booking Time', 'pixelforge'),
        'id' => 'table_booking_datetime',
        'type' => 'text',
        'attributes' => ['readonly' => 'readonly'],
        'escape_cb' => __NAMESPACE__ . '\\format_booking_datetime',
    ]);

    $bookingBox->add_field([
        'name' => esc_html__('Customer Notes', 'pixelforge'),
        'id' => 'table_booking_notes',
        'type' => 'textarea_small',
        'attributes' => ['readonly' => 'readonly'],
    ]);
}

function get_section_options(): array
{
    $sections = get_posts([
        'post_type' => BookingSection::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $options = [];

    foreach ($sections as $section) {
        $options[$section->ID] = $section->post_title;
    }

    return $options;
}

function get_table_options(): array
{
    $tables = get_posts([
        'post_type' => BookingTable::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $options = [];

    foreach ($tables as $table) {
        $options[$table->ID] = $table->post_title;
    }

    return $options;
}

function get_menu_options(): array
{
    $menus = get_posts([
        'post_type' => BookingMenu::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $options = [];

    foreach ($menus as $menu) {
        $options[$menu->ID] = $menu->post_title;
    }

    return $options;
}

function get_day_options(): array
{
    return [
        'monday' => esc_html__('Monday', 'pixelforge'),
        'tuesday' => esc_html__('Tuesday', 'pixelforge'),
        'wednesday' => esc_html__('Wednesday', 'pixelforge'),
        'thursday' => esc_html__('Thursday', 'pixelforge'),
        'friday' => esc_html__('Friday', 'pixelforge'),
        'saturday' => esc_html__('Saturday', 'pixelforge'),
        'sunday' => esc_html__('Sunday', 'pixelforge'),
    ];
}

function format_post_label($value): string
{
    $id = absint($value);

    if ($id === 0) {
        return '';
    }

    $title = get_the_title($id);

    if ($title) {
        return $title;
    }

    return (string) $value;
}

function format_booking_datetime($value): string
{
    $timestamp = absint($value);

    if ($timestamp === 0) {
        return '';
    }

    $format = sprintf('%s %s', get_option('date_format'), get_option('time_format'));

    return wp_date($format, $timestamp, wp_timezone());
}
