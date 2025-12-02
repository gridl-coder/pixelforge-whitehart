<section id="events" class="home-events-hero">
  <div class="home-events-hero__inner">

    @if ($highlightEvent)
      <h1>
        <span class="subtitle">{{ __('Our Next Event is...', 'pixelforge') }}</span>
        {{ $highlightEvent['title'] }}!
      </h1>

      <div class="section-separator">
        <span><x-section-divider/></span>
      </div>

      @if (!empty($highlightEvent['image']['url']))
        <a href="{{ esc_url($highlightEvent['link']) }}"
           class="event-link"
           @if(!empty($highlightEvent['isExternal'])) target="_blank" rel="noopener" @endif>
          <img src="{{ esc_url($highlightEvent['image']['url']) }}"
               class="event-thumbnail img-fluid img-thumbnail img-rounded"
               alt="{{ esc_attr($highlightEvent['title']) }}"
               loading="lazy">
        </a>
      @endif
    @else
      <h1>
        <span class="subtitle">{{ __('Stay tuned', 'pixelforge') }}</span>
        {{ __('New events coming soon', 'pixelforge') }}
      </h1>
    @endif
  </div>
</section>

<section class="stage-events">
  <div class="container">
    <h1>
      <span class="subtitle">{{ __('Live Music, Sport, DJ’s & More.', 'pixelforge') }}</span>
      {{ __('Upcoming Events', 'pixelforge') }}
    </h1>

    <div class="section-separator">
      <span><x-section-divider/></span>
    </div>

    <p class="lead mb-3 stage-events__copy">
      {{ __('At The White Hart Inn we believe in bringing people together. Whether it’s live music nights, pool tournaments, or simply good company over a pint, we maintain a lively and welcoming vibe.', 'pixelforge') }}
    </p>

    <div class="nav_dec"><span></span></div>

    <div class="row events-container justify-content-center">
      @forelse ($upcomingEvents as $event)
        <div class="col-md-4 col-sm-6 col-6 event">
          @if (!empty($event['image']['url']))
            <a href="{{ esc_url($event['link']) }}" class="event-link"
               @if(!empty($event['isExternal'])) target="_blank" rel="noopener" @endif>
              <img src="{{ esc_url($event['image']['url']) }}"
                   class="event-thumbnail img-fluid img-thumbnail img-rounded"
                   alt="{{ esc_attr($event['title']) }}"
                   loading="lazy">
            </a>
          @endif

          <div class="event-info">
            @if (!empty($event['formattedDate']))
              <span class="event-date"><i class="fa-solid fa-calendar-days"></i> {{ $event['formattedDate'] }}</span>
            @endif
            <h3>{{ $event['title'] }}</h3>
          </div>

          <div class="nav_dec"><span></span></div>
        </div>
      @empty
        <div class="col-12">
          <p
            class="text-center">{{ __('There are no upcoming events right now. Please check back soon!', 'pixelforge') }}</p>
        </div>
      @endforelse
<?php
  /**
      <div class="p-4 text-center">
        <a href="{{ esc_url($eventsArchiveUrl) }}" class="btn btn-lg">
          <svg width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.0.0-alpha3 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) --><path d="M.0002 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48L448 192H0L.0002 464zM134.1 304.3l54.63-8l24.5-49.5c4.5-9 17.12-8.875 21.5 0l24.5 49.5l54.75 8c9.751 1.5 13.75 13.5 6.626 20.5L281 363.4l9.375 54.63c1.75 9.875-8.625 17.25-17.38 12.62L224 404.8l-48.88 25.88C166.4 435.3 156 427.7 157.8 418l9.375-54.63L127.5 324.8C120.3 317.8 124.3 305.8 134.1 304.3zM400 64H352V31.1C352 14.4 337.6 0 320 0C302.4 0 288 14.4 288 31.1V64H160V31.1C160 14.4 145.6 0 128 0S96 14.4 96 31.1V64H48c-26.51 0-48 21.49-48 48L0 160h448l.0002-48C448 85.49 426.5 64 400 64z"/></svg>

          {{ __('View All Events', 'pixelforge') }}
        </a>
      </div>
   **/?>
   </div>
  </div>
</section>
