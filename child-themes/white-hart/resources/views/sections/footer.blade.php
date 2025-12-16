<footer id="mastfooter" class="content-info mastfooter" itemscope itemtype="https://schema.org/BarOrPub">
  <div class="content-info__inner">
    @if (!empty($companyProfile['address']))
      <p class="content-info__address" itemprop="address">{!! nl2br(esc_html($companyProfile['address'])) !!}</p>
    @endif
  </div>
</footer>
