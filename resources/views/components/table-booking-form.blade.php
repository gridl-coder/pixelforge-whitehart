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

      <button class="booking-form__submit" type="submit">{{ __('Book Table', 'pixelforge') }}</button>
    </form>

    <script>
      (() => {
        const menuSelect = document.getElementById('pixelforge_booking_menu');
        const timeSelect = document.getElementById('pixelforge_booking_time');
        const slots = @json($menuSlots);

        const rebuildTimes = () => {
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

        menuSelect.addEventListener('change', rebuildTimes);
        rebuildTimes();
      })();
    </script>
  @endif
</section>
