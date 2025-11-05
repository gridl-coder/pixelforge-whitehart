import.meta.glob([
  '../images/**',
  '../fonts/**',
], { eager: true });

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
