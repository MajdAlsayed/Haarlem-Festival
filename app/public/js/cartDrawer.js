document.addEventListener("DOMContentLoaded", () => {
  const drawer = document.getElementById("cartDrawer");
  const backdrop = document.getElementById("cartDrawerBackdrop");
  const openBtn = document.getElementById("cartOpenBtn");
  const closeBtn = document.getElementById("cartCloseBtn");

  const cartItemsEl = document.getElementById("cartItems");
  const cartEmptyEl = document.getElementById("cartEmpty");
  const cartCountLabel = document.getElementById("cartCountLabel");
  const cartTotalLabel = document.getElementById("cartTotalLabel");
  const cartBadge = document.getElementById("cartBadge");

  // If any required element missing, stop silently
  if (!drawer || !backdrop || !openBtn || !closeBtn) return;

  function openCart() {
    drawer.classList.add("is-open");
    backdrop.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    backdrop.setAttribute("aria-hidden", "false");
    refreshCart();
  }

  function closeCart() {
    drawer.classList.remove("is-open");
    backdrop.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    backdrop.setAttribute("aria-hidden", "true");
  }

  async function refreshCart() {
    try {
      const res = await fetch("/cart", { headers: { "Accept": "application/json" } });
      const data = await res.json();

      const count = Number(data.count ?? 0);
      const total = Number(data.total ?? 0);

      if (cartCountLabel) cartCountLabel.textContent = String(count);
      if (cartTotalLabel) cartTotalLabel.textContent = total.toFixed(2);
      if (cartBadge) cartBadge.textContent = String(count);

      const items = data.items ?? [];
      if (!items.length) {
        if (cartEmptyEl) cartEmptyEl.style.display = "flex";
        if (cartItemsEl) cartItemsEl.innerHTML = "";
        return;
      }

      if (cartEmptyEl) cartEmptyEl.style.display = "none";

      if (cartItemsEl) {
        cartItemsEl.innerHTML = items.map(it => `
          <div class="cart-item">
            <div class="cart-item-title">${escapeHtml(it.title)}</div>
            <div class="cart-item-row">
              <div>€ ${(Number(it.unit_price) || 0).toFixed(2)}</div>
              <div class="cart-qty">
                <button type="button" onclick="cartUpdate(${it.id}, ${it.qty - 1})">-</button>
                <span>${it.qty}</span>
                <button type="button" onclick="cartUpdate(${it.id}, ${it.qty + 1})">+</button>
                <button type="button" class="cart-remove" onclick="cartRemove(${it.id})">Remove</button>
              </div>
            </div>
          </div>
        `).join("");
      }
    } catch (e) {
      // optional: console.log(e);
    }
  }

  async function cartRemove(id) {
    const body = new URLSearchParams({ ticket_product_id: String(id) });
    await fetch("/cart/remove", { method: "POST", body });
    refreshCart();
  }

  async function cartUpdate(id, qty) {
    const body = new URLSearchParams({
      ticket_product_id: String(id),
      qty: String(qty),
    });
    await fetch("/cart/update", { method: "POST", body });
    refreshCart();
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // Events
  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openCart();
  });

  closeBtn.addEventListener("click", closeCart);
  backdrop.addEventListener("click", closeCart);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeCart();
  });

  // expose for onclick buttons
  window.cartRemove = cartRemove;
  window.cartUpdate = cartUpdate;

  // ✅ only refresh badge on load, DO NOT open drawer
  refreshCart();
});
