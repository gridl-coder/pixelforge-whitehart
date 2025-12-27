<footer id="mastfooter" class="content-info mastfooter" itemscope itemtype="https://schema.org/BarOrPub">
  <div class="content-info__inner">
    @if (!empty($companyProfile['address']))
      <p class="pt-3 d-block content-info__address" itemprop="address"><a href="https://www.tripadvisor.co.uk/Restaurant_Review-g191280-d33974336-Reviews-The_White_Hart_Restaurant-Bodmin_Cornwall_England.html" target="_blank"> {!! nl2br(esc_html($companyProfile['address'])) !!}</a></p>
    @endif
    <p class="pb-5 d-block">Handcrafted with <span class="color--red">❤</span>︎ by Danny Cheeseman @ PixelForge, Bodmin.</p>
  </div>
</footer>
