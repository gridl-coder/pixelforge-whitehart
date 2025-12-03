<section class="home-intro">
  @if (!empty($homeIntro['headerImage']['url']))
    <img class="home-intro-image"
         src="{{ esc_url($homeIntro['headerImage']['url']) }}"
         alt="{{ esc_attr($homeIntro['headerImage']['alt']) }}"
         loading="lazy"/>
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

    <div class="home-intro-pods">
      <ul class="amenities-list">
        @foreach ($homeIntro['features'] as $feature)
          <li>
            <span class="amenities-list__icon" aria-hidden="true">
              <img width="30"
                   src="{{ $feature['path'] }}"
                   class="img-fluid"
                   alt=""
                   loading="lazy">
            </span>
            <span>{{ $feature['label'] }}</span>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

</section>
