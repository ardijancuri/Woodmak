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
  var mobilePaginationMax = 760;
  if (window.wmCatalogFilters && wmCatalogFilters.mobileCollapseMax) {
    mobileCollapseMax = parseInt(wmCatalogFilters.mobileCollapseMax, 10) || 920;
  }
  if (window.wmCatalogFilters && wmCatalogFilters.mobilePaginationMax) {
    mobilePaginationMax = parseInt(wmCatalogFilters.mobilePaginationMax, 10) || 760;
  }

  var isMobileFilters = window.matchMedia('(max-width: ' + mobileCollapseMax + 'px)').matches;
  var mobilePaginationQuery = window.matchMedia('(max-width: ' + mobilePaginationMax + 'px)');

  function setPaginationItemHidden(item, isHidden) {
    if (!item) {
      return;
    }

    item.classList.toggle('wm-pagination-hidden', isHidden);
    if (isHidden) {
      item.setAttribute('aria-hidden', 'true');
    } else {
      item.removeAttribute('aria-hidden');
    }
  }

  function syncCompactPagination() {
    var paginationList = document.querySelector('.woocommerce nav.woocommerce-pagination ul.page-numbers, .woocommerce-pagination ul.page-numbers');
    if (!paginationList) {
      return;
    }

    var items = Array.prototype.slice.call(paginationList.children || []);
    if (!items.length) {
      return;
    }

    var prevItem = null;
    var nextItem = null;
    var currentItem = null;
    var dotsItems = [];
    var numericItems = [];

    items.forEach(function (item) {
      var pageEl = item.querySelector('.page-numbers');
      if (!pageEl) {
        return;
      }

      if (pageEl.classList.contains('prev')) {
        prevItem = item;
        return;
      }

      if (pageEl.classList.contains('next')) {
        nextItem = item;
        return;
      }

      if (pageEl.classList.contains('dots')) {
        dotsItems.push(item);
        return;
      }

      var pageText = (pageEl.textContent || '').trim();
      if (/^\d+$/.test(pageText)) {
        numericItems.push(item);
      }

      if (pageEl.classList.contains('current')) {
        currentItem = item;
      }
    });

    items.forEach(function (item) {
      setPaginationItemHidden(item, false);
    });

    if (!mobilePaginationQuery.matches || numericItems.length <= 5) {
      return;
    }

    var keepItems = new Set();
    if (prevItem) {
      keepItems.add(prevItem);
    }
    if (nextItem) {
      keepItems.add(nextItem);
    }
    if (numericItems.length) {
      keepItems.add(numericItems[0]);
      keepItems.add(numericItems[numericItems.length - 1]);
    }
    if (currentItem) {
      keepItems.add(currentItem);
    }

    items.forEach(function (item) {
      var shouldHide = dotsItems.indexOf(item) !== -1 || !keepItems.has(item);
      setPaginationItemHidden(item, shouldHide);
    });
  }

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

  form.querySelectorAll('[data-wm-category-link]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
      ) {
        return;
      }

      var baseUrl = link.getAttribute('data-wm-category-base-url') || link.getAttribute('href');
      if (!baseUrl) {
        return;
      }

      event.preventDefault();

      var params = new URLSearchParams(window.location.search);
      ['wm_cat', 'wm_cat[]', 'paged', 'product-page'].forEach(function (key) {
        params.delete(key);
      });

      var targetUrl = new URL(baseUrl, window.location.origin);
      targetUrl.search = params.toString();
      window.location.href = targetUrl.toString();
    });
  });

  if (!window.wmCatalogFilters || !wmCatalogFilters.restUrl) {
    syncCompactPagination();
    if (mobilePaginationQuery.addEventListener) {
      mobilePaginationQuery.addEventListener('change', syncCompactPagination);
    } else if (mobilePaginationQuery.addListener) {
      mobilePaginationQuery.addListener(syncCompactPagination);
    }
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

        syncCompactPagination();

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

  syncCompactPagination();
  if (mobilePaginationQuery.addEventListener) {
    mobilePaginationQuery.addEventListener('change', syncCompactPagination);
  } else if (mobilePaginationQuery.addListener) {
    mobilePaginationQuery.addListener(syncCompactPagination);
  }
})();
