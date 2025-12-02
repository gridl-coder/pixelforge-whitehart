<?php

namespace PixelForge\Seven;

use function PixelForge\CMB2\get_theme_option;

const DEFAULT_API_URL = 'https://gateway.seven.io/api/sms';

function get_api_key(): string
{
    $key = (string) get_theme_option('seven_api_key', '');

    if ($key === '') {
        $envKey = getenv('SEVEN_API_KEY');

        if ($envKey !== false) {
            $key = (string) $envKey;
        }
    }

    return trim($key);
}

function get_api_url(): string
{
    $url = trim((string) get_theme_option('seven_api_url', DEFAULT_API_URL));

    if ($url === '') {
        return DEFAULT_API_URL;
    }

    return rtrim($url, '/');
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
        $digits = ltrim($digits, '0');
        $digits = $countryCode . $digits;
    }

    return '+' . ltrim($digits, '+');
}

function send_sms(array $args): bool
{
    $apiKey = get_api_key();
    $recipient = isset($args['to']) ? normalize_phone((string) $args['to']) : '';
    $message = trim((string) ($args['message'] ?? ''));
    $sender = (string) get_theme_option('seven_sender_id', '');

    if ($apiKey === '' || $recipient === '' || $message === '') {
        $reasons = [];

        if ($apiKey === '') {
            $reasons[] = 'missing API key';
        }

        if ($recipient === '') {
            $reasons[] = 'missing phone';
        }

        if ($message === '') {
            $reasons[] = 'missing message';
        }

        error_log('seven SMS skipped: ' . implode(', ', $reasons));

        return false;
    }

    $payload = array_filter([
        'to' => $recipient,
        'text' => $message,
        'from' => $sender,
        'json' => 1,
    ], static function ($value) {
        return $value !== '' && $value !== null;
    });

    $response = wp_remote_post(get_api_url(), [
        'headers' => [
            'X-Api-Key' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ],
        'body' => $payload,
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        error_log('seven SMS request failed: ' . $response->get_error_message());
        return false;
    }

    $status = wp_remote_retrieve_response_code($response);

    if ($status < 200 || $status >= 300) {
        error_log('seven SMS returned status ' . $status . ': ' . wp_remote_retrieve_body($response));
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $parsed = json_decode($body, true);
    $messageId = is_array($parsed) ? ($parsed['messages'][0]['id'] ?? ($parsed['message_id'] ?? null)) : null;

    if ($messageId) {
        error_log(sprintf('seven SMS sent to %s (id: %s)', $recipient, $messageId));
    } else {
        error_log(sprintf('seven SMS sent to %s', $recipient));
    }

    return true;
}
