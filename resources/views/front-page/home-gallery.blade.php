<section class="home-gallery">
  <div class="container">
    <div class="home-gallery__header">
      <h1>
        <span class="home-gallery__subtitle">{{ $homeGallery['subtitle'] }}</span>
        {{ $homeGallery['title'] }}
      </h1>

      <div class="section-separator">
        <span><x-section-divider/></span>
      </div>

      <p class="home-gallery__copy lead">
        {{ __('A few snapshots from the bar, our events and food that we serve.', 'pixelforge') }} <br/>
        {{ __('Drop by, say hello, and make your own memories with us.', 'pixelforge') }}

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
                 data-lightbox-caption="{{ esc_attr($image['caption'] ?? '') }}">
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
    <div class="home-gallery-lightbox__content">
      <img class="home-gallery-lightbox__image" src="" alt="">
      <p class="home-gallery-lightbox__caption" data-home-gallery-lightbox-caption></p>
    </div>
  </div>
</section>
