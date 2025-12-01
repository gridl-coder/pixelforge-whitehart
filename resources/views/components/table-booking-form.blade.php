@php
  $old = $feedback['old'] ?? [];
  $selectedMenu = $old['menu'] ?? ($menus[0]->ID ?? null);
  $initialSlots = $selectedMenu && isset($menuSlots[$selectedMenu]) ? $menuSlots[$selectedMenu] : [];
  $ajaxUrl = admin_url('admin-ajax.php');
@endphp

<section class="booking-form container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
          <h2 class="h4 mb-3 text-dark">{{ __('Reserve your table', 'pixelforge') }}</h2>
          <p class="text-muted mb-4">{{ __('Pick your menu, area, date, and hour. We will hold the table and email you a confirmation.', 'pixelforge') }}</p>

          @if(! empty($feedback['errors']))
            <div class="alert alert-danger" role="alert">
              <p class="fw-semibold mb-2">{{ __('There was a problem with your booking:', 'pixelforge') }}</p>
              <ul class="mb-0 ps-3">
                @foreach($feedback['errors'] as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if(! empty($feedback['success']))
            <div class="alert alert-success" role="alert">
              {{ $feedback['success'] }}
            </div>
          @endif

          @if(empty($sections) || empty($menus))
            <div class="alert alert-info" role="alert">
              {{ __('Add at least one section, table, and booking menu in the dashboard to enable table bookings.', 'pixelforge') }}
            </div>
          @else
            <form method="post" class="booking-form__form needs-validation" novalidate data-booking-form>
              <input type="hidden" name="pixelforge_booking_form" value="1">
              @php(wp_nonce_field(\PixelForge\Bookings\NONCE_ACTION, 'pixelforge_booking_nonce'))

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_name">{{ __('Name', 'pixelforge') }}</label>
                  <input class="form-control" id="pixelforge_booking_name" type="text" name="pixelforge_booking_name" value="{{ $old['name'] ?? '' }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_email">{{ __('Email', 'pixelforge') }}</label>
                  <input class="form-control" id="pixelforge_booking_email" type="email" name="pixelforge_booking_email" value="{{ $old['email'] ?? '' }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_phone">{{ __('Phone', 'pixelforge') }}</label>
                  <input class="form-control" id="pixelforge_booking_phone" type="tel" name="pixelforge_booking_phone" value="{{ $old['phone'] ?? '' }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_party_size">{{ __('Party Size', 'pixelforge') }}</label>
                  <input
                    class="form-control"
                    id="pixelforge_booking_party_size"
                    type="number"
                    min="1"
                    max="{{ $maxParty ?? 12 }}"
                    step="1"
                    name="pixelforge_booking_party_size"
                    value="{{ $old['party_size'] ?? '' }}"
                    required
                  >
                  <small class="text-muted">{{ sprintf(__('Up to %d guests per booking online.', 'pixelforge'), $maxParty ?? 12) }}</small>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_menu">{{ __('Menu', 'pixelforge') }}</label>
                  <select class="form-select" name="pixelforge_booking_menu" id="pixelforge_booking_menu" required>
                    @foreach($menus as $menu)
                      <option value="{{ $menu->ID }}" @selected(($old['menu'] ?? $menus[0]->ID ?? null) === $menu->ID)>{{ $menu->post_title }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_section">{{ __('Area', 'pixelforge') }}</label>
                  <select class="form-select" name="pixelforge_booking_section" id="pixelforge_booking_section" required>
                    @foreach($sections as $section)
                      <option value="{{ $section->ID }}" @selected(($old['section'] ?? null) === $section->ID)>{{ $section->post_title }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_date">{{ __('Date', 'pixelforge') }}</label>
                  <input class="form-control" id="pixelforge_booking_date" type="date" name="pixelforge_booking_date" value="{{ $old['date'] ?? '' }}" min="{{ $minDate }}" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="pixelforge_booking_time">{{ __('Time', 'pixelforge') }}</label>
                  <select class="form-select" name="pixelforge_booking_time" id="pixelforge_booking_time" required>
                    @foreach($initialSlots as $slot)
                      <option value="{{ $slot }}" @selected(($old['time'] ?? null) === $slot)>{{ $slot }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label" for="pixelforge_booking_notes">{{ __('Notes (optional)', 'pixelforge') }}</label>
                  <textarea class="form-control" id="pixelforge_booking_notes" name="pixelforge_booking_notes" rows="3">{{ $old['notes'] ?? '' }}</textarea>
                </div>
              </div>

              <div class="d-flex align-items-center gap-3 mt-4">
                <button class="btn btn-primary px-4" type="submit">{{ __('Book Table', 'pixelforge') }}</button>
                <p class="text-muted mb-0">{{ __('Hourly slots are held for you once confirmed.', 'pixelforge') }}</p>
              </div>
            </form>
          @endif
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex align-items-center flex-wrap gap-2">
          <div>
            <h3 class="h5 mb-0">{{ __('Availability calendar', 'pixelforge') }}</h3>
            <small class="text-white-50">{{ __('Automatically updates when you change menu, area, or party size.', 'pixelforge') }}</small>
          </div>
          <div class="ms-auto d-flex flex-wrap gap-2">
            <span class="badge bg-availability-open">{{ __('Available', 'pixelforge') }}</span>
            <span class="badge bg-availability-limited">{{ __('Limited', 'pixelforge') }}</span>
            <span class="badge bg-availability-full">{{ __('Booked', 'pixelforge') }}</span>
            <span class="badge bg-secondary">{{ __('Closed', 'pixelforge') }}</span>
          </div>
        </div>
        <div class="card-body">
          <div
            class="booking-calendar"
            data-booking-calendar
            data-ajax="{{ esc_url($ajaxUrl) }}"
            data-nonce="{{ esc_attr($availabilityNonce) }}"
            data-start="{{ $minDate }}"
            data-days="7"
            data-empty-text="{{ __('No available slots found for the selected criteria.', 'pixelforge') }}"
          >
            <div class="text-center py-4" data-calendar-loading>
              <div class="spinner-border text-warning" role="status" aria-hidden="true"></div>
              <p class="text-muted mt-2 mb-0">{{ __('Loading availability...', 'pixelforge') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  (() => {
    const menuSelect = document.getElementById('pixelforge_booking_menu');
    const timeSelect = document.getElementById('pixelforge_booking_time');
    const sectionSelect = document.getElementById('pixelforge_booking_section');
    const partySizeInput = document.getElementById('pixelforge_booking_party_size');
    const dateInput = document.getElementById('pixelforge_booking_date');
    const calendarRoot = document.querySelector('[data-booking-calendar]');
    const slots = @json($menuSlots);

    const renderSpinner = () => {
      if (!calendarRoot) return;
      calendarRoot.innerHTML = `
        <div class="text-center py-4">
          <div class="spinner-border text-warning" role="status" aria-hidden="true"></div>
          <p class="text-muted mt-2 mb-0">${calendarRoot.dataset.loadingText || 'Loading availability...'}</p>
        </div>
      `;
    };

    const renderCalendar = (days = []) => {
      if (!calendarRoot) return;

      if (!Array.isArray(days) || !days.length) {
        calendarRoot.innerHTML = `<p class="text-muted mb-0">${calendarRoot.dataset.emptyText}</p>`;
        return;
      }

      const row = document.createElement('div');
      row.className = 'row g-3';

      days.forEach((day) => {
        const col = document.createElement('div');
        col.className = 'col-12 col-md-6 col-lg-4';

        const card = document.createElement('div');
        card.className = 'booking-calendar__day card h-100 shadow-sm';

        const header = document.createElement('div');
        header.className = 'card-header bg-light';
        header.innerHTML = `<div class="fw-semibold text-dark">${day.label}</div><div class="small text-muted">${day.date}</div>`;

        const list = document.createElement('ul');
        list.className = 'list-group list-group-flush';

        day.slots.forEach((slot) => {
          const item = document.createElement('li');
          item.className = 'list-group-item d-flex justify-content-between align-items-center gap-2';
          const isLimited = slot.status === 'available' && slot.available <= 2;
          const statusClass = slot.status === 'available'
            ? (isLimited ? 'badge bg-availability-limited' : 'badge bg-availability-open')
            : slot.status === 'booked'
              ? 'badge bg-availability-full'
              : 'badge bg-secondary';

          const availabilityText = slot.status === 'available'
            ? `${slot.available} ${slot.available === 1 ? 'table' : 'tables'} ${slot.booked ? `• ${slot.booked} booked` : ''}`
            : slot.status === 'booked'
              ? '{{ __('Fully booked', 'pixelforge') }}'
              : '{{ __('Not bookable', 'pixelforge') }}';

          item.innerHTML = `
            <div class="fw-semibold text-dark">${slot.time}</div>
            <div class="d-flex align-items-center gap-2">
              <small class="text-muted d-none d-md-inline">${availabilityText}</small>
              <span class="${statusClass}">${isLimited ? '{{ __('Limited', 'pixelforge') }}' : (slot.status === 'available' ? '{{ __('Available', 'pixelforge') }}' : slot.status === 'booked' ? '{{ __('Booked', 'pixelforge') }}' : '{{ __('Closed', 'pixelforge') }}')}</span>
            </div>
          `;

          list.appendChild(item);
        });

        card.appendChild(header);
        card.appendChild(list);
        col.appendChild(card);
        row.appendChild(col);
      });

      calendarRoot.innerHTML = '';
      calendarRoot.appendChild(row);
    };

    const rebuildTimes = () => {
      if (!menuSelect || !timeSelect) return;

      const menuId = menuSelect.value;
      const menuSlots = slots[menuId] || [];
      const previous = timeSelect.value;

      timeSelect.innerHTML = '';

      menuSlots.forEach((slot) => {
        const option = document.createElement('option');
        option.value = slot;
        option.textContent = slot;
        option.selected = slot === previous;
        timeSelect.appendChild(option);
      });
    };

    const fetchAvailability = async () => {
      if (!calendarRoot || !calendarRoot.dataset.ajax) return;

      renderSpinner();

      const params = new URLSearchParams({
        action: 'pixelforge_booking_availability',
        menu: menuSelect?.value || '',
        section: sectionSelect?.value || '',
        party_size: partySizeInput?.value || '1',
        start: dateInput?.value || calendarRoot.dataset.start,
        days: calendarRoot.dataset.days || '7',
        _ajax_nonce: calendarRoot.dataset.nonce,
      });

      try {
        const response = await fetch(calendarRoot.dataset.ajax, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: params.toString(),
        });

        const payload = await response.json();

        if (payload.success) {
          renderCalendar(payload.data.days);
        } else {
          calendarRoot.innerHTML = `<p class="text-danger mb-0">${payload.data?.message || '{{ __('Unable to load availability right now.', 'pixelforge') }}'}</p>`;
        }
      } catch (error) {
        calendarRoot.innerHTML = `<p class="text-danger mb-0">{{ __('Unable to load availability right now.', 'pixelforge') }}</p>`;
      }
    };

    menuSelect?.addEventListener('change', () => {
      rebuildTimes();
      fetchAvailability();
    });

    sectionSelect?.addEventListener('change', fetchAvailability);
    partySizeInput?.addEventListener('input', fetchAvailability);
    dateInput?.addEventListener('change', fetchAvailability);

    rebuildTimes();
    fetchAvailability();
  })();
</script>
