<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_theme_options_metabox');

function register_theme_options_metabox(): void
{
    if (!function_exists('new_cmb2_box')) {
        return;
    }

    $cmb_options = \new_cmb2_box([
        'id' => 'pixelforge_theme_options_page',
        'title' => esc_html__('PixelForge Options', 'cmb2'),
        'object_types' => ['options-page'],
        'option_key' => 'pixelforge_theme_options',
        'icon_url' => 'dashicons-palmtree',
        'message_cb' => __NAMESPACE__ . '\\options_page_message_callback',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Business Logo', 'cmb2'),
        'id' => 'business_logo',
        'type' => 'file',
        'options' => [
            'url' => true,
        ],
        'query_args' => [
            'type' => 'image',
        ],
        'preview_size' => 'medium',
    ]);


    $cmb_options->add_field([
        'name' => esc_html__('Business Email', 'cmb2'),
        'id' => 'business_email',
        'type' => 'text_email',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Business Telephone', 'cmb2'),
        'id' => 'business_telephone',
        'type' => 'text_medium',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Business Address', 'cmb2'),
        'id' => 'business_address',
        'type' => 'text_medium',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Seasonal Styling', 'pixelforge'),
        'id' => 'seasonal_styling_title',
        'type' => 'title',
        'desc' => esc_html__('Enable limited-run visual themes for holidays and events.', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Homepage Seasonal Theme', 'pixelforge'),
        'id' => 'seasonal_theme',
        'type' => 'select',
        'default' => 'none',
        'options' => [
            'none' => esc_html__('Off', 'pixelforge'),
            'christmas' => esc_html__('Christmas', 'pixelforge'),
        ],
        'desc' => esc_html__('Adds optional festive styling such as snowfall to the homepage.', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Enable Table Bookings', 'pixelforge'),
        'id' => 'enable_bookings',
        'type' => 'checkbox',
        'desc' => esc_html__('Uncheck to disable the booking form and availability calendar on the front end.', 'pixelforge'),
        'default' => 1,
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Booking Data Tools', 'pixelforge'),
        'id' => 'booking_tools',
        'type' => 'title',
        'desc' => esc_html__('Use the button below to permanently delete all booking records, tables, sections, and menus.', 'pixelforge'),
        'after_row' => function () {
            $url = wp_nonce_url(admin_url('admin-post.php?action=pixelforge_delete_booking_data'), 'pixelforge_delete_booking_data');

            echo '<p><a class="button button-secondary" href="' . esc_url($url) . '" onclick="return confirm(\'' . esc_js(__('This will permanently delete all booking data. Continue?', 'pixelforge')) . '\');">' . esc_html__('Delete all booking data', 'pixelforge') . '</a></p>';
        },
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Brevo Email', 'pixelforge'),
        'id' => 'brevo_options',
        'type' => 'title',
        'desc' => esc_html__('Store your Brevo API credentials to send booking confirmations by email.', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Brevo API Key', 'pixelforge'),
        'id' => 'brevo_api_key',
        'type' => 'text',
        'attributes' => [
            'type' => 'password',
        ],
        'desc' => esc_html__('Transactional/V3 API key used for booking emails.', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Brevo Sender Email', 'pixelforge'),
        'id' => 'brevo_sender_email',
        'type' => 'text_email',
        'desc' => esc_html__('Must be a validated sender/domain in Brevo.', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Brevo Sender Name', 'pixelforge'),
        'id' => 'brevo_sender_name',
        'type' => 'text_medium',
        'desc' => esc_html__('Appears as the from name on confirmation emails (defaults to site name).', 'pixelforge'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('SEO & Social Defaults', 'cmb2'),
        'id' => 'seo_defaults_title',
        'type' => 'title',
        'desc' => esc_html__('Configure default metadata used for SEO and social sharing. Individual pages can override these values.', 'cmb2'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Default Meta Description', 'cmb2'),
        'id' => 'seo_meta_description',
        'type' => 'textarea_small',
        'attributes' => [
            'maxlength' => 160,
        ],
        'sanitization_cb' => 'sanitize_textarea_field',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Default Meta Keywords', 'cmb2'),
        'id' => 'seo_meta_keywords',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 180,
        ],
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Default Open Graph Title', 'cmb2'),
        'id' => 'seo_og_title',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 60,
        ],
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Default Open Graph Description', 'cmb2'),
        'id' => 'seo_og_description',
        'type' => 'textarea_small',
        'attributes' => [
            'maxlength' => 200,
        ],
        'sanitization_cb' => 'sanitize_textarea_field',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Default Open Graph Image', 'cmb2'),
        'id' => 'seo_og_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'query_args' => [
            'type' => 'image',
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Twitter Username', 'cmb2'),
        'id' => 'seo_twitter_username',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 30,
            'pattern' => '^@?[A-Za-z0-9_]{1,15}$',
        ],
        'sanitization_cb' => function ($value) {
            $value = sanitize_text_field((string) $value);

            if ($value === '') {
                return '';
            }

            return strpos($value, '@') === 0 ? $value : "@{$value}";
        },
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Twitter Default Image', 'cmb2'),
        'id' => 'seo_twitter_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'query_args' => [
            'type' => 'image',
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Enable Sitewide Noindex', 'cmb2'),
        'id' => 'seo_noindex_sitewide',
        'type' => 'checkbox',
        'desc' => esc_html__('When enabled the entire site will output a noindex meta tag, unless a specific page overrides it.', 'cmb2'),
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Facebook URL', 'cmb2'),
        'id' => 'business_facebook',
        'type' => 'text_url',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Instagram URL', 'cmb2'),
        'id' => 'business_instagram',
        'type' => 'text_url',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Google URL', 'cmb2'),
        'id' => 'business_google',
        'type' => 'text_url',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('TikTok URL', 'cmb2'),
        'id' => 'business_tiktok',
        'type' => 'text_url',
    ]);

    $cmb_options->add_field([
        'name' => esc_html__('Site Health & Performance', 'pixelforge'),
        'id' => 'site_health_overview',
        'type' => 'title',
        'desc' => esc_html__('Review performance-related checks and open the Site Health dashboard.', 'pixelforge'),
        'after_row' => function () {
            $url = admin_url('site-health.php');

            echo '<p>' . esc_html__('WordPress runs PixelForge health tests for Imagick, persistent object cache, and page cache headers.', 'pixelforge') . '</p>';
            echo '<p>' . esc_html__('Use the button below to open the Site Health screen and view the results under Direct tests.', 'pixelforge') . '</p>';
            echo '<p><a class="button button-secondary" href="' . esc_url($url) . '">' . esc_html__('Open Site Health', 'pixelforge') . '</a></p>';
        },
    ]);

}
