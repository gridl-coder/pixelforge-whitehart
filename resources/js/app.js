import.meta.glob([
  '../images/**',
  '../fonts/**',
], { eager: true });

import jQuery from 'jquery';

const $ = jQuery;
// Ensure slick can find the global jQuery instance before loading the plugin
window.jQuery = window.jQuery || $;
window.$ = window.$ || $;

const loadSlick = async () => {
  if (typeof $.fn.slick === 'function') {
    return;
  }

  await import('slick-carousel');
};

const initNavigation = () => {
  if (!document.getElementById('navButton') || !document.getElementById('mainNav')) {
    return;
  }

  const toggleNavigation = (force) => {
    const navButtonRef = document.getElementById('navButton');
    const mainNavRef = document.getElementById('mainNav');

    if (!navButtonRef || !mainNavRef) {
      return;
    }

    const shouldOpen = typeof force === 'boolean'
      ? force
      : !mainNavRef.classList.contains('open');

    mainNavRef.classList.toggle('open', shouldOpen);
    navButtonRef.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
  };

  document.addEventListener('click', (event) => {
    const clickTarget = event.target;

    if (!clickTarget) {
      return;
    }

    if (clickTarget.closest('#navButton')) {
      event.preventDefault();
      toggleNavigation();
      return;
    }

    if (clickTarget.closest('[data-nav-close]')) {
      toggleNavigation(false);
      return;
    }

    if (clickTarget.closest('.main-nav-list a')) {
      toggleNavigation(false);
    }
  }, { passive: false });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initNavigation);
} else {
  initNavigation();
}

const getScrollOffset = () => {
  const header = document.getElementById('masthead');
  const isMobileHeaderFixed = window.matchMedia('(max-width: 575.98px)').matches;

  if (!header || !isMobileHeaderFixed) {
    return 0;
  }

  return header.getBoundingClientRect().height + 12;
};

const initSmoothAnchorScroll = () => {
  const anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');

  if (!anchorLinks.length) {
    return;
  }

  anchorLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      const targetId = link.getAttribute('href');

      if (!targetId || targetId === '#' || link.getAttribute('target') === '_blank') {
        return;
      }

      const targetElement = document.querySelector(targetId);

      if (!targetElement) {
        return;
      }

      event.preventDefault();

      const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
      const offsetPosition = targetPosition - getScrollOffset();

      window.scrollTo({
        top: Math.max(offsetPosition, 0),
        behavior: 'smooth',
      });
    });
  });
};

const enableJsClass = () => {
  const html = document.documentElement;

  if (!html.classList.contains('js-enabled')) {
    html.classList.add('js-enabled');
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', enableJsClass);
} else {
  enableJsClass();
}

const initHomeGalleryCarousel = async () => {
  const $gallerySliders = $('[data-home-gallery-slider]');

  if (!$gallerySliders.length) {
    return;
  }

  await loadSlick();

  if (typeof $gallerySliders.slick !== 'function') {
    return;
  }

  $gallerySliders.each((index, slider) => {
    const $slider = $(slider);

    if ($slider.hasClass('slick-initialized')) {
      return;
    }

    $slider.slick({
      dots: true,
      arrows: true,
      autoplay: true,
      autoplaySpeed: 2500,
      slidesToShow: 4,
      slidesToScroll: 1,
      adaptiveHeight: false,
      fade: false,
      centerMode: true,
      responsive: [
        {
          breakpoint: 1200,
          settings: { slidesToShow: 4 },
        },
        {
          breakpoint: 992,
          settings: { slidesToShow: 3 },
        },
        {
          breakpoint: 576,
          settings: { slidesToShow: 1 },
        },
      ],
    });
  });
};

const initFoodBannerSlider = async () => {
  const $foodBannerSlider = $('[data-food-banner-slider]');

  if (!$foodBannerSlider.length) {
    return;
  }

  await loadSlick();

  if (typeof $foodBannerSlider.slick !== 'function') {
    return;
  }

  $foodBannerSlider.each((index, slider) => {
    const $slider = $(slider);

    if ($slider.hasClass('slick-initialized')) {
      return;
    }

    $slider.slick({
      dots: true,
      arrows: false,
      autoplay: true,
      autoplaySpeed: 3000,
      slidesToShow: 1,
      slidesToScroll: 1,
      adaptiveHeight: false,
    });
  });
};

const initHomeGalleryLightbox = () => {
  const lightbox = document.querySelector('[data-home-gallery-lightbox]');

  if (!lightbox) {
    return;
  }

  const lightboxImage = lightbox.querySelector('.home-gallery-lightbox__image');
  const lightboxCaption = lightbox.querySelector('[data-home-gallery-lightbox-caption]');
  const closeButtons = lightbox.querySelectorAll('[data-home-gallery-lightbox-close]');
  const previousButton = lightbox.querySelector('[data-home-gallery-lightbox-prev]');
  const nextButton = lightbox.querySelector('[data-home-gallery-lightbox-next]');

  let galleries = {};
  let activeGallery = 'default';
  let activeIndex = -1;

  const isClonedSlide = (element) => {
    const slide = element.closest('.slick-slide');
    return !!(slide && slide.classList.contains('slick-cloned'));
  };

  const closeLightbox = () => {
    lightbox.hidden = true;
    document.body.style.overflow = '';
    activeGallery = 'default';
    activeIndex = -1;

    if (lightboxImage) {
      lightboxImage.src = '';
      lightboxImage.alt = '';
    }

    if (lightboxCaption) {
      lightboxCaption.textContent = '';
    }
  };

  const updateNavigationVisibility = (imageCount) => {
    if (!previousButton || !nextButton) {
      return;
    }

    const hasMultipleImages = imageCount > 1;
    previousButton.hidden = !hasMultipleImages;
    nextButton.hidden = !hasMultipleImages;
  };

  const renderSlide = () => {
    const galleryItems = galleries[activeGallery] || [];

    if (!galleryItems.length || activeIndex < 0) {
      closeLightbox();
      return;
    }

    const target = galleryItems[activeIndex];
    const src = target.getAttribute('data-lightbox-src') || target.getAttribute('src');
    const alt = target.getAttribute('alt') || '';
    const caption = target.getAttribute('data-lightbox-caption') || '';

    if (lightboxImage && src) {
      lightboxImage.src = src;
      lightboxImage.alt = alt;
    }

    if (lightboxCaption) {
      lightboxCaption.textContent = caption || '';
      lightboxCaption.hidden = !caption;
    }

    updateNavigationVisibility(galleryItems.length);
  };

  const collectGalleries = () => {
    galleries = {};

    document.querySelectorAll('[data-lightbox-src]').forEach((image) => {
      if (isClonedSlide(image)) {
        return;
      }

      const galleryName = image.getAttribute('data-lightbox-gallery') || 'default';

      if (!galleries[galleryName]) {
        galleries[galleryName] = [];
      }

      galleries[galleryName].push(image);
    });
  };

  const findOriginalTarget = (target) => {
    const galleryName = target.getAttribute('data-lightbox-gallery') || 'default';
    const targetId = target.getAttribute('data-lightbox-id');

    if (!targetId) {
      return target;
    }

    const possibleMatches = Array.from(document.querySelectorAll(`[data-lightbox-gallery="${galleryName}"][data-lightbox-id="${targetId}"]`))
      .filter((node) => !isClonedSlide(node));

    return possibleMatches[0] || target;
  };

  const openLightbox = (target) => {
    const normalizedTarget = findOriginalTarget(target);
    const galleryName = normalizedTarget.getAttribute('data-lightbox-gallery') || 'default';

    collectGalleries();

    activeGallery = galleryName;
    activeIndex = (galleries[galleryName] || []).indexOf(normalizedTarget);

    if (activeIndex < 0) {
      activeIndex = 0;
    }

    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';

    renderSlide();
  };

  const changeSlide = (direction) => {
    const galleryItems = galleries[activeGallery] || [];

    if (!galleryItems.length) {
      return;
    }

    const total = galleryItems.length;
    activeIndex = (activeIndex + direction + total) % total;

    renderSlide();
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-lightbox-src]');

    if (!trigger) {
      return;
    }

    event.preventDefault();

    openLightbox(trigger);
  });

  closeButtons.forEach((button) => {
    button.addEventListener('click', () => closeLightbox());
  });

  if (previousButton) {
    previousButton.addEventListener('click', () => changeSlide(-1));
  }

  if (nextButton) {
    nextButton.addEventListener('click', () => changeSlide(1));
  }

  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (!lightbox || lightbox.hidden) {
      return;
    }

    if (event.key === 'Escape') {
      closeLightbox();
    }

    if (event.key === 'ArrowLeft') {
      changeSlide(-1);
    }

    if (event.key === 'ArrowRight') {
      changeSlide(1);
    }
  });
};

const initBookingMenuSliders = async () => {
  await loadSlick();

  const $sliders = $('.booking-menu-slider');

  if (!$sliders.length || typeof $sliders.slick !== 'function') {
    return;
  }

  $sliders.each((index, slider) => {
    const $slider = $(slider);

    if ($slider.hasClass('slick-initialized')) {
      return;
    }

    $slider.slick({
      dots: true,
      arrows: true,
      adaptiveHeight: false,
      autoplay: true,
      autoplaySpeed: 2500,
      slidesToShow: 4,
      slidesToScroll: 1,
      responsive: [
        {
          breakpoint: 1200,
          settings: { slidesToShow: 3 },
        },
        {
          breakpoint: 992,
          settings: { slidesToShow: 2 },
        },
        {
          breakpoint: 576,
          settings: { slidesToShow: 1 },
        },
      ],
    });
  });
};


if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initFoodBannerSlider().catch((error) => console.error('Failed to init food banner slider', error));
    initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
    initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
    initHomeGalleryLightbox();
    initSmoothAnchorScroll();
  });
} else {
  initFoodBannerSlider().catch((error) => console.error('Failed to init food banner slider', error));
  initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
  initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
  initHomeGalleryLightbox();
  initSmoothAnchorScroll();
}
