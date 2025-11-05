<?php
$logo = trim($company['logo'] ?? '');
$phone = trim($company['phone'] ?? '');
$email = trim($company['email'] ?? '');
$address = trim($company['address'] ?? '');
$facebook = trim($company['facebook'] ?? '');
$instagram = trim($company['instagram'] ?? '');
$maps_link = trim($company['maps_link'] ?? '');
?>

<div class="main-logo">
  <a href="/" title="<?= esc_attr(get_bloginfo('name')); ?>>">
    <?php
    if (!empty($logo)) {
      // If you store file ID or array, adapt accordingly. CMB2 file field returns array by default
      if (is_array($logo) && isset($logo['url'])) {
        echo '<img class="img-fluid" src="' . esc_url($logo['url']) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
      } else {
        echo '<img class="img-fluid" src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
      }
    } ?>
  </a>
</div>


<header class="main-header">

  <div class="main-header-container">

    <div class="main-nav-wrapper">

      <div class="nav_dec"><span></span></div>

      @if (has_nav_menu('primary_navigation'))
        <nav class="main-nav" id="mainNav">
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'main-nav-list', 'echo' => false]) !!}
        </nav>
      @endif

      <div class="nav_dec"><span></span></div>

    </div>

    <div class="main-header-contacts">
      <div class="main-header-contacts-wrap">
        <ul>
          <li class="phone"><a href="tel:<?= $phone; ?>"><i
                class="fa-solid fa-circle-phone-hangup"></i><span><?= $phone; ?></span></a>
          </li>
          <li class="email">
            <a href="mailto:<?= $email; ?>">
              <i class="fa-solid fa-circle-envelope"></i>
              <span><?= $email; ?></span>
            </a>
          </li>
          <li class="directions"><a href="<?= $maps_link;?>" target="_blank">
              <i class="fa-solid fa-compass"></i><span>The White Hart, <?= $address; ?>.</span></a>
          </li>
        </ul>
      </div>
      <div class="header-social">
        <a href="<?= $facebook; ?>" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="<?= $instagram; ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
      </div>
      <div class="nav-separator"><span></span></div>
    </div>

    <a class="nav-button-wrap" id="navButton" href="#">

      <div class="nav-button h_mn_menu">
        <span></span><span></span><span></span>
      </div>
    </a>

  </div>

</header>
