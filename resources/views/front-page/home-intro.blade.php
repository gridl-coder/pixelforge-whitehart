<?php
$pub_location = get_post_meta(get_the_ID(), 'home_location', true);
$header_image = get_post_meta(get_the_ID(), 'home_header_image', true);
?>
<br id="home" class="stage-anchor"/>

<section class="stage-intro">

  <header class="stage-intro-header">

    <img class="stage-intro-header-image"
         src="<?= $header_image; ?>"/>

    <div class="stage-intro-header-inner">

      <h1>
        <span style="display: block; padding-top: 30px;">Welcome to</span>
        <?= get_the_title(); ?>
        <span style="display: block;"><?= $pub_location; ?></span>
      </h1>

      <div class="nav_dec"><span></span></div>
      <?= wpautop(get_the_content()); ?>


    </div>

  </header>

  <div class="stage-intro-pods" style="max-width: 1200px; margin: 0 auto;">

    <div class="row">
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-guitars"></i>
          </div>
          <h3>Live Music</h3>
        </div>
      </div>
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-tv-retro"></i>
          </div>
          <h3>Live Sports</h3>
        </div>
      </div>
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-pool-8-ball"></i>
          </div>
          <h3>Pool Table</h3>
        </div>
      </div>
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-bullseye-arrow"></i>
          </div>
          <h3>Dart Board</h3>
        </div>
      </div>

      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-family"></i>
          </div>
          <h3>Family Friendly</h3>
        </div>
      </div>
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-dog-leashed"></i>
          </div>
          <h3>Dog Friendly</h3>
        </div>
      </div>
      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-square-parking"></i>
          </div>
          <h3>Parking</h3>
        </div>
      </div>

      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-burger-soda"></i>
          </div>
          <h3>Pub Food</h3>
        </div>
      </div>

      <div class="col-4">
        <div class="inner">
          <div class="icon">
            <i class="fa-solid fa-sun-cloud"></i>
          </div>
          <h3>Beer Garden</h3>
        </div>
      </div>

    </div>

  </div>

</section>

<div class="brush-dec"></div>
