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

const parseJsonData = (value) => {
  if (!value) {
    return null;
  }

  try {
    return JSON.parse(value);
  } catch (error) {
    console.warn('Failed to parse booking form data', error);
    return null;
  }
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

  const lightboxImage = lightbox.querySelector('.pub-bodmin__gallery-lightbox__image');
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

const initBookingForms = () => {
  if (typeof window.jQuery === 'undefined') {
    console.warn('Booking form requires jQuery to run.');
    return;
  }

  window.jQuery(($) => {
    $('.booking-form').each((index, container) => {
      const props = parseJsonData(container.getAttribute('data-booking-props'));

      if (!props) {
        return;
      }

      const form = $(container).find('.booking-form__form');

      if (!form.length) {
        return;
      }

      const steps = form.find('.booking-form__step');
      const progressSteps = form.find('.booking-form__progress-step');
      const menuSelect = form.find('#pixelforge_booking_menu');
      const timeSelect = form.find('#pixelforge_booking_time');
      const sectionSelect = form.find('select[name="pixelforge_booking_section"]');
      const dateInput = form.find('input[name="pixelforge_booking_date"]');
      const partyInput = form.find('select[name="pixelforge_booking_party_size"]');
      const notice = form.find('#booking_availability_notice');
      const menuSlots = props.slots || {};
      const menuDays = props.days || {};
      const menuWindows = props.windows || {};
      const dayLabels = props.dayLabels || {};
      const minDateString = props.minDate || '';
      const ajaxUrl = form.data('ajax-url');
      const unavailableDateMessage = props.messages?.unavailableDate
        || 'Selected date is unavailable for this menu.';
      const availabilityNotSet = props.messages?.availabilityNotSet
        || 'Service times not set';
      const unavailableSectionMessage = props.messages?.unavailableSection
        || 'Selected area is fully booked for this date.';
      const submissionErrorMessage = props.messages?.submissionError
        || 'We could not submit your booking right now. Please try again in a moment.';
      const alerts = {
        error: $(container).find('.booking-form__alert--error'),
        success: $(container).find('.booking-form__alert--success'),
      };
      const menuMeta = $(container).find('#booking_menu_meta');

      let currentStep = 0;

      const hideForm = () => {
        form.addClass('is-hidden').attr('aria-hidden', 'true');
      };

      const hideAlerts = () => {
        Object.values(alerts).forEach((alert) => alert.removeClass('is-visible'));
      };

      $(container).find('.booking-form__alert-close').on('click', (event) => {
        $(event.currentTarget).closest('.booking-form__alert').removeClass('is-visible');
      });

      const formatDate = (date) => {
        if (!(date instanceof Date) || Number.isNaN(date.valueOf())) {
          return '';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
      };

      const parseDate = (value) => {
        if (!value) {
          return null;
        }

        const [year, month, day] = value.split('-').map(Number);

        if (!year || !month || !day) {
          return null;
        }

        const parsed = new Date(year, month - 1, day);

        return Number.isNaN(parsed.valueOf()) ? null : parsed;
      };

      const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

      const baseMinDate = parseDate(minDateString) || new Date();
      const preferredStartDate = new Date(baseMinDate.getTime());
      preferredStartDate.setDate(preferredStartDate.getDate() + 1);

      const findNextAllowedDate = (startDate, allowedDays = []) => {
        if (!(startDate instanceof Date) || Number.isNaN(startDate.valueOf())) {
          return null;
        }

        if (!Array.isArray(allowedDays) || allowedDays.length === 0) {
          return startDate;
        }

        const cursor = new Date(startDate.getTime());

        for (let i = 0; i < 90; i += 1) {
          if (allowedDays.includes(dayNames[cursor.getDay()])) {
            return cursor;
          }

          cursor.setDate(cursor.getDate() + 1);
        }

        return null;
      };

      const isAllowedDay = (date, allowedDays = []) => {
        if (!(date instanceof Date) || Number.isNaN(date.valueOf())) {
          return false;
        }

        if (!Array.isArray(allowedDays) || allowedDays.length === 0) {
          return true;
        }

        return allowedDays.includes(dayNames[date.getDay()]);
      };

      const getAllowedDays = () => {
        const selectedMenu = Number(menuSelect.val());
        const days = menuDays[selectedMenu] || {};

        return Object.keys(days).filter((day) => days[day] === '1');
      };

      const rebuildTimes = () => {
        const selectedMenu = Number(menuSelect.val());
        const options = menuSlots[selectedMenu] || [];
        const selectedTime = timeSelect.val();

        timeSelect.find('option').remove();

        if (!options.length) {
          notice.text(availabilityNotSet).addClass('is-visible');
          return;
        }

        options.forEach((option) => {
          const optionElement = document.createElement('option');
          optionElement.value = option;
          optionElement.textContent = option;
          optionElement.selected = selectedTime === option;
          timeSelect.append(optionElement);
        });
      };

      const renderAlert = (type, messages) => {
        if (!Array.isArray(messages) || !alerts[type]) {
          return;
        }

        alerts[type].find('[data-alert-body]').empty();

        const list = $('<ul/>').addClass('booking-form__alert-list');

        messages.forEach((message) => {
          $('<li/>').text(message).appendTo(list);
        });

        list.appendTo(alerts[type].find('[data-alert-body]'));
        alerts[type].addClass('is-visible');
      };

      const renderMenuMeta = (menu) => {
        const days = menuDays[menu];
        const windows = menuWindows[menu];

        menuMeta.empty();

        if (!days && !windows) {
          return;
        }

        const daysList = $('<div/>').addClass('booking-form__meta-days');

        Object.entries(days || {}).forEach(([dayKey, isActive]) => {
          const dayLabel = dayLabels[dayKey] || dayKey;
          daysList.append(
            $('<span/>')
              .text(dayLabel)
              .toggleClass('booking-form__day--inactive', isActive === '0')
              .addClass('booking-form__day'),
          );
        });

        if (daysList.children().length) {
          daysList.appendTo(menuMeta);
        }

        if (windows) {
          const windowLabel = typeof windows === 'object'
            ? windows.label || (windows.start && windows.end ? `${windows.start} - ${windows.end}` : '')
            : windows;

          if (windowLabel) {
            menuMeta.append(
              $('<p/>')
                .text(windowLabel)
                .addClass('booking-form__meta-window'),
            );
          }
        }
      };

      const updateMenuMeta = () => {
        const menu = menuSelect.val();

        if (!menu) {
          return;
        }

        renderMenuMeta(menu);
      };

      const enforceAllowedDate = () => {
        const selectedMenu = Number(menuSelect.val());
        const date = parseDate(dateInput.val());
        const allowedDays = getAllowedDays();

        dateInput.attr('min', minDateString);

        if (!date) {
          return;
        }

        if (!isAllowedDay(date, allowedDays)) {
          const nextDate = findNextAllowedDate(date, allowedDays);

          if (nextDate) {
            dateInput.val(formatDate(nextDate));
          }
        }

        const updatedDate = parseDate(dateInput.val());

        if (!updatedDate) {
          return;
        }

        const isMenuConfigured = menuDays[selectedMenu] && menuSlots[selectedMenu];
        const isDateAllowed = isAllowedDay(updatedDate, allowedDays);

        if (isMenuConfigured && !isDateAllowed) {
          notice.text(unavailableDateMessage).addClass('is-visible');
        } else {
          notice.removeClass('is-visible');
        }
      };

      const updateDateForMenu = () => {
        const selectedMenu = Number(menuSelect.val());
        const allowedDays = getAllowedDays();
        const date = parseDate(dateInput.val());
        const selectedMenuSlots = menuSlots[selectedMenu] || [];

        if (!selectedMenuSlots.length) {
          notice.text(availabilityNotSet).addClass('is-visible');
        }

        if (date && isAllowedDay(date, allowedDays)) {
          return;
        }

        const preferredDate = date || preferredStartDate;
        const nextAvailableDate = findNextAllowedDate(preferredDate, allowedDays);

        if (nextAvailableDate) {
          dateInput.val(formatDate(nextAvailableDate));
        }
      };

      const fetchAvailability = () => {
        const menuId = Number(menuSelect.val());
        const sectionId = Number(sectionSelect.val());
        const date = dateInput.val();
        const partySize = Number(partyInput.val());

        if (!menuId || !sectionId || !date || !partySize) {
          return;
        }

        const params = new URLSearchParams({
          action: 'pixelforge_check_table_availability',
          menu: menuId,
          date,
          party_size: partySize,
        });

        notice.text('').removeClass('is-visible');
        dateInput.removeClass('booking-form__input--unavailable');

        fetch(`${ajaxUrl}?${params.toString()}`)
          .then((response) => response.json())
          .then((data) => {
            const availableSections = Array.isArray(data.availableSections)
              ? data.availableSections.map(Number)
              : [];

            const sectionSlots = typeof data.availableSlots === 'object' && data.availableSlots
              ? data.availableSlots
              : {};

            const availableSlots = Array.isArray(sectionSlots[sectionId])
              ? sectionSlots[sectionId]
              : [];

            if (availableSections.length) {
              sectionSelect.find('option').each((optionIndex, option) => {
                const optionValue = Number(option.value);
                option.disabled = !availableSections.includes(optionValue);
              });
            }

            if (data.date) {
              dateInput.val(data.date);
            }

            rebuildTimes();

            timeSelect.find('option').each((optionIndex, option) => {
              option.disabled = !availableSlots.includes(option.value);
            });

            if (data.unavailableDate) {
              notice.text(unavailableDateMessage).addClass('is-visible');
              dateInput.addClass('booking-form__input--unavailable');
              timeSelect.attr('disabled', 'disabled');
              return;
            }

            if (availableSlots.length === 0) {
              if (dateInput.val()) {
                notice.text(unavailableSectionMessage).addClass('is-visible');
                dateInput.addClass('booking-form__input--unavailable');
              }

              timeSelect.attr('disabled', 'disabled');
            } else {
              timeSelect.removeAttr('disabled');
            }
          })
          .catch(() => {
            notice.text(unavailableSectionMessage).addClass('is-visible');
            dateInput.addClass('booking-form__input--unavailable');
          });
      };

      const validateStep = (index) => {
        const fields = steps.eq(index).find('input, select, textarea').toArray();

        for (let i = 0; i < fields.length; i += 1) {
          if (!fields[i].checkValidity()) {
            fields[i].reportValidity();
            return false;
          }
        }

        return true;
      };

      const showStep = (index) => {
        currentStep = Math.max(0, Math.min(index, steps.length - 1));
        steps.removeClass('is-active').attr('aria-hidden', 'true');
        steps.eq(currentStep).addClass('is-active').attr('aria-hidden', 'false');

        progressSteps.removeClass('is-active is-complete');
        progressSteps.each(function updateProgress(idx) {
          $(this)
            .toggleClass('is-active', idx === currentStep)
            .toggleClass('is-complete', idx < currentStep);
        });
      };

      form.on('click', '.booking-form__nav--next', (event) => {
        event.preventDefault();

        if (validateStep(currentStep)) {
          showStep(currentStep + 1);
        }
      });

      form.on('click', '.booking-form__nav--prev', (event) => {
        event.preventDefault();
        showStep(currentStep - 1);
      });

      form.on('submit', (event) => {
        event.preventDefault();

        if (!validateStep(currentStep)) {
          return;
        }

        hideAlerts();

        const submitButton = form.find('.booking-form__submit');
        submitButton.prop('disabled', true).addClass('is-loading');

        const formData = new FormData(form.get(0));
        formData.append('action', 'pixelforge_submit_booking');

        fetch(ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.errors && data.errors.length) {
              renderAlert('error', data.errors);
              return;
            }

            if (data.success) {
              form.get(0).reset();
              rebuildTimes();
              showStep(0);
              renderAlert('success', data.success);
              hideForm();
            }
          })
          .catch(() => {
            renderAlert('error', [submissionErrorMessage]);
          })
          .finally(() => {
            submitButton.prop('disabled', false).removeClass('is-loading');
          });
      });

      menuSelect.on('change', () => {
        updateDateForMenu();
        updateMenuMeta();
        fetchAvailability();
      });

      sectionSelect.on('change', fetchAvailability);

      dateInput.on('change input', () => {
        updateDateForMenu();
        enforceAllowedDate();
        fetchAvailability();
      });

      dateInput.on('keydown', (event) => {
        event.preventDefault();
      });

      partyInput.on('change', fetchAvailability);

      if (alerts.success.hasClass('is-visible')) {
        hideForm();
      }

      rebuildTimes();
      updateDateForMenu();
      enforceAllowedDate();
      updateMenuMeta();
      fetchAvailability();
    });
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

const initAmenitiesOverlay = () => {
  const overlay = document.getElementById('amenity-overlay');
  if (!overlay) return;

  const closeButton = overlay.querySelector('.pub-bodmin__amenity-overlay__close');
  const titleEl = overlay.querySelector('.pub-bodmin__amenity-overlay__title');
  const descEl = overlay.querySelector('.pub-bodmin__amenity-overlay__description');
  const img1El = overlay.querySelector('.pub-bodmin__amenity-overlay__image--1');
  const img2El = overlay.querySelector('.pub-bodmin__amenity-overlay__image--2');

  const closeOverlay = () => {
    overlay.hidden = true;
    document.body.style.overflow = '';
  };

  const openOverlay = (data) => {
    titleEl.textContent = data.title;
    descEl.textContent = data.description;

    if (data.image1 && data.image1.url) {
      img1El.src = data.image1.url;
      img1El.alt = data.image1.alt || '';
      img1El.hidden = false;
    } else {
      img1El.hidden = true;
    }

    if (data.image2 && data.image2.url) {
      img2El.src = data.image2.url;
      img2El.alt = data.image2.alt || '';
      img2El.hidden = false;
    } else {
      img2El.hidden = true;
    }

    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  document.querySelectorAll('.pub-bodmin__amenities-list__button').forEach(item => {
    item.addEventListener('click', () => {
      const title = item.getAttribute('data-amenity-title');
      const description = item.getAttribute('data-amenity-description');
      const image1 = JSON.parse(item.getAttribute('data-amenity-image1') || 'null');
      const image2 = JSON.parse(item.getAttribute('data-amenity-image2') || 'null');

      if (description) {
          openOverlay({ title, description, image1, image2 });
      }
    });
  });

  closeButton.addEventListener('click', closeOverlay);

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeOverlay();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !overlay.hidden) closeOverlay();
  });
};

const lazyInitBookingForm = () => {
    const bookingForm = document.querySelector('.booking-form');
    if (!bookingForm) {
        return;
    }

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                initBookingForms();
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '200px' });

    observer.observe(bookingForm);
};

const initBackToTop = () => {
  const backToTopButton = document.getElementById('back-to-top');
  if (!backToTopButton) return;

  const scrollFunction = () => {
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
      backToTopButton.hidden = false;
    } else {
      backToTopButton.hidden = true;
    }
  };

  window.onscroll = scrollFunction;

  backToTopButton.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
};


if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initFoodBannerSlider().catch((error) => console.error('Failed to init food banner slider', error));
    initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
    initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
    initHomeGalleryLightbox();
    lazyInitBookingForm();
    initSmoothAnchorScroll();
    initAmenitiesOverlay();
    initBackToTop();
  });
} else {
  initFoodBannerSlider().catch((error) => console.error('Failed to init food banner slider', error));
  initBookingMenuSliders().catch((error) => console.error('Failed to init booking menu slider', error));
  initHomeGalleryCarousel().catch((error) => console.error('Failed to init home gallery slider', error));
  initHomeGalleryLightbox();
  lazyInitBookingForm();
  initSmoothAnchorScroll();
  initAmenitiesOverlay();
  initBackToTop();
}
