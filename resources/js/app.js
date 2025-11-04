import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

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
