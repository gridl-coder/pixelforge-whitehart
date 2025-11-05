<div class="main-logo">
  <a href="{{ esc_url(home_url('/')) }}" title="{{ esc_attr($companyProfile['name'] ?? get_bloginfo('name')) }}">
    @if (!empty($companyProfile['logo']['url']))
      <img class="img-fluid" src="{{ esc_url($companyProfile['logo']['url']) }}" alt="{{ esc_attr($companyProfile['logo']['alt']) }}">
    @else
      <span class="main-logo__text">{{ $companyProfile['name'] ?? get_bloginfo('name') }}</span>
    @endif
  </a>
</div>

<header class="main-header">
  <div class="main-header-container">
    <div class="main-nav-wrapper">
      <div class="nav_dec"><span></span></div>

      @if (has_nav_menu('primary_navigation'))
        <nav class="main-nav" id="mainNav" aria-label="{{ esc_attr__('Primary navigation', 'pixelforge') }}">
          {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'main-nav-list',
            'echo' => false,
          ]) !!}
        </nav>
      @endif

      <div class="nav_dec"><span></span></div>
    </div>

    <div class="main-header-contacts">
      <div class="main-header-contacts-wrap">
        <ul>
          @if (!empty($companyProfile['phone']))
            <li class="phone">
              <a href="tel:{{ $companyProfile['phone']['tel'] }}">
                <i class="fa-solid fa-circle-phone-hangup"></i>
                <span>{{ $companyProfile['phone']['display'] }}</span>
              </a>
            </li>
          @endif

          @if (!empty($companyProfile['email']))
            <li class="email">
              <a href="mailto:{{ antispambot($companyProfile['email']) }}">
                <i class="fa-solid fa-circle-envelope"></i>
                <span>{{ antispambot($companyProfile['email']) }}</span>
              </a>
            </li>
          @endif

          @if (!empty($companyProfile['mapUrl']) && !empty($companyProfile['address']))
            <li class="directions">
              <a href="{{ esc_url($companyProfile['mapUrl']) }}" target="_blank" rel="noopener">
                <i class="fa-solid fa-compass"></i>
                <span>{{ sprintf(__('%s, %s.', 'pixelforge'), $companyProfile['name'], $companyProfile['address']) }}</span>
              </a>
            </li>
          @elseif (!empty($companyProfile['address']))
            <li class="directions">
              <span>
                <i class="fa-solid fa-compass"></i>
                <span>{{ $companyProfile['address'] }}</span>
              </span>
            </li>
          @endif
        </ul>
      </div>

      @if (!empty($companyProfile['social']))
        <div class="header-social">
          @foreach ($companyProfile['social'] as $network => $url)
            <a href="{{ esc_url($url) }}" target="_blank" rel="noopener" aria-label="{{ esc_attr(sprintf(__('Follow us on %s', 'pixelforge'), ucfirst($network))) }}">
              <i class="fa-brands fa-{{ $network === 'facebook' ? 'facebook-f' : $network }}"></i>
            </a>
          @endforeach
        </div>
      @endif

      <div class="nav-separator"><span></span></div>
    </div>

    <button type="button"
            class="nav-button-wrap"
            id="navButton"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="{{ esc_attr__('Toggle navigation', 'pixelforge') }}">
      <span class="nav-button" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </span>
    </button>
  </div>
</header>
