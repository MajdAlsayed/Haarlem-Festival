document.addEventListener('DOMContentLoaded', () => {
    const cartBadge = document.getElementById('cartBadge');
    const cartCountLabel = document.getElementById('cartCountLabel');
    const cartTotalLabel = document.getElementById('cartTotalLabel');
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const offcanvasEl = document.getElementById('cartOffcanvas');

    let offcanvasInstance = null;
    if (offcanvasEl && window.bootstrap) {
        offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    }

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderCart(cart) {
        const itemCount = Number(cart?.item_count || 0);
        const total = Number(cart?.total || 0);
        const items = Array.isArray(cart?.items) ? cart.items : [];

        if (cartBadge) cartBadge.textContent = String(itemCount);
        if (cartCountLabel) cartCountLabel.textContent = String(itemCount);
        if (cartTotalLabel) cartTotalLabel.textContent = money(total);

        if (!cartItems || !cartEmpty) return;

        if (items.length === 0) {
            cartItems.innerHTML = '';
            cartEmpty.classList.remove('d-none');
            return;
        }

        cartEmpty.classList.add('d-none');

        cartItems.innerHTML = items.map(item => {
            const subtitleParts = [
                item.event_title || '',
                item.event_day || '',
                item.start_time || ''
            ].filter(Boolean);

            return `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1">${escapeHtml(item.name)}</h6>
                                <div class="text-muted small mb-1">${escapeHtml(item.description || '')}</div>
                                ${subtitleParts.length ? `<div class="small">${escapeHtml(subtitleParts.join(' • '))}</div>` : ''}
                                <div class="small mt-2">€ ${money(item.price)} each</div>
                            </div>

                            <button
                                class="btn btn-sm btn-outline-danger cart-remove-btn"
                                type="button"
                                data-cart-item-id="${item.cart_item_id}"
                            >
                                ×
                            </button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button
                                    class="btn btn-outline-secondary cart-qty-btn"
                                    type="button"
                                    data-action="decrease"
                                    data-cart-item-id="${item.cart_item_id}"
                                    data-current-qty="${item.quantity}"
                                >-</button>

                                <input
                                    type="text"
                                    class="form-control text-center"
                                    value="${item.quantity}"
                                    readonly
                                >

                                <button
                                    class="btn btn-outline-secondary cart-qty-btn"
                                    type="button"
                                    data-action="increase"
                                    data-cart-item-id="${item.cart_item_id}"
                                    data-current-qty="${item.quantity}"
                                >+</button>
                            </div>

                            <strong>€ ${money(item.line_total)}</strong>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function request(url, payload = null) {
        const options = {
            method: payload ? 'POST' : 'GET',
            headers: {}
        };

        if (payload) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(payload);
        }

        const response = await fetch(url, options);
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Cart request failed.');
        }

        return data;
    }

    async function refreshCart() {
        try {
            const data = await request('/cart');
            renderCart(data.cart);
        } catch (error) {
            console.error(error);
        }
    }

    async function addToCart(ticketDetailsId, quantity = 1) {
        try {
            const data = await request('/cart/add', {
                ticket_details_id: ticketDetailsId,
                quantity: quantity
            });

            renderCart(data.cart);

            if (offcanvasInstance) {
                offcanvasInstance.show();
            }
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    async function updateCartItem(cartItemId, quantity) {
        try {
            const data = await request('/cart/update', {
                cart_item_id: cartItemId,
                quantity: quantity
            });

            renderCart(data.cart);
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    async function removeCartItem(cartItemId) {
        try {
            const data = await request('/cart/remove', {
                cart_item_id: cartItemId
            });

            renderCart(data.cart);
        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    }

    document.addEventListener('click', async (event) => {
        const addBtn = event.target.closest('[data-ticket-details-id]');
        if (addBtn) {
            event.preventDefault();
            const ticketDetailsId = Number(addBtn.dataset.ticketDetailsId || 0);
            if (ticketDetailsId > 0) {
                await addToCart(ticketDetailsId, 1);
            }
            return;
        }

        const removeBtn = event.target.closest('.cart-remove-btn');
        if (removeBtn) {
            event.preventDefault();
            const cartItemId = Number(removeBtn.dataset.cartItemId || 0);
            if (cartItemId > 0) {
                await removeCartItem(cartItemId);
            }
            return;
        }

        const qtyBtn = event.target.closest('.cart-qty-btn');
        if (qtyBtn) {
            event.preventDefault();
            const cartItemId = Number(qtyBtn.dataset.cartItemId || 0);
            const currentQty = Number(qtyBtn.dataset.currentQty || 1);
            const action = qtyBtn.dataset.action || '';
            const nextQty = action === 'increase' ? currentQty + 1 : currentQty - 1;

            if (cartItemId > 0) {
                await updateCartItem(cartItemId, nextQty);
            }
        }
    });

    refreshCart();
});