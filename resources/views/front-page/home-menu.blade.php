<section class="food-banner">

  <div class="container food-banner__container">

    <div class="row">


      <div class="col-12 food-banner-content">

        <div class="boxed-container boxed-container--muted">

          <div class="boxed-content-item text-start">
            <h1>{{ __('Food Service Times', 'pixelforge') }}</h1>
            @foreach ($serviceTimes as $time)
              <p><strong>{{ $time['label'] }}</strong><br/>{{ $time['hours'] }}</p>
            @endforeach

            <a href="#table-booking" class="btn">Book a table</a>
          </div>


        </div>
        <div class="nav_dec"><span></span></div>
        @if (!empty($alesImage['url']))
          <img src="{{ esc_url($alesImage['url']) }}" class="img-fluid"
               alt="{{ esc_attr($alesImage['alt']) }}" loading="lazy">
        @endif
        <div class="nav_dec"><span></span></div>

        @if (!empty($guestPopup['url']))
          <img class="img-fluid food-banner__poster" src="{{ esc_url($guestPopup['url']) }}"
               alt="{{ esc_attr($guestPopup['alt']) }}" loading="lazy"/>
        @endif

      </div>
    </div>
  </div>
</section>
<div class="brush-dec2"></div>

<div class="container text-center pt-5 pb-5">
  <h1>
    Our Menus
  </h1>
  <div class="section-separator">
      <span><svg fill="#cbba57" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 75" width="60" height="60"
                 aria-hidden="true" focusable="false">
  <g data-name="Layer 1">
    <path
      d="M51,29H42.3103C52.32666,21.657,56,9.986,56,1a1,1,0,0,0-2,0,34.41553,34.41553,0,0,1-.727,6.89282A11.40928,11.40928,0,0,0,50.457,3.293.99989.99989,0,0,0,49.043,4.707a10.35162,10.35162,0,0,1,2.92578,7.82422c0,.00952.00519.0174.00543.02686a33.85354,33.85354,0,0,1-1.45154,3.49377A10.39416,10.39416,0,0,0,47.457,10.293.99989.99989,0,0,0,46.043,11.707c1.623,1.623,2.73669,3.65424,2.7644,7.407a29.90475,29.90475,0,0,1-8.32874,8.7569,13.13361,13.13361,0,0,0-.52991-7.18732,1.0001,1.0001,0,1,0-1.89746.63281c.871,2.61285.9176,4.3855.15521,7.68359h-16.413c-.76239-3.2981-.71576-5.07074.15521-7.68359a1.0001,1.0001,0,0,0-1.89746-.63281,13.13361,13.13361,0,0,0-.52991,7.18732,29.93606,29.93606,0,0,1-8.51465-9.06006c.03571-3.68231,1.05841-5.46191,2.70032-7.10382A.99989.99989,0,0,0,12.293,10.293a10.29142,10.29142,0,0,0-2.97528,5.4057,34.01378,34.01378,0,0,1-1.32245-3.222c.00018-.0083.00476-.0152.00476-.02356,0-3.48486.835-5.874,2.707-7.74609A.99989.99989,0,0,0,9.293,3.293a10.498,10.498,0,0,0-2.6156,4.34076A34.37234,34.37234,0,0,1,6,1,1,1,0,0,0,4,1C4,9.986,7.67334,21.657,17.6897,29H9a.99959.99959,0,0,0-.98633,1.16455c.50342,3.022,1.74854,5.12549,3.70068,6.252,2.20605,1.27344,5.20313,1.28906,9.13135.04248-.81348,8.76807.54053,10.43359,2.09521,12.34717,1.09326,1.34521,2.22363,2.73633,2.81592,7.54395A4.17888,4.17888,0,0,0,29.91113,60h.17773a4.17888,4.17888,0,0,0,4.1543-3.6499c.59229-4.80762,1.72266-6.19873,2.81592-7.54395,1.55469-1.91357,2.90869-3.5791,2.09521-12.34717,3.92871,1.24707,6.92676,1.23193,9.13135-.04248,1.95215-1.12646,3.19727-3.23,3.70068-6.252A.99959.99959,0,0,0,51,29Zm-3.71387,5.68457c-1.90088,1.09814-4.9082.88965-8.93506-.62109a1,1,0,0,0-1.34473,1.04688c1.03467,9.31543-.0835,10.69189-1.49951,12.43457-1.15234,1.418-2.58594,3.18262-3.249,8.561A2.1761,2.1761,0,0,1,30.08887,58h-.17773a2.17611,2.17611,0,0,1-2.16895-1.89453c-.66309-5.37793-2.09668-7.14258-3.249-8.56055-1.416-1.74268-2.53418-3.11914-1.49951-12.43457a1.00018,1.00018,0,0,0-1.34473-1.04687c-4.02637,1.50977-7.03271,1.71875-8.93506.62109A5.63748,5.63748,0,0,1,10.23438,31H49.76563A5.63748,5.63748,0,0,1,47.28613,34.68457Z"></path>
  </g>
</svg>
</span>
  </div>
  <?= do_shortcode('[pixelforge_booking_menus]'); ?>
  <a href="#table-booking" class="btn mb-3 mt-3">Book a table</a>


</div>
<div class="brush-dec"></div>

<section class="stage-menu">

  <br id="menu" class="stage-anchor"/>

  <div class="boxed-container boxed-container--menu">

    <div class="boxed-content-item">

      <h1>
        <span class="subtitle"> Food from the Hart </span>
        Main Menu
      </h1>

      <p class="lead" style="max-width: 700px; display: block; margin: 30px auto"> At The White Hart Inn you’ll
        find a hearty Food Menu alongside a well - stocked and lively bar, large screen TV, pool table and
        live music on occasion for a vibrant social experience .</p>

      <div class="section-separator">
    <span>
        <svg fill="#cbba57" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 75" x="0px" y="0px" width="60"
             height="60">
    <g data - name="Layer 1">
        <path
          d="M51,29H42.3103C52.32666,21.657,56,9.986,56,1a1,1,0,0,0-2,0,34.41553,34.41553,0,0,1-.727,6.89282A11.40928,11.40928,0,0,0,50.457,3.293.99989.99989,0,0,0,49.043,4.707a10.35162,10.35162,0,0,1,2.92578,7.82422c0,.00952.00519.0174.00543.02686a33.85354,33.85354,0,0,1-1.45154,3.49377A10.39416,10.39416,0,0,0,47.457,10.293.99989.99989,0,0,0,46.043,11.707c1.623,1.623,2.73669,3.65424,2.7644,7.407a29.90475,29.90475,0,0,1-8.32874,8.7569,13.13361,13.13361,0,0,0-.52991-7.18732,1.0001,1.0001,0,1,0-1.89746.63281c.871,2.61285.9176,4.3855.15521,7.68359h-16.413c-.76239-3.2981-.71576-5.07074.15521-7.68359a1.0001,1.0001,0,0,0-1.89746-.63281,13.13361,13.13361,0,0,0-.52991,7.18732,29.93606,29.93606,0,0,1-8.51465-9.06006c.03571-3.68231,1.05841-5.46191,2.70032-7.10382A.99989.99989,0,0,0,12.293,10.293a10.29142,10.29142,0,0,0-2.97528,5.4057,34.01378,34.01378,0,0,1-1.32245-3.222c.00018-.0083.00476-.0152.00476-.02356,0-3.48486.835-5.874,2.707-7.74609A.99989.99989,0,0,0,9.293,3.293a10.498,10.498,0,0,0-2.6156,4.34076A34.37234,34.37234,0,0,1,6,1,1,1,0,0,0,4,1C4,9.986,7.67334,21.657,17.6897,29H9a.99959.99959,0,0,0-.98633,1.16455c.50342,3.022,1.74854,5.12549,3.70068,6.252,2.20605,1.27344,5.20313,1.28906,9.13135.04248-.81348,8.76807.54053,10.43359,2.09521,12.34717,1.09326,1.34521,2.22363,2.73633,2.81592,7.54395A4.17888,4.17888,0,0,0,29.91113,60h.17773a4.17888,4.17888,0,0,0,4.1543-3.6499c.59229-4.80762,1.72266-6.19873,2.81592-7.54395,1.55469-1.91357,2.90869-3.5791,2.09521-12.34717,3.92871,1.24707,6.92676,1.23193,9.13135-.04248,1.95215-1.12646,3.19727-3.23,3.70068-6.252A.99959.99959,0,0,0,51,29Zm-3.71387,5.68457c-1.90088,1.09814-4.9082.88965-8.93506-.62109a1,1,0,0,0-1.34473,1.04688c1.03467,9.31543-.0835,10.69189-1.49951,12.43457-1.15234,1.418-2.58594,3.18262-3.249,8.561A2.1761,2.1761,0,0,1,30.08887,58h-.17773a2.17611,2.17611,0,0,1-2.16895-1.89453c-.66309-5.37793-2.09668-7.14258-3.249-8.56055-1.416-1.74268-2.53418-3.11914-1.49951-12.43457a1.00018,1.00018,0,0,0-1.34473-1.04687c-4.02637,1.50977-7.03271,1.71875-8.93506.62109A5.63748,5.63748,0,0,1,10.23438,31H49.76563A5.63748,5.63748,0,0,1,47.28613,34.68457Z"/>
    </g>
</svg>
    </span>
      </div>
      <p class="menu-times-main">
        Our Main Menu is served <strong>Mon / Tue / Wed / Thur: 9:00 - 13:00 </strong><br/>
        <a href="#table-booking" class="btn mb-3">Book a table</a><br/>

        <em> Takeaway Service Available.</em>
      </p>
      <div class="nav_dec"><span></span></div>

      <div class="menu--main menu">

        <div class="row">

          <div class="col-md-6">

            <div class="menu-section menu-section--left">

              <h3>Breakfast </h3>
              <div class="menu-item">
                <h4>Small Breakfast <span class="price"> £9.50 </span></h4>
                <p>Sausage, Bacon, Egg (Fried, Poached or Scrambled), Beans, Tomatoes, Mushrooms, Hash Brown &
                  Toast.</p>
              </div>

              <div class="menu-item">
                <h4>Large Breakfast <span class="price"> £12.50 </span></h4>
                <p>2 Sausages, 2 Bacon, 2 Eggs (Fried, Poached or Scrambled), Tomatoes, Mushrooms, Hash Browns, Bubble &
                  Squeak, Beans,
                  Black Pudding & Toast</p>
              </div>

              <div class="menu-item">
                <h4>Tradesmans Breakfast <span class="price"> £15.50 </span></h4>
                <p>3 Sausages, 3 Bacon, 3 Eggs (Fried, Poached or Scrambled), Tomatoes, Mushrooms, Hash Browns, Bubble &
                  Squeak, Beans,
                  Black Pudding & Toast</p>
              </div>

              <div class="menu-item">
                <h4>Vegetarian Breakfast <span class="price"> £9.00 </span></h4>
                <p>Vegetarian Sausage, 2 Eggs (Fried, Poached or Scrambled), Tomatoes, Mushrooms, Hash Browns, Bubble &
                  Squeak, Beans.</p>
              </div>

              <div class="menu-item">
                <h4>Eggs Benedict <span class="price">£7.00 </span></h4>
                <p>Poached Eggs & Ham served on Toast, topped with Homemade Hollandaise Sauce.</p>
              </div>
              <div class="menu-item">
                <h4>Eggs Florentine <span class="price">£6.50 </span></h4>
                <p>Poached Eggs served on Toast, topped with Spinach & Homemade Hollandaise Sauce.</p>
              </div>

              <div class="menu-item">
                <h4>Smoked Kipper <span class="price">£6.00 </span></h4>
                <p>Smoked Kipper served with a Poached egg and slice of Toast.</p>
              </div>

              <div class="menu-item">
                <h4>Smoked Haddock <span class="price">£6.00 </span></h4>
                <p>Smoked Haddock served with a Poached egg and slice of Toast.</p>
              </div>
            </div>

          </div>

          <div class="col-md-6">
            <div class=" menu-section menu-section--right">
              <h3>Kids Breakfast</h3>
              <div class="menu-item">
                <h4>Pancakes with Maple <span class="price"> £5.00 </span></h4>
                <p>Add Bacon for £1</p>
              </div>
              <div class="menu-item">
                <h4>Kids 3 Item Breakfast <span class="price"> £5.50 </span></h4>
                <p>Choose 3 items from the Breakfast Menu</p>
              </div>

              <div class="menu-item">
                <h4>Kids Beans on Toast <span class="price"> £4.00 </span></h4>
              </div>
              <div class="menu-item">
                <h4>Kids Cheese on Toast <span class="price"> £4.00 </span></h4>
              </div>
              <div class="menu-item">
                <h4>Kids Egg on Toast <span class="price"> £4.00 </span></h4>
              </div>

            </div>

            <div class="menu-section menu-section--right">

              <h3> Sandwiches / Toasties / Paninis </h3>
              <p><small> Served with Side Salad & Tortilla Chips .</small></p>
              <div class="menu-item">
                <h4> Tuna Mayo <span class="price"> £5.95 </span></h4>
              </div>
              <div class="menu-item">
                <h4> BLT <span class="price"> £5.95 </span></h4>
              </div>
              <div class="menu-item">
                <h4> Bacon, Brie &amp; Cranberry <span class="price"> £5.95 </span></h4>
              </div>
              <div class="menu-item">
                <h4> Breakfast Club <span class="price"> £5.95 </span></h4>
              </div>
              <div class="menu-item">
                <h4> Ham &amp; Cheese <span class="price"> £5.95 </span></h4>
              </div>

            </div>

            <div class="menu-section menu-section--right">

              <h3> Snack Boxes with Chips </h3>
              <div class="menu-item">
                <h4> Chicken Strips <span class="price"> £4.95 </span></h4>
                <h4> Cod Bites <span class="price"> £4.95 </span></h4>
                <h4> Popcorn Chicken <span class="price"> £4.95 </span></h4>
                <h4> BBQ Chicken Wings <span class="price"> £4.95 </span></h4>
                <h4> Sweet Chilli Chicken Wings <span class="price"> £4.95 </span></h4>

              </div>
            </div>

          </div>
        </div>
        <a href="#table-booking" class="btn mb-3">Book a table</a>

        <div>
          <p><strong> Allergen Information:</strong> Our kitchen deals with a variety of allergens
            so we are unable to cater for people with specific allergies or special dietry
            requirements .</p>


        </div>

      </div>
    </div>
  </div>
</section>

<div class="brush-dec2"></div>
