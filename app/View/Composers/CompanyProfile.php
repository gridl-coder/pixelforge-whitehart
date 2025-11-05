<?php

namespace PixelForge\View\Composers;

use Roots\Acorn\View\Composer;
use function esc_url_raw;
use function filter_var;
use function get_bloginfo;
use function PixelForge\CMB2\get_theme_option;
use function preg_replace;
use function sanitize_email;
use function wp_get_attachment_image_url;

class CompanyProfile extends Composer
{
    protected static $views = [
        'sections.header',
        'sections.footer',
    ];

    public function with(): array
    {
        $name = get_bloginfo('name');
        $logo = $this->resolveMediaUrl(get_theme_option('business_logo'));
        $email = sanitize_email((string) get_theme_option('business_email', ''));
        $phone = (string) get_theme_option('business_telephone', '');
        $address = (string) get_theme_option('business_address', '');

        return [
            'companyProfile' => [
                'name' => $name,
                'logo' => $logo ? [
                    'url' => $logo,
                    'alt' => $name,
                ] : null,
                'email' => $email !== '' ? $email : null,
                'phone' => $this->formatPhone($phone),
                'address' => $address !== '' ? $address : null,
                'mapUrl' => $this->sanitizeUrl(get_theme_option('business_google', '')),
                'social' => array_filter([
                    'facebook' => $this->sanitizeUrl(get_theme_option('business_facebook', '')),
                    'instagram' => $this->sanitizeUrl(get_theme_option('business_instagram', '')),
                    'tiktok' => $this->sanitizeUrl(get_theme_option('business_tiktok', '')),
                ]),
            ],
        ];
    }

    private function resolveMediaUrl($value): ?string
    {
        if (is_array($value)) {
            if (! empty($value['url'])) {
                return (string) $value['url'];
            }

            if (! empty($value['ID'])) {
                $url = wp_get_attachment_image_url((int) $value['ID'], 'full');

                if ($url) {
                    return $url;
                }
            }
        }

        if (is_numeric($value)) {
            $url = wp_get_attachment_image_url((int) $value, 'full');

            if ($url) {
                return $url;
            }
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * @return array{display?: string, tel?: string}|null
     */
    private function formatPhone(string $phone): ?array
    {
        $clean = preg_replace('/[^0-9+]/', '', $phone);

        if (! $clean) {
            return null;
        }

        return [
            'display' => trim($phone),
            'tel' => $clean,
        ];
    }

    private function sanitizeUrl($value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $url = esc_url_raw($value);

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
