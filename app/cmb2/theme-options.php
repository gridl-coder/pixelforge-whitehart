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

}
