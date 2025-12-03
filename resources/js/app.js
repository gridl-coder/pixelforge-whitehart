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

const navButton = document.getElementById('navButton');
const mainNav = document.getElementById('mainNav');
const navCloseButtons = document.querySelectorAll('[data-nav-close]');

const toggleNavigation = (force) => {
  if (!mainNav || !navButton) {
    return;
  }

  const shouldOpen = typeof force === 'boolean'
    ? force
    : !mainNav.classList.contains('open');

  mainNav.classList.toggle('open', shouldOpen);
  navButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
};

if (navButton && mainNav) {
  navButton.addEventListener('click', (event) => {
    event.preventDefault();
    toggleNavigation();
  });

  navCloseButtons.forEach((button) => {
    button.addEventListener('click', () => toggleNavigation(false));
  });

  document.querySelectorAll('.main-nav-list a').forEach((link) => {
    link.addEventListener('click', () => toggleNavigation(false));
  });
}

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

const initCarouselSliders = async () => {
  await loadSlick();

  const $carousels = $('.carousel-slider');

  if (!$carousels.length || typeof $carousels.slick !== 'function') {
    return;
  }

  $carousels.slick({
    dots: true,
    arrows: false,
    adaptiveHeight: false,
    autoplay: true,
    autoplaySpeed: 2000,
    slidesToShow: 1,
    slidesToScroll: 1,
  });
};

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
      slidesToShow: 6,
      slidesToScroll: 1,
      adaptiveHeight: false,
      fade: false,
      centerMode: true,
      responsive: [
        {
          breakpoint: 1200,
          settings: { slidesToShow: 6 },
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

const initHomeGalleryLightbox = () => {
  const lightbox = document.querySelector('[data-home-gallery-lightbox]');

  if (!lightbox) {
    return;
  }

  const lightboxImage = lightbox.querySelector('.home-gallery-lightbox__image');
  const lightboxCaption = lightbox.querySelector('[data-home-gallery-lightbox-caption]');
  const closeButtons = lightbox.querySelectorAll('[data-home-gallery-lightbox-close]');

  const closeLightbox = () => {
    lightbox.hidden = true;
    document.body.style.overflow = '';

    if (lightboxImage) {
      lightboxImage.src = '';
      lightboxImage.alt = '';
    }

    if (lightboxCaption) {
      lightboxCaption.textContent = '';
    }
  };

  const openLightbox = (src, alt, caption) => {
    if (!lightboxImage) {
      return;
    }

    lightboxImage.src = src;
    lightboxImage.alt = alt || '';

    if (lightboxCaption) {
      lightboxCaption.textContent = caption || '';
      lightboxCaption.hidden = !caption;
    }

    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  document.querySelectorAll('[data-lightbox-src]').forEach((image) => {
    image.addEventListener('click', (event) => {
      event.preventDefault();

      const target = event.currentTarget;
      const src = target.getAttribute('data-lightbox-src') || target.getAttribute('src');
      const alt = target.getAttribute('alt') || '';
      const caption = target.getAttribute('data-lightbox-caption') || '';

      if (src) {
        openLightbox(src, alt, caption);
      }
    });
  });

  closeButtons.forEach((button) => {
    button.addEventListener('click', () => closeLightbox());
  });

  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && lightbox && !lightbox.hidden) {
      closeLightbox();
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
    initCarouselSliders().catch((error) => console.error('Failed to init carousel', error));
    initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
    initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
    initHomeGalleryLightbox();
  });
} else {
  initCarouselSliders().catch((error) => console.error('Failed to init carousel', error));
  initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
  initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
  initHomeGalleryLightbox();
}
