<section class="events-hero">
  <div class="events-hero__inner">
    @if ($highlightEvent)
      <h1>
        <span class="events-hero__subtitle">{{ __('Our Next Event is...', 'pixelforge') }}</span>
        {{ $highlightEvent['title'] }}!
      </h1>

      <div class="section-separator">
        <span><x-section-divider/></span>
      </div>

      @if (!empty($highlightEvent['image']['url']))
        <a href="{{ esc_url($highlightEvent['link']) }}"
           class="events-hero__link"
           @if(!empty($highlightEvent['isExternal'])) target="_blank" rel="noopener" @endif>
          <img src="{{ esc_url($highlightEvent['image']['url']) }}"
               class="events-hero__thumbnail img-fluid img-thumbnail img-rounded"
               alt="{{ esc_attr($highlightEvent['title']) }}"
               loading="lazy">
        </a>
      @endif
    @else
      <h1>
        <span class="events-hero__subtitle">{{ __('Stay tuned', 'pixelforge') }}</span>
        {{ __('New events coming soon', 'pixelforge') }}
      </h1>
    @endif
  </div>
</section>
