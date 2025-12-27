@php
  $old = $feedback['old'] ?? [];
  $selectedMenu = $old['menu'] ?? ($menus[0]->ID ?? null);
  $initialSlots = $selectedMenu && isset($menuSlots[$selectedMenu]) ? $menuSlots[$selectedMenu] : [];
  $bookingProps = [
    'slots' => $menuSlots,
    'days' => $menuDays,
    'windows' => $menuWindows,
    'dayLabels' => $dayLabels,
    'minDate' => $minDate,
    'messages' => [
      'unavailableDate' => __('Selected date is unavailable for this menu.', 'pixelforge'),
      'availabilityNotSet' => __('Service times not set', 'pixelforge'),
      'unavailableSection' => __('Selected area is fully booked for this date.', 'pixelforge'),
      'submissionError' => __('We could not submit your booking right now. Please try again in a moment.', 'pixelforge'),
    ],
  ];
@endphp

<section class="booking-form" data-booking-props='@json($bookingProps)'>
  <div class="booking-form__alert booking-form__alert--error @if(! empty($feedback['errors'])) is-visible @endif" data-alert="error" role="alert" aria-live="polite">
    <button class="booking-form__alert-close" type="button" aria-label="{{ __('Close alert', 'pixelforge') }}">&times;</button>
    <div class="booking-form__alert-content">
      <p class="booking-form__alert-title">{{ __('There was a problem with your booking:', 'pixelforge') }}</p>
      <div class="booking-form__alert-body" data-alert-body="error">
        @if(! empty($feedback['errors']))
          <ul class="booking-form__alert-list">
            @foreach($feedback['errors'] as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>

  <div class="booking-form__alert booking-form__alert--success @if(! empty($feedback['success'])) is-visible @endif" data-alert="success" role="alert" aria-live="polite">
    <button class="booking-form__alert-close" type="button" aria-label="{{ __('Close alert', 'pixelforge') }}">&times;</button>
    <div class="booking-form__alert-content">
      <div class="booking-form__alert-body" data-alert-body="success">
        @if(! empty($feedback['success']))
          {!! $feedback['success'] !!}
        @endif
      </div>
    </div>
  </div>

  @if(empty($sections) || empty($menus))
    <p class="booking-form__notice">{{ __('Add at least one section, table, and booking menu in the dashboard to enable table bookings.', 'pixelforge') }}</p>
  @else
    <form method="post" class="booking-form__form" novalidate data-ajax-url="{{ admin_url('admin-ajax.php') }}">
      <input type="hidden" name="pixelforge_booking_form" value="1">
      @php(wp_nonce_field(\PixelForge\Bookings\NONCE_ACTION, 'pixelforge_booking_nonce'))

      <ol class="booking-form__progress">
        <li class="booking-form__progress-step is-active">{{ __('Reservation', 'pixelforge') }}</li>
        <li class="booking-form__progress-step">{{ __('Your details', 'pixelforge') }}</li>
        <li class="booking-form__progress-step">{{ __('Notes', 'pixelforge') }}</li>
      </ol>

      <div class="booking-form__steps">
        <fieldset class="booking-form__step is-active" data-step="1">
          <legend class="booking-form__step-title">{{ __('Book your table', 'pixelforge') }}</legend>
          <p class="booking-form__step-hint">{{ sprintf(__('Bookings last %d minutes.', 'pixelforge'), \PixelForge\Bookings\BOOKING_SLOT_MINUTES) }}</p>

          <div class="row g-3">
            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Party Size', 'pixelforge') }}</span>
              <select class="booking-form__input form-select" name="pixelforge_booking_party_size" required>
                @for ($partySize = 2; $partySize <= 12; $partySize += 1)
                  <option value="{{ $partySize }}" @selected(($old['party_size'] ?? 2) === $partySize)>
                    {{ sprintf(_n('%d guest', '%d guests', $partySize, 'pixelforge'), $partySize) }}
                  </option>
                @endfor
              </select>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Menu', 'pixelforge') }}</span>
              <select class="booking-form__input form-select" name="pixelforge_booking_menu" id="pixelforge_booking_menu" required>
                @foreach($menus as $menu)
                  <option value="{{ $menu->ID }}" @selected(($old['menu'] ?? $menus[0]->ID ?? null) === $menu->ID)>{{ $menu->post_title }}</option>
                @endforeach
              </select>
              <p class="booking-form__meta" id="booking_menu_meta" aria-live="polite"></p>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Area', 'pixelforge') }}</span>
              <select class="booking-form__input form-select" name="pixelforge_booking_section" required>
                @foreach($sections as $section)
                  <option value="{{ $section->ID }}" @selected(($old['section'] ?? null) === $section->ID)>{{ $section->post_title }}</option>
                @endforeach
              </select>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Date', 'pixelforge') }}</span>
              <input class="booking-form__input form-control" type="date" name="pixelforge_booking_date" value="{{ $old['date'] ?? '' }}" min="{{ $minDate }}" required>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Time', 'pixelforge') }}</span>
              <select class="booking-form__input form-select" name="pixelforge_booking_time" id="pixelforge_booking_time" required>
                @foreach($initialSlots as $slot)
                  <option value="{{ $slot }}" @selected(($old['time'] ?? null) === $slot)>{{ $slot }}</option>
                @endforeach
              </select>
            </label>

            <div class="col-12">
              <p class="booking-form__notice booking-form__notice--availability" id="booking_availability_notice" aria-live="polite"></p>
            </div>
          </div>

          <div class="booking-form__actions">
            <button class="booking-form__nav booking-form__nav--next btn btn-primary" type="button">{{ __('Next', 'pixelforge') }}</button>
          </div>
        </fieldset>

        <fieldset class="booking-form__step" data-step="2">
          <legend class="booking-form__step-title">{{ __('Tell us about you', 'pixelforge') }}</legend>
          <p class="booking-form__step-hint">{{ __('We will use these details to confirm your booking and keep you updated.', 'pixelforge') }}</p>

          <div class="row g-3">
            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Name', 'pixelforge') }}</span>
              <input class="booking-form__input form-control" type="text" name="pixelforge_booking_name" value="{{ $old['name'] ?? '' }}" required>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Email', 'pixelforge') }}</span>
              <input class="booking-form__input form-control" type="email" name="pixelforge_booking_email" value="{{ $old['email'] ?? '' }}" required>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Phone', 'pixelforge') }}</span>
              <input class="booking-form__input form-control" type="tel" name="pixelforge_booking_phone" value="{{ $old['phone'] ?? '' }}" required>
            </label>
          </div>

          <div class="booking-form__actions">
            <button class="booking-form__nav booking-form__nav--prev btn btn-outline-light" type="button">{{ __('Back', 'pixelforge') }}</button>
            <button class="booking-form__nav booking-form__nav--next btn btn-primary" type="button">{{ __('Next', 'pixelforge')}}</button>
          </div>
        </fieldset>

        <fieldset class="booking-form__step" data-step="3">
          <legend class="booking-form__step-title">{{ __('Add a note (optional)', 'pixelforge') }}</legend>
          <p class="booking-form__step-hint">{{ __('Tell us about allergies, accessibility needs, or anything else we should know.', 'pixelforge') }}</p>

          <label class="booking-form__field">
            <span class="booking-form__label form-label">{{ __('Notes', 'pixelforge') }}</span>
            <textarea class="booking-form__input form-control" name="pixelforge_booking_notes" rows="4">{{ $old['notes'] ?? '' }}</textarea>
          </label>

          <p class="booking-form__notice mt-1">{{ __('Only one active booking is allowed per customer. If you need to arrange more than one booking, please call us.', 'pixelforge') }}</p>

          <label class="booking-form__hp" style="position: absolute; left: -9999px;">
            {{ __('Leave this field empty', 'pixelforge') }}
            <input type="text" name="pixelforge_booking_hp" tabindex="-1" autocomplete="off">
          </label>

          <div class="booking-form__actions">
            <button class="booking-form__nav booking-form__nav--prev btn btn-outline-light" type="button">{{ __('Back', 'pixelforge') }}</button>
            <button class="booking-form__submit btn btn-primary mt-2" type="submit">{{ __('Book Table', 'pixelforge') }}</button>
          </div>
        </fieldset>
      </div>
    </form>
  @endif
</section>
