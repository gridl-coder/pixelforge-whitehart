<?php

namespace PixelForge;

use function PixelForge\CMB2\get_theme_option;

add_filter('pre_get_document_title', __NAMESPACE__ . '\\override_document_title');
add_action('wp_head', __NAMESPACE__ . '\\output_meta_tags', 1);

function override_document_title(string $title): string
{
    if (is_admin()) {
        return $title;
    }

    $postId = (int) get_queried_object_id();

    if ($postId > 0) {
        $customTitle = get_post_meta($postId, 'pixelforge_meta_title', true);

        if (is_string($customTitle) && $customTitle !== '') {
            return wp_strip_all_tags($customTitle);
        }
    }

    return $title;
}

function output_meta_tags(): void
{
    if (is_admin()) {
        return;
    }

    $context = build_meta_context();

    $tags = [];

    if ($context['canonical']) {
        $tags[] = sprintf('<link rel="canonical" href="%s" />', esc_url($context['canonical']));
    }

    if ($context['description']) {
        $tags[] = sprintf('<meta name="description" content="%s" />', esc_attr($context['description']));
    }

    if ($context['keywords']) {
        $tags[] = sprintf('<meta name="keywords" content="%s" />', esc_attr($context['keywords']));
    }

    if ($context['noindex']) {
        $tags[] = '<meta name="robots" content="noindex,nofollow" />';
    } elseif ($context['should_index']) {
        $tags[] = '<meta name="robots" content="index,follow" />';
    }

    $tags[] = sprintf('<meta property="og:type" content="%s" />', esc_attr($context['og_type']));
    $tags[] = sprintf('<meta property="og:site_name" content="%s" />', esc_attr(get_bloginfo('name', 'display')));

    if ($context['og_title']) {
        $tags[] = sprintf('<meta property="og:title" content="%s" />', esc_attr($context['og_title']));
    }

    if ($context['og_description']) {
        $tags[] = sprintf('<meta property="og:description" content="%s" />', esc_attr($context['og_description']));
    }

    if ($context['og_url']) {
        $tags[] = sprintf('<meta property="og:url" content="%s" />', esc_url($context['og_url']));
    }

    if ($context['og_image']) {
        $tags[] = sprintf('<meta property="og:image" content="%s" />', esc_url($context['og_image']));
    }

    if ($context['twitter_card']) {
        $tags[] = sprintf('<meta name="twitter:card" content="%s" />', esc_attr($context['twitter_card']));
    }

    if ($context['twitter_site']) {
        $tags[] = sprintf('<meta name="twitter:site" content="%s" />', esc_attr($context['twitter_site']));
    }

    if ($context['twitter_title']) {
        $tags[] = sprintf('<meta name="twitter:title" content="%s" />', esc_attr($context['twitter_title']));
    }

    if ($context['twitter_description']) {
        $tags[] = sprintf('<meta name="twitter:description" content="%s" />', esc_attr($context['twitter_description']));
    }

    if ($context['twitter_image']) {
        $tags[] = sprintf('<meta name="twitter:image" content="%s" />', esc_url($context['twitter_image']));
    }

    if (empty($tags)) {
        return;
    }

    echo "\n    <!-- PixelForge SEO -->\n    " . implode("\n    ", $tags) . "\n";
}

/**
 * @return array<string, mixed>
 */
function build_meta_context(): array
{
    $postId = (int) get_queried_object_id();
    $isSingular = $postId > 0;

    $description = '';
    $keywords = '';
    $noindex = false;
    $twitterTitle = '';
    $twitterDescription = '';
    $twitterImage = '';
    $ogTitle = '';
    $ogDescription = '';
    $ogImage = '';

    if ($isSingular) {
        $description = normalize_text(get_post_meta($postId, 'pixelforge_meta_description', true));
        $keywords = normalize_text(get_post_meta($postId, 'pixelforge_meta_keywords', true));
        $noindex = normalize_checkbox(get_post_meta($postId, 'pixelforge_noindex', true));
        $twitterTitle = normalize_text(get_post_meta($postId, 'pixelforge_twitter_title', true));
        $twitterDescription = normalize_text(get_post_meta($postId, 'pixelforge_twitter_description', true));
        $ogTitle = normalize_text(get_post_meta($postId, 'pixelforge_og_title', true));
        $ogDescription = normalize_text(get_post_meta($postId, 'pixelforge_og_description', true));
        $ogImage = normalize_image(get_post_meta($postId, 'pixelforge_og_image', true));
        $twitterImage = normalize_image(get_post_meta($postId, 'pixelforge_twitter_image', true));
    }

    $defaultDescription = normalize_text(get_theme_option('seo_meta_description', get_bloginfo('description', 'display')));
    $defaultKeywords = normalize_text(get_theme_option('seo_meta_keywords', ''));
    $defaultOgTitle = normalize_text(get_theme_option('seo_og_title', ''));
    $defaultOgDescription = normalize_text(get_theme_option('seo_og_description', $defaultDescription));
    $defaultOgImage = normalize_image(get_theme_option('seo_og_image'));
    $defaultTwitterImage = normalize_image(get_theme_option('seo_twitter_image'));
    $twitterUsername = normalize_text(get_theme_option('seo_twitter_username', ''));
    $sitewideNoindex = normalize_checkbox(get_theme_option('seo_noindex_sitewide', false));

    $documentTitle = wp_get_document_title();

    $description = $description ?: $defaultDescription;
    $keywords = $keywords ?: $defaultKeywords;
    $ogTitle = $ogTitle ?: ($defaultOgTitle ?: $documentTitle);
    $ogDescription = $ogDescription ?: $description ?: $defaultOgDescription;
    $ogImage = $ogImage ?: $twitterImage ?: ($isSingular ? get_featured_image_url($postId) : '') ?: $defaultOgImage ?: $defaultTwitterImage;
    $twitterTitle = $twitterTitle ?: $ogTitle;
    $twitterDescription = $twitterDescription ?: $ogDescription;
    $twitterImage = $twitterImage ?: $ogImage ?: $defaultTwitterImage;
    $noindex = $noindex || $sitewideNoindex;

    $canonical = '';
    if (function_exists('wp_get_canonical_url')) {
        $canonical = (string) wp_get_canonical_url($postId ?: null);
    }

    $ogUrl = $canonical;
    if (! $ogUrl) {
        if ($isSingular) {
            $ogUrl = get_permalink($postId);
        } else {
            $request = '';

            if (isset($GLOBALS['wp']) && is_object($GLOBALS['wp']) && property_exists($GLOBALS['wp'], 'request')) {
                $request = (string) $GLOBALS['wp']->request;
            }

            $path = $request !== '' ? add_query_arg([], $request) : '';

            $ogUrl = home_url($path);
        }
    }

    $ogType = $isSingular ? 'article' : 'website';
    $twitterCard = $twitterImage ? 'summary_large_image' : 'summary';

    return [
        'description' => $description,
        'keywords' => $keywords,
        'og_title' => $ogTitle,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'og_url' => $ogUrl,
        'og_type' => $ogType,
        'canonical' => $canonical,
        'twitter_card' => $twitterCard,
        'twitter_site' => $twitterUsername,
        'twitter_title' => $twitterTitle,
        'twitter_description' => $twitterDescription,
        'twitter_image' => $twitterImage,
        'noindex' => $noindex,
        'should_index' => ! $noindex,
    ];
}

function normalize_text($value): string
{
    if (is_string($value)) {
        $value = trim(wp_strip_all_tags($value));

        return $value;
    }

    if (is_array($value)) {
        return normalize_text(implode(', ', $value));
    }

    if (is_scalar($value)) {
        return normalize_text((string) $value);
    }

    return '';
}

function normalize_checkbox($value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        $value = strtolower($value);

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    if (is_numeric($value)) {
        return (bool) $value;
    }

    return false;
}

function normalize_image($value): string
{
    if (is_array($value)) {
        if (isset($value['id'])) {
            $value = $value['id'];
        } elseif (isset($value['attachment_id'])) {
            $value = $value['attachment_id'];
        } elseif (isset($value['url'])) {
            $value = $value['url'];
        }
    }

    if (is_numeric($value)) {
        $url = wp_get_attachment_image_url((int) $value, 'full');

        if ($url) {
            return $url;
        }
    }

    if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
        return $value;
    }

    return '';
}

function get_featured_image_url(int $postId): string
{
    if (! has_post_thumbnail($postId)) {
        return '';
    }

    $url = get_the_post_thumbnail_url($postId, 'full');

    return is_string($url) ? $url : '';
}
