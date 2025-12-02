<?php

namespace PixelForge\FrontlineSMS;

use function PixelForge\CMB2\get_theme_option;

const DEFAULT_API_URL = 'https://cloud.frontlinesms.com/api/1/messages.json';

function get_api_token(): string
{
    $token = (string) get_theme_option('frontlinesms_api_token', '');

    if ($token === '') {
        $envToken = getenv('FRONTLINESMS_API_TOKEN');

        if ($envToken !== false) {
            $token = (string) $envToken;
        }
    }

    return trim($token);
}

function get_api_url(): string
{
    $url = trim((string) get_theme_option('frontlinesms_api_url', DEFAULT_API_URL));

    if ($url === '') {
        $url = DEFAULT_API_URL;
    }

    $url = rtrim($url, '/');

    if (substr($url, -5) !== '.json') {
        $url .= '.json';
    }

    return $url;
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
    $token = get_api_token();
    $recipient = isset($args['to']) ? normalize_phone((string) $args['to']) : '';
    $message = trim((string) ($args['message'] ?? ''));
    $sender = (string) get_theme_option('frontlinesms_sender_id', get_theme_option('brevo_sms_sender', ''));
    $channel = (string) get_theme_option('frontlinesms_channel', '');

    if ($token === '' || $recipient === '' || $message === '') {
        $reasons = [];

        if ($token === '') {
            $reasons[] = 'missing token';
        }

        if ($recipient === '') {
            $reasons[] = 'missing phone';
        }

        if ($message === '') {
            $reasons[] = 'missing message';
        }

        error_log('FrontlineSMS skipped: ' . implode(', ', $reasons));

        return false;
    }

    $payload = [
        'message' => array_filter([
            'to' => $recipient,
            'body' => $message,
            'from' => $sender !== '' ? $sender : null,
            'channel' => $channel !== '' ? $channel : null,
        ]),
    ];

    $response = wp_remote_post(get_api_url(), [
        'headers' => [
            'Authorization' => 'Token token=' . $token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        error_log('FrontlineSMS request failed: ' . $response->get_error_message());
        return false;
    }

    $status = wp_remote_retrieve_response_code($response);

    if ($status < 200 || $status >= 300) {
        error_log('FrontlineSMS returned status ' . $status . ': ' . wp_remote_retrieve_body($response));
        return false;
    }

    error_log(sprintf('FrontlineSMS sent to %s', $recipient));

    return true;
}
