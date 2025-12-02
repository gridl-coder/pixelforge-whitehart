<?php

namespace PixelForge\BookingAdmin;

use DateTimeImmutable;
use PixelForge\PostTypes\BookingMenu;
use PixelForge\PostTypes\BookingSection;
use PixelForge\PostTypes\BookingTable;
use PixelForge\PostTypes\TableBooking;
use WP_Post;
use WP_User;
use function PixelForge\Bookings\get_booking_details;
use function PixelForge\Bookings\get_table_labels;
use function PixelForge\Bookings\normalize_table_ids;

const LOGIN_NONCE_ACTION = 'pixelforge_booking_admin_login';
const BOOKING_NONCE_ACTION = 'pixelforge_booking_admin_manage';

add_action('admin_post_nopriv_pixelforge_booking_admin_login', __NAMESPACE__ . '\\handle_login');
add_action('admin_post_pixelforge_booking_admin_create', __NAMESPACE__ . '\\handle_create');
add_action('admin_post_pixelforge_booking_admin_update', __NAMESPACE__ . '\\handle_update');
add_action('admin_post_pixelforge_booking_admin_delete', __NAMESPACE__ . '\\handle_delete');
add_action('admin_init', __NAMESPACE__ . '\\restrict_staff_admin_access');
add_filter('login_redirect', __NAMESPACE__ . '\\redirect_staff_login', 10, 3);

function handle_login(): void
{
    check_admin_referer(LOGIN_NONCE_ACTION);

    $redirect = get_redirect_target($_POST['redirect_to'] ?? home_url('/'));
    $username = sanitize_user(wp_unslash($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $user = wp_signon([
        'user_login' => $username,
        'user_password' => $password,
        'remember' => true,
    ], false);

    if (is_wp_error($user)) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode($user->get_error_message()), $redirect));
        exit;
    }

    wp_safe_redirect(remove_query_arg('booking_admin_error', $redirect));
    exit;
}

function handle_create(): void
{
    if (!current_user_can('edit_posts')) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('You do not have permission to manage bookings.', 'pixelforge')), home_url('/')));
        exit;
    }

    check_admin_referer(BOOKING_NONCE_ACTION);

    $redirect = get_redirect_target($_POST['redirect_to'] ?? home_url('/'));
    $data = collect_booking_form_data($_POST);
    $errors = validate_booking_data($data);

    if ($errors !== []) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(implode(' ', $errors)), $redirect));
        exit;
    }

    $timestamp = $data['timestamp'];
    $title = sprintf(
        __('Booking for %1$s on %2$s', 'pixelforge'),
        $data['name'],
        wp_date('M j, Y H:i', $timestamp, wp_timezone())
    );

    $bookingId = wp_insert_post([
        'post_type' => TableBooking::KEY,
        'post_status' => 'publish',
        'post_title' => $title,
    ]);

    if (is_wp_error($bookingId) || !$bookingId) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('Unable to save this booking right now.', 'pixelforge')), $redirect));
        exit;
    }

    persist_booking_meta($bookingId, $data);
    update_post_meta($bookingId, 'table_booking_verified', 1);
    delete_post_meta($bookingId, 'table_booking_verification_token');

    wp_safe_redirect(add_query_arg('booking_admin_notice', 'created', $redirect));
    exit;
}

function handle_update(): void
{
    if (!current_user_can('edit_posts')) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('You do not have permission to manage bookings.', 'pixelforge')), home_url('/')));
        exit;
    }

    check_admin_referer(BOOKING_NONCE_ACTION);

    $redirect = get_redirect_target($_POST['redirect_to'] ?? home_url('/'));
    $bookingId = absint($_POST['booking_id'] ?? 0);

    if ($bookingId === 0 || !current_user_can('edit_post', $bookingId)) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('We could not find that booking.', 'pixelforge')), $redirect));
        exit;
    }

    $data = collect_booking_form_data($_POST);
    $errors = validate_booking_data($data);

    if ($errors !== []) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(implode(' ', $errors)), $redirect));
        exit;
    }

    $title = sprintf(
        __('Booking for %1$s on %2$s', 'pixelforge'),
        $data['name'],
        wp_date('M j, Y H:i', $data['timestamp'], wp_timezone())
    );

    wp_update_post([
        'ID' => $bookingId,
        'post_title' => $title,
    ]);

    persist_booking_meta($bookingId, $data);

    wp_safe_redirect(add_query_arg('booking_admin_notice', 'updated', $redirect));
    exit;
}

function handle_delete(): void
{
    if (!current_user_can('delete_posts')) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('You do not have permission to delete bookings.', 'pixelforge')), home_url('/')));
        exit;
    }

    check_admin_referer(BOOKING_NONCE_ACTION);

    $redirect = get_redirect_target($_POST['redirect_to'] ?? home_url('/'));
    $bookingId = absint($_POST['booking_id'] ?? 0);

    if ($bookingId === 0 || !current_user_can('delete_post', $bookingId)) {
        wp_safe_redirect(add_query_arg('booking_admin_error', rawurlencode(__('We could not find that booking.', 'pixelforge')), $redirect));
        exit;
    }

    wp_trash_post($bookingId);

    wp_safe_redirect(add_query_arg('booking_admin_notice', 'deleted', $redirect));
    exit;
}

function restrict_staff_admin_access(): void
{
    if (!is_user_logged_in() || !is_admin()) {
        return;
    }

    $user = wp_get_current_user();

    if (!is_table_booking_staff($user)) {
        return;
    }

    if (wp_doing_ajax()) {
        return;
    }

    $script = isset($_SERVER['PHP_SELF']) ? basename((string)$_SERVER['PHP_SELF']) : '';

    if ($script === 'admin-post.php') {
        return;
    }

    wp_safe_redirect(get_booking_admin_page_url());
    exit;
}

function redirect_staff_login($redirectTo, $requestedRedirect, $user)
{
    if ($user instanceof WP_User && is_table_booking_staff($user)) {
        return get_booking_admin_page_url();
    }

    return $redirectTo;
}

function collect_booking_form_data(array $source): array
{
    $tableIds = isset($source['table_ids']) ? normalize_table_ids($source['table_ids']) : [];
    $date = sanitize_text_field(wp_unslash($source['date'] ?? ''));
    $time = sanitize_text_field(wp_unslash($source['time'] ?? ''));
    $timestamp = 0;

    if ($date !== '' && $time !== '') {
        $datetime = DateTimeImmutable::createFromFormat('Y-m-d H:i', sprintf('%s %s', $date, $time), wp_timezone());
        $timestamp = $datetime ? $datetime->getTimestamp() : 0;
    }

    return [
        'name' => sanitize_text_field(wp_unslash($source['name'] ?? '')),
        'email' => sanitize_email(wp_unslash($source['email'] ?? '')),
        'phone' => sanitize_text_field(wp_unslash($source['phone'] ?? '')),
        'party_size' => absint($source['party_size'] ?? 0),
        'menu' => absint($source['menu'] ?? 0),
        'section' => absint($source['section'] ?? 0),
        'table_ids' => $tableIds,
        'timestamp' => $timestamp,
        'notes' => sanitize_textarea_field(wp_unslash($source['notes'] ?? '')),
        'date' => $date,
        'time' => $time,
    ];
}

function validate_booking_data(array $data): array
{
    $errors = [];

    if ($data['name'] === '') {
        $errors[] = __('Please enter the guest name.', 'pixelforge');
    }

    if ($data['email'] === '' || !is_email($data['email'])) {
        $errors[] = __('Please provide a valid email.', 'pixelforge');
    }

    if ($data['phone'] === '') {
        $errors[] = __('Please add a contact number.', 'pixelforge');
    }

    if ($data['party_size'] < 2) {
        $errors[] = __('Party size must be at least 2.', 'pixelforge');
    }

    if ($data['party_size'] > 12) {
        $errors[] = __('Party size cannot exceed 12.', 'pixelforge');
    }

    if ($data['menu'] === 0 || get_post_type($data['menu']) !== BookingMenu::KEY) {
        $errors[] = __('Choose a menu for this booking.', 'pixelforge');
    }

    if ($data['section'] === 0 || get_post_type($data['section']) !== BookingSection::KEY) {
        $errors[] = __('Choose a section for this booking.', 'pixelforge');
    }

    if ($data['table_ids'] === []) {
        $errors[] = __('Select at least one table.', 'pixelforge');
    }

    if ($data['timestamp'] === 0) {
        $errors[] = __('Provide a valid booking date and time.', 'pixelforge');
    }

    return $errors;
}

function persist_booking_meta(int $bookingId, array $data): void
{
    update_post_meta($bookingId, 'table_booking_customer_name', $data['name']);
    update_post_meta($bookingId, 'table_booking_customer_email', $data['email']);
    update_post_meta($bookingId, 'table_booking_customer_phone', $data['phone']);
    update_post_meta($bookingId, 'table_booking_party_size', $data['party_size']);
    update_post_meta($bookingId, 'table_booking_menu_id', $data['menu']);
    update_post_meta($bookingId, 'table_booking_section_id', $data['section']);
    update_post_meta($bookingId, 'table_booking_table_id', $data['table_ids']);
    update_post_meta($bookingId, 'table_booking_datetime', $data['timestamp']);
    update_post_meta($bookingId, 'table_booking_notes', $data['notes']);
}

function get_panel_context(): array
{
    $bookings = get_posts([
        'post_type' => TableBooking::KEY,
        'post_status' => ['publish', 'draft', 'pending'],
        'numberposts' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'table_booking_datetime',
        'order' => 'DESC',
    ]);

    return [
        'menus' => get_posts([
            'post_type' => BookingMenu::KEY,
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]),
        'sections' => get_posts([
            'post_type' => BookingSection::KEY,
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]),
        'tables' => get_posts([
            'post_type' => BookingTable::KEY,
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]),
        'bookings' => array_map(__NAMESPACE__ . '\\format_booking', $bookings),
    ];
}

function format_booking(WP_Post $booking): array
{
    $details = get_booking_details($booking->ID);

    return [
        'id' => $booking->ID,
        'title' => $booking->post_title,
        'status' => get_post_status($booking),
        'details' => $details,
        'verified' => (bool)get_post_meta($booking->ID, 'table_booking_verified', true),
        'table_label' => get_table_labels($details['table_ids']),
    ];
}

function get_redirect_target(string $value): string
{
    $fallback = home_url('/');
    $validated = wp_validate_redirect($value, $fallback);

    return $validated ?: $fallback;
}

function get_booking_admin_page_url(): string
{
    $page = get_page_by_path('table-booking-admin');

    if ($page) {
        return get_permalink($page);
    }

    $templatePages = get_pages([
        'number' => 1,
        'meta_key' => '_wp_page_template',
        'meta_value' => 'views/template-booking-admin.blade.php',
    ]);

    if ($templatePages !== []) {
        return get_permalink($templatePages[0]);
    }

    return home_url('/table-booking-admin/');
}

function is_table_booking_staff(WP_User $user): bool
{
    if ($user->user_login === 'staff') {
        return true;
    }

    return in_array('staff', (array)$user->roles, true);
}
