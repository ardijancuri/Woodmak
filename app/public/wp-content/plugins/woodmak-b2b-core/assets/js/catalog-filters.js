(function () {
  var form = document.querySelector('[data-wm-catalog-filters]');
  if (!form) {
    return;
  }

  function setSectionExpanded(toggle, section, target, isExpanded) {
    toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    target.hidden = !isExpanded;
    if (section) {
      section.classList.toggle('is-collapsed', !isExpanded);
    }
  }

  var mobileCollapseMax = 920;
  if (window.wmCatalogFilters && wmCatalogFilters.mobileCollapseMax) {
    mobileCollapseMax = parseInt(wmCatalogFilters.mobileCollapseMax, 10) || 920;
  }

  var isMobileFilters = window.matchMedia('(max-width: ' + mobileCollapseMax + 'px)').matches;

  form.querySelectorAll('[data-wm-filter-toggle]').forEach(function (toggle) {
    var targetId = toggle.getAttribute('aria-controls');
    var target = targetId ? document.getElementById(targetId) : null;
    if (!target) {
      return;
    }
    var section = toggle.closest('[data-wm-filter-section]');
    var defaultExpanded = toggle.getAttribute('aria-expanded') === 'true';

    setSectionExpanded(toggle, section, target, isMobileFilters ? false : defaultExpanded);

    toggle.addEventListener('click', function () {
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      setSectionExpanded(toggle, section, target, !expanded);
    });
  });

  if (!window.wmCatalogFilters || !wmCatalogFilters.restUrl) {
    return;
  }

  function updateFromQuery(queryString, push) {
    var url = wmCatalogFilters.restUrl + '?' + queryString;

    fetch(url, { credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (data) {
        var products = document.querySelector('ul.products');
        var productsLoop = document.querySelector('#wm-products-loop');
        var pagination = document.querySelector('.woocommerce-pagination');
        var resultCount = document.querySelector('.woocommerce-result-count');

        if (products && data.products_html) {
          var wrapper = document.createElement('div');
          wrapper.innerHTML = data.products_html;
          var newProducts = wrapper.querySelector('ul.products');
          if (newProducts) {
            products.outerHTML = newProducts.outerHTML;
          }
        } else if (productsLoop && data.products_html) {
          productsLoop.innerHTML = data.products_html;
        }

        if (pagination && typeof data.pagination_html !== 'undefined') {
          pagination.innerHTML = data.pagination_html;
        }

        if (resultCount && data.result_count) {
          resultCount.textContent = data.result_count;
        }

        if (push) {
          var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
          window.history.pushState({}, '', newUrl);
        }
      })
      .catch(function () {
        // Fail gracefully if REST response fails.
      });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    var params = new URLSearchParams(new FormData(form));
    updateFromQuery(params.toString(), true);
  });

  window.addEventListener('popstate', function () {
    var params = new URLSearchParams(window.location.search);
    form.querySelectorAll('input, select').forEach(function (el) {
      if (!el.name) {
        return;
      }

      if (el.type === 'checkbox' || el.type === 'radio') {
        var keyValues = params.getAll(el.name);
        el.checked = keyValues.indexOf(el.value) !== -1;
        return;
      }

      el.value = params.get(el.name) || '';
    });
    updateFromQuery(params.toString(), false);
  });
})();
