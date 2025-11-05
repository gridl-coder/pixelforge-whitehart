import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

$('#navButton').on('click', function (e) {

  var menu = $('#mainNav');

  if (menu.hasClass('open')) {
    $(menu).removeClass('open');
  } else {
    $(menu).addClass('open');
  }


  e.preventDefault();
  return false;
});

$('.main-nav a').on('click', function (e) {
  $('#mainNav').removeClass('open');
});


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
