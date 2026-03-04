(function ($) {
  function syncSliderControls(context) {
    $(context).find('[data-wm-suggest-track]').each(function () {
      var track = this;
      var $wrap = $(track).closest('.wm-cart-sidebar__suggestions');
      var $prev = $wrap.find('[data-wm-suggest-prev]');
      var $next = $wrap.find('[data-wm-suggest-next]');
      if (!$prev.length || !$next.length) {
        return;
      }

      var maxScrollLeft = Math.max(0, track.scrollWidth - track.clientWidth);
      var left = Math.round(track.scrollLeft);
      $prev.prop('disabled', left <= 0);
      $next.prop('disabled', left >= maxScrollLeft - 1);
    });
  }

  function scrollSuggestions(button, direction) {
    var $wrap = $(button).closest('.wm-cart-sidebar__suggestions');
    var track = $wrap.find('[data-wm-suggest-track]').get(0);
    if (!track) {
      return;
    }

    var card = track.querySelector('.wm-cart-sidebar__suggestion-card');
    var scrollAmount = card ? card.getBoundingClientRect().width + 12 : 220;
    track.scrollBy({
      left: direction > 0 ? scrollAmount : -scrollAmount,
      behavior: 'smooth'
    });

    window.setTimeout(function () {
      syncSliderControls($wrap);
    }, 260);
  }

  function openSidebar() {
    $('#wm-cart-sidebar').addClass('is-open').attr('aria-hidden', 'false');
    $('#wm-cart-sidebar-overlay').addClass('is-open');
    $('body').addClass('wm-cart-open');
    syncSliderControls(document);
  }

  function closeSidebar() {
    $('#wm-cart-sidebar').removeClass('is-open').attr('aria-hidden', 'true');
    $('#wm-cart-sidebar-overlay').removeClass('is-open');
    $('body').removeClass('wm-cart-open');
  }

  function refreshSidebar() {
    if (!window.wmCartSidebar || !wmCartSidebar.restUrl) {
      return;
    }

    fetch(wmCartSidebar.restUrl, {
      credentials: 'same-origin',
      headers: {
        'X-WP-Nonce': wmCartSidebar.nonce || ''
      }
    })
      .then(function (response) { return response.json(); })
      .then(function (payload) {
        if (payload && payload.html) {
          replaceSidebarHtml(payload.html);
        }
      })
      .catch(function () {
        // Keep existing sidebar markup if refresh fails.
      });
  }

  function replaceSidebarHtml(html) {
    if (!html || typeof html !== 'string' || !html.trim().length) {
      return;
    }

    var hasWrapper = /id=["']wm-cart-sidebar-inner["']/.test(html);
    var $inner = $('#wm-cart-sidebar-inner');

    if (hasWrapper) {
      if ($inner.length) {
        $inner.replaceWith(html);
      } else {
        $('#wm-cart-sidebar').append(html);
      }
    } else if ($inner.length) {
      $inner.html(html);
    } else {
      $('#wm-cart-sidebar').append('<div id="wm-cart-sidebar-inner">' + html + '</div>');
    }

    syncSliderControls('#wm-cart-sidebar-inner');
  }

  $(document).on('click', '[data-wm-cart-open]', function (event) {
    event.preventDefault();
    openSidebar();
  });

  $(document).on('click', '[data-wm-cart-close]', function (event) {
    event.preventDefault();
    closeSidebar();
  });

  $(document).on('click', '[data-wm-suggest-prev]', function (event) {
    event.preventDefault();
    scrollSuggestions(this, -1);
  });

  $(document).on('click', '[data-wm-suggest-next]', function (event) {
    event.preventDefault();
    scrollSuggestions(this, 1);
  });

  $(document).on('scroll', '[data-wm-suggest-track]', function () {
    syncSliderControls($(this).closest('.wm-cart-sidebar__suggestions'));
  });

  $(document.body).on('added_to_cart', function (event, fragments) {
    var usedFragments = false;
    if (fragments && fragments['#wm-cart-sidebar-inner']) {
      replaceSidebarHtml(fragments['#wm-cart-sidebar-inner']);
      usedFragments = true;
    }

    if (!usedFragments) {
      refreshSidebar();
    }
    openSidebar();
  });

  $(document.body).on('removed_from_cart wc_fragments_loaded', function () {
    if ($('#wm-cart-sidebar').hasClass('is-open')) {
      refreshSidebar();
    }
  });

  $(window).on('resize', function () {
    syncSliderControls(document);
  });

  syncSliderControls(document);
})(jQuery);
