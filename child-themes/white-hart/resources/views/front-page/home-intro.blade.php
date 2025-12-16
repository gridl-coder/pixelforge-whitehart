<section class="home-intro pub-bodmin-hero" itemprop="subjectOf" itemscope itemtype="https://schema.org/WebPage">
  @if (!empty($homeIntro['headerImage']['url']))
    <img class="home-intro-image"
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

  <div class="home-intro-inner">
    <h1>
      <span class="home-intro__heading-prefix">{{ __('Welcome to', 'pixelforge') }}</span>
      The White Hart
      @if (!empty($homeIntro['location']))
        <span class="home-intro__location">{{ $homeIntro['location'] }}</span>
      @endif
    </h1>

    <div class="nav_dec"><span></span></div>

    @if (!empty($homeIntro['content']))
      {!! $homeIntro['content'] !!}
    @endif

    <p class="home-intro__seo-note lead">
      {{ __('Your welcoming pub in Bodmin for pub food, Sunday roasts, live music and friendly gatherings.', 'pixelforge') }}
    </p>

    <div class="home-intro-pods">
      <ul class="amenities-list">
        @foreach ($homeIntro['features'] as $feature)
          <li>
            <span class="amenities-list__icon" aria-hidden="true">
              <img width="30"
                   height="30"
                   src="{{ $feature['path'] }}"
                   class="img-fluid"
                   alt="{{ $feature['label'] }}"
                   loading="lazy"
                   decoding="async">
            </span>
            <span>{{ $feature['label'] }}</span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

</section>
