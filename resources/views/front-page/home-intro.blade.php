<br id="home" class="stage-anchor"/>

<section class="stage-intro">
  <header class="stage-intro-header">
    @if (!empty($homeIntro['headerImage']['url']))
      <img class="stage-intro-header-image"
           src="{{ esc_url($homeIntro['headerImage']['url']) }}"
           alt="{{ esc_attr($homeIntro['headerImage']['alt']) }}"/>
    @endif

    <div class="stage-intro-header-inner">
      <h1>
        <span class="stage-intro__heading-prefix">{{ __('Welcome to', 'pixelforge') }}</span>
        {{ $homeIntro['title'] }}
        @if (!empty($homeIntro['location']))
          <span class="stage-intro__location">{{ $homeIntro['location'] }}</span>
        @endif
      </h1>

      <div class="nav_dec"><span></span></div>

      @if (!empty($homeIntro['content']))
        {!! $homeIntro['content'] !!}
      @endif
    </div>
  </header>

  <div class="stage-intro-pods">
    <ul class="amenities-list">
      @foreach ($homeIntro['features'] as $feature)
        <li>
          <span class="amenities-list__icon" aria-hidden="true">
            <i class="{{ $feature['icon'] }}"></i>
          </span>
          <span>{{ $feature['label'] }}</span>
        </li>
      @endforeach
    </ul>
  </div>
</section>

<div class="brush-dec"></div>
