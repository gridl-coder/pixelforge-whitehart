<footer id="mastfooter" class="content-info mastfooter">
  <div class="content-info__inner">
    @if (!empty($companyProfile['address']))
      <p class="content-info__address">{!! nl2br(esc_html($companyProfile['address'])) !!}</p>
    @endif

    @if (!empty($companyProfile['email']))
      <p class="content-info__email">
        <a href="mailto:{{ antispambot($companyProfile['email']) }}">{{ antispambot($companyProfile['email']) }}</a>
      </p>
    @endif

    @if (!empty($companyProfile['phone']))
      <p class="content-info__phone">
        <a href="tel:{{ $companyProfile['phone']['tel'] }}">{{ esc_html($companyProfile['phone']['display']) }}</a>
      </p>
    @endif
  </div>
</footer>
