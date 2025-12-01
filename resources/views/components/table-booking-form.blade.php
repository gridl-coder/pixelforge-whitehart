@php
  $old = $feedback['old'] ?? [];
  $selectedMenu = $old['menu'] ?? ($menus[0]->ID ?? null);
  $initialSlots = $selectedMenu && isset($menuSlots[$selectedMenu]) ? $menuSlots[$selectedMenu] : [];
@endphp

<section class="booking-form">
  @if(! empty($feedback['errors']))
    <div class="booking-form__alert booking-form__alert--error">
      <p class="booking-form__alert-title">{{ __('There was a problem with your booking:', 'pixelforge') }}</p>
      <ul class="booking-form__alert-list">
        @foreach($feedback['errors'] as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(! empty($feedback['success']))
    <div class="booking-form__alert booking-form__alert--success">
      {{ $feedback['success'] }}
    </div>
  @endif

  @if(empty($sections) || empty($menus))
    <p class="booking-form__notice">{{ __('Add at least one section, table, and booking menu in the dashboard to enable table bookings.', 'pixelforge') }}</p>
  @else
    <form method="post" class="booking-form__form">
      <input type="hidden" name="pixelforge_booking_form" value="1">
      @php(wp_nonce_field(\PixelForge\Bookings\NONCE_ACTION, 'pixelforge_booking_nonce'))

      <div class="booking-form__grid">
        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Name', 'pixelforge') }}</span>
          <input class="booking-form__input" type="text" name="pixelforge_booking_name" value="{{ $old['name'] ?? '' }}" required>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Email', 'pixelforge') }}</span>
          <input class="booking-form__input" type="email" name="pixelforge_booking_email" value="{{ $old['email'] ?? '' }}" required>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Phone', 'pixelforge') }}</span>
          <input class="booking-form__input" type="tel" name="pixelforge_booking_phone" value="{{ $old['phone'] ?? '' }}" required>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Party Size', 'pixelforge') }}</span>
          <input class="booking-form__input" type="number" min="1" step="1" name="pixelforge_booking_party_size" value="{{ $old['party_size'] ?? '' }}" required>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Menu', 'pixelforge') }}</span>
          <select class="booking-form__input" name="pixelforge_booking_menu" id="pixelforge_booking_menu" required>
            @foreach($menus as $menu)
              <option value="{{ $menu->ID }}" @selected(($old['menu'] ?? $menus[0]->ID ?? null) === $menu->ID)>{{ $menu->post_title }}</option>
            @endforeach
          </select>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Area', 'pixelforge') }}</span>
          <select class="booking-form__input" name="pixelforge_booking_section" required>
            @foreach($sections as $section)
              <option value="{{ $section->ID }}" @selected(($old['section'] ?? null) === $section->ID)>{{ $section->post_title }}</option>
            @endforeach
          </select>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Date', 'pixelforge') }}</span>
          <input class="booking-form__input" type="date" name="pixelforge_booking_date" value="{{ $old['date'] ?? '' }}" min="{{ $minDate }}" required>
        </label>

        <label class="booking-form__field">
          <span class="booking-form__label">{{ __('Time', 'pixelforge') }}</span>
          <select class="booking-form__input" name="pixelforge_booking_time" id="pixelforge_booking_time" required>
            @foreach($initialSlots as $slot)
              <option value="{{ $slot }}" @selected(($old['time'] ?? null) === $slot)>{{ $slot }}</option>
            @endforeach
          </select>
        </label>
      </div>

      <label class="booking-form__field">
        <span class="booking-form__label">{{ __('Notes (optional)', 'pixelforge') }}</span>
        <textarea class="booking-form__input" name="pixelforge_booking_notes" rows="4">{{ $old['notes'] ?? '' }}</textarea>
      </label>

      <p class="booking-form__notice booking-form__notice--availability" id="booking_availability_notice" aria-live="polite"></p>

      <div class="booking-form__calendar" aria-live="polite">
        <p class="booking-form__label">{{ __('Availability calendar', 'pixelforge') }}</p>
        <div id="booking_calendar" class="booking-form__calendar-grid" role="grid" aria-label="{{ __('Booking availability calendar', 'pixelforge') }}"></div>
      </div>

      <button class="booking-form__submit" type="submit">{{ __('Book Table', 'pixelforge') }}</button>
    </form>

    <script>
      (() => {
        const menuSelect = document.getElementById('pixelforge_booking_menu');
        const timeSelect = document.getElementById('pixelforge_booking_time');
        const sectionSelect = document.querySelector('select[name="pixelforge_booking_section"]');
        const dateInput = document.querySelector('input[name="pixelforge_booking_date"]');
        const partyInput = document.querySelector('input[name="pixelforge_booking_party_size"]');
        const notice = document.getElementById('booking_availability_notice');
        const calendar = document.getElementById('booking_calendar');
        const slots = @json($menuSlots);
        const ajaxUrl = @json(admin_url('admin-ajax.php'));
        const unavailableDateMessage = @json(__('Selected date is unavailable for this menu.', 'pixelforge'));
        const unavailableSectionMessage = @json(__('Selected area is fully booked for this date.', 'pixelforge'));
        const bookingsDisabledMessage = @json(__('Bookings are currently unavailable.', 'pixelforge'));
        let unavailableDates = new Set();

        const setNotice = (text) => {
          notice.textContent = text || '';
        };

        const rebuildTimes = (availableSlots = []) => {
          const menuId = menuSelect.value;
          const menuSlots = slots[menuId] || [];
          const previous = timeSelect.value;

          timeSelect.innerHTML = '';

          menuSlots.forEach((slot) => {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;

            if (availableSlots.length > 0 && ! availableSlots.includes(slot)) {
              option.disabled = true;
              option.textContent = `${slot} ({{ __('Fully booked', 'pixelforge') }})`;
            }

            option.selected = slot === previous && ! option.disabled;
            timeSelect.appendChild(option);
          });

          if (! timeSelect.querySelector('option:checked')) {
            const firstAvailable = timeSelect.querySelector('option:not([disabled])');

            if (firstAvailable) {
              firstAvailable.selected = true;
            }
          }
        };

        const setSectionsAvailability = (availableSections = []) => {
          const availableIds = availableSections.map(String);

          Array.from(sectionSelect.options).forEach((option) => {
            option.disabled = ! availableIds.includes(option.value);
          });

          if (! availableIds.includes(sectionSelect.value) && availableIds.length > 0) {
            sectionSelect.value = availableIds[0];
          }
        };

        const applyAvailability = (data) => {
          if (! data) {
            rebuildTimes();
            return;
          }

          if (data.bookingsDisabled) {
            setNotice(bookingsDisabledMessage);
            rebuildTimes();
            return;
          }

          if (data.unavailableDate) {
            dateInput.classList.add('booking-form__input--unavailable');
            dateInput.setCustomValidity(unavailableDateMessage);
            setSectionsAvailability([]);
            rebuildTimes([]);
            setNotice(unavailableDateMessage);
            return;
          }

          dateInput.classList.remove('booking-form__input--unavailable');
          dateInput.setCustomValidity('');

          setSectionsAvailability(data.availableSections || []);

          const availableSlotsMap = data.availableSlots || {};
          const availableSlots = availableSlotsMap[sectionSelect.value] || [];

          rebuildTimes(availableSlots);

          if ((data.availableSections || []).length === 0) {
            setNotice(unavailableDateMessage);
          } else if (availableSlots.length === 0) {
            setNotice(unavailableSectionMessage);
          } else {
            setNotice('');
          }
        };

        const applyCalendar = (dates = []) => {
          unavailableDates = new Set(dates.filter((entry) => ! entry.available).map((entry) => entry.date));

          if (! calendar) {
            return;
          }

          calendar.innerHTML = '';

          if (dates.length === 0) {
            calendar.textContent = @json(__('No calendar data available.', 'pixelforge'));
            return;
          }

          dates.forEach((entry) => {
            const dayButton = document.createElement('button');
            dayButton.type = 'button';
            dayButton.className = 'booking-form__calendar-day';
            dayButton.textContent = new Date(entry.date).getDate();
            dayButton.title = entry.date;
            dayButton.setAttribute('aria-label', `${entry.date}${entry.available ? '' : ' - {{ __('Unavailable', 'pixelforge') }}'}`);

            if (! entry.available) {
              dayButton.disabled = true;
              dayButton.classList.add('booking-form__calendar-day--unavailable');
            }

            if (dateInput.value === entry.date) {
              dayButton.classList.add('booking-form__calendar-day--selected');
            }

            dayButton.addEventListener('click', () => {
              if (entry.available) {
                dateInput.value = entry.date;
                dateInput.dispatchEvent(new Event('change'));
              }
            });

            calendar.appendChild(dayButton);
          });
        };

        const fetchAvailability = () => {
          const menuId = menuSelect.value;
          const date = dateInput.value;
          const partySize = partyInput.value;

          if (! menuId || ! date || ! partySize) {
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

        const fetchCalendar = () => {
          const menuId = menuSelect.value;
          const partySize = partyInput.value;

          if (! menuId || ! partySize) {
            applyCalendar([]);
            return;
          }

          const params = new URLSearchParams({
            action: 'pixelforge_get_booking_calendar',
            menu: menuId,
            party_size: partySize,
          });

          fetch(`${ajaxUrl}?${params.toString()}`, { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => {
              if (data.bookingsDisabled) {
                setNotice(bookingsDisabledMessage);
                applyCalendar([]);
                return;
              }

              applyCalendar(data.dates || []);
            })
            .catch(() => {
              applyCalendar([]);
            });
        };

        const enforceDateValidity = () => {
          const value = dateInput.value;

          if (value && unavailableDates.has(value)) {
            dateInput.classList.add('booking-form__input--unavailable');
            dateInput.setCustomValidity(unavailableDateMessage);
            setNotice(unavailableDateMessage);
            return;
          }

          dateInput.classList.remove('booking-form__input--unavailable');
          dateInput.setCustomValidity('');
        };

        menuSelect.addEventListener('change', fetchAvailability);
        sectionSelect.addEventListener('change', fetchAvailability);
        dateInput.addEventListener('change', () => {
          enforceDateValidity();
          fetchAvailability();
        });
        partyInput.addEventListener('input', fetchAvailability);
        menuSelect.addEventListener('change', fetchCalendar);
        partyInput.addEventListener('input', fetchCalendar);

        rebuildTimes();
        fetchAvailability();
        fetchCalendar();
      })();
    </script>
  @endif
</section>
