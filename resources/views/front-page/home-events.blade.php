<section id="home-events" class="home-events bodmin-live-events" itemprop="event" itemscope itemtype="https://schema.org/EventSeries">
  <meta itemprop="name" content="Live music and events at The White Hart Inn Bodmin">
  <meta itemprop="location" content="Bodmin, Cornwall">
  <div class="home-events__gallery">
    @include('front-page.home-gallery')
  </div>
</section>

<section class="events-list" itemprop="subEvents">
  <div class="container">
    <h1>
      <span class="events-list__subtitle">{{ __('Live Music in Bodmin, Sport, DJ’s & More.', 'pixelforge') }}</span>
      {{ __('Upcoming Events at our Bodmin Pub', 'pixelforge') }}
    </h1>

    <div class="section-separator">
      <span><x-section-divider/></span>
    </div>

    <p class="lead events-list__copy" itemprop="description">
      {{ __('At The White Hart Inn in Bodmin we believe in bringing people together. Whether it’s live music nights, pool tournaments, quiz evenings, or simply good company over a pint of Cornish ale, we maintain a lively and welcoming vibe.', 'pixelforge') }}
    </p>

    <div class="nav_dec"><span></span></div>

    <div class="row events-list__grid justify-content-center">
      @forelse ($upcomingEvents as $event)
        <div class="col-md-4 col-sm-6 col-6 events-list__card" itemscope itemtype="https://schema.org/Event" itemprop="subEvent">
          @if ($event['date'] ?? null)
            <meta itemprop="startDate" content="{{ $event['date']->format('c') }}">
          @endif
          @if (!empty($event['image']['url']))
            <a href="{{ esc_url($event['link']) }}" class="events-list__link" itemprop="url"
               @if(!empty($event['isExternal'])) target="_blank" rel="noopener" @endif>
              <img src="{{ esc_url($event['image']['url']) }}"
                  class="events-list__image img-fluid img-thumbnail img-rounded"
                  alt="{{ esc_attr($event['title']) }}"
                  loading="lazy"
                  decoding="async"
                  sizes="(min-width: 992px) 360px, 48vw"
                  itemprop="image">
            </a>
          @endif

          <div class="events-list__info">
            @if (!empty($event['formattedDate']))
              <span class="events-list__date"><i class="fa-solid fa-calendar-days"></i> {{ $event['formattedDate'] }}</span>
            @endif
            <h3 class="events-list__title" itemprop="name">{{ $event['title'] }}</h3>
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
