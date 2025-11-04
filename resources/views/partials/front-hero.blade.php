<section class="front-hero">
  <div class="front-hero__inner">
    <div class="front-hero__copy">
      <h1 class="front-hero__title">{{ $hero['title'] ?? get_bloginfo('name', 'display') }}</h1>

      @if (! empty($hero['text']))
        <p class="front-hero__text">{{ $hero['text'] }}</p>
      @endif
    </div>

    @php($heroImage = $hero['image'] ?? [])
    @if (! empty($heroImage['url']))
      <figure class="front-hero__media">
        <img
          src="{{ esc_url($heroImage['url']) }}"
          alt="{{ esc_attr($heroImage['alt'] ?? ($hero['title'] ?? get_bloginfo('name', 'display'))) }}"
          loading="lazy"
        >
      </figure>
    @endif
  </div>
</section>
