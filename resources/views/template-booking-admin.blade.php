{{--
  Template Name: Booking Admin Panel
--}}

@php($redirect = get_permalink())
@php($panel = \PixelForge\BookingAdmin\get_panel_context())
@php($notice = sanitize_text_field(wp_unslash($_GET['booking_admin_notice'] ?? '')))
@php($error = isset($_GET['booking_admin_error']) ? wp_kses_post(wp_unslash($_GET['booking_admin_error'])) : '')

@extends('layouts.booking-admin')

@section('content')
  <section class="booking-admin">
    <div class="booking-admin__header">
      <h1>{{ __('Table Booking Admin', 'pixelforge') }}</h1>
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

      <div class="booking-admin__grid">
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
                <input type="number" name="party_size" min="1" step="1" required>
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
            </div>

            <label class="booking-admin__field">
              <span>{{ __('Tables', 'pixelforge') }}</span>
              <div class="booking-admin__checkboxes">
                @foreach ($panel['tables'] as $table)
                  @php($tableSeats = get_post_meta($table->ID, 'booking_table_seats', true))
                  @php($tableSection = get_post_meta($table->ID, 'booking_table_section', true))
                  <label class="booking-admin__checkbox">
                    <input type="checkbox" name="table_ids[]" value="{{ $table->ID }}">
                    <span>{{ $table->post_title }}</span>
                    <small>
                      @if ($tableSection)
                        {{ get_the_title($tableSection) }} ·
                      @endif
                      {{ sprintf(__('Seats: %s', 'pixelforge'), $tableSeats ?: '—') }}
                    </small>
                  </label>
                @endforeach
              </div>
            </label>

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

        <div class="booking-admin__card">
          <div class="booking-admin__card-header">
            <div>
              <h2>{{ __('Existing bookings', 'pixelforge') }}</h2>
              <p class="booking-admin__muted">{{ __('Review, update, or cancel bookings.', 'pixelforge') }}</p>
            </div>
            <span class="booking-admin__pill">{{ count($panel['bookings']) }} {{ __('records', 'pixelforge') }}</span>
          </div>

          @if ($panel['bookings'] === [])
            <p class="booking-admin__muted">{{ __('No bookings found yet.', 'pixelforge') }}</p>
          @else
            <div class="booking-admin__list">
              @foreach ($panel['bookings'] as $booking)
                <details class="booking-admin__booking">
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
                          <input type="number" name="party_size" min="1" step="1" value="{{ $booking['details']['party_size'] }}" required>
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
                        <div class="booking-admin__checkboxes">
                          @foreach ($panel['tables'] as $table)
                            @php($tableSeats = get_post_meta($table->ID, 'booking_table_seats', true))
                            @php($tableSection = get_post_meta($table->ID, 'booking_table_section', true))
                            <label class="booking-admin__checkbox">
                              <input type="checkbox" name="table_ids[]" value="{{ $table->ID }}" @checked(in_array($table->ID, $booking['details']['table_ids'], true))>
                              <span>{{ $table->post_title }}</span>
                              <small>
                                @if ($tableSection)
                                  {{ get_the_title($tableSection) }} ·
                                @endif
                                {{ sprintf(__('Seats: %s', 'pixelforge'), $tableSeats ?: '—') }}
                              </small>
                            </label>
                          @endforeach
                        </div>
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
    @endif
  </section>
@endsection
