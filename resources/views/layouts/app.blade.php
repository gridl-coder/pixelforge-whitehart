<!doctype html>
<html @php(language_attributes())>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())

  <meta name="google-site-verification" content="g4mpPHu3-MnbzKZuVELoHVfvQx5eO6xkHl3VAGdhxjY"/>
  <link rel="manifest" href="<?= get_template_directory_uri(); ?>/public/build/manifest.json">

  @if (!empty($seasonalStyles['enabled']))
    @vite('resources/css/christmas.scss')
  @endif


  @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body @php(body_class())>
@php(wp_body_open())

<div id="app">
  <a class="sr-only focus:not-sr-only" href="#main">
    {{ __('Skip to content', 'pixelforge') }}
  </a>

  @include('sections.header')

  <main id="main" class="main">
    <div class="container">
      @yield('content')
    </div>
  </main>

  @hasSection('sidebar')
    <aside class="sidebar">
      @yield('sidebar')
    </aside>
  @endif

  @include('sections.footer')

  <button id="back-to-top" class="back-to-top" title="Go to top" hidden>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
      <path d="M11.293 8.293a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L13 11.414V19a1 1 0 11-2 0v-7.586L8.707 13.707a1 1 0 01-1.414-1.414l4-4zM4 5a1 1 0 110-2h16a1 1 0 110 2H4z"/>
    </svg>
    <span class="sr-only">{{ __('Back to top', 'pixelforge') }}</span>
  </button>
</div>

@php(do_action('get_footer'))
@php(wp_footer())
</body>
</html>
