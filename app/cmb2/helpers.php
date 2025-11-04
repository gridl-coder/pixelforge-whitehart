<?php

namespace PixelForge\CMB2;

/**
 * Determine whether the current metabox should render on the front page.
 *
 * @param \CMB2 $cmb
 */
function show_if_front_page($cmb): bool
{
    if (! is_object($cmb) || ! property_exists($cmb, 'object_id')) {
        return false;
    }

    return get_option('page_on_front') === $cmb->object_id;
}

/**
 * Determine whether the current field should render when the post has a `cats` tag.
 *
 * @param \CMB2_Field $field
 */
function hide_if_no_cats($field): bool
{
    if (! is_object($field) || ! property_exists($field, 'object_id')) {
        return true;
    }

    return has_tag('cats', $field->object_id);
}

/**
 * Customize the success message shown when the options page is saved.
 *
 * @param \CMB2 $cmb
 * @param array{should_notify?: bool, is_updated?: bool, setting: string, code: string, message: string, type: string} $args
 */
function options_page_message_callback($cmb, array $args): void
{
    if (empty($args['should_notify'])) {
        return;
    }

    if (! empty($args['is_updated'])) {
        $args['message'] = sprintf(esc_html__('%s &mdash; Updated!', 'cmb2'), $cmb->prop('title'));
    }

    add_settings_error($args['setting'], $args['code'], $args['message'], $args['type']);
}

/**
 * Retrieve a value from the PixelForge theme options with a fallback.
 *
 * @template T
 * @param string $key
 * @param T|null $default
 * @return mixed|T|null
 */
function get_theme_option(string $key, $default = null)
{
    if (function_exists('cmb2_get_option')) {
        $value = cmb2_get_option('pixelforge_theme_options', $key, null);

        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }
    }

    $options = get_option('pixelforge_theme_options', []);

    if (is_array($options) && array_key_exists($key, $options)) {
        $value = $options[$key];

        if ($value !== null && $value !== '' && $value !== []) {
            return $value;
        }
    }

    return $default;
}
