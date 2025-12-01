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
const AVAILABILITY_NONCE_ACTION = 'pixelforge_booking_availability';
const MAX_PARTY_SIZE = 12;

function is_booking_enabled(): bool
{
    return (bool) get_theme_option('enable_bookings', true);
}

add_action('init', __NAMESPACE__ . '\\register_booking_shortcodes');
add_action('init', __NAMESPACE__ . '\\handle_booking_submission');
add_action('pixelforge_send_booking_reminder', __NAMESPACE__ . '\\send_booking_reminder');
add_action('wp_ajax_pixelforge_booking_availability', __NAMESPACE__ . '\\handle_availability_request');
add_action('wp_ajax_nopriv_pixelforge_booking_availability', __NAMESPACE__ . '\\handle_availability_request');
add_action('admin_post_pixelforge_delete_booking_data', __NAMESPACE__ . '\\handle_delete_booking_data');
add_action('admin_notices', __NAMESPACE__ . '\\render_booking_admin_notices');

function register_booking_shortcodes(): void
{
    add_shortcode('pixelforge_table_booking', __NAMESPACE__ . '\\render_booking_form_shortcode');
}

function render_booking_form_shortcode(): string
{
    if (! is_booking_enabled()) {
        return '<div class="alert alert-warning mb-4">' . esc_html__(
            'Online bookings are currently disabled. Please contact the venue directly.',
            'pixelforge'
        ) . '</div>';
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

    $availabilityNonce = wp_create_nonce(AVAILABILITY_NONCE_ACTION);

    return \Roots\view('components.table-booking-form', [
        'sections' => $sections,
        'menus' => $menus,
        'menuSlots' => $menuSlots,
        'feedback' => $feedback,
        'minDate' => $today->format('Y-m-d'),
        'availabilityNonce' => $availabilityNonce,
        'maxParty' => MAX_PARTY_SIZE,
    ])->render();
}

function handle_booking_submission(): void
{
    if (! is_booking_enabled()) {
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
    ];

    $feedback = [
        'errors' => [],
        'success' => null,
        'old' => $data,
    ];

    if ($data['name'] === '') {
        $feedback['errors'][] = __('Please enter your name.', 'pixelforge');
    }

    if ($data['email'] === '' || ! is_email($data['email'])) {
        $feedback['errors'][] = __('Please enter a valid email address.', 'pixelforge');
    }

    if ($data['phone'] === '') {
        $feedback['errors'][] = __('Please enter a contact phone number.', 'pixelforge');
    }

    if ($data['party_size'] < 1) {
        $feedback['errors'][] = __('Please choose how many seats you need.', 'pixelforge');
    }

    if ($data['party_size'] > MAX_PARTY_SIZE) {
        $feedback['errors'][] = sprintf(
            __('Bookings are limited to a maximum of %d guests. Please contact the team for larger parties.', 'pixelforge'),
            MAX_PARTY_SIZE
        );
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

    $tableIds = find_table_allocation($data['section'], $data['party_size'], $timestamp);

    if ($tableIds === []) {
        $feedback['errors'][] = __('No tables are available for that area and time. Please try another slot.', 'pixelforge');
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
        'post_status' => 'publish',
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
    update_post_meta($bookingId, 'table_booking_table_ids', $tableIds);
    delete_post_meta($bookingId, 'table_booking_table_id');

    foreach ($tableIds as $tableId) {
        add_post_meta($bookingId, 'table_booking_table_id', $tableId);
    }
    update_post_meta($bookingId, 'table_booking_datetime', $timestamp);
    update_post_meta($bookingId, 'table_booking_notes', $data['notes']);

    send_booking_emails($bookingId, $data, $tableIds, $timestamp);
    schedule_booking_reminder($bookingId, $timestamp);

    $feedback['success'] = __('Your table is reserved! We have emailed confirmation to you and the team.', 'pixelforge');
    $feedback['old'] = [];

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

function get_section_tables(int $sectionId): array
{
    $query = new WP_Query([
        'post_type' => BookingTable::KEY,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'booking_table_seats',
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => 'booking_table_section',
                'value' => $sectionId,
                'compare' => '=',
            ],
        ],
    ]);

    return array_map(static function ($table) {
        return [
            'id' => (int) $table->ID,
            'seats' => (int) get_post_meta($table->ID, 'booking_table_seats', true),
        ];
    }, $query->posts);
}

function get_available_tables(int $sectionId, int $timestamp): array
{
    $tables = get_section_tables($sectionId);

    return array_values(array_filter($tables, static function ($table) use ($timestamp) {
        return ! table_has_conflict($table['id'], $timestamp);
    }));
}

function find_table_allocation(int $sectionId, int $partySize, int $timestamp): array
{
    $availableTables = get_available_tables($sectionId, $timestamp);

    if ($availableTables === []) {
        return [];
    }

    usort($availableTables, static function ($a, $b) {
        return $a['seats'] <=> $b['seats'];
    });

    $best = null;
    $tableCount = count($availableTables);

    $search = function ($index, $selected, $seatTotal) use (&$search, &$best, $availableTables, $partySize, $tableCount) {
        if ($seatTotal >= $partySize) {
            if (
                $best === null
                || $seatTotal < $best['seats']
                || ($seatTotal === $best['seats'] && count($selected) < count($best['tables']))
            ) {
                $best = [
                    'tables' => $selected,
                    'seats' => $seatTotal,
                ];
            }

            return;
        }

        if ($index >= $tableCount) {
            return;
        }

        if ($best !== null && $seatTotal >= $best['seats']) {
            return;
        }

        $table = $availableTables[$index];

        $search($index + 1, [...$selected, $table['id']], $seatTotal + $table['seats']);
        $search($index + 1, $selected, $seatTotal);
    };

    $search(0, [], 0);

    return $best['tables'] ?? [];
}

function table_has_conflict(int $tableId, int $timestamp): bool
{
    $slotStart = $timestamp;
    $slotEnd = $timestamp + HOUR_IN_SECONDS - 1;

    $conflict = new WP_Query([
        'post_type' => TableBooking::KEY,
        'post_status' => ['publish', 'pending', 'draft', 'private'],
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'table_booking_table_id',
                'value' => $tableId,
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

    return ! empty($conflict->posts);
}

function count_availability_for_tables(array $tableIds, int $timestamp): array
{
    $counts = [
        'available' => 0,
        'booked' => 0,
    ];

    foreach ($tableIds as $tableId) {
        if (table_has_conflict($tableId, $timestamp)) {
            $counts['booked']++;
        } else {
            $counts['available']++;
        }
    }

    return $counts;
}

function get_booking_table_titles(array $tableIds): string
{
    if ($tableIds === []) {
        return __('Unknown table', 'pixelforge');
    }

    $titles = array_map(static function ($id) {
        $title = get_the_title($id);

        return $title ?: __('Table', 'pixelforge');
    }, $tableIds);

    return implode(', ', $titles);
}

function send_booking_emails(int $bookingId, array $data, array $tableIds, int $timestamp): void
{
    $adminEmail = get_theme_option('business_email', get_option('admin_email'));

    $tableNames = get_booking_table_titles($tableIds);
    $menuName = get_the_title($data['menu']);
    $sectionName = get_the_title($data['section']);
    $datetime = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, wp_timezone());

    $message = sprintf(
        "%s\n\n%s\n%s\n%s\n%s",
        sprintf(__('Booking reference: #%d', 'pixelforge'), $bookingId),
        sprintf(__('Name: %s', 'pixelforge'), $data['name']),
        sprintf(__('Party Size: %d', 'pixelforge'), $data['party_size']),
        sprintf(__('Menu: %s', 'pixelforge'), $menuName ?: __('Unknown menu', 'pixelforge')),
        sprintf(__('Tables: %1$s (%2$s)', 'pixelforge'), $tableNames, $sectionName ?: __('No section', 'pixelforge'))
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

function schedule_booking_reminder(int $bookingId, int $timestamp): void
{
    $sendAt = max(time() + MINUTE_IN_SECONDS, $timestamp - DAY_IN_SECONDS);

    if ($sendAt >= $timestamp) {
        return;
    }

    if (wp_next_scheduled('pixelforge_send_booking_reminder', [$bookingId])) {
        return;
    }

    wp_schedule_single_event($sendAt, 'pixelforge_send_booking_reminder', [$bookingId]);
}

function send_booking_reminder(int $bookingId): void
{
    $booking = get_post($bookingId);

    if (! $booking || $booking->post_type !== TableBooking::KEY) {
        return;
    }

    $customerEmail = get_post_meta($bookingId, 'table_booking_customer_email', true);
    $customerName = get_post_meta($bookingId, 'table_booking_customer_name', true);
    $timestamp = (int) get_post_meta($bookingId, 'table_booking_datetime', true);

    if (! $customerEmail || $timestamp <= time()) {
        return;
    }

    $tableIds = get_post_meta($bookingId, 'table_booking_table_ids', true);
    $tableIds = is_array($tableIds) ? array_map('intval', $tableIds) : [(int) get_post_meta($bookingId, 'table_booking_table_id', true)];
    $tableIds = array_filter($tableIds);
    $tableName = get_booking_table_titles($tableIds);
    $menuName = get_the_title((int) get_post_meta($bookingId, 'table_booking_menu_id', true));
    $sectionName = get_the_title((int) get_post_meta($bookingId, 'table_booking_section_id', true));
    $datetime = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, wp_timezone());

    $message = sprintf(
        "%s\n%s\n%s",
        sprintf(__('Hi %s, this is a reminder for your booking tomorrow.', 'pixelforge'), $customerName ?: __('there', 'pixelforge')),
        sprintf(__('Date & Time: %s', 'pixelforge'), $datetime),
        sprintf(__('Table: %1$s (%2$s) — Menu: %3$s', 'pixelforge'), $tableName ?: __('Table', 'pixelforge'), $sectionName ?: __('Area', 'pixelforge'), $menuName ?: __('Menu', 'pixelforge'))
    );

    wp_mail($customerEmail, __('Reminder: your booking is tomorrow', 'pixelforge'), $message);
}

function handle_availability_request(): void
{
    check_ajax_referer(AVAILABILITY_NONCE_ACTION);

    if (! is_booking_enabled()) {
        wp_send_json_error(['message' => __('Bookings are currently disabled.', 'pixelforge')]);
    }

    $menuId = absint(wp_unslash($_POST['menu'] ?? 0));
    $sectionId = absint(wp_unslash($_POST['section'] ?? 0));
    $partySize = max(1, min(MAX_PARTY_SIZE, absint(wp_unslash($_POST['party_size'] ?? 1))));
    $days = max(1, min(14, absint(wp_unslash($_POST['days'] ?? 7))));
    $startDate = sanitize_text_field(wp_unslash($_POST['start'] ?? ''));

    $start = DateTimeImmutable::createFromFormat('Y-m-d', $startDate, wp_timezone()) ?: new DateTimeImmutable('today', wp_timezone());

    $payload = build_availability_payload($menuId, $sectionId, $partySize, $start, $days);

    wp_send_json_success(['days' => $payload]);
}

function build_availability_payload(int $menuId, int $sectionId, int $partySize, DateTimeImmutable $start, int $days): array
{
    $slots = build_menu_slots($menuId);
    $tables = get_section_tables($sectionId);
    $tableIds = array_map(static fn($table) => $table['id'], $tables);

    if ($slots === [] || $tables === []) {
        return [];
    }

    $payload = [];

    for ($i = 0; $i < $days; $i++) {
        $date = $start->add(new DateInterval("P{$i}D"));
        $isDayAllowed = menu_allows_day($menuId, $date);
        $slotData = [];

        foreach ($slots as $slot) {
            $bookingDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', sprintf('%s %s', $date->format('Y-m-d'), $slot), wp_timezone());

            if (! $bookingDate || ! $isDayAllowed) {
                $slotData[] = [
                    'time' => $slot,
                    'available' => 0,
                    'booked' => count($tableIds),
                    'status' => 'closed',
                ];

                continue;
            }

            $timestamp = $bookingDate->getTimestamp();
            $counts = count_availability_for_tables($tableIds, $timestamp);
            $allocation = find_table_allocation($sectionId, $partySize, $timestamp);
            $status = $allocation !== [] ? 'available' : 'booked';

            $slotData[] = [
                'time' => $slot,
                'available' => $counts['available'],
                'booked' => $counts['booked'],
                'status' => $status,
            ];
        }

        $payload[] = [
            'date' => $date->format('Y-m-d'),
            'label' => $date->format(get_option('date_format')),
            'slots' => $slotData,
            'allowed' => $isDayAllowed,
        ];
    }

    return $payload;
}

function handle_delete_booking_data(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have permission to clear booking data.', 'pixelforge'));
    }

    check_admin_referer('pixelforge_delete_booking_data');

    $postTypes = [
        BookingSection::KEY,
        BookingTable::KEY,
        BookingMenu::KEY,
        TableBooking::KEY,
    ];

    foreach ($postTypes as $postType) {
        $posts = get_posts([
            'post_type' => $postType,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
        ]);

        foreach ($posts as $postId) {
            wp_delete_post($postId, true);
            wp_clear_scheduled_hook('pixelforge_send_booking_reminder', [(int) $postId]);
        }
    }

    wp_safe_redirect(add_query_arg('pixelforge_booking_reset', '1', wp_get_referer() ?: admin_url()));
    exit;
}

function render_booking_admin_notices(): void
{
    if (! isset($_GET['pixelforge_booking_reset'])) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('All booking data has been deleted.', 'pixelforge') . '</p></div>';
}
