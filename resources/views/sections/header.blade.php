<header id="masthead" class="main-header">

  <div class="container">

    <div class="row">

      <div class="brand col-4 col-md-2">
        <a href="{{ esc_url(home_url('/')) }}" title="{{ esc_attr($companyProfile['name'] ?? get_bloginfo('name')) }}">
          @if (!empty($companyProfile['logo']['url']))
            <img class="img-fluid" src="{{ esc_url($companyProfile['logo']['url']) }}"
                 alt="{{ esc_attr($companyProfile['logo']['alt']) }}">
          @else
            <span class="main-logo__text">{{ $companyProfile['name'] ?? get_bloginfo('name') }}</span>
          @endif
        </a>
      </div>

      @if (has_nav_menu('primary_navigation'))
        <nav class="mastnav col-8 col-md-10" id="mastnav" aria-label="{{ esc_attr__('Primary navigation', 'pixelforge') }}">
          {!! wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'menu_class' => 'main-nav-list',
            'echo' => false,
          ]) !!}
        </nav>
      @endif

      <div class="quick-links col-md-3 d-md-none">

        <ul>
          @if (!empty($companyProfile['phone']))
            <li class="phone">
              <a href="tel:{{ $companyProfile['phone']['tel'] }}">
                <i class="fa-solid fa-circle-phone-hangup">
                  <svg width="34" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
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
                  <svg width="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
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
                  <svg width="30" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
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
                  <svg width="34" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
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
