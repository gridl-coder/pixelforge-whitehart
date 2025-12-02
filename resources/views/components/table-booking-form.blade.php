@php
  $old = $feedback['old'] ?? [];
  $selectedMenu = $old['menu'] ?? ($menus[0]->ID ?? null);
  $initialSlots = $selectedMenu && isset($menuSlots[$selectedMenu]) ? $menuSlots[$selectedMenu] : [];
@endphp

<section class="booking-form">
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
    <div class="booking-form__alert-body" data-alert-body="success">
      @if(! empty($feedback['success']))
        {!! $feedback['success'] !!}
      @endif
    </div>
  </div>

  @if(empty($sections) || empty($menus))
    <p class="booking-form__notice">{{ __('Add at least one section, table, and booking menu in the dashboard to enable table bookings.', 'pixelforge') }}</p>
  @else
    <form method="post" class="booking-form__form" novalidate data-ajax-url="{{ admin_url('admin-ajax.php') }}">
      <input type="hidden" name="pixelforge_booking_form" value="1">
      @php(wp_nonce_field(\PixelForge\Bookings\NONCE_ACTION, 'pixelforge_booking_nonce'))

      <ol class="booking-form__progress">
        <li class="booking-form__progress-step is-active">{{ __('Your details', 'pixelforge') }}</li>
        <li class="booking-form__progress-step">{{ __('Reservation', 'pixelforge') }}</li>
        <li class="booking-form__progress-step">{{ __('Notes', 'pixelforge') }}</li>
      </ol>

      <div class="booking-form__steps">
        <fieldset class="booking-form__step is-active" data-step="1">
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
            <button class="booking-form__nav booking-form__nav--next btn btn-primary" type="button">{{ __('Next', 'pixelforge') }}</button>
          </div>
        </fieldset>

        <fieldset class="booking-form__step" data-step="2">
          <legend class="booking-form__step-title">{{ __('Choose your table', 'pixelforge') }}</legend>
          <p class="booking-form__step-hint">{{ sprintf(__('Bookings last %d minutes so everyone gets time to savour their meal.', 'pixelforge'), \PixelForge\Bookings\BOOKING_SLOT_MINUTES) }}</p>

          <div class="row g-3">
            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Party Size', 'pixelforge') }}</span>
              <input class="booking-form__input form-control" type="number" min="1" step="1" name="pixelforge_booking_party_size" value="{{ $old['party_size'] ?? '' }}" required>
            </label>

            <label class="booking-form__field col-md-4">
              <span class="booking-form__label form-label">{{ __('Menu', 'pixelforge') }}</span>
              <select class="booking-form__input form-select" name="pixelforge_booking_menu" id="pixelforge_booking_menu" required>
                @foreach($menus as $menu)
                  <option value="{{ $menu->ID }}" @selected(($old['menu'] ?? $menus[0]->ID ?? null) === $menu->ID)>{{ $menu->post_title }}</option>
                @endforeach
              </select>
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
            <button class="booking-form__nav booking-form__nav--prev btn btn-outline-light" type="button">{{ __('Back', 'pixelforge') }}</button>
            <button class="booking-form__nav booking-form__nav--next btn btn-primary" type="button">{{ __('Next', 'pixelforge') }}</button>
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

    <script>
      jQuery(($) => {
        const form = $('.booking-form__form');
        const steps = form.find('.booking-form__step');
        const progressSteps = form.find('.booking-form__progress-step');
        const menuSelect = $('#pixelforge_booking_menu');
        const timeSelect = $('#pixelforge_booking_time');
        const sectionSelect = form.find('select[name="pixelforge_booking_section"]');
        const dateInput = form.find('input[name="pixelforge_booking_date"]');
        const partyInput = form.find('input[name="pixelforge_booking_party_size"]');
        const notice = $('#booking_availability_notice');
        const slots = @json($menuSlots);
        const ajaxUrl = form.data('ajax-url');
        const unavailableDateMessage = @json(__('Selected date is unavailable for this menu.', 'pixelforge'));
        const unavailableSectionMessage = @json(__('Selected area is fully booked for this date.', 'pixelforge'));
        const alerts = {
          error: $('.booking-form__alert--error'),
          success: $('.booking-form__alert--success'),
        };

        let currentStep = 0;

        const hideAlerts = () => {
          Object.values(alerts).forEach((alert) => alert.removeClass('is-visible'));
        };

        $('.booking-form__alert-close').on('click', (event) => {
          $(event.currentTarget).closest('.booking-form__alert').removeClass('is-visible');
        });

        const renderAlert = (type, content) => {
          hideAlerts();

          if (!alerts[type]) {
            return;
          }

          if (!content) {
            return;
          }

          const body = alerts[type].find(`[data-alert-body="${type}"]`);

          if (Array.isArray(content)) {
            const list = $('<ul/>').addClass('booking-form__alert-list');
            content.forEach((item) => list.append($('<li/>').text(item)));
            body.html(list);
          } else {
            body.html(content);
          }

          alerts[type].addClass('is-visible');
        };

        const setNotice = (text) => {
          notice.text(text || '');
        };

        const rebuildTimes = (availableSlots = []) => {
          const menuId = menuSelect.val();
          const menuSlots = slots[menuId] || [];
          const previous = timeSelect.val();

          timeSelect.empty();

          menuSlots.forEach((slot) => {
            const option = $('<option/>').val(slot).text(slot);

            if (availableSlots.length > 0 && !availableSlots.includes(slot)) {
              option.prop('disabled', true);
              option.text(`${slot} ({{ __('Fully booked', 'pixelforge') }})`);
            }

            if (slot === previous && !option.prop('disabled')) {
              option.prop('selected', true);
            }

            timeSelect.append(option);
          });

          if (!timeSelect.find('option:selected').length) {
            const firstAvailable = timeSelect.find('option:not([disabled])').first();

            if (firstAvailable.length) {
              firstAvailable.prop('selected', true);
            }
          }
        };

        const setSectionsAvailability = (availableSections = []) => {
          const availableIds = availableSections.map(String);

          sectionSelect.find('option').each(function setOption() {
            $(this).prop('disabled', !availableIds.includes($(this).val()));
          });

          if (!availableIds.includes(sectionSelect.val()) && availableIds.length > 0) {
            sectionSelect.val(availableIds[0]);
          }
        };

        const applyAvailability = (data) => {
          if (!data) {
            rebuildTimes();
            return;
          }

          if (data.unavailableDate) {
            dateInput.addClass('booking-form__input--unavailable');
            dateInput.get(0).setCustomValidity(unavailableDateMessage);
            setSectionsAvailability([]);
            rebuildTimes([]);
            setNotice(unavailableDateMessage);
            return;
          }

          dateInput.removeClass('booking-form__input--unavailable');
          dateInput.get(0).setCustomValidity('');

          setSectionsAvailability(data.availableSections || []);

          const availableSlotsMap = data.availableSlots || {};
          const availableSlots = availableSlotsMap[sectionSelect.val()] || [];

          rebuildTimes(availableSlots);

          if ((data.availableSections || []).length === 0) {
            setNotice(unavailableDateMessage);
          } else if (availableSlots.length === 0) {
            setNotice(unavailableSectionMessage);
          } else {
            setNotice('');
          }
        };

        const fetchAvailability = () => {
          const menuId = menuSelect.val();
          const date = dateInput.val();
          const partySize = partyInput.val();

          if (!menuId || !date || !partySize) {
            rebuildTimes();
            return;
          }

          const params = new URLSearchParams({
            action: 'pixelforge_check_table_availability',
            menu: menuId,
            date,
            party_size: partySize,
          });

          fetch(`${ajaxUrl}?${params.toString()}`, { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => applyAvailability(data))
            .catch(() => {
              rebuildTimes();
              setNotice('');
            });
        };

        const validateStep = (index) => {
          const fields = steps.eq(index).find('input, select, textarea').toArray();

          for (let i = 0; i < fields.length; i += 1) {
            if (!fields[i].checkValidity()) {
              fields[i].reportValidity();
              return false;
            }
          }

          return true;
        };

        const showStep = (index) => {
          currentStep = Math.max(0, Math.min(index, steps.length - 1));
          steps.removeClass('is-active').attr('aria-hidden', 'true');
          steps.eq(currentStep).addClass('is-active').attr('aria-hidden', 'false');

          progressSteps.removeClass('is-active is-complete');
          progressSteps.each(function updateProgress(idx) {
            $(this)
              .toggleClass('is-active', idx === currentStep)
              .toggleClass('is-complete', idx < currentStep);
          });
        };

        form.on('click', '.booking-form__nav--next', (event) => {
          event.preventDefault();

          if (validateStep(currentStep)) {
            showStep(currentStep + 1);
          }
        });

        form.on('click', '.booking-form__nav--prev', (event) => {
          event.preventDefault();
          showStep(currentStep - 1);
        });

        form.on('submit', (event) => {
          event.preventDefault();

          if (!validateStep(currentStep)) {
            return;
          }

          hideAlerts();

          const submitButton = form.find('.booking-form__submit');
          submitButton.prop('disabled', true).addClass('is-loading');

          const formData = new FormData(form.get(0));
          formData.append('action', 'pixelforge_submit_booking');

          fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.errors && data.errors.length) {
                renderAlert('error', data.errors);
                return;
              }

              if (data.success) {
                form.get(0).reset();
                rebuildTimes();
                showStep(0);
                renderAlert('success', data.success);
              }
            })
            .catch(() => {
              renderAlert('error', [{{ json_encode(__('We could not submit your booking right now. Please try again in a moment.', 'pixelforge')) }}]);
            })
            .finally(() => {
              submitButton.prop('disabled', false).removeClass('is-loading');
            });
        });

        menuSelect.on('change', fetchAvailability);
        sectionSelect.on('change', fetchAvailability);
        dateInput.on('change', fetchAvailability);
        partyInput.on('input', fetchAvailability);

        rebuildTimes();
        fetchAvailability();
      });
    </script>
  @endif
</section>
