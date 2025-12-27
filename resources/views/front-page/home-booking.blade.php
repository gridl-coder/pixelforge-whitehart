<div class="booking-form-container">

  <div class="container pt-5" id="table-booking">
    <h1>
      <span class="subtitle">Eat at The White Hart.</span>
      Table Booking
    </h1>

    @include('components.section-separator')

    <div style="max-width: 840px; margin: 0 auto 50px auto; text-align: left;">

      <?= do_shortcode('[pixelforge_table_booking]'); ?>

      <p class="text-center">
        <a href="https://whitehartbodmin.square.site/" class="btn mb-3 mt-3" target="_blank" rel="noopener noreferrer">Order
          Food Delivery</a>
      </p>
    </div>
  </div>
</div>

