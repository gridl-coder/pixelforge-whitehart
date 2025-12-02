<?php

namespace PixelForge\Brevo;

use function PixelForge\CMB2\get_theme_option;

const API_BASE = 'https://api.brevo.com/v3';

function get_api_key(): string
{
    $key = (string) get_theme_option('brevo_api_key', '');

    if ($key === '') {
        $envKey = getenv('BREVO_API_KEY');

        if ($envKey !== false) {
            $key = (string) $envKey;
        }
    }

    return trim($key);
}

function get_sender_email(): string
{
    $email = (string) get_theme_option('brevo_sender_email', '');

    if ($email === '') {
        $email = (string) get_theme_option('business_email', get_option('admin_email'));
    }

    return $email;
}

function get_sender_name(): string
{
    $name = (string) get_theme_option('brevo_sender_name', '');

    if ($name === '') {
        $name = get_bloginfo('name');
    }

    return $name;
}

function normalize_phone(string $phone): string
{
    $digits = preg_replace('/[^\d]/', '', $phone);

    if ($digits === '') {
        return '';
    }

    $countryCode = (string) get_theme_option('brevo_sms_country_code', '');
    $countryCode = ltrim($countryCode, '+');

    if ($countryCode !== '' && strpos($digits, $countryCode) !== 0) {
        $digits = $countryCode . $digits;
    }

    return '+' . ltrim($digits, '+');
}

function send_email(array $args): bool
{
    $apiKey = get_api_key();
    $recipient = $args['to'] ?? '';

    if ($apiKey === '' || $recipient === '') {
        return false;
    }

    $payload = array_filter([
        'sender' => [
            'email' => get_sender_email(),
            'name' => get_sender_name(),
        ],
        'to' => [
            [
                'email' => $recipient,
                'name' => $args['toName'] ?? '',
            ],
        ],
        'subject' => $args['subject'] ?? '',
        'htmlContent' => $args['html'] ?? null,
        'textContent' => $args['text'] ?? null,
    ]);

    return brevo_request('smtp/email', $payload, $apiKey);
}

function send_sms(array $args): bool
{
    $apiKey = get_api_key();
    $recipient = isset($args['to']) ? normalize_phone((string) $args['to']) : '';
    $sender = (string) get_theme_option('brevo_sms_sender', '');

    if ($apiKey === '' || $recipient === '' || $sender === '') {
        return false;
    }

    $payload = [
        'sender' => $sender,
        'recipient' => $recipient,
        'content' => $args['message'] ?? '',
        'tag' => 'table_booking',
    ];

    return brevo_request('transactionalSMS/sms', $payload, $apiKey);
}

function brevo_request(string $endpoint, array $payload, string $apiKey): bool
{
    $response = wp_remote_post(API_BASE . '/' . ltrim($endpoint, '/'), [
        'headers' => [
            'api-key' => $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        error_log('Brevo request failed: ' . $response->get_error_message());
        return false;
    }

    $status = wp_remote_retrieve_response_code($response);

    if ($status < 200 || $status >= 300) {
        error_log('Brevo request returned status ' . $status . ': ' . wp_remote_retrieve_body($response));
        return false;
    }

    return true;
}
