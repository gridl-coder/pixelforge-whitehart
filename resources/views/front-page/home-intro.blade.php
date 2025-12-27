<section class="pub-bodmin__intro pub-bodmin-hero" itemprop="subjectOf" itemscope itemtype="https://schema.org/WebPage">
  @if (!empty($homeIntro['headerImage']['url']))
    <img class="pub-bodmin__intro-image"
         src="{{ esc_url($homeIntro['headerImage']['url']) }}"
         alt="{{ esc_attr($homeIntro['headerImage']['alt']) }}"
         loading="eager"
         decoding="async"
         fetchpriority="high"
         {{-- Provide a responsive srcset for modern browsers when available --}}
         @if (!empty($homeIntro['headerImage']['srcset']))
           srcset="{{ $homeIntro['headerImage']['srcset'] }}"
         sizes="{{ $homeIntro['headerImage']['sizes'] ?? '100vw' }}"
         @else
           sizes="100vw"
         @endif
         {{-- Provide explicit width and height if available to reduce CLS --}}
         @if (!empty($homeIntro['headerImage']['width']))
           width="{{ $homeIntro['headerImage']['width'] }}"
         @endif
         @if (!empty($homeIntro['headerImage']['height']))
           height="{{ $homeIntro['headerImage']['height'] }}"
      @endif
    />
  @endif

  <div class="pub-bodmin__intro-inner">
    <h1>
      <span class="pub-bodmin__intro__heading-prefix">{{ __('Welcome to', 'pixelforge') }}</span>
      The White Hart
      @if (!empty($homeIntro['location']))
        <span class="pub-bodmin__intro__location">{{ $homeIntro['location'] }}</span>
      @endif
    </h1>

    <div class="nav_dec"><span></span></div>

    <?= wpautop(get_the_content()); ?>

  </div>

  <div class="pub-bodmin__intro-pods">
    <ul class="pub-bodmin__amenities-list">
      @foreach ($homeIntro['features'] as $feature)
        <li class="pub-bodmin__amenities-list__item">
          <button
            class="pub-bodmin__amenities-list__button"
            data-amenity-title="{{ $feature['label'] }}"
            data-amenity-description="{{ $feature['description'] }}"
            @if(!empty($feature['image1'])) data-amenity-image1="{{ json_encode($feature['image1']) }}" @endif
            @if(!empty($feature['image2'])) data-amenity-image2="{{ json_encode($feature['image2']) }}" @endif
          >
            <span class="pub-bodmin__amenities-list__icon" aria-hidden="true">
              <img width="30"
                   height="30"
                   src="{{ $feature['path'] }}"
                   class="img-fluid"
                   alt=""
                   loading="lazy"
                   decoding="async">
            </span>
            <span>{{ $feature['label'] }}</span>
          </button>
        </li>
      @endforeach
    </ul>
  </div>

  <div id="amenity-overlay" class="pub-bodmin__amenity-overlay" hidden>
    <button type="button" class="pub-bodmin__amenity-overlay__close" aria-label="Close overlay">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <div class="pub-bodmin__amenity-overlay__content">
      <h2 class="pub-bodmin__amenity-overlay__title"></h2>
      <div class="pub-bodmin__amenity-overlay__description"></div>
      <div class="pub-bodmin__amenity-overlay__images">
        <img class="pub-bodmin__amenity-overlay__image pub-bodmin__amenity-overlay__image--1" src="" alt="" hidden>
        <img class="pub-bodmin__amenity-overlay__image pub-bodmin__amenity-overlay__image--2" src="" alt="" hidden>
      </div>
    </div>
  </div>
</section>
