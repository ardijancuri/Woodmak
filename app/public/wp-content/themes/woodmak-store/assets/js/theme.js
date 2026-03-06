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
})();
