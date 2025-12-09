<header id="masthead" class="main-header">

  <div class="container">

    <div class="row align-items-center">

      <div class="brand col-8 col-md-2">
        <a href="{{ esc_url(home_url('/')) }}" title="{{ esc_attr($companyProfile['name'] ?? get_bloginfo('name')) }}">
          @if (!empty($companyProfile['logo']['url']))
            <img class="img-fluid" src="{{ esc_url($companyProfile['logo']['url']) }}"
                 alt="{{ esc_attr($companyProfile['logo']['alt']) }}"
                 decoding="async"
                 loading="eager"
                 fetchpriority="high">
          @else
            <span class="main-logo__text">{{ $companyProfile['name'] ?? get_bloginfo('name') }}</span>
          @endif
        </a>
      </div>

      @if (has_nav_menu('primary_navigation'))
        <div class="nav-toggle col-4 d-md-none text-end">
          <button class="nav-toggle__button" id="navButton" type="button"
                  aria-controls="mainNav" aria-expanded="false">
            <span class="nav-toggle__icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28">
                <path fill="currentColor" d="M3 7.25C3 6.56 3.56 6 4.25 6h15.5C20.44 6 21 6.56 21 7.25S20.44 8.5 19.75 8.5H4.25C3.56 8.5 3 7.94 3 7.25zm0 4.75c0-.69.56-1.25 1.25-1.25h15.5c.69 0 1.25.56 1.25 1.25S20.44 13.25 19.75 13.25H4.25C3.56 13.25 3 12.69 3 12zm1.25 3.5c-.69 0-1.25.56-1.25 1.25S3.56 18 4.25 18h15.5c.69 0 1.25-.56 1.25-1.25S20.44 15.5 19.75 15.5z"/>
              </svg>
            </span>
            <span class="nav-toggle__label">{{ __('Menu', 'pixelforge') }}</span>
          </button>
        </div>

        <nav class="mastnav main-nav col-12 col-md-10" id="mainNav" aria-label="{{ esc_attr__('Primary navigation', 'pixelforge') }}">
          <button class="nav-close d-md-none" type="button" data-nav-close>
            <span class="nav-close__icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28">
                <path fill="currentColor" d="M6.225 4.811a1 1 0 0 0-1.414 1.414L10.586 12l-5.775 5.775a1 1 0 1 0 1.414 1.414L12 13.414l5.775 5.775a1 1 0 0 0 1.414-1.414L13.414 12l5.775-5.775a1 1 0 0 0-1.414-1.414L12 10.586z"/>
              </svg>
            </span>
            <span class="sr-only">{{ __('Close menu', 'pixelforge') }}</span>
          </button>
          {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'main-nav-list',
            'container' => false,
            'echo' => false,
          ]) !!}
        </nav>
      @endif

      <div class="quick-links col-12 col-md-3">

        <ul>
          @if (!empty($companyProfile['phone']))
            <li class="phone">
              <a href="tel:{{ $companyProfile['phone']['tel'] }}">
                <i class="fa-solid fa-circle-phone-hangup">
                  <svg width="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                    <path
                      d="M635.4 293.6l-52.73 84.25c-7.631 12.2-23.09 17.35-36.63 11.98l-105.4-42.1c-12.45-4.925-20.14-17.65-18.71-30.87l6.64-66.46C358.3 226.5 281.8 226.5 211.6 250.4l6.621 66.51c1.338 13.27-6.28 25.86-18.67 30.85l-105.5 42.13c-13.6 5.307-29.04 .2437-36.72-12.04l-52.71-84.22C-2.861 281.8-1.118 266.5 8.85 256.5C180.4 85.16 459.6 85.17 631.1 256.5C641.1 266.5 642.9 281.8 635.4 293.6z"/>
                  </svg>
                </i>
                <span>{{ $companyProfile['phone']['display'] }}</span>
              </a>
            </li>
          @endif

          @if (!empty($companyProfile['email']))
            <li class="email">
              <a href="mailto:{{ antispambot($companyProfile['email']) }}">
                <i class="fa-solid fa-circle-envelope">
                  <svg width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path
                      d="M256 352c-16.53 0-33.06-5.422-47.16-16.41L0 173.2V400C0 426.5 21.49 448 48 448h416c26.51 0 48-21.49 48-48V173.2l-208.8 162.5C289.1 346.6 272.5 352 256 352zM16.29 145.3l212.2 165.1c16.19 12.6 38.87 12.6 55.06 0l212.2-165.1C505.1 137.3 512 125 512 112C512 85.49 490.5 64 464 64h-416C21.49 64 0 85.49 0 112C0 125 6.01 137.3 16.29 145.3z"/>
                  </svg>

                </i>
                <span>{{ antispambot($companyProfile['email']) }}</span>
              </a>
            </li>
          @endif

          @if (!empty($companyProfile['mapUrl']) && !empty($companyProfile['address']))
            <li class="directions">
              <a href="{{ esc_url($companyProfile['mapUrl']) }}" target="_blank" rel="noopener">
                <i class="fa-solid fa-compass">
                  <svg width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path
                      d="M265.4 233.4c-12.5 12.5-12.5 32.75 0 45.25s32.75 12.5 45.25 0s12.5-32.77 0-45.25C298.1 220.9 277.9 220.9 265.4 233.4zM288 0C146.6 0 32 114.6 32 256c0 141.4 114.6 256 256 256s256-114.6 256-256C544 114.6 429.4 0 288 0zM414.1 156l-65.97 144.4c-2.719 5.969-9.778 13.02-15.75 15.76l-144.3 65.97c-16.66 7.609-33.81-9.547-26.19-26.2l65.97-144.3c2.719-5.984 9.781-13.05 15.78-15.78l144.3-65.97C404.6 122.3 421.8 139.4 414.1 156z"/>
                  </svg>

                </i>
                <span>{{ sprintf(__('%s, %s.', 'pixelforge'), $companyProfile['name'], $companyProfile['address']) }}</span>
              </a>
            </li>
          @elseif (!empty($companyProfile['address']))
            <li class="directions">
              <a href="{{ esc_url($companyProfile['mapUrl']) }}" target="_blank" rel="noopener">
                <i class="fa-solid fa-compass">
                  <svg width="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path
                      d="M265.4 233.4c-12.5 12.5-12.5 32.75 0 45.25s32.75 12.5 45.25 0s12.5-32.77 0-45.25C298.1 220.9 277.9 220.9 265.4 233.4zM288 0C146.6 0 32 114.6 32 256c0 141.4 114.6 256 256 256s256-114.6 256-256C544 114.6 429.4 0 288 0zM414.1 156l-65.97 144.4c-2.719 5.969-9.778 13.02-15.75 15.76l-144.3 65.97c-16.66 7.609-33.81-9.547-26.19-26.2l65.97-144.3c2.719-5.984 9.781-13.05 15.78-15.78l144.3-65.97C404.6 122.3 421.8 139.4 414.1 156z"/>
                  </svg>

                </i>
                <span>{{ $companyProfile['address'] }}</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</header>
