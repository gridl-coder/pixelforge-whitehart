<section id="events" class="stage-menu pt-5 home-events-hero">
  <div class="home-events-hero__inner">
    @if ($highlightEvent)
      <h1>
        <span class="subtitle">{{ __('Our Next Event is...', 'pixelforge') }}</span>
        {{ $highlightEvent['title'] }}!
      </h1>

      <div class="section-separator">
        <span><x-section-divider /></span>
      </div>

      @if (!empty($highlightEvent['image']['url']))
        @php($highlightTarget = !empty($highlightEvent['externalUrl']))
        <a href="{{ esc_url($highlightEvent['externalUrl'] ?? $highlightEvent['permalink']) }}"
           class="event-link"
           @if($highlightTarget) target="_blank" rel="noopener" @endif>
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

<div class="brush-dec2"></div>

<section class="stage-events">
  <div class="container">
    <h1>
      <span class="subtitle">{{ __('Live Music, Sport, DJ’s & More.', 'pixelforge') }}</span>
      {{ __('Upcoming Events', 'pixelforge') }}
    </h1>

    <div class="section-separator">
      <span><x-section-divider /></span>
    </div>

    <p class="lead mb-3 stage-events__copy">
      {{ __('At The White Hart Inn we believe in bringing people together. Whether it’s live music nights, pool tournaments, or simply good company over a pint, we maintain a lively and welcoming vibe.', 'pixelforge') }}
    </p>

    <div class="nav_dec"><span></span></div>

    <div class="row events-container justify-content-center">
      @forelse ($upcomingEvents as $event)
        <div class="col-md-4 col-sm-6 col-6 event">
          @if (!empty($event['image']['url']))
            @php($isExternal = !empty($event['externalUrl']))
            <a href="{{ esc_url($event['externalUrl'] ?? $event['permalink']) }}" class="event-link" @if($isExternal) target="_blank" rel="noopener" @endif>
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
          <p class="text-center">{{ __('There are no upcoming events right now. Please check back soon!', 'pixelforge') }}</p>
        </div>
      @endforelse

      <div class="p-4 text-center">
        <a href="{{ esc_url($eventsArchiveUrl) }}" class="btn btn-lg">
          <i class="fa-regular fa-calendar-star"></i>
          {{ __('View All Events', 'pixelforge') }}
        </a>
      </div>
    </div>
  </div>
</section>
