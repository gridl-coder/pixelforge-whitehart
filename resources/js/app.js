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

  document.querySelectorAll('.main-nav a').forEach((link) => {
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
    adaptiveHeight: true,
    autoplay: true,
    autoplaySpeed: 4000,
    slidesToShow: 1,
    slidesToScroll: 1,
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initCarouselSliders().catch((error) => console.error('Failed to init carousel', error));
  });
} else {
  initCarouselSliders().catch((error) => console.error('Failed to init carousel', error));
}
