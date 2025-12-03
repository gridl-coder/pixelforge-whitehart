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
        {{ __('A few snapshots from the bar, the kitchen, and our events. Drop by, say hello, and make your own memories with us.', 'pixelforge') }}
      </p>
    </div>

    <div class="home-gallery__grid">
      @forelse ($homeGallery['images'] as $image)
        <figure class="home-gallery__item">
          <img class="home-gallery__image"
               src="{{ esc_url($image['url']) }}"
               alt="{{ esc_attr($image['alt']) }}"
               loading="lazy">
          @if (!empty($image['caption']))
            <figcaption class="home-gallery__caption">{{ $image['caption'] }}</figcaption>
          @endif
        </figure>
      @empty
        <p class="home-gallery__empty">{{ __('Check back soon for our latest photos.', 'pixelforge') }}</p>
      @endforelse
    </div>
  </div>
</section>
