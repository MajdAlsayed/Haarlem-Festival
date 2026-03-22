/**
 * Cart drawer UI + sync with /cart API (CSRF on POST via window.__CSRF_CART__).
 */
(function () {
  function getCsrf() {
    return typeof window.__CSRF_CART__ === 'string' ? window.__CSRF_CART__ : '';
  }

  function setCsrf(token) {
    if (typeof token === 'string' && token) {
      window.__CSRF_CART__ = token;
    }
  }

  function syncCart() {
    fetch('/cart', { credentials: 'same-origin' })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data && data.csrf) setCsrf(data.csrf);
        var n = 0;
        if (data && data.items && data.items.length) {
          data.items.forEach(function (it) {
            n += parseInt(it.quantity, 10) || 0;
          });
        }
        var badge = document.getElementById('cartBadge');
        if (badge) badge.textContent = String(n);
        var label = document.getElementById('cartCountLabel');
        if (label) label.textContent = String(n);
        var total = document.getElementById('cartTotalLabel');
        if (total && data && data.total) total.textContent = data.total;
      })
      .catch(function () {});
  }

  document.addEventListener('DOMContentLoaded', function () {
    var openBtn = document.getElementById('cartOpenBtn');
    var closeBtn = document.getElementById('cartCloseBtn');
    var drawer = document.getElementById('cartDrawer');
    var backdrop = document.getElementById('cartDrawerBackdrop');

    function openDrawer() {
      if (drawer) drawer.setAttribute('aria-hidden', 'false');
      if (backdrop) backdrop.setAttribute('aria-hidden', 'false');
      syncCart();
    }

    function closeDrawer() {
      if (drawer) drawer.setAttribute('aria-hidden', 'true');
      if (backdrop) backdrop.setAttribute('aria-hidden', 'true');
    }

    if (openBtn) openBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);

    syncCart();
  });
})();
