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
</div>

@php(do_action('get_footer'))
@php(wp_footer())
</body>
</html>
