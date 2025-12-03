{{--
  Template Name: Booking Admin Panel
--}}

@php($redirect = get_permalink())
@php($panel = \PixelForge\BookingAdmin\get_panel_context())
@php($notice = sanitize_text_field(wp_unslash($_GET['booking_admin_notice'] ?? '')))
@php($error = isset($_GET['booking_admin_error']) ? wp_kses_post(wp_unslash($_GET['booking_admin_error'])) : '')
@php($today = wp_date('Y-m-d', time(), wp_timezone()))
@php($currentMonth = wp_date('Y-m', time(), wp_timezone()))
@php($currentWeek = wp_date('o-\\WW', time(), wp_timezone()))

@extends('layouts.booking-admin')

@section('content')
  <section class="booking-admin">
    <div class="booking-admin__header">
      <h1>{{ __('Table Booking Admin', 'pixelforge') }}</h1>

      @if (is_user_logged_in() && current_user_can('edit_posts'))
        <nav class="booking-admin__tabs" aria-label="{{ __('Booking admin pages', 'pixelforge') }}">
          <a class="booking-admin__tab is-active" href="#booking-create" data-panel-toggle="create" role="tab" aria-controls="booking-panel-create" aria-selected="true">
            {{ __('Add booking', 'pixelforge') }}
          </a>
          <a class="booking-admin__tab" href="#booking-view" data-panel-toggle="list" role="tab" aria-controls="booking-panel-list" aria-selected="false">
            {{ __('View bookings', 'pixelforge') }}
          </a>
          <a class="booking-admin__tab" href="#booking-calendar" data-panel-toggle="calendar" role="tab" aria-controls="booking-panel-calendar" aria-selected="false">
            {{ __('Calendar', 'pixelforge') }}
          </a>
        </nav>
      @endif
    </div>

    @if ($notice !== '')
      <div class="booking-admin__notice booking-admin__notice--success">
        @switch($notice)
          @case('created')
            {{ __('Booking created successfully.', 'pixelforge') }}
            @break
          @case('updated')
            {{ __('Booking updated successfully.', 'pixelforge') }}
            @break
          @case('deleted')
            {{ __('Booking moved to trash.', 'pixelforge') }}
            @break
          @default
            {{ __('Action completed.', 'pixelforge') }}
        @endswitch
      </div>
    @endif

    @if ($error !== '')
      <div class="booking-admin__notice booking-admin__notice--error">{!! $error !!}</div>
    @endif

    @if (! is_user_logged_in())
      <div class="booking-admin__card">
        <h2>{{ __('Staff Login', 'pixelforge') }}</h2>
        <p class="booking-admin__muted">{{ __('Sign in with your staff account to view and manage table bookings.', 'pixelforge') }}</p>

        <form class="booking-admin__form" action="{{ admin_url('admin-post.php') }}" method="post">
          @php(wp_nonce_field(\PixelForge\BookingAdmin\LOGIN_NONCE_ACTION))
          <input type="hidden" name="action" value="pixelforge_booking_admin_login">
          <input type="hidden" name="redirect_to" value="{{ esc_url($redirect) }}">

          <label class="booking-admin__field">
            <span>{{ __('Username', 'pixelforge') }}</span>
            <input type="text" name="username" required autocomplete="username">
          </label>

          <label class="booking-admin__field">
            <span>{{ __('Password', 'pixelforge') }}</span>
            <input type="password" name="password" required autocomplete="current-password">
          </label>

          <button class="booking-admin__button" type="submit">{{ __('Sign in', 'pixelforge') }}</button>
        </form>
      </div>
    @elseif (! current_user_can('edit_posts'))
      <div class="booking-admin__card">
        <h2>{{ __('No access', 'pixelforge') }}</h2>
        <p class="booking-admin__muted">{{ __('Your account cannot manage bookings. Please contact an administrator.', 'pixelforge') }}</p>
      </div>
    @else
      <div class="booking-admin__topbar">
        <span>{{ sprintf(__('Signed in as %s', 'pixelforge'), wp_get_current_user()->display_name ?: wp_get_current_user()->user_login) }}</span>
        <a class="booking-admin__button booking-admin__button--ghost" href="{{ wp_logout_url($redirect) }}">{{ __('Log out', 'pixelforge') }}</a>
      </div>

      <div class="booking-admin__panels" data-panel-container>
        <div class="booking-admin__panel is-active" id="booking-panel-create" data-panel="create" role="tabpanel" aria-label="{{ __('Add booking', 'pixelforge') }}">
          <div class="booking-admin__card">
            <h2>{{ __('Create booking', 'pixelforge') }}</h2>
            <p class="booking-admin__muted">{{ __('Add a new booking.', 'pixelforge') }}</p>

            <form class="booking-admin__form" action="{{ admin_url('admin-post.php') }}" method="post">
              @php(wp_nonce_field(\PixelForge\BookingAdmin\BOOKING_NONCE_ACTION))
              <input type="hidden" name="action" value="pixelforge_booking_admin_create">
              <input type="hidden" name="redirect_to" value="{{ esc_url($redirect) }}">

              <label class="booking-admin__field">
                <span>{{ __('Guest name', 'pixelforge') }}</span>
                <input type="text" name="name" required>
              </label>

              <div class="booking-admin__field-grid">
                <label class="booking-admin__field">
                  <span>{{ __('Email', 'pixelforge') }}</span>
                  <input type="email" name="email" required>
                </label>

                <label class="booking-admin__field">
                  <span>{{ __('Phone', 'pixelforge') }}</span>
                  <input type="tel" name="phone" required>
                </label>

                <label class="booking-admin__field">
                  <span>{{ __('Party size', 'pixelforge') }}</span>
                  <select name="party_size" required>
                    @for ($partySize = 2; $partySize <= 12; $partySize += 1)
                      <option value="{{ $partySize }}">{{ $partySize }}</option>
                    @endfor
                  </select>
                </label>
              </div>

              <div class="booking-admin__field-grid">
                <label class="booking-admin__field">
                  <span>{{ __('Menu', 'pixelforge') }}</span>
                  <select name="menu" required>
                    <option value="">{{ __('Select a menu', 'pixelforge') }}</option>
                    @foreach ($panel['menus'] as $menu)
                      <option value="{{ $menu->ID }}">{{ $menu->post_title }}</option>
                    @endforeach
                  </select>
                </label>

                <label class="booking-admin__field">
                  <span>{{ __('Section', 'pixelforge') }}</span>
                  <select name="section" required>
                    <option value="">{{ __('Select a section', 'pixelforge') }}</option>
                    @foreach ($panel['sections'] as $section)
                      <option value="{{ $section->ID }}">{{ $section->post_title }}</option>
                    @endforeach
                  </select>
                </label>

                <label class="booking-admin__field">
                  <span>{{ __('Tables', 'pixelforge') }}</span>
                  <select name="table_ids[]" multiple required>
                    @foreach ($panel['tables'] as $table)
                      @php($tableSeats = get_post_meta($table->ID, 'booking_table_seats', true))
                      @php($tableSection = get_post_meta($table->ID, 'booking_table_section', true))
                      <option value="{{ $table->ID }}">
                        {{ $table->post_title }}
                        @if ($tableSection)
                          ({{ get_the_title($tableSection) }})
                        @endif
                        — {{ sprintf(__('Seats: %s', 'pixelforge'), $tableSeats ?: '—') }}
                      </option>
                    @endforeach
                  </select>
                </label>

              </div>

              <div class="booking-admin__field-grid">
                <label class="booking-admin__field">
                  <span>{{ __('Date', 'pixelforge') }}</span>
                  <input type="date" name="date" required>
                </label>

                <label class="booking-admin__field">
                  <span>{{ __('Time', 'pixelforge') }}</span>
                  <input type="time" name="time" required>
                </label>
              </div>

              <label class="booking-admin__field">
                <span>{{ __('Notes', 'pixelforge') }}</span>
                <textarea name="notes" rows="3" placeholder="{{ __('Allergies, accessibility needs, etc.', 'pixelforge') }}"></textarea>
              </label>

              <button class="booking-admin__button" type="submit">{{ __('Save booking', 'pixelforge') }}</button>
            </form>
          </div>
        </div>

        <div class="booking-admin__panel" id="booking-panel-list" data-panel="list" role="tabpanel" aria-label="{{ __('View bookings', 'pixelforge') }}">
          <div class="booking-admin__card">
          <div class="booking-admin__card-header booking-admin__card-header--stacked">
            <div>
              <h2>{{ __('Existing bookings', 'pixelforge') }}</h2>
              <p class="booking-admin__muted">{{ __('Review, update, or cancel bookings.', 'pixelforge') }}</p>
            </div>
            <div class="booking-admin__export">
              <span class="booking-admin__pill">{{ count($panel['bookings']) }} {{ __('records', 'pixelforge') }}</span>

              <form class="booking-admin__export-form" action="{{ admin_url('admin-post.php') }}" method="post" data-export-form>
                @php(wp_nonce_field(\PixelForge\BookingAdmin\EXPORT_NONCE_ACTION))
                <input type="hidden" name="action" value="pixelforge_booking_admin_export">
                <input type="hidden" name="redirect_to" value="{{ esc_url($redirect) }}">

                <label class="booking-admin__field booking-admin__field--compact">
                  <span>{{ __('Export', 'pixelforge') }}</span>
                  <select name="period" data-export-range>
                    <option value="day">{{ __('By day', 'pixelforge') }}</option>
                    <option value="week">{{ __('By week', 'pixelforge') }}</option>
                    <option value="month">{{ __('By month', 'pixelforge') }}</option>
                    <option value="custom">{{ __('Custom range', 'pixelforge') }}</option>
                  </select>
                </label>

                <div class="booking-admin__export-inputs">
                  <label class="booking-admin__field booking-admin__field--compact" data-export-field="day">
                    <span>{{ __('Date', 'pixelforge') }}</span>
                    <input type="date" name="range_start" value="{{ $today }}" required>
                  </label>

                  <label class="booking-admin__field booking-admin__field--compact" data-export-field="week" hidden>
                    <span>{{ __('Week', 'pixelforge') }}</span>
                    <input type="week" name="range_week" value="{{ $currentWeek }}" disabled required>
                  </label>

                  <label class="booking-admin__field booking-admin__field--compact" data-export-field="month" hidden>
                    <span>{{ __('Month', 'pixelforge') }}</span>
                    <input type="month" name="range_month" value="{{ $currentMonth }}" disabled required>
                  </label>

                  <div class="booking-admin__field-grid booking-admin__field-grid--tight" data-export-field="custom" hidden>
                    <label class="booking-admin__field booking-admin__field--compact">
                      <span>{{ __('Start', 'pixelforge') }}</span>
                      <input type="date" name="range_start" value="{{ $today }}" disabled required>
                    </label>

                    <label class="booking-admin__field booking-admin__field--compact">
                      <span>{{ __('End', 'pixelforge') }}</span>
                      <input type="date" name="range_end" value="{{ $today }}" disabled required>
                    </label>
                  </div>
                </div>

                <button class="booking-admin__button" type="submit">{{ __('Export PDF', 'pixelforge') }}</button>
              </form>
            </div>
          </div>

            @if ($panel['bookings'] === [])
              <p class="booking-admin__muted">{{ __('No bookings found yet.', 'pixelforge') }}</p>
            @else
              <div class="booking-admin__list">
                @foreach ($panel['bookings'] as $booking)
                  <details class="booking-admin__booking" id="booking-record-{{ $booking['id'] }}" data-booking-id="{{ $booking['id'] }}">
                    <summary>
                      <div>
                        <strong>{{ $booking['details']['name'] }}</strong>
                        <span class="booking-admin__muted">{{ $booking['details']['date'] }} · {{ $booking['details']['time'] }}</span>
                      </div>
                      <span class="booking-admin__pill {{ $booking['verified'] ? 'booking-admin__pill--success' : '' }}">
                        {{ $booking['verified'] ? __('Confirmed', 'pixelforge') : __('Pending', 'pixelforge') }}
                      </span>
                    </summary>

                    <div class="booking-admin__booking-body">
                      <dl class="booking-admin__meta">
                        <div>
                          <dt>{{ __('Menu', 'pixelforge') }}</dt>
                          <dd>{{ $booking['details']['menu_name'] ?: '—' }}</dd>
                        </div>
                        <div>
                          <dt>{{ __('Section', 'pixelforge') }}</dt>
                          <dd>{{ $booking['details']['section_name'] ?: '—' }}</dd>
                        </div>
                        <div>
                          <dt>{{ __('Tables', 'pixelforge') }}</dt>
                          <dd>{{ $booking['table_label'] ?: '—' }}</dd>
                        </div>
                        <div>
                          <dt>{{ __('Party size', 'pixelforge') }}</dt>
                          <dd>{{ $booking['details']['party_size'] }}</dd>
                        </div>
                        <div>
                          <dt>{{ __('Contact', 'pixelforge') }}</dt>
                          <dd>
                            <div>{{ $booking['details']['email'] }}</div>
                            <div>{{ $booking['details']['phone'] }}</div>
                          </dd>
                        </div>
                        <div>
                          <dt>{{ __('Notes', 'pixelforge') }}</dt>
                          <dd>{{ $booking['details']['notes'] ?: '—' }}</dd>
                        </div>
                      </dl>

                      <form class="booking-admin__form booking-admin__form--inline" action="{{ admin_url('admin-post.php') }}" method="post">
                        @php(wp_nonce_field(\PixelForge\BookingAdmin\BOOKING_NONCE_ACTION))
                        <input type="hidden" name="action" value="pixelforge_booking_admin_update">
                        <input type="hidden" name="booking_id" value="{{ $booking['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ esc_url($redirect) }}">

                        <div class="booking-admin__field-grid">
                          <label class="booking-admin__field">
                            <span>{{ __('Guest name', 'pixelforge') }}</span>
                            <input type="text" name="name" value="{{ $booking['details']['name'] }}" required>
                          </label>
                          <label class="booking-admin__field">
                            <span>{{ __('Email', 'pixelforge') }}</span>
                            <input type="email" name="email" value="{{ $booking['details']['email'] }}" required>
                          </label>
                          <label class="booking-admin__field">
                            <span>{{ __('Phone', 'pixelforge') }}</span>
                            <input type="tel" name="phone" value="{{ $booking['details']['phone'] }}" required>
                          </label>
                          <label class="booking-admin__field">
                            <span>{{ __('Party size', 'pixelforge') }}</span>
                            <select name="party_size" required>
                              @for ($partySize = 2; $partySize <= 12; $partySize += 1)
                                <option value="{{ $partySize }}" @selected($booking['details']['party_size'] === $partySize)>{{ $partySize }}</option>
                              @endfor
                            </select>
                          </label>
                        </div>

                        <div class="booking-admin__field-grid">
                          <label class="booking-admin__field">
                            <span>{{ __('Menu', 'pixelforge') }}</span>
                            <select name="menu" required>
                              @foreach ($panel['menus'] as $menu)
                                <option value="{{ $menu->ID }}" @selected($booking['details']['menu'] === $menu->ID)>{{ $menu->post_title }}</option>
                              @endforeach
                            </select>
                          </label>

                          <label class="booking-admin__field">
                            <span>{{ __('Section', 'pixelforge') }}</span>
                            <select name="section" required>
                              @foreach ($panel['sections'] as $section)
                                <option value="{{ $section->ID }}" @selected($booking['details']['section'] === $section->ID)>{{ $section->post_title }}</option>
                              @endforeach
                            </select>
                          </label>
                        </div>

                        <label class="booking-admin__field">
                          <span>{{ __('Tables', 'pixelforge') }}</span>
                          <select name="table_ids[]" multiple required>
                            @foreach ($panel['tables'] as $table)
                              @php($tableSeats = get_post_meta($table->ID, 'booking_table_seats', true))
                              @php($tableSection = get_post_meta($table->ID, 'booking_table_section', true))
                              <option value="{{ $table->ID }}" @selected(in_array($table->ID, $booking['details']['table_ids'], true))>
                                {{ $table->post_title }}
                                @if ($tableSection)
                                  ({{ get_the_title($tableSection) }})
                                @endif
                                — {{ sprintf(__('Seats: %s', 'pixelforge'), $tableSeats ?: '—') }}
                              </option>
                            @endforeach
                          </select>
                        </label>

                        <div class="booking-admin__field-grid">
                          <label class="booking-admin__field">
                            <span>{{ __('Date', 'pixelforge') }}</span>
                            <input type="date" name="date" value="{{ $booking['details']['timestamp'] ? wp_date('Y-m-d', $booking['details']['timestamp'], wp_timezone()) : '' }}" required>
                          </label>

                          <label class="booking-admin__field">
                            <span>{{ __('Time', 'pixelforge') }}</span>
                            <input type="time" name="time" value="{{ $booking['details']['timestamp'] ? wp_date('H:i', $booking['details']['timestamp'], wp_timezone()) : '' }}" required>
                          </label>
                        </div>

                        <label class="booking-admin__field">
                          <span>{{ __('Notes', 'pixelforge') }}</span>
                          <textarea name="notes" rows="2">{{ $booking['details']['notes'] }}</textarea>
                        </label>

                        <div class="booking-admin__actions">
                          <button class="booking-admin__button" type="submit">{{ __('Update booking', 'pixelforge') }}</button>
                        </div>
                      </form>

                      <form class="booking-admin__actions" action="{{ admin_url('admin-post.php') }}" method="post" onsubmit="return confirm('{{ __('Move this booking to the trash?', 'pixelforge') }}');">
                        @php(wp_nonce_field(\PixelForge\BookingAdmin\BOOKING_NONCE_ACTION))
                        <input type="hidden" name="action" value="pixelforge_booking_admin_delete">
                        <input type="hidden" name="booking_id" value="{{ $booking['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ esc_url($redirect) }}">
                        <button class="booking-admin__button booking-admin__button--danger" type="submit">{{ __('Trash booking', 'pixelforge') }}</button>
                      </form>
                    </div>
                  </details>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        @php($calendarBookings = array_values(array_filter(array_map(function ($booking) {
          if (empty($booking['details']['timestamp'])) {
            return null;
          }

          return [
            'id' => $booking['id'],
            'date' => wp_date('Y-m-d', $booking['details']['timestamp'], wp_timezone()),
            'time' => $booking['details']['time'],
            'name' => $booking['details']['name'],
            'status' => $booking['verified'] ? 'confirmed' : 'pending',
            'party_size' => $booking['details']['party_size'],
            'table_label' => $booking['table_label'],
          ];
        }, $panel['bookings']))))

        <div class="booking-admin__panel" id="booking-panel-calendar" data-panel="calendar" role="tabpanel" aria-label="{{ __('Calendar', 'pixelforge') }}">
          <div class="booking-admin__card booking-admin__card--flush">
            <div class="booking-admin__card-header booking-admin__card-header--stacked">
              <div>
                <h2>{{ __('Calendar view', 'pixelforge') }}</h2>
                <p class="booking-admin__muted">{{ __('Browse bookings month by month.', 'pixelforge') }}</p>
              </div>

              <div class="booking-admin__calendar-nav">
                <button class="booking-admin__button booking-admin__button--ghost" type="button" data-calendar-prev aria-label="{{ __('Previous month', 'pixelforge') }}">‹</button>
                <div class="booking-admin__calendar-month" data-calendar-month></div>
                <button class="booking-admin__button booking-admin__button--ghost" type="button" data-calendar-next aria-label="{{ __('Next month', 'pixelforge') }}">›</button>
              </div>
            </div>

            <div class="booking-admin__calendar" data-booking-calendar data-bookings='@json($calendarBookings)'>
              <div class="booking-admin__calendar-weekdays">
                <span>{{ __('Sun', 'pixelforge') }}</span>
                <span>{{ __('Mon', 'pixelforge') }}</span>
                <span>{{ __('Tue', 'pixelforge') }}</span>
                <span>{{ __('Wed', 'pixelforge') }}</span>
                <span>{{ __('Thu', 'pixelforge') }}</span>
                <span>{{ __('Fri', 'pixelforge') }}</span>
                <span>{{ __('Sat', 'pixelforge') }}</span>
              </div>
              <div class="booking-admin__calendar-grid" data-calendar-grid></div>
            </div>
          </div>
        </div>
      </div>
    @endif
  </section>

  @if (is_user_logged_in() && current_user_can('edit_posts'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const tabs = Array.from(document.querySelectorAll('[data-panel-toggle]'));
        const panels = Array.from(document.querySelectorAll('[data-panel]'));
        const panelContainer = document.querySelector('[data-panel-container]');
        const exportForm = document.querySelector('[data-export-form]');

        if (exportForm) {
          const rangeSelect = exportForm.querySelector('[data-export-range]');
          const fields = Array.from(exportForm.querySelectorAll('[data-export-field]'));

          function toggleExportFields() {
            const target = rangeSelect ? rangeSelect.value : '';

            fields.forEach(function (field) {
              const isActive = field.getAttribute('data-export-field') === target;
              field.hidden = !isActive;

              Array.from(field.querySelectorAll('input')).forEach(function (input) {
                input.disabled = !isActive;
              });
            });
          }

          if (rangeSelect) {
            rangeSelect.addEventListener('change', toggleExportFields);
          }

          toggleExportFields();
        }

        function setActivePanel(target) {
          tabs.forEach(function (link) {
            const isActive = link.getAttribute('data-panel-toggle') === target;
            link.classList.toggle('is-active', isActive);
            link.setAttribute('aria-selected', isActive ? 'true' : 'false');
          });

          panels.forEach(function (panel) {
            const isMatch = panel.getAttribute('data-panel') === target;
            panel.classList.toggle('is-active', isMatch);
            panel.setAttribute('aria-hidden', isMatch ? 'false' : 'true');
          });
        }

        tabs.forEach(function (tab) {
          tab.addEventListener('click', function (event) {
            event.preventDefault();
            setActivePanel(tab.getAttribute('data-panel-toggle'));
          });
        });

        if (panelContainer) {
          panelContainer.setAttribute('data-panels-ready', 'true');
        }

        setActivePanel('create');

        const calendarWrapper = document.querySelector('[data-booking-calendar]');

        if (!calendarWrapper) {
          return;
        }

        const monthLabel = document.querySelector('[data-calendar-month]');
        const grid = calendarWrapper.querySelector('[data-calendar-grid]');
        const prev = document.querySelector('[data-calendar-prev]');
        const next = document.querySelector('[data-calendar-next]');
        const today = new Date();
        let current = new Date(today.getFullYear(), today.getMonth(), 1);

        const bookings = JSON.parse(calendarWrapper.getAttribute('data-bookings') || '[]');

        function focusBooking(bookingId) {
          const target = document.querySelector('[data-booking-id="' + bookingId + '"]');

          if (!target) {
            return;
          }

          setActivePanel('list');
          target.open = true;
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });

          const summary = target.querySelector('summary');

          if (summary) {
            summary.focus({ preventScroll: true });
          }
        }

        function getBookingsForDay(year, month, day) {
          return bookings.filter(function (booking) {
            if (!booking.date) {
              return false;
            }

            const date = new Date(booking.date + 'T00:00:00');
            return date.getFullYear() === year && date.getMonth() === month && date.getDate() === day;
          });
        }

        function renderCalendar() {
          const year = current.getFullYear();
          const month = current.getMonth();
          const firstDay = new Date(year, month, 1);
          const daysInMonth = new Date(year, month + 1, 0).getDate();
          const leadingOffset = (firstDay.getDay() + 7) % 7;

          if (monthLabel) {
            monthLabel.textContent = firstDay.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
          }

          grid.innerHTML = '';

          for (let i = 0; i < leadingOffset; i += 1) {
            const filler = document.createElement('div');
            filler.className = 'booking-admin__calendar-day booking-admin__calendar-day--empty';
            grid.appendChild(filler);
          }

          for (let day = 1; day <= daysInMonth; day += 1) {
            const cell = document.createElement('div');
            cell.className = 'booking-admin__calendar-day';

            const heading = document.createElement('div');
            heading.className = 'booking-admin__calendar-date';
            heading.textContent = day.toString();
            cell.appendChild(heading);

            const items = document.createElement('div');
            items.className = 'booking-admin__calendar-events';

            getBookingsForDay(year, month, day).forEach(function (booking) {
              const tag = document.createElement('div');
              tag.className = 'booking-admin__calendar-event' + (booking.status === 'confirmed' ? ' is-confirmed' : '');
              tag.innerHTML = '<strong>' + booking.time + '</strong> ' + booking.name + ' · ' + booking.party_size + ' ' + '{{ __('guests', 'pixelforge') }}';
              tag.setAttribute('tabindex', '0');
              tag.setAttribute('role', 'button');
              tag.setAttribute('aria-label', '{{ __('View booking', 'pixelforge') }}');
              tag.dataset.bookingId = booking.id;

              tag.addEventListener('click', function () {
                focusBooking(booking.id);
              });

              tag.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                  event.preventDefault();
                  focusBooking(booking.id);
                }
              });

              if (booking.table_label) {
                const small = document.createElement('span');
                small.className = 'booking-admin__calendar-subtext';
                small.textContent = booking.table_label;
                tag.appendChild(small);
              }

              items.appendChild(tag);
            });

            if (!items.childElementCount) {
              const placeholder = document.createElement('div');
              placeholder.className = 'booking-admin__calendar-empty';
              placeholder.textContent = '{{ __('No bookings', 'pixelforge') }}';
              items.appendChild(placeholder);
            }

            cell.appendChild(items);
            grid.appendChild(cell);
          }
        }

        if (prev) {
          prev.addEventListener('click', function () {
            current.setMonth(current.getMonth() - 1);
            renderCalendar();
          });
        }

        if (next) {
          next.addEventListener('click', function () {
            current.setMonth(current.getMonth() + 1);
            renderCalendar();
          });
        }

        renderCalendar();
      });
    </script>
  @endif
@endsection
