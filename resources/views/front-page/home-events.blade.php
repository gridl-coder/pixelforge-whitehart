<section id="home-events" class="home-events">
  <div class="home-events__gallery">
    @include('front-page.home-gallery')
  </div>
</section>

<section class="events-list">
  <div class="container">
    <h1>
      <span class="events-list__subtitle">{{ __('Live Music, Sport, DJ’s & More.', 'pixelforge') }}</span>
      {{ __('Upcoming Events', 'pixelforge') }}
    </h1>

    <div class="section-separator">
      <span><x-section-divider/></span>
    </div>

    <p class="lead events-list__copy">
      {{ __('At The White Hart Inn we believe in bringing people together. Whether it’s live music nights, pool tournaments, or simply good company over a pint, we maintain a lively and welcoming vibe.', 'pixelforge') }}
    </p>

    <div class="nav_dec"><span></span></div>

    <div class="row events-list__grid justify-content-center">
      @forelse ($upcomingEvents as $event)
        <div class="col-md-4 col-sm-6 col-6 events-list__card">
          @if (!empty($event['image']['url']))
            <a href="{{ esc_url($event['link']) }}" class="events-list__link"
               @if(!empty($event['isExternal'])) target="_blank" rel="noopener" @endif>
              <img src="{{ esc_url($event['image']['url']) }}"
                   class="events-list__image img-fluid img-thumbnail img-rounded"
                   alt="{{ esc_attr($event['title']) }}"
                   loading="lazy">
            </a>
          @endif

          <div class="events-list__info">
            @if (!empty($event['formattedDate']))
              <span class="events-list__date"><i class="fa-solid fa-calendar-days"></i> {{ $event['formattedDate'] }}</span>
            @endif
            <h3 class="events-list__title">{{ $event['title'] }}</h3>
          </div>

          <div class="nav_dec"><span></span></div>
        </div>
      @empty
        <div class="col-12">
          <p class="text-center events-list__empty">{{ __('There are no upcoming events right now. Please check back soon!', 'pixelforge') }}</p>
        </div>
      @endforelse
    </div>
  </div>
</section>
