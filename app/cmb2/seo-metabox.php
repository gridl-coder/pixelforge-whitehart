<?php

namespace PixelForge\CMB2;

add_action('cmb2_admin_init', __NAMESPACE__ . '\\register_seo_metabox');

function register_seo_metabox(): void
{
    if (! function_exists('new_cmb2_box')) {
        return;
    }

    $cmb_seo = \new_cmb2_box([
        'id' => 'pixelforge_seo_metabox',
        'title' => esc_html__('SEO & Social Settings', 'cmb2'),
        'object_types' => ['page', 'post'],
        'context' => 'normal',
        'priority' => 'high',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Meta Title', 'cmb2'),
        'id' => 'pixelforge_meta_title',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 60,
        ],
        'sanitization_cb' => 'sanitize_text_field',
        'desc' => esc_html__('Overrides the document title when provided.', 'cmb2'),
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Meta Description', 'cmb2'),
        'id' => 'pixelforge_meta_description',
        'type' => 'textarea_small',
        'attributes' => [
            'maxlength' => 160,
        ],
        'sanitization_cb' => 'sanitize_textarea_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Meta Keywords', 'cmb2'),
        'id' => 'pixelforge_meta_keywords',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 180,
        ],
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Open Graph Title', 'cmb2'),
        'id' => 'pixelforge_og_title',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 60,
        ],
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Open Graph Description', 'cmb2'),
        'id' => 'pixelforge_og_description',
        'type' => 'textarea_small',
        'attributes' => [
            'maxlength' => 200,
        ],
        'sanitization_cb' => 'sanitize_textarea_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Open Graph Image', 'cmb2'),
        'id' => 'pixelforge_og_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'query_args' => [
            'type' => 'image',
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Twitter Title', 'cmb2'),
        'id' => 'pixelforge_twitter_title',
        'type' => 'text_medium',
        'attributes' => [
            'maxlength' => 70,
        ],
        'sanitization_cb' => 'sanitize_text_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Twitter Description', 'cmb2'),
        'id' => 'pixelforge_twitter_description',
        'type' => 'textarea_small',
        'attributes' => [
            'maxlength' => 200,
        ],
        'sanitization_cb' => 'sanitize_textarea_field',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Twitter Image', 'cmb2'),
        'id' => 'pixelforge_twitter_image',
        'type' => 'file',
        'options' => [
            'url' => false,
        ],
        'query_args' => [
            'type' => 'image',
        ],
        'preview_size' => 'medium',
    ]);

    $cmb_seo->add_field([
        'name' => esc_html__('Robots Noindex', 'cmb2'),
        'id' => 'pixelforge_noindex',
        'type' => 'checkbox',
        'desc' => esc_html__('Prevents search engines from indexing this page when checked.', 'cmb2'),
    ]);
}
