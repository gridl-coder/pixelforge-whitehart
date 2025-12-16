<!doctype html>
<html @php(language_attributes())>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())

  <meta name="google-site-verification" content="g4mpPHu3-MnbzKZuVELoHVfvQx5eO6xkHl3VAGdhxjY"/>
  <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('images/favicons/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('images/favicons/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('images/favicons/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('images/favicons/site.webmanifest') }}">

  @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body @php(body_class())>
@php(wp_body_open())

<div id="app">
  <a class="sr-only focus:not-sr-only" href="#main">
    {{ __('Skip to content', 'pixelforge') }}
  </a>

  <main id="main" class="main">
    @yield('content')
  </main>

</div>

@php(wp_footer())
</body>
</html>
