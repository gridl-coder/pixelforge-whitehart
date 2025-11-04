<header id="masthead" class="banner masthead">
  @php($logo = trim($company['logo'] ?? ''))

    @if ($logo)
    <a class="brand" href="{{ home_url('/') }}"><img src="{!! $logo !!}" alt="<?= get_bloginfo('name');?> Logo"></a>
    @else
    <h1 class="logo <?= get_bloginfo('name');?>-logo"><a class="brand" href="{{ home_url('/') }}"><?= get_bloginfo('name');?></a></h1>
    @endif

  @if (has_nav_menu('primary_navigation'))
    <nav class="nav-primary" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
      {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false]) !!}
    </nav>
  @endif

</header>
