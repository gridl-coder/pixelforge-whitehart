<?php

namespace PixelForge\Bookings;

use DateInterval;
use DateTimeImmutable;
use PixelForge\PostTypes\BookingMenu;
use PixelForge\PostTypes\BookingSection;
use PixelForge\PostTypes\BookingTable;
use PixelForge\PostTypes\TableBooking;
use WP_Query;
use function PixelForge\CMB2\get_theme_option;

const NONCE_ACTION = 'pixelforge_table_booking';

add_action('init', __NAMESPACE__ . '\\register_booking_shortcodes');
add_action('init', __NAMESPACE__ . '\\handle_booking_submission');
add_action('init', __NAMESPACE__ . '\\handle_verification_request');
add_action('init', __NAMESPACE__ . '\\cleanup_expired_unverified_bookings');
add_action('wp_ajax_pixelforge_check_table_availability', __NAMESPACE__ . '\\check_table_availability');
add_action('wp_ajax_nopriv_pixelforge_check_table_availability', __NAMESPACE__ . '\\check_table_availability');
add_filter('manage_table_booking_posts_columns', __NAMESPACE__ . '\\register_table_booking_columns');
add_action('manage_table_booking_posts_custom_column', __NAMESPACE__ . '\\render_table_booking_columns', 10, 2);

function register_booking_shortcodes(): void
{
    add_shortcode('pixelforge_table_booking', __NAMESPACE__ . '\\render_booking_form_shortcode');
}

function render_booking_form_shortcode(): string
{
    if (! (bool) get_theme_option('enable_bookings', 1)) {
        return '<p class="booking-form__notice">' . esc_html__('Table bookings are currently unavailable.', 'pixelforge') . '</p>';
    }

    $sections = get_posts([
        'post_type' => BookingSection::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $menus = get_posts([
        'post_type' => BookingMenu::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $menuSlots = [];

    foreach ($menus as $menu) {
        $menuSlots[$menu->ID] = build_menu_slots((int) $menu->ID);
    }

    $feedback = get_feedback();

    $today = new DateTimeImmutable('today', wp_timezone());

    return \Roots\view('components.table-booking-form', [
        'sections' => $sections,
        'menus' => $menus,
        'menuSlots' => $menuSlots,
        'feedback' => $feedback,
        'minDate' => $today->format('Y-m-d'),
    ])->render();
}

function handle_booking_submission(): void
{
    if (! (bool) get_theme_option('enable_bookings', 1)) {
        return;
    }

    if (! isset($_POST['pixelforge_booking_form']) || $_POST['pixelforge_booking_form'] !== '1') {
        return;
    }

    if (! isset($_POST['pixelforge_booking_nonce']) || ! wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['pixelforge_booking_nonce'])),
        NONCE_ACTION
    )) {
        return;
    }

    $data = [
        'name' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_name'] ?? '')),
        'email' => sanitize_email(wp_unslash($_POST['pixelforge_booking_email'] ?? '')),
        'phone' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_phone'] ?? '')),
        'party_size' => absint(wp_unslash($_POST['pixelforge_booking_party_size'] ?? 0)),
        'menu' => absint(wp_unslash($_POST['pixelforge_booking_menu'] ?? 0)),
        'section' => absint(wp_unslash($_POST['pixelforge_booking_section'] ?? 0)),
        'date' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_date'] ?? '')),
        'time' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_time'] ?? '')),
        'notes' => sanitize_textarea_field(wp_unslash($_POST['pixelforge_booking_notes'] ?? '')),
        'verification_method' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_verification_method'] ?? 'email')),
        'honeypot' => sanitize_text_field(wp_unslash($_POST['pixelforge_booking_hp'] ?? '')),
    ];

    $feedback = [
        'errors' => [],
        'success' => null,
        'old' => $data,
    ];

    if ($data['honeypot'] !== '') {
        $feedback['errors'][] = __('We could not process your booking. Please try again or contact us.', 'pixelforge');
    }

    if ($data['name'] === '') {
        $feedback['errors'][] = __('Please enter your name.', 'pixelforge');
    }

    if ($data['email'] === '' || ! is_email($data['email'])) {
        $feedback['errors'][] = __('Please enter a valid email address.', 'pixelforge');
    }

    if ($data['phone'] === '') {
        $feedback['errors'][] = __('Please enter a contact phone number.', 'pixelforge');
    }

    if (! in_array($data['verification_method'], ['email', 'sms'], true)) {
        $feedback['errors'][] = __('Please choose a verification method.', 'pixelforge');
    }

    if ($data['party_size'] < 1) {
        $feedback['errors'][] = __('Please choose how many seats you need.', 'pixelforge');
    }

    if ($data['menu'] === 0 || get_post_type($data['menu']) !== BookingMenu::KEY) {
        $feedback['errors'][] = __('Please pick a menu to book.', 'pixelforge');
    }

    if ($data['section'] === 0 || get_post_type($data['section']) !== BookingSection::KEY) {
        $feedback['errors'][] = __('Please select an area.', 'pixelforge');
    }

    $bookingDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', sprintf('%s %s', $data['date'], $data['time']), wp_timezone());

    if (! $bookingDate) {
        $feedback['errors'][] = __('Please choose a valid date and time.', 'pixelforge');
    }

    $timeWindow = get_menu_time_window($data['menu']);

    if (! $timeWindow) {
        $feedback['errors'][] = __('This menu is missing booking hours.', 'pixelforge');
    }

    $now = new DateTimeImmutable('now', wp_timezone());

    if ($bookingDate && $bookingDate < $now) {
        $feedback['errors'][] = __('Please choose a time in the future.', 'pixelforge');
    }

    if ($bookingDate && $timeWindow) {
        $slotStart = time_from_string($data['time']);

        if (! $slotStart) {
            $feedback['errors'][] = __('Please choose a valid time slot.', 'pixelforge');
        } else {
            if ((int) $slotStart->format('i') !== 0) {
                $feedback['errors'][] = __('Bookings are limited to hourly slots.', 'pixelforge');
            }

            if ($slotStart < $timeWindow['start'] || $slotStart >= $timeWindow['end']) {
                $feedback['errors'][] = __('Selected time is outside the menu availability.', 'pixelforge');
            }

            if (! menu_allows_day($data['menu'], $bookingDate)) {
                $feedback['errors'][] = __('Selected day is not available for this menu.', 'pixelforge');
            }
        }
    }

    if (! empty($feedback['errors'])) {
        set_feedback($feedback);
        return;
    }

    $timestamp = $bookingDate ? $bookingDate->getTimestamp() : 0;

    if (customer_has_active_booking($data['email'], $data['phone'], $timestamp)) {
        $feedback['errors'][] = __('You already have a booking with us. Please call to arrange multiple bookings.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    $tableIds = find_available_tables($data['section'], $data['party_size'], $timestamp);

    if (! $tableIds) {
        $suggestion = find_next_available_slot($data['section'], $data['party_size'], $data['menu'], $bookingDate ?: new DateTimeImmutable('now', wp_timezone()));

        if ($suggestion) {
            $feedback['errors'][] = sprintf(
                __('No tables are available for that area and time. The next available slot is %s.', 'pixelforge'),
                wp_date('M j, Y H:i', $suggestion->getTimestamp(), wp_timezone())
            );
        } else {
            $feedback['errors'][] = __('No tables are available for that area and time. Please try another slot.', 'pixelforge');
        }

        set_feedback($feedback);
        return;
    }

    $bookingTitle = sprintf(
        __('Booking for %1$s on %2$s', 'pixelforge'),
        $data['name'],
        wp_date('M j, Y H:i', $timestamp, wp_timezone())
    );

    $bookingId = wp_insert_post([
        'post_type' => TableBooking::KEY,
        'post_status' => 'pending',
        'post_title' => $bookingTitle,
    ]);

    if (is_wp_error($bookingId) || ! $bookingId) {
        $feedback['errors'][] = __('Unable to save your booking right now. Please try again later.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    update_post_meta($bookingId, 'table_booking_customer_name', $data['name']);
    update_post_meta($bookingId, 'table_booking_customer_email', $data['email']);
    update_post_meta($bookingId, 'table_booking_customer_phone', $data['phone']);
    update_post_meta($bookingId, 'table_booking_party_size', $data['party_size']);
    update_post_meta($bookingId, 'table_booking_menu_id', $data['menu']);
    update_post_meta($bookingId, 'table_booking_section_id', $data['section']);
    update_post_meta($bookingId, 'table_booking_table_id', $tableIds);
    update_post_meta($bookingId, 'table_booking_datetime', $timestamp);
    update_post_meta($bookingId, 'table_booking_notes', $data['notes']);

    $token = wp_generate_password(20, false, false);
    $expiresAt = time() + (3 * HOUR_IN_SECONDS);

    update_post_meta($bookingId, 'table_booking_verification_token', $token);
    update_post_meta($bookingId, 'table_booking_verification_expires_at', $expiresAt);
    update_post_meta($bookingId, 'table_booking_verified', 0);
    update_post_meta($bookingId, 'table_booking_verification_method', $data['verification_method']);

    send_verification_messages($bookingId, $data, $token);

    $feedback['success'] = __('Please verify your booking using the link we sent. Unverified bookings are removed after 3 hours. If you need multiple bookings, call us and we will help.', 'pixelforge');
    $feedback['old'] = [];

    set_feedback($feedback);
}

function handle_verification_request(): void
{
    if (! isset($_GET['pixelforge_booking_verify'], $_GET['booking_id'], $_GET['token'])) {
        return;
    }

    $bookingId = absint($_GET['booking_id']);
    $token = sanitize_text_field(wp_unslash($_GET['token']));

    $feedback = [
        'errors' => [],
        'success' => null,
        'old' => [],
    ];

    if (! $bookingId || ! $token) {
        $feedback['errors'][] = __('Invalid booking verification link.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    $storedToken = get_post_meta($bookingId, 'table_booking_verification_token', true);
    $expiresAt = (int) get_post_meta($bookingId, 'table_booking_verification_expires_at', true);
    $alreadyVerified = (bool) get_post_meta($bookingId, 'table_booking_verified', true);

    if ($alreadyVerified) {
        $feedback['success'] = __('This booking is already verified.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    if (! $storedToken || hash_equals($storedToken, $token) === false) {
        $feedback['errors'][] = __('Invalid booking verification link.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    if ($expiresAt && time() > $expiresAt) {
        wp_trash_post($bookingId);
        $feedback['errors'][] = __('This booking link has expired. Please book again.', 'pixelforge');
        set_feedback($feedback);
        return;
    }

    update_post_meta($bookingId, 'table_booking_verified', 1);
    delete_post_meta($bookingId, 'table_booking_verification_token');

    wp_update_post([
        'ID' => $bookingId,
        'post_status' => 'publish',
    ]);

    send_booking_emails(
        $bookingId,
        [
            'name' => get_post_meta($bookingId, 'table_booking_customer_name', true),
            'email' => get_post_meta($bookingId, 'table_booking_customer_email', true),
            'phone' => get_post_meta($bookingId, 'table_booking_customer_phone', true),
            'party_size' => (int) get_post_meta($bookingId, 'table_booking_party_size', true),
            'menu' => (int) get_post_meta($bookingId, 'table_booking_menu_id', true),
            'section' => (int) get_post_meta($bookingId, 'table_booking_section_id', true),
            'notes' => get_post_meta($bookingId, 'table_booking_notes', true),
        ],
        (array) get_post_meta($bookingId, 'table_booking_table_id', true),
        (int) get_post_meta($bookingId, 'table_booking_datetime', true)
    );

    $feedback['success'] = __('Booking verified! We look forward to seeing you.', 'pixelforge');
    set_feedback($feedback);
}

function get_feedback(): array
{
    global $pixelforge_booking_feedback;

    if (! isset($pixelforge_booking_feedback)) {
        return [
            'errors' => [],
            'success' => null,
            'old' => [],
        ];
    }

    return $pixelforge_booking_feedback;
}

function set_feedback(array $feedback): void
{
    global $pixelforge_booking_feedback;

    $pixelforge_booking_feedback = $feedback;
}

function get_menu_time_window(int $menuId): ?array
{
    $start = time_from_string(get_post_meta($menuId, 'booking_menu_start_time', true));
    $end = time_from_string(get_post_meta($menuId, 'booking_menu_end_time', true));

    if (! $start || ! $end || $end <= $start) {
        return null;
    }

    return [
        'start' => $start,
        'end' => $end,
    ];
}

function time_from_string(string $time): ?DateTimeImmutable
{
    $time = trim($time);

    if ($time === '') {
        return null;
    }

    $parsed = DateTimeImmutable::createFromFormat('!H:i', $time, wp_timezone());

    if (! $parsed) {
        return null;
    }

    return $parsed;
}

function menu_allows_day(int $menuId, DateTimeImmutable $date): bool
{
    $days = get_post_meta($menuId, 'booking_menu_days', true);

    if (! is_array($days) || $days === []) {
        return true;
    }

    $dayKey = strtolower($date->format('l'));

    return in_array($dayKey, $days, true);
}

function build_menu_slots(int $menuId): array
{
    $window = get_menu_time_window($menuId);

    if (! $window) {
        return [];
    }

    $slots = [];
    $cursor = $window['start'];

    while ($cursor < $window['end']) {
        $slots[] = $cursor->format('H:i');
        $cursor = $cursor->add(new DateInterval('PT1H'));
    }

    return $slots;
}

function find_available_tables(int $sectionId, int $partySize, int $timestamp): ?array
{
    $tables = get_section_tables($sectionId);

    $bookedTables = get_booked_tables_for_section($sectionId, $timestamp);

    $availableTables = array_values(array_filter($tables, static function (array $table) use ($bookedTables) {
        return ! in_array($table['id'], $bookedTables, true);
    }));

    if ($availableTables === []) {
        return null;
    }

    usort($availableTables, static function (array $a, array $b) {
        return $b['seats'] <=> $a['seats'];
    });

    $selected = [];
    $remaining = $partySize;

    foreach ($availableTables as $table) {
        $selected[] = $table['id'];
        $remaining -= $table['seats'];

        if ($remaining <= 0) {
            return $selected;
        }
    }

    return null;
}

function find_next_available_slot(int $sectionId, int $partySize, int $menuId, DateTimeImmutable $from): ?DateTimeImmutable
{
    $slots = build_menu_slots($menuId);

    if ($slots === []) {
        return null;
    }

    $now = new DateTimeImmutable('now', wp_timezone());

    for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
        $date = $from->modify(sprintf('+%d day', $dayOffset));

        if (! menu_allows_day($menuId, $date)) {
            continue;
        }

        foreach ($slots as $slot) {
            $slotDate = DateTimeImmutable::createFromFormat(
                'Y-m-d H:i',
                sprintf('%s %s', $date->format('Y-m-d'), $slot),
                wp_timezone()
            );

            if (! $slotDate) {
                continue;
            }

            if ($slotDate <= $from || $slotDate <= $now) {
                continue;
            }

            if (find_available_tables($sectionId, $partySize, $slotDate->getTimestamp())) {
                return $slotDate;
            }
        }
    }

    return null;
}

function get_available_slots_for_section(int $sectionId, int $partySize, int $menuId, DateTimeImmutable $date): array
{
    $slots = build_menu_slots($menuId);
    $now = new DateTimeImmutable('now', wp_timezone());
    $available = [];

    foreach ($slots as $slot) {
        $slotDate = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i',
            sprintf('%s %s', $date->format('Y-m-d'), $slot),
            wp_timezone()
        );

        if (! $slotDate || $slotDate <= $now) {
            continue;
        }

        if (find_available_tables($sectionId, $partySize, $slotDate->getTimestamp())) {
            $available[] = $slot;
        }
    }

    return $available;
}

function customer_has_active_booking(string $email, string $phone, int $timestamp): bool
{
    $query = new WP_Query([
        'post_type' => TableBooking::KEY,
        'post_status' => ['publish', 'pending', 'draft', 'private'],
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'table_booking_customer_email',
                'value' => $email,
                'compare' => '=',
            ],
            [
                'key' => 'table_booking_customer_phone',
                'value' => $phone,
                'compare' => '=',
            ],
        ],
    ]);

    if (! $query->posts) {
        return false;
    }

    foreach ($query->posts as $bookingId) {
        $bookingTime = (int) get_post_meta((int) $bookingId, 'table_booking_datetime', true);
        $verified = (bool) get_post_meta((int) $bookingId, 'table_booking_verified', true);
        $expiresAt = (int) get_post_meta((int) $bookingId, 'table_booking_verification_expires_at', true);

        if ($bookingTime < $timestamp) {
            continue;
        }

        if ($verified || ($expiresAt && time() <= $expiresAt)) {
            return true;
        }
    }

    return false;
}

function get_section_tables(int $sectionId): array
{
    $query = new WP_Query([
        'post_type' => BookingTable::KEY,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'booking_table_seats',
        'order' => 'DESC',
        'meta_query' => [
            [
                'key' => 'booking_table_section',
                'value' => $sectionId,
                'compare' => '=',
            ],
        ],
    ]);

    $tables = [];

    foreach ($query->posts as $table) {
        $seats = (int) get_post_meta((int) $table->ID, 'booking_table_seats', true);

        if ($seats < 1) {
            continue;
        }

        $tables[] = [
            'id' => (int) $table->ID,
            'seats' => $seats,
        ];
    }

    return $tables;
}

function get_booked_tables_for_section(int $sectionId, int $timestamp): array
{
    $slotStart = $timestamp;
    $slotEnd = $timestamp + HOUR_IN_SECONDS - 1;

    $bookings = new WP_Query([
        'post_type' => TableBooking::KEY,
        'post_status' => ['publish', 'pending', 'draft', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'table_booking_section_id',
                'value' => $sectionId,
                'compare' => '=',
            ],
            [
                'key' => 'table_booking_datetime',
                'value' => [$slotStart, $slotEnd],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ],
        ],
    ]);

    $tables = [];

    foreach ($bookings->posts as $bookingId) {
        if (! is_booking_active((int) $bookingId)) {
            continue;
        }

        $tables = array_merge($tables, normalize_table_ids(get_post_meta((int) $bookingId, 'table_booking_table_id', true)));
    }

    return array_values(array_unique(array_filter($tables)));
}

function is_booking_active(int $bookingId): bool
{
    $verified = (bool) get_post_meta($bookingId, 'table_booking_verified', true);
    $expiresAt = (int) get_post_meta($bookingId, 'table_booking_verification_expires_at', true);

    if ($verified) {
        return true;
    }

    if ($expiresAt === 0) {
        return true;
    }

    return time() <= $expiresAt;
}

function normalize_table_ids($value): array
{
    if (is_array($value)) {
        return array_values(array_filter(array_map('absint', $value)));
    }

    $id = absint($value);

    if ($id === 0) {
        return [];
    }

    return [$id];
}

function send_booking_emails(int $bookingId, array $data, array $tableIds, int $timestamp): void
{
    $adminEmail = get_theme_option('business_email', get_option('admin_email'));

    $tableNames = array_map('get_the_title', normalize_table_ids($tableIds));
    $menuName = get_the_title($data['menu']);
    $sectionName = get_the_title($data['section']);
    $datetime = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, wp_timezone());

    $message = sprintf(
        "%s\n\n%s\n%s\n%s\n%s",
        sprintf(__('Booking reference: #%d', 'pixelforge'), $bookingId),
        sprintf(__('Name: %s', 'pixelforge'), $data['name']),
        sprintf(__('Party Size: %d', 'pixelforge'), $data['party_size']),
        sprintf(__('Menu: %s', 'pixelforge'), $menuName ?: __('Unknown menu', 'pixelforge')),
        sprintf(
            __('Table: %s (%s)', 'pixelforge'),
            $tableNames ? implode(', ', $tableNames) : __('Unknown table', 'pixelforge'),
            $sectionName ?: __('No section', 'pixelforge')
        )
    );

    $message .= sprintf("\n%s", sprintf(__('Date & Time: %s', 'pixelforge'), $datetime));
    $message .= sprintf("\n%s", sprintf(__('Email: %s', 'pixelforge'), $data['email']));
    $message .= sprintf("\n%s", sprintf(__('Phone: %s', 'pixelforge'), $data['phone']));

    if ($data['notes'] !== '') {
        $message .= sprintf("\n%s", sprintf(__('Notes: %s', 'pixelforge'), $data['notes']));
    }

    wp_mail($adminEmail, __('New table booking received', 'pixelforge'), $message);
    wp_mail($data['email'], __('Your table booking confirmation', 'pixelforge'), $message);
}

function send_verification_messages(int $bookingId, array $data, string $token): void
{
    $verificationUrl = add_query_arg(
        [
            'pixelforge_booking_verify' => 1,
            'booking_id' => $bookingId,
            'token' => rawurlencode($token),
        ],
        home_url('/')
    );

    $message = sprintf(
        "%s\n\n%s\n%s",
        sprintf(__('Hi %s, please verify your booking.', 'pixelforge'), $data['name']),
        sprintf(__('Click to verify: %s', 'pixelforge'), $verificationUrl),
        __('Unverified bookings are removed after 3 hours.', 'pixelforge')
    );

    wp_mail($data['email'], __('Verify your table booking', 'pixelforge'), $message);

    if ($data['verification_method'] === 'sms') {
        /**
         * Allow implementers to send booking verification via SMS providers.
         */
        do_action('pixelforge_send_booking_verification_sms', $data, $message, $verificationUrl);
    }

    $adminEmail = get_theme_option('business_email', get_option('admin_email'));

    wp_mail(
        $adminEmail,
        __('New booking awaiting verification', 'pixelforge'),
        sprintf(__('Booking #%d is awaiting customer verification.', 'pixelforge'), $bookingId)
    );
}

function cleanup_expired_unverified_bookings(): void
{
    $expired = new WP_Query([
        'post_type' => TableBooking::KEY,
        'post_status' => ['pending', 'draft', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'table_booking_verification_expires_at',
                'value' => time(),
                'compare' => '<',
                'type' => 'NUMERIC',
            ],
            [
                'key' => 'table_booking_verified',
                'value' => 1,
                'compare' => '!=',
            ],
        ],
    ]);

    foreach ($expired->posts as $bookingId) {
        wp_trash_post((int) $bookingId);
    }
}

function check_table_availability(): void
{
    if (! (bool) get_theme_option('enable_bookings', 1)) {
        wp_send_json([
            'unavailableDate' => true,
            'availableSections' => [],
            'availableSlots' => [],
        ]);
    }

    $menuId = absint($_GET['menu'] ?? 0);
    $partySize = max(1, absint($_GET['party_size'] ?? 0));
    $dateValue = sanitize_text_field(wp_unslash($_GET['date'] ?? ''));

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateValue, wp_timezone());

    if ($menuId === 0 || ! $date) {
        wp_send_json([
            'unavailableDate' => true,
            'availableSections' => [],
            'availableSlots' => [],
        ]);
    }

    if (! menu_allows_day($menuId, $date)) {
        wp_send_json([
            'unavailableDate' => true,
            'availableSections' => [],
            'availableSlots' => [],
        ]);
    }

    $sections = get_posts([
        'post_type' => BookingSection::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $availableSlots = [];
    $availableSections = [];

    foreach ($sections as $section) {
        $slots = get_available_slots_for_section((int) $section->ID, $partySize, $menuId, $date);

        if ($slots !== []) {
            $availableSlots[$section->ID] = $slots;
            $availableSections[] = $section->ID;
        }
    }

    wp_send_json([
        'unavailableDate' => $availableSections === [],
        'availableSections' => $availableSections,
        'availableSlots' => $availableSlots,
        'date' => $date->format('Y-m-d'),
    ]);
}

function register_table_booking_columns(array $columns): array
{
    $newColumns = [];

    foreach ($columns as $key => $label) {
        $newColumns[$key] = $label;

        if ($key === 'title') {
            $newColumns['booking_details'] = __('Menu & Tables', 'pixelforge');
        }
    }

    return $newColumns;
}

function render_table_booking_columns(string $column, int $postId): void
{
    if ($column !== 'booking_details') {
        return;
    }

    $menuId = absint(get_post_meta($postId, 'table_booking_menu_id', true));
    $tableIds = get_post_meta($postId, 'table_booking_table_id', true);

    $menu = $menuId ? get_the_title($menuId) : '';
    $tables = get_table_labels($tableIds);

    $parts = [];

    if ($menu) {
        $parts[] = sprintf(__('Menu: %s', 'pixelforge'), $menu);
    }

    if ($tables) {
        $parts[] = sprintf(__('Table: %s', 'pixelforge'), $tables);
    }

    echo esc_html(implode(' | ', $parts));
}

function get_table_labels($tableIds): string
{
    $tables = array_filter(array_map('get_the_title', normalize_table_ids($tableIds)));

    if ($tables === []) {
        return '';
    }

    return implode(', ', $tables);
}
