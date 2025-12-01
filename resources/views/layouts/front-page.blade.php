<!doctype html>
<html @php(language_attributes())>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())

  <meta name="google-site-verification" content="g4mpPHu3-MnbzKZuVELoHVfvQx5eO6xkHl3VAGdhxjY"/>
  <link rel="apple-touch-icon" sizes="180x180"
        href="<?= get_template_directory_uri(); ?>/resources/images/favicons/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32"
        href="<?= get_template_directory_uri(); ?>/resources/images/favicons/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16"
        href="<?= get_template_directory_uri(); ?>/resources/images/favicons/favicon-16x16.png">
  <link rel="manifest" href="<?= get_template_directory_uri(); ?>/resources/images/favicons/site.webmanifest">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">


  @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body @php(body_class())>
@php(wp_body_open())

<div id="app">
  <a class="sr-only focus:not-sr-only" href="#main">
    {{ __('Skip to content', 'pixelforge') }}
  </a>

  @include('sections.header')

  <main id="main" class="main main-stage">
    @yield('content')

    @include('sections.footer')
  </main>


</div>

@php(do_action('get_footer'))
@php(wp_footer())
</body>
</html>
