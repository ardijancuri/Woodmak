(function () {
  var navToggle = document.querySelector('[data-ws-nav-toggle]');
  var mobileNav = document.getElementById('ws-mobile-nav');
  var mobileNavOverlay = document.querySelector('.ws-mobile-nav__overlay');
  var navCloseTriggers = document.querySelectorAll('[data-ws-nav-close]');
  var categoriesToggle = document.querySelector('[data-ws-categories-toggle]');
  var categoriesMega = document.querySelector('[data-ws-category-mega]');
  var mobileCategoriesToggle = document.querySelector('[data-ws-mobile-categories-toggle]');
  var mobileCategoriesPanel = document.querySelector('[data-ws-mobile-categories-panel]');

  var closeMobileCategories = function () {
    if (!mobileCategoriesToggle || !mobileCategoriesPanel) {
      return;
    }

    mobileCategoriesPanel.hidden = true;
    mobileCategoriesToggle.classList.remove('is-open');
    mobileCategoriesToggle.setAttribute('aria-expanded', 'false');
  };

  var closeMobileNav = function () {
    if (!mobileNav) {
      return;
    }

    mobileNav.classList.remove('is-open');
    mobileNav.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ws-nav-lock');

    if (mobileNavOverlay) {
      mobileNavOverlay.classList.remove('is-open');
    }

    if (navToggle) {
      navToggle.setAttribute('aria-expanded', 'false');
    }

    closeMobileCategories();
  };

  var openMobileNav = function () {
    if (!mobileNav) {
      return;
    }

    mobileNav.classList.add('is-open');
    mobileNav.setAttribute('aria-hidden', 'false');
    document.body.classList.add('ws-nav-lock');

    if (mobileNavOverlay) {
      mobileNavOverlay.classList.add('is-open');
    }

    if (navToggle) {
      navToggle.setAttribute('aria-expanded', 'true');
    }
  };

  if (mobileNav && navToggle) {
    navToggle.addEventListener('click', function () {
      var shouldOpen = !mobileNav.classList.contains('is-open');
      if (shouldOpen) {
        openMobileNav();
      } else {
        closeMobileNav();
      }
    });
  }

  navCloseTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      closeMobileNav();
    });
  });

  if (mobileNav) {
    mobileNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        closeMobileNav();
      });
    });
  }

  document.querySelectorAll('[data-ws-nav-links]').forEach(function (nav) {
    var currentPath = window.location.pathname.replace(/\/$/, '');
    nav.querySelectorAll('a').forEach(function (link) {
      var href;
      try {
        href = new URL(link.href).pathname.replace(/\/$/, '');
      } catch (e) {
        href = '';
      }
      if (href && href === currentPath) {
        link.classList.add('is-active');
      }
    });
  });

  if (mobileCategoriesToggle && mobileCategoriesPanel) {
    mobileCategoriesToggle.addEventListener('click', function () {
      var shouldOpen = mobileCategoriesPanel.hidden;
      mobileCategoriesPanel.hidden = !shouldOpen;
      mobileCategoriesToggle.classList.toggle('is-open', shouldOpen);
      mobileCategoriesToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    });
  }

  if (categoriesToggle && categoriesMega) {
    var closeMegaMenu = function () {
      categoriesMega.hidden = true;
      categoriesToggle.classList.remove('is-open');
      categoriesToggle.setAttribute('aria-expanded', 'false');
    };

    categoriesToggle.addEventListener('click', function () {
      var shouldOpen = categoriesMega.hidden;
      categoriesMega.hidden = !shouldOpen;
      categoriesToggle.classList.toggle('is-open', shouldOpen);
      categoriesToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
      if (categoriesMega.hidden) {
        return;
      }

      if (categoriesMega.contains(event.target) || categoriesToggle.contains(event.target)) {
        return;
      }

      closeMegaMenu();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeMobileNav();
        closeMegaMenu();
      }
    });
  } else {
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeMobileNav();
        closeMobileCategories();
      }
    });
  }

  document.querySelectorAll('[data-ws-product-tabs]').forEach(function (productTabs) {
    var triggers = productTabs.querySelectorAll('[data-ws-tab-trigger]');
    var panels = productTabs.querySelectorAll('[data-ws-tab-panel]');

    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        var target = trigger.getAttribute('data-ws-tab-trigger');

        triggers.forEach(function (item) {
          var isActive = item === trigger;
          item.classList.toggle('is-active', isActive);
          item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
          var isActive = panel.getAttribute('data-ws-tab-panel') === target;
          panel.classList.toggle('is-active', isActive);
          panel.hidden = !isActive;
        });
      });
    });
  });

  document.querySelectorAll('[data-ws-faq]').forEach(function (faqList) {
    var items = Array.prototype.slice.call(faqList.querySelectorAll('.ws-home-faq__item'));

    if (!items.length) {
      return;
    }

    items.forEach(function (item) {
      item.addEventListener('toggle', function () {
        if (!item.open) {
          return;
        }

        items.forEach(function (otherItem) {
          if (otherItem === item || !otherItem.open) {
            return;
          }

          otherItem.open = false;
        });
      });
    });
  });

  var initSingleProductGallery = function () {
    if (!document.body.classList.contains('single-product')) {
      return;
    }

    document.querySelectorAll('[data-ws-product-gallery]').forEach(function (gallery) {
      var slides = Array.prototype.slice.call(gallery.querySelectorAll('.ws-product-gallery__slide'));
      var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.ws-product-gallery__thumb'));

      if (!slides.length) {
        return;
      }

      var setActiveGalleryImage = function (index) {
        slides.forEach(function (slide, slideIndex) {
          var isActive = slideIndex === index;
          slide.classList.toggle('is-active', isActive);
          slide.hidden = !isActive;
        });

        thumbs.forEach(function (thumb, thumbIndex) {
          var isActive = thumbIndex === index;
          thumb.classList.toggle('is-active', isActive);
          thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
      };

      setActiveGalleryImage(0);

      if (slides.length <= 1 || !thumbs.length) {
        return;
      }

      thumbs.forEach(function (thumb, index) {
        thumb.addEventListener('click', function (event) {
          event.preventDefault();
          setActiveGalleryImage(index);
        });
      });
    });
  };

  var dispatchNativeChange = function (element) {
    if (!element) {
      return;
    }

    var changeEvent;

    try {
      changeEvent = new Event('change', { bubbles: true });
    } catch (error) {
      changeEvent = document.createEvent('Event');
      changeEvent.initEvent('change', true, true);
    }

    element.dispatchEvent(changeEvent);
  };

  var initVariationSwatches = function () {
    if (!document.body.classList.contains('single-product')) {
      return;
    }

    document.querySelectorAll('.single-product .product .summary').forEach(function (summary) {
      var form = summary.querySelector('form.variations_form');
      var swatchPanel = summary.querySelector('[data-ws-summary-swatches]');

      if (!form || !swatchPanel) {
        return;
      }

      var swatches = Array.prototype.slice.call(swatchPanel.querySelectorAll('[data-ws-summary-swatch]'));

      if (!swatches.length) {
        return;
      }

      var getSelect = function (targetName) {
        if (!targetName) {
          return null;
        }

        return form.querySelector('select[name="' + targetName + '"]');
      };

      var syncAttribute = function (targetName) {
        var select = getSelect(targetName);

        if (!select) {
          return;
        }

        var enabledOptions = {};

        Array.prototype.slice.call(select.options).forEach(function (option) {
          if (!option.value) {
            return;
          }

          enabledOptions[option.value] = !option.disabled;
        });

        swatches.forEach(function (swatch) {
          if (swatch.getAttribute('data-ws-target-attribute') !== targetName) {
            return;
          }

          var value = swatch.getAttribute('data-ws-attribute-value');
          var isActive = value === select.value;
          var isEnabled = Object.prototype.hasOwnProperty.call(enabledOptions, value) ? enabledOptions[value] : false;

          swatch.classList.toggle('is-active', isActive);
          swatch.disabled = !isEnabled;
          swatch.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          swatch.setAttribute('aria-disabled', isEnabled ? 'false' : 'true');
        });
      };

      var syncAllAttributes = function () {
        var seen = {};

        swatches.forEach(function (swatch) {
          var targetName = swatch.getAttribute('data-ws-target-attribute');

          if (!targetName || seen[targetName]) {
            return;
          }

          seen[targetName] = true;
          syncAttribute(targetName);
        });
      };

      swatches.forEach(function (swatch) {
        swatch.addEventListener('click', function (event) {
          var targetName = swatch.getAttribute('data-ws-target-attribute');
          var select = getSelect(targetName);

          event.preventDefault();

          if (!select || swatch.disabled) {
            return;
          }

          select.value = swatch.getAttribute('data-ws-attribute-value');
          dispatchNativeChange(select);
          window.setTimeout(syncAllAttributes, 0);
          window.setTimeout(syncAllAttributes, 60);
        });
      });

      form.addEventListener('change', function (event) {
        if (!event.target || event.target.tagName !== 'SELECT') {
          return;
        }

        window.setTimeout(syncAllAttributes, 0);
        window.setTimeout(syncAllAttributes, 60);
      });

      form.addEventListener('click', function (event) {
        if (!event.target.closest('.reset_variations')) {
          return;
        }

        window.setTimeout(syncAllAttributes, 0);
        window.setTimeout(syncAllAttributes, 60);
      });

      syncAllAttributes();
      window.setTimeout(syncAllAttributes, 60);
      window.setTimeout(syncAllAttributes, 180);
    });
  };

  initSingleProductGallery();
  initVariationSwatches();
})();
