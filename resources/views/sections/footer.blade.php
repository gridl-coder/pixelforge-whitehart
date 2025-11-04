<footer id="mastfooter" class="content-info mastfooter">
  <div class="content-info__inner">
    @php($address = trim($company['address'] ?? ''))
    @php($email = trim($company['email'] ?? ''))
    @php($phone = trim($company['phone'] ?? ''))

    @if ($address)
      <p class="content-info__address">{!! nl2br(esc_html($address)) !!}</p>
    @endif

    @if ($email)
      <p class="content-info__email">
        <a href="mailto:{{ antispambot($email) }}">{{ antispambot($email) }}</a>
      </p>
    @endif

    @if ($phone)
      <p class="content-info__phone">
        <a href="tel:{{ preg_replace('/[^0-9\+]/', '', $phone) }}">{{ esc_html($phone) }}</a>
      </p>
    @endif
  </div>
</footer>
