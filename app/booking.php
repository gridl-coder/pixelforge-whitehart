<?php

namespace PixelForge\Bookings;

use DateInterval;
use DateTimeImmutable;
use PixelForge\PostTypes\BookingMenu;
use PixelForge\PostTypes\BookingSection;
use PixelForge\PostTypes\BookingTable;
use PixelForge\PostTypes\TableBooking;
use WP_Query;
use function PixelForge\Brevo\send_email;
use function PixelForge\CMB2\get_theme_option;

const NONCE_ACTION = 'pixelforge_table_booking';
const BOOKING_SLOT_MINUTES = 90;
const BOOKING_SLOT_SECONDS = BOOKING_SLOT_MINUTES * MINUTE_IN_SECONDS;

add_action('init', __NAMESPACE__ . '\\register_booking_shortcodes');
add_action('init', __NAMESPACE__ . '\\handle_booking_submission');
add_action('template_redirect', __NAMESPACE__ . '\\maybe_confirm_booking');
add_action('wp_ajax_pixelforge_check_table_availability', __NAMESPACE__ . '\\check_table_availability');
add_action('wp_ajax_nopriv_pixelforge_check_table_availability', __NAMESPACE__ . '\\check_table_availability');
add_action('wp_ajax_pixelforge_submit_booking', __NAMESPACE__ . '\\handle_booking_ajax_submission');
add_action('wp_ajax_nopriv_pixelforge_submit_booking', __NAMESPACE__ . '\\handle_booking_ajax_submission');
add_filter('manage_table_booking_posts_columns', __NAMESPACE__ . '\\register_table_booking_columns');
add_action('manage_table_booking_posts_custom_column', __NAMESPACE__ . '\\render_table_booking_columns', 10, 2);
add_action('pixelforge_send_booking_reminder', __NAMESPACE__ . '\\send_booking_reminder');

function register_booking_shortcodes(): void
{
    add_shortcode('pixelforge_table_booking', __NAMESPACE__ . '\\render_booking_form_shortcode');
    add_shortcode('pixelforge_booking_menus', __NAMESPACE__ . '\\render_booking_menus_shortcode');
}

function render_booking_form_shortcode(): string
{
    if (!(bool)get_theme_option('enable_bookings', 1)) {
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
    $menuDays = [];
    $menuWindows = [];

    $dayOptions = function_exists('\\PixelForge\\CMB2\\get_day_options')
        ? \PixelForge\CMB2\get_day_options()
        : [
            'monday' => esc_html__('Monday', 'pixelforge'),
            'tuesday' => esc_html__('Tuesday', 'pixelforge'),
            'wednesday' => esc_html__('Wednesday', 'pixelforge'),
            'thursday' => esc_html__('Thursday', 'pixelforge'),
            'friday' => esc_html__('Friday', 'pixelforge'),
            'saturday' => esc_html__('Saturday', 'pixelforge'),
            'sunday' => esc_html__('Sunday', 'pixelforge'),
        ];

    foreach ($menus as $menu) {
        $menuSlots[$menu->ID] = build_menu_slots((int)$menu->ID);

        $days = get_post_meta((int)$menu->ID, 'booking_menu_days', true);
        $menuDays[$menu->ID] = is_array($days) ? array_values(array_map('strtolower', $days)) : [];

        $window = get_menu_time_window((int)$menu->ID);
        $menuWindows[$menu->ID] = $window
            ? [
                'start' => $window['start']->format('H:i'),
                'end' => $window['end']->format('H:i'),
            ]
            : null;
    }

    $feedback = get_feedback();

    if (isset($_GET['booking_confirmed']) && empty($feedback['success'])) {
        if (sanitize_text_field(wp_unslash($_GET['booking_confirmed'])) === '1') {
            $feedback['success'] = __('<p><strong>Thanks!</strong><p>Your booking is now confirmed. We look forward to seeing you.</p>', 'pixelforge');
        } else {
            $feedback['errors'][] = __('<p><strong>We could not verify this booking link.</strong></p><p>Please contact us to confirm your reservation.</p>', 'pixelforge');
        }
    }

    $today = new DateTimeImmutable('today', wp_timezone());

    return \Roots\view('components.table-booking-form', [
        'sections' => $sections,
        'menus' => $menus,
        'menuSlots' => $menuSlots,
        'menuDays' => $menuDays,
        'menuWindows' => $menuWindows,
        'dayLabels' => $dayOptions,
        'feedback' => $feedback,
        'minDate' => $today->format('Y-m-d'),
    ])->render();
}

function render_booking_menus_shortcode(): string
{
    $menus = get_posts([
        'post_type' => BookingMenu::KEY,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    if ($menus === []) {
        return '';
    }

    $dayOptions = function_exists('\\PixelForge\\CMB2\\get_day_options')
        ? \PixelForge\CMB2\get_day_options()
        : [
            'monday' => esc_html__('Monday', 'pixelforge'),
            'tuesday' => esc_html__('Tuesday', 'pixelforge'),
            'wednesday' => esc_html__('Wednesday', 'pixelforge'),
            'thursday' => esc_html__('Thursday', 'pixelforge'),
            'friday' => esc_html__('Friday', 'pixelforge'),
            'saturday' => esc_html__('Saturday', 'pixelforge'),
            'sunday' => esc_html__('Sunday', 'pixelforge'),
        ];

    $dayOrder = array_keys($dayOptions);

    $output = '<div class="booking-menu-shortcode booking-menu-slider">';

    foreach ($menus as $menu) {
        $days = get_post_meta($menu->ID, 'booking_menu_days', true);
        $availableDays = [];

        if (is_array($days) && $days !== []) {
            foreach ($dayOrder as $key) {
                if (in_array($key, $days, true)) {
                    $availableDays[] = $dayOptions[$key] ?? ucfirst($key);
                }
            }
        } else {
            $availableDays[] = esc_html__('Every day', 'pixelforge');
        }

        $window = get_menu_time_window((int)$menu->ID);
        $timeLabel = $window
            ? sprintf('%s - %s', $window['start']->format('H:i'), $window['end']->format('H:i'))
            : esc_html__('Times not set', 'pixelforge');

        $thumbnail = get_the_post_thumbnail($menu->ID, 'large', [
            'class' => 'booking-menu__image',
            'loading' => 'lazy',
        ]);

        $thumbnailLink = get_the_post_thumbnail_url($menu->ID);

        $output .= '<div class="booking-menu-shortcode__item">';

        $output .= '<p class="booking-menu-shortcode__title"><strong>' . esc_html(get_the_title($menu)) . '</strong><br>'
            . esc_html(implode(' / ', $availableDays)) . '<br>'
            . esc_html($timeLabel) . '</p>';

        if ($thumbnail) {
            $output .= '<div class="booking-menu-shortcode__thumb"><a target="_blank" href="' . $thumbnailLink . '">' . $thumbnail . '</a></div>';
        }

        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}

function handle_booking_submission(): void
{
    if (wp_doing_ajax()) {
        return;
    }

    if (!(bool)get_theme_option('enable_bookings', 1)) {
        return;
    }

    if (!isset($_POST['pixelforge_booking_form']) || $_POST['pixelforge_booking_form'] !== '1') {
        return;
    }

    if (!isset($_POST['pixelforge_booking_nonce']) || !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['pixelforge_booking_nonce'])),
            NONCE_ACTION
        )) {
        return;
    }

    $feedback = process_booking_submission(collect_booking_data($_POST));

    set_feedback($feedback);
}

function handle_booking_ajax_submission(): void
{
    if (!(bool)get_theme_option('enable_bookings', 1)) {
        wp_send_json([
            'errors' => [__('Table bookings are currently disabled.', 'pixelforge')],
            'success' => null,
            'old' => collect_booking_data($_POST),
        ]);
    }

    if (!isset($_POST['pixelforge_booking_form']) || $_POST['pixelforge_booking_form'] !== '1') {
        wp_send_json([
            'errors' => [__('We could not verify your booking details. Please refresh and try again.', 'pixelforge')],
            'success' => null,
            'old' => collect_booking_data($_POST),
        ]);
    }

    if (!isset($_POST['pixelforge_booking_nonce']) || !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['pixelforge_booking_nonce'])),
            NONCE_ACTION
        )) {
        wp_send_json([
            'errors' => [__('We could not verify your booking details. Please refresh and try again.', 'pixelforge')],
            'success' => null,
            'old' => collect_booking_data($_POST),
        ]);
    }

    $feedback = process_booking_submission(collect_booking_data($_POST));

    set_feedback($feedback);

    wp_send_json($feedback);
}

function collect_booking_data(array $source): array
{
    return [
        'name' => sanitize_text_field(wp_unslash($source['pixelforge_booking_name'] ?? '')),
        'email' => sanitize_email(wp_unslash($source['pixelforge_booking_email'] ?? '')),
        'phone' => sanitize_text_field(wp_unslash($source['pixelforge_booking_phone'] ?? '')),
        'party_size' => absint(wp_unslash($source['pixelforge_booking_party_size'] ?? 0)),
        'menu' => absint(wp_unslash($source['pixelforge_booking_menu'] ?? 0)),
        'section' => absint(wp_unslash($source['pixelforge_booking_section'] ?? 0)),
        'date' => sanitize_text_field(wp_unslash($source['pixelforge_booking_date'] ?? '')),
        'time' => sanitize_text_field(wp_unslash($source['pixelforge_booking_time'] ?? '')),
        'notes' => sanitize_textarea_field(wp_unslash($source['pixelforge_booking_notes'] ?? '')),
        'honeypot' => sanitize_text_field(wp_unslash($source['pixelforge_booking_hp'] ?? '')),
    ];
}

function process_booking_submission(array $data): array
{
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

    if ($data['email'] === '' || !is_email($data['email'])) {
        $feedback['errors'][] = __('Please enter a valid email address.', 'pixelforge');
    }

    if ($data['phone'] === '') {
        $feedback['errors'][] = __('Please enter a contact phone number.', 'pixelforge');
    }

    if ($data['party_size'] < 2) {
        $feedback['errors'][] = __('Please choose how many seats you need (minimum 2).', 'pixelforge');
    }

    if ($data['party_size'] > 12) {
        $feedback['errors'][] = __('Bookings are limited to 12 guests online. Please call us for larger parties.', 'pixelforge');
    }

    if ($data['menu'] === 0 || get_post_type($data['menu']) !== BookingMenu::KEY) {
        $feedback['errors'][] = __('Please pick a menu to book.', 'pixelforge');
    }

    if ($data['section'] === 0 || get_post_type($data['section']) !== BookingSection::KEY) {
        $feedback['errors'][] = __('Please select an area.', 'pixelforge');
    }

    $bookingDate = DateTimeImmutable::createFromFormat('Y-m-d H:i', sprintf('%s %s', $data['date'], $data['time']), wp_timezone());

    if (!$bookingDate) {
        $feedback['errors'][] = __('Please choose a valid date and time.', 'pixelforge');
    }

    $timeWindow = get_menu_time_window($data['menu']);

    if (!$timeWindow) {
        $feedback['errors'][] = __('This menu is missing booking hours.', 'pixelforge');
    }

    $now = new DateTimeImmutable('now', wp_timezone());

    if ($bookingDate && $bookingDate < $now) {
        $feedback['errors'][] = __('Please choose a time in the future.', 'pixelforge');
    }

    if ($bookingDate && $timeWindow) {
        $slotStart = time_from_string($data['time']);
        $allowedSlots = build_menu_slots($data['menu']);

        if (!$slotStart || !in_array($data['time'], $allowedSlots, true)) {
            $feedback['errors'][] = __('Bookings are limited to 90-minute slots.', 'pixelforge');
        } else {
            if ($slotStart < $timeWindow['start'] || $slotStart >= $timeWindow['end']) {
                $feedback['errors'][] = __('Selected time is outside the menu availability.', 'pixelforge');
            }

            if (!menu_allows_day($data['menu'], $bookingDate)) {
                $feedback['errors'][] = __('Selected day is not available for this menu.', 'pixelforge');
            }
        }
    }

    if (!empty($feedback['errors'])) {
        return $feedback;
    }

    $timestamp = $bookingDate ? $bookingDate->getTimestamp() : 0;

    if (customer_has_active_booking($data['email'], $data['phone'], $timestamp)) {
        $feedback['errors'][] = __('You already have a booking with us. Please call to arrange multiple bookings.', 'pixelforge');

        return $feedback;
    }

    $tableIds = find_available_tables($data['section'], $data['party_size'], $timestamp);

    if (!$tableIds) {
        $suggestion = find_next_available_slot($data['section'], $data['party_size'], $data['menu'], $bookingDate ?: new DateTimeImmutable('now', wp_timezone()));

        if ($suggestion) {
            $feedback['errors'][] = sprintf(
                __('No tables are available for that area and time. The next available slot is %s.', 'pixelforge'),
                wp_date('M j, Y H:i', $suggestion->getTimestamp(), wp_timezone())
            );
        } else {
            $feedback['errors'][] = __('No tables are available for that area and time. Please try another slot.', 'pixelforge');
        }

        return $feedback;
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

    if (is_wp_error($bookingId) || !$bookingId) {
        $feedback['errors'][] = __('Unable to save your booking right now. Please try again later.', 'pixelforge');

        return $feedback;
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

    $verificationToken = wp_generate_password(20, false, false);

    update_post_meta($bookingId, 'table_booking_verified', 0);
    update_post_meta($bookingId, 'table_booking_verification_token', $verificationToken);

    send_booking_notifications($bookingId, $data, $tableIds, $timestamp, $verificationToken);

    $feedback['success'] = __('<p><strong>Thanks! We\'ve reserved this slot.</strong></p><p>Please confirm your booking from the link we sent via email so we can finalise it.</p>', 'pixelforge');
    $feedback['old'] = [];

    return $feedback;
}

function get_feedback(): array
{
    global $pixelforge_booking_feedback;

    if (!isset($pixelforge_booking_feedback)) {
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

function maybe_confirm_booking(): void
{
    $bookingId = absint($_GET['pixelforge_booking_confirm'] ?? 0);
    $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';

    if ($bookingId === 0 || $token === '') {
        return;
    }

    $storedToken = (string)get_post_meta($bookingId, 'table_booking_verification_token', true);

    if ($storedToken === '' || !hash_equals($storedToken, $token)) {
        wp_safe_redirect(add_query_arg('booking_confirmed', '0', home_url('/')));
        exit;
    }

    update_post_meta($bookingId, 'table_booking_verified', 1);
    delete_post_meta($bookingId, 'table_booking_verification_token');

    $details = get_booking_details($bookingId);

    send_verified_notifications($bookingId, $details);

    schedule_booking_reminder($bookingId, $details['timestamp']);

    wp_safe_redirect(add_query_arg('booking_confirmed', '1', home_url('/')));
    exit;
}

function get_menu_time_window(int $menuId): ?array
{
    $start = time_from_string(get_post_meta($menuId, 'booking_menu_start_time', true));
    $end = time_from_string(get_post_meta($menuId, 'booking_menu_end_time', true));

    if (!$start || !$end || $end <= $start) {
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

    if (!$parsed) {
        return null;
    }

    return $parsed;
}

function menu_allows_day(int $menuId, DateTimeImmutable $date): bool
{
    $days = get_post_meta($menuId, 'booking_menu_days', true);

    if (!is_array($days) || $days === []) {
        return true;
    }

    $dayKey = strtolower($date->format('l'));

    return in_array($dayKey, $days, true);
}

function build_menu_slots(int $menuId): array
{
    $window = get_menu_time_window($menuId);

    if (!$window) {
        return [];
    }

    $slots = [];
    $cursor = $window['start'];

    while ($cursor < $window['end']) {
        $slots[] = $cursor->format('H:i');
        $cursor = $cursor->add(new DateInterval(sprintf('PT%dM', BOOKING_SLOT_MINUTES)));
    }

    return $slots;
}

function find_available_tables(int $sectionId, int $partySize, int $timestamp): ?array
{
    $tables = get_section_tables($sectionId);

    $bookedTables = get_booked_tables_for_section($sectionId, $timestamp);

    $availableTables = array_values(array_filter($tables, static function (array $table) use ($bookedTables) {
        return !in_array($table['id'], $bookedTables, true);
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

        if (!menu_allows_day($menuId, $date)) {
            continue;
        }

        foreach ($slots as $slot) {
            $slotDate = DateTimeImmutable::createFromFormat(
                'Y-m-d H:i',
                sprintf('%s %s', $date->format('Y-m-d'), $slot),
                wp_timezone()
            );

            if (!$slotDate) {
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

        if (!$slotDate || $slotDate <= $now) {
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

    if (!$query->posts) {
        return false;
    }

    foreach ($query->posts as $bookingId) {
        $bookingTime = (int)get_post_meta((int)$bookingId, 'table_booking_datetime', true);
        $status = get_post_status((int)$bookingId);

        if ($bookingTime >= time() && $status !== 'trash') {
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
        $seats = (int)get_post_meta((int)$table->ID, 'booking_table_seats', true);

        if ($seats < 1) {
            continue;
        }

        $tables[] = [
            'id' => (int)$table->ID,
            'seats' => $seats,
        ];
    }

    return $tables;
}

function get_booked_tables_for_section(int $sectionId, int $timestamp): array
{
    $slotStart = $timestamp;
    $slotEnd = $timestamp + BOOKING_SLOT_SECONDS - 1;

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
        if (!is_booking_active((int)$bookingId)) {
            continue;
        }

        $tables = array_merge($tables, normalize_table_ids(get_post_meta((int)$bookingId, 'table_booking_table_id', true)));
    }

    return array_values(array_unique(array_filter($tables)));
}

function is_booking_active(int $bookingId): bool
{
    $bookingTime = (int)get_post_meta($bookingId, 'table_booking_datetime', true);
    $status = get_post_status($bookingId);

    if ($bookingTime === 0 || $status === 'trash') {
        return false;
    }

    return $bookingTime >= time();
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

function send_booking_notifications(int $bookingId, array $data, array $tableIds, int $timestamp, string $token): void
{
    $details = get_booking_details($bookingId, $data, $tableIds, $timestamp);
    $summary = build_booking_summary($bookingId, $details);
    $confirmationLink = get_booking_confirmation_link($bookingId, $token);

    $customerSubject = __('Confirm your table booking', 'pixelforge');
    $customerBody = build_booking_email_html([
        'title' => $customerSubject,
        'intro' => __('Please confirm your booking by clicking the link below. We will hold the slot while you verify.', 'pixelforge'),
        'summary' => $summary,
        'cta' => [
            'label' => __('Confirm booking', 'pixelforge'),
            'url' => $confirmationLink,
        ],
    ]);

    $customerText = build_booking_email_text([
        'intro' => __('Please confirm your booking by visiting this link:', 'pixelforge'),
        'summary' => $summary,
        'cta' => $confirmationLink,
    ]);

    $customerSent = send_email([
        'to' => $details['email'],
        'toName' => $details['name'],
        'subject' => $customerSubject,
        'html' => $customerBody,
        'text' => $customerText,
    ]);

    if (!$customerSent) {
        wp_mail($details['email'], $customerSubject, $customerText);
    }

    $adminEmail = get_theme_option('business_email', get_option('admin_email'));
    $adminSubject = __('New table booking pending confirmation', 'pixelforge');
    $adminBody = build_booking_email_text([
        'intro' => __('A new booking was submitted and is awaiting customer verification.', 'pixelforge'),
        'summary' => $summary,
    ]);

    $adminHtml = build_booking_email_html([
        'title' => $adminSubject,
        'intro' => __('A new booking was submitted and is awaiting customer verification.', 'pixelforge'),
        'summary' => $summary,
    ]);

    $adminSent = send_email([
        'to' => $adminEmail,
        'subject' => $adminSubject,
        'text' => $adminBody,
        'html' => $adminHtml,
    ]);

    if (!$adminSent) {
        wp_mail($adminEmail, $adminSubject, $adminBody);
    }
}

function send_verified_notifications(int $bookingId, array $details): void
{
    $summary = build_booking_summary($bookingId, $details);
    $adminEmail = get_theme_option('business_email', get_option('admin_email'));
    $adminSubject = __('Booking verified by customer', 'pixelforge');
    $adminBody = build_booking_email_text([
        'intro' => __('The customer confirmed their booking via email.', 'pixelforge'),
        'summary' => $summary,
    ]);

    $adminHtml = build_booking_email_html([
        'title' => $adminSubject,
        'intro' => __('The customer confirmed their booking via email.', 'pixelforge'),
        'summary' => $summary,
    ]);

    $adminSent = send_email([
        'to' => $adminEmail,
        'subject' => $adminSubject,
        'text' => $adminBody,
        'html' => $adminHtml,
    ]);

    if (!$adminSent) {
        wp_mail($adminEmail, $adminSubject, $adminBody);
    }

    $customerSubject = __('Your table booking is confirmed', 'pixelforge');
    $customerBody = build_booking_email_text([
        'intro' => __('Thanks for confirming! Here are your booking details:', 'pixelforge'),
        'summary' => $summary,
    ]);

    $customerHtml = build_booking_email_html([
        'title' => $customerSubject,
        'intro' => __('Thanks for confirming! Here are your booking details:', 'pixelforge'),
        'summary' => $summary,
    ]);

    $customerSent = send_email([
        'to' => $details['email'],
        'toName' => $details['name'],
        'subject' => $customerSubject,
        'text' => $customerBody,
        'html' => $customerHtml,
    ]);

    if (!$customerSent) {
        wp_mail($details['email'], $customerSubject, $customerBody);
    }
}

function schedule_booking_reminder(int $bookingId, int $timestamp = 0): void
{
    if ((int)get_post_meta($bookingId, 'table_booking_verified', true) !== 1) {
        return;
    }

    $timestamp = $timestamp ?: (int)get_post_meta($bookingId, 'table_booking_datetime', true);

    if ($timestamp === 0) {
        return;
    }

    $now = time();

    if ($timestamp <= $now) {
        return;
    }

    wp_clear_scheduled_hook('pixelforge_send_booking_reminder', [$bookingId]);
    delete_post_meta($bookingId, 'table_booking_reminder_sent');

    $reminderAt = $timestamp - DAY_IN_SECONDS;

    if ($reminderAt <= $now) {
        send_booking_reminder($bookingId);
        return;
    }

    wp_schedule_single_event($reminderAt, 'pixelforge_send_booking_reminder', [$bookingId]);
}

function send_booking_reminder(int $bookingId): void
{
    if (get_post_meta($bookingId, 'table_booking_reminder_sent', true)) {
        return;
    }

    if ((int)get_post_meta($bookingId, 'table_booking_verified', true) !== 1) {
        return;
    }

    if (!is_booking_active($bookingId)) {
        return;
    }

    $details = get_booking_details($bookingId);

    if (!is_email($details['email'])) {
        return;
    }

    $summary = build_booking_summary($bookingId, $details);
    $subject = __('Reminder: your table booking is coming up', 'pixelforge');
    $textBody = build_booking_email_text([
        'intro' => __(
            'This is a friendly reminder about your booking tomorrow. If your plans change, please let us know.',
            'pixelforge'
        ),
        'summary' => $summary,
    ]);

    $htmlBody = build_booking_email_html([
        'title' => $subject,
        'intro' => __(
            'This is a friendly reminder about your booking tomorrow. If your plans change, please let us know.',
            'pixelforge'
        ),
        'summary' => $summary,
    ]);

    $sent = send_email([
        'to' => $details['email'],
        'toName' => $details['name'],
        'subject' => $subject,
        'text' => $textBody,
        'html' => $htmlBody,
    ]);

    if (!$sent) {
        wp_mail($details['email'], $subject, $textBody);
    }

    update_post_meta($bookingId, 'table_booking_reminder_sent', time());
}

function build_booking_summary(int $bookingId, array $details): string
{
    $lines = [
        sprintf(__('Booking reference: #%d', 'pixelforge'), $bookingId),
        sprintf(__('Name: %s', 'pixelforge'), $details['name']),
        sprintf(__('Party Size: %d', 'pixelforge'), $details['party_size']),
        sprintf(__('Menu: %s', 'pixelforge'), $details['menu_name'] ?: __('Unknown menu', 'pixelforge')),
        sprintf(
            __('Table: %s (%s)', 'pixelforge'),
            $details['table_label'] ?: __('Unknown table', 'pixelforge'),
            $details['section_name'] ?: __('No section', 'pixelforge')
        ),
        sprintf(__('Date & Time: %s', 'pixelforge'), $details['formatted_datetime']),
        sprintf(__('Email: %s', 'pixelforge'), $details['email']),
        sprintf(__('Phone: %s', 'pixelforge'), $details['phone']),
    ];

    if ($details['notes'] !== '') {
        $lines[] = sprintf(__('Notes: %s', 'pixelforge'), $details['notes']);
    }

    if (!(bool)get_theme_option('enable_bookings', 1)) {
        $lines[] = __('Bookings are currently disabled in the theme options.', 'pixelforge');
    }

    return implode("\n", array_filter($lines));
}

function build_booking_email_html(array $args): string
{
    $title = (string)($args['title'] ?? '');
    $intro = (string)($args['intro'] ?? '');
    $summary = (string)($args['summary'] ?? '');
    $cta = (array)($args['cta'] ?? []);

    $summaryItems = array_filter(array_map('trim', explode("\n", $summary)));
    $ctaLabel = (string)($cta['label'] ?? '');
    $ctaUrl = (string)($cta['url'] ?? '');

    $logoUrl = get_brand_logo_url();
    $contact = get_contact_details();

    $phoneHref = preg_replace('/\s+/', '', $contact['phone']);

    $listItems = array_map(static function ($line) {
        return sprintf('<li style="margin-bottom: 6px;">%s</li>', esc_html((string)$line));
    }, $summaryItems);

    $ctaHtml = '';

    if ($ctaLabel !== '' && $ctaUrl !== '') {
        $ctaHtml = sprintf(
            '<div style="margin: 24px 0 12px; text-align: center;">'
            . '<a href="%1$s" style="background:#cbba57; color:#080904; padding: 12px 20px; text-decoration: none; '
            . 'font-weight: 700; letter-spacing: 0.02em; display: inline-block; border-radius: 4px;">%2$s</a>'
            . '</div>',
            esc_url($ctaUrl),
            esc_html($ctaLabel)
        );
    }

    return sprintf(
        '<div style="background:#f8f8f2; padding: 28px; font-family: \"Raleway\", Arial, sans-serif; color:#080904;">
            <div style="max-width: 620px; margin: 0 auto; background:#ffffff; border: 1px solid #e4e1d6; border-radius: 10px; overflow: hidden;">
                <div style="background: #080904; padding: 18px 22px; text-align: center;">
                    %1$s
                </div>
                <div style="padding: 26px 26px 10px;">
                    <h1 style="font-family: \"Cormorant Garamond\", Georgia, serif; font-size: 28px; margin: 0 0 12px; color:#080904;">%2$s</h1>
                    <p style="font-size: 16px; line-height: 1.6; margin: 0 0 18px;">%3$s</p>
                    %4$s
                    <div style="background: #f3f0e5; border-radius: 8px; padding: 18px 20px; margin: 8px 0 18px;">
                        <h2 style="font-family: \"Cormorant Garamond\", Georgia, serif; font-size: 20px; margin: 0 0 10px; color:#080904;">%5$s</h2>
                        <ul style="padding-left: 18px; margin: 0; list-style: disc;">%6$s</ul>
                    </div>
                    %7$s
                    <div style="border-top: 1px solid #e4e1d6; margin-top: 18px; padding-top: 16px;">
                        <p style="font-size: 14px; margin: 0 0 6px; font-weight: 700;">%8$s</p>
                        <p style="font-size: 14px; margin: 0;">
                            <a href="tel:%9$s" style="color:#cbba57; text-decoration: none; font-weight: 600;">%10$s</a><br>
                            <a href="mailto:%11$s" style="color:#cbba57; text-decoration: none; font-weight: 600;">%11$s</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>',
        $logoUrl !== ''
            ? sprintf('<img src="%1$s" alt="%2$s" style="max-height: 64px; width: auto;">', esc_url($logoUrl), esc_attr(get_bloginfo('name')))
            : '',
        esc_html($title),
        esc_html($intro),
        $ctaHtml,
        esc_html(__('Booking details', 'pixelforge')),
        implode('', $listItems),
        build_booking_note_html($ctaLabel, $ctaUrl),
        esc_html(__('Contact us', 'pixelforge')),
        esc_attr($phoneHref),
        esc_html($contact['phone']),
        esc_html($contact['email'])
    );
}

function build_booking_note_html(string $ctaLabel, string $ctaUrl): string
{
    if ($ctaLabel === '' || $ctaUrl === '') {
        return '';
    }

    return sprintf(
        '<p style="font-size: 13px; color:#555; margin: 0 0 6px; text-align: center;">%s</p>',
        esc_html(__('If the button above does not work, copy and paste the link into your browser.', 'pixelforge'))
    );
}

function build_booking_email_text(array $args): string
{
    $intro = (string)($args['intro'] ?? '');
    $summary = (string)($args['summary'] ?? '');
    $cta = (string)($args['cta'] ?? '');
    $contact = get_contact_details();

    $sections = array_filter([
        $intro,
        $cta !== '' ? $cta : null,
        $summary,
        sprintf(
            "%s\n%s: %s\n%s: %s",
            __('Contact us', 'pixelforge'),
            __('Phone', 'pixelforge'),
            $contact['phone'],
            __('Email', 'pixelforge'),
            $contact['email']
        ),
    ]);

    return implode("\n\n", $sections);
}

function get_brand_logo_url(): string
{
    $logoPath = 'resources/images/the-white-hart-bodmin-logo.png';
    $logoUrl = get_theme_file_uri($logoPath);

    return is_string($logoUrl) ? $logoUrl : '';
}

function get_contact_details(): array
{
    return [
        'phone' => '07922 214361',
        'email' => 'bodmin@theh.art',
    ];
}

function get_booking_details(int $bookingId, array $data = [], array $tableIds = [], int $timestamp = 0): array
{
    $menuId = $data['menu'] ?? get_post_meta($bookingId, 'table_booking_menu_id', true);
    $sectionId = $data['section'] ?? get_post_meta($bookingId, 'table_booking_section_id', true);
    $tableIds = $tableIds ?: normalize_table_ids(get_post_meta($bookingId, 'table_booking_table_id', true));
    $timestamp = $timestamp ?: (int)get_post_meta($bookingId, 'table_booking_datetime', true);
    $menuId = absint($menuId);
    $sectionId = absint($sectionId);

    $datetime = wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, wp_timezone());

    return [
        'name' => $data['name'] ?? get_post_meta($bookingId, 'table_booking_customer_name', true),
        'email' => $data['email'] ?? get_post_meta($bookingId, 'table_booking_customer_email', true),
        'phone' => $data['phone'] ?? get_post_meta($bookingId, 'table_booking_customer_phone', true),
        'party_size' => (int)($data['party_size'] ?? get_post_meta($bookingId, 'table_booking_party_size', true)),
        'menu_name' => $menuId ? get_the_title($menuId) : '',
        'section_name' => $sectionId ? get_the_title($sectionId) : '',
        'table_label' => get_table_labels($tableIds),
        'notes' => $data['notes'] ?? (string)get_post_meta($bookingId, 'table_booking_notes', true),
        'formatted_datetime' => $datetime,
        'menu' => $menuId,
        'section' => $sectionId,
        'table_ids' => $tableIds,
        'timestamp' => $timestamp,
        'date' => $timestamp ? wp_date('M j, Y', $timestamp, wp_timezone()) : '',
        'time' => $timestamp ? wp_date(get_option('time_format'), $timestamp, wp_timezone()) : '',
    ];
}

function get_booking_confirmation_link(int $bookingId, string $token): string
{
    return add_query_arg([
        'pixelforge_booking_confirm' => $bookingId,
        'token' => $token,
    ], home_url('/'));
}

function check_table_availability(): void
{
    if (!(bool)get_theme_option('enable_bookings', 1)) {
        wp_send_json([
            'unavailableDate' => true,
            'availableSections' => [],
            'availableSlots' => [],
        ]);
    }

    $menuId = absint($_GET['menu'] ?? 0);
    $partySize = min(12, max(2, absint($_GET['party_size'] ?? 0)));
    $dateValue = sanitize_text_field(wp_unslash($_GET['date'] ?? ''));

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateValue, wp_timezone());

    if ($menuId === 0 || !$date) {
        wp_send_json([
            'unavailableDate' => true,
            'availableSections' => [],
            'availableSlots' => [],
        ]);
    }

    if (!menu_allows_day($menuId, $date)) {
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
        $slots = get_available_slots_for_section((int)$section->ID, $partySize, $menuId, $date);

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
