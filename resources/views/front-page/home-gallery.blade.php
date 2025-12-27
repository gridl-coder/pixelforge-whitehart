<section class="pub-bodmin__gallery pub-bodmin-home-gallery" id="pub-bodmin-home-gallery" itemprop="hasPart"
         itemscope itemtype="https://schema.org/CollectionPage">
  <div class="container">
    <div class="pub-bodmin__gallery-header">
      <h1>
        <span class="pub-bodmin__gallery-subtitle subtitle">{{ $homeGallery['subtitle'] }}</span>
        {{ $homeGallery['title'] }}
      </h1>

      <div class="section-separator">
        <span><x-section-divider/></span>
      </div>

      <p class="pub-bodmin__gallery-copy lead" itemprop="description">
        See some photos of our historic pub in Bodmin, our events, theme nights and more.
      </p>
    </div>
  </div>

  @if (!empty($homeGallery['images']))
    <div class="pub-bodmin__gallery-carousel">
      <div class="pub-bodmin__gallery-slider" data-home-gallery-slider>
        @foreach ($homeGallery['images'] as $image)
          <figure class="pub-bodmin__gallery-slide">
            @if (!empty($image['id']))
              {!! wp_get_attachment_image($image['id'], 'medium_large', false, [
                  'class' => 'pub-bodmin__gallery-image',
                  'loading' => 'lazy',
                  'decoding' => 'async',
                  'sizes' => '(min-width: 1200px) 400px, (min-width: 768px) 300px, 45vw',
                  'data-lightbox-src' => esc_url($image['url']),
                  'data-lightbox-caption' => esc_attr($image['caption'] ?? ''),
                  'data-lightbox-gallery' => 'home-gallery',
                  'data-lightbox-id' => $loop->index,
              ]) !!}
            @else
              {{-- Fallback for images without an ID (e.g., external URLs or theme defaults) --}}
              <img class="pub-bodmin__gallery-image"
                   src="{{ esc_url($image['url']) }}"
                   alt="{{ esc_attr($image['alt']) }}"
                   loading="lazy"
                   decoding="async"
                   sizes="(min-width: 1200px) 400px, (min-width: 768px) 300px, 45vw"
                   data-lightbox-src="{{ esc_url($image['url']) }}"
                   data-lightbox-caption="{{ esc_attr($image['caption'] ?? '') }}"
                   data-lightbox-gallery="home-gallery"
                   data-lightbox-id="{{ $loop->index }}">
            @endif

            @if (!empty($image['caption']))
              <figcaption class="pub-bodmin__gallery-caption">{{ $image['caption'] }}</figcaption>
            @endif
          </figure>
        @endforeach
      </div>
    </div>
  @else
    <div class="container">
      <p class="pub-bodmin__gallery-empty">{{ __('Check back soon for our latest photos.', 'pixelforge') }}</p>
    </div>
  @endif

  <div class="pub-bodmin__gallery-lightbox" data-home-gallery-lightbox hidden>
    <button type="button" class="pub-bodmin__gallery-lightbox__close" data-home-gallery-lightbox-close aria-label="{{ __('Close image', 'pixelforge') }}">
      &times;
    </button>
    <button type="button" class="pub-bodmin__gallery-lightbox__nav pub-bodmin__gallery-lightbox__nav--prev" data-home-gallery-lightbox-prev aria-label="{{ __('Previous image', 'pixelforge') }}">
      &#8249;
    </button>
    <button type="button" class="pub-bodmin__gallery-lightbox__nav pub-bodmin__gallery-lightbox__nav--next" data-home-gallery-lightbox-next aria-label="{{ __('Next image', 'pixelforge') }}">
      &#8250;
    </button>
    <div class="pub-bodmin__gallery-lightbox__content">
      <img class="pub-bodmin__gallery-lightbox__image" src="" alt="" loading="lazy" decoding="async">
      <p class="pub-bodmin__gallery-lightbox__caption" data-home-gallery-lightbox-caption></p>
    </div>
  </div>
</section>
