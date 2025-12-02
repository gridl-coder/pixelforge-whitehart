<footer id="mastfooter" class="content-info mastfooter">
  <div class="content-info__inner">
    @if (!empty($companyProfile['address']))
      <p class="content-info__address">{!! nl2br(esc_html($companyProfile['address'])) !!}</p>
    @endif
  </div>
</footer>
