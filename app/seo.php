<?php

namespace PixelForge;

use function PixelForge\CMB2\get_theme_option;

add_filter('pre_get_document_title', __NAMESPACE__ . '\\override_document_title');
add_action('wp_head', __NAMESPACE__ . '\\output_meta_tags', 1);
add_action('wp_head', __NAMESPACE__ . '\\output_schema', 3);

/**
 * Override the document title with a custom meta title if set.
 */
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

/**
 * Output standard SEO meta tags (Open Graph, Twitter, Robots, etc.).
 */
function output_meta_tags(): void
{
    if (is_admin()) {
        return;
    }

    $context = build_meta_context();
    $tags = [];

    // Standard Meta
    if ($context['canonical']) {
        $tags[] = sprintf('<link rel="canonical" href="%s" />', esc_url($context['canonical']));
    }

    if ($context['description']) {
        $tags[] = sprintf('<meta name="description" content="%s" />', esc_attr($context['description']));
    }

    if ($context['keywords']) {
        $tags[] = sprintf('<meta name="keywords" content="%s" />', esc_attr($context['keywords']));
    }

    // Robots
    if ($context['noindex']) {
        $tags[] = '<meta name="robots" content="noindex,nofollow" />';
    } else {
        $tags[] = '<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />';
    }

    // Open Graph
    $tags[] = sprintf('<meta property="og:locale" content="%s" />', esc_attr($context['og_locale']));
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

    if ($context['published_time']) {
        $tags[] = sprintf('<meta property="article:published_time" content="%s" />', esc_attr($context['published_time']));
    }

    if ($context['modified_time']) {
        $tags[] = sprintf('<meta property="article:modified_time" content="%s" />', esc_attr($context['modified_time']));
    }

    // Twitter Card
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
 * Output JSON-LD Schema for WebPage or Article.
 */
function output_schema(): void
{
    if (is_admin()) {
        return;
    }

    $context = build_meta_context();
    $schema = [];

    $baseSchema = [
        '@context' => 'https://schema.org',
        '@id' => $context['og_url'] . '#webpage',
        'url' => $context['og_url'],
        'name' => $context['og_title'],
        'description' => $context['description'],
        'inLanguage' => $context['og_locale'],
        'isPartOf' => [
            '@id' => home_url('/#website'),
        ],
    ];

    if ($context['is_singular'] && is_singular('post')) {
        $schema = array_merge($baseSchema, [
            '@type' => 'Article',
            'headline' => $context['og_title'],
            'datePublished' => $context['published_time'],
            'dateModified' => $context['modified_time'],
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', get_post_field('post_author', get_queried_object_id())),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $context['og_image'], // Fallback to OG image if no specific logo
                ],
            ],
        ]);

        if ($context['og_image']) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $context['og_image'],
            ];
        }
    } else {
        $schema = array_merge($baseSchema, [
            '@type' => 'WebPage',
        ]);
    }

    if (empty($schema)) {
        return;
    }

    $json = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json) {
        echo "\n    <!-- PixelForge Schema -->\n    <script type=\"application/ld+json\">{$json}</script>\n";
    }
}

/**
 * Build the context array for meta tags and schema.
 *
 * @return array<string, mixed>
 */
function build_meta_context(): array
{
    $postId = (int) get_queried_object_id();
    $isSingular = is_singular() && $postId > 0;

    $description = '';
    $keywords = '';
    $noindex = false;
    $twitterTitle = '';
    $twitterDescription = '';
    $twitterImage = '';
    $ogTitle = '';
    $ogDescription = '';
    $ogImage = '';
    $publishedTime = '';
    $modifiedTime = '';

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

        $post = get_post($postId);
        $publishedTime = get_the_date('c', $post);
        $modifiedTime = get_the_modified_date('c', $post);
    }

    $defaultDescription = normalize_text(get_theme_option('seo_meta_description', get_bloginfo('description', 'display')));
    $defaultKeywords = normalize_text(get_theme_option('seo_meta_keywords', ''));
    $defaultOgTitle = normalize_text(get_theme_option('seo_og_title', ''));
    $defaultOgDescription = normalize_text(get_theme_option('seo_og_description', $defaultDescription));
    $defaultOgImage = normalize_image(get_theme_option('seo_og_image'));
    $defaultTwitterImage = normalize_image(get_theme_option('seo_twitter_image'));
    $twitterUsername = normalize_text(get_theme_option('seo_twitter_username', ''));
    $sitewideNoindex = normalize_checkbox(get_theme_option('seo_noindex_sitewide', false));
    $blogPublic = get_option('blog_public');

    $documentTitle = wp_get_document_title();

    $description = $description ?: $defaultDescription;
    $keywords = $keywords ?: $defaultKeywords;
    $ogTitle = $ogTitle ?: ($defaultOgTitle ?: $documentTitle);
    $ogDescription = $ogDescription ?: $description ?: $defaultOgDescription;
    $ogImage = $ogImage ?: $twitterImage ?: ($isSingular ? get_featured_image_url($postId) : '') ?: $defaultOgImage ?: $defaultTwitterImage;
    $twitterTitle = $twitterTitle ?: $ogTitle;
    $twitterDescription = $twitterDescription ?: $ogDescription;
    $twitterImage = $twitterImage ?: $ogImage ?: $defaultTwitterImage;

    // Respect WordPress "Discourage search engines" setting
    $noindex = $noindex || $sitewideNoindex || '0' === $blogPublic;

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
    $ogLocale = get_locale();

    return [
        'is_singular' => $isSingular,
        'description' => $description,
        'keywords' => $keywords,
        'og_title' => $ogTitle,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'og_url' => $ogUrl,
        'og_type' => $ogType,
        'og_locale' => $ogLocale,
        'published_time' => $publishedTime,
        'modified_time' => $modifiedTime,
        'canonical' => $canonical,
        'twitter_card' => $twitterCard,
        'twitter_site' => $twitterUsername,
        'twitter_title' => $twitterTitle,
        'twitter_description' => $twitterDescription,
        'twitter_image' => $twitterImage,
        'noindex' => $noindex,
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
