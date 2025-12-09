<section class="home-gallery pub-bodmin-home-gallery" id="pub-bodmin-home-gallery" itemprop="hasPart"
         itemscope itemtype="https://schema.org/CollectionPage">
  <div class="container">
    <div class="home-gallery__header">
      <h1>
        <span class="home-gallery__subtitle">{{ $homeGallery['subtitle'] }}</span>
        {{ $homeGallery['title'] }}
      </h1>

      <div class="section-separator">
        <span><x-section-divider/></span>
      </div>

      <p class="home-gallery__copy lead" itemprop="description">
        {{ __('See our Bodmin pub in action: favourite pub food dishes, live music nights, and a lively bar atmosphere.', 'pixelforge') }}
        <br/>
        {{ __('Drop by The White Hart Inn in Bodmin for local ales, pub food, and friendly gatherings or order food delivery for cosy nights in.', 'pixelforge') }}

      </p>
    </div>
  </div>

  @if (!empty($homeGallery['images']))
    <div class="home-gallery__carousel">
      <div class="home-gallery__slider" data-home-gallery-slider>
        @foreach ($homeGallery['images'] as $image)
          <figure class="home-gallery__slide">
            <img class="home-gallery__image"
                 src="{{ esc_url($image['url']) }}"
                 alt="{{ esc_attr($image['alt']) }}"
                 loading="lazy"
                 data-lightbox-src="{{ esc_url($image['url']) }}"
                 data-lightbox-caption="{{ esc_attr($image['caption'] ?? '') }}"
                 data-lightbox-gallery="home-gallery"
                 data-lightbox-id="{{ $loop->index }}">
            @if (!empty($image['caption']))
              <figcaption class="home-gallery__caption">{{ $image['caption'] }}</figcaption>
            @endif
          </figure>
        @endforeach
      </div>
    </div>
  @else
    <div class="container">
      <p class="home-gallery__empty">{{ __('Check back soon for our latest photos.', 'pixelforge') }}</p>
    </div>
  @endif

  <div class="home-gallery-lightbox" data-home-gallery-lightbox hidden>
    <button type="button" class="home-gallery-lightbox__close" data-home-gallery-lightbox-close aria-label="{{ __('Close image', 'pixelforge') }}">
      &times;
    </button>
    <button type="button" class="home-gallery-lightbox__nav home-gallery-lightbox__nav--prev" data-home-gallery-lightbox-prev aria-label="{{ __('Previous image', 'pixelforge') }}">
      &#8249;
    </button>
    <button type="button" class="home-gallery-lightbox__nav home-gallery-lightbox__nav--next" data-home-gallery-lightbox-next aria-label="{{ __('Next image', 'pixelforge') }}">
      &#8250;
    </button>
    <div class="home-gallery-lightbox__content">
      <img class="home-gallery-lightbox__image" src="" alt="">
      <p class="home-gallery-lightbox__caption" data-home-gallery-lightbox-caption></p>
    </div>
  </div>
</section>
