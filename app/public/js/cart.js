document.addEventListener('DOMContentLoaded', function () {
    var cartBadge = document.getElementById('cartBadge');
    var cartCountLabel = document.getElementById('cartCountLabel');
    var cartTotalLabel = document.getElementById('cartTotalLabel');
    var cartItems = document.getElementById('cartItems');
    var cartEmpty = document.getElementById('cartEmpty');
    var offcanvasEl = document.getElementById('cartOffcanvas');
    var offcanvasInstance = null;

    // NOTE: not in course slides (bootstrap offcanvas).
    if (offcanvasEl && window.bootstrap) {
        // Bootstrap opens the cart drawer here; this is not custom code.
        offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    }

    function getCsrf() {
        // NOTE: not in course slides (CSRF token from PHP session).
        if (typeof window.__CSRF_CART__ === 'string') {
            return window.__CSRF_CART__;
        }

        return '';
    }

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function apiGetCart() {
        return fetch('/api/cart')
            .then(function (response) {
                return response.json();
            });
    }

    function apiPost(url, data) {
        data._csrf = getCsrf();

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(function (response) {
            return response.json();
        });
    }

    function refreshCart() {
        apiGetCart()
            .then(function (data) {
                if (data.success) {
                    renderCart(data.cart);
                } else {
                    alert(data.message || 'Could not load cart.');
                }
            })
            .catch(function () {
                alert('Could not load cart.');
            });
    }

    function addToCart(ticketDetailsId, quantity, contributionTotal) {
        var data = {
            ticket_details_id: ticketDetailsId,
            quantity: quantity
        };

        if (contributionTotal !== null) {
            data.contribution_total = contributionTotal;
        }

        apiPost('/api/cart/add', data).then(function (data) {
            if (data.success) {
                renderCart(data.cart);

                // NOTE: not in course slides (bootstrap offcanvas).
                if (offcanvasInstance) {
                    offcanvasInstance.show();
                }
            } else {
                alert(data.message || 'Could not add item.');
            }
        }).catch(function () {
            alert('Could not add item.');
        });
    }

    function updateCartItem(cartItemId, quantity) {
        apiPost('/api/cart/update', {
            cart_item_id: cartItemId,
            quantity: quantity
        }).then(function (data) {
            if (data.success) {
                renderCart(data.cart);
            } else {
                alert(data.message || 'Could not update item.');
            }
        }).catch(function () {
            alert('Could not update item.');
        });
    }

    function removeCartItem(cartItemId) {
        apiPost('/api/cart/remove', {
            cart_item_id: cartItemId
        }).then(function (data) {
            if (data.success) {
                renderCart(data.cart);
            } else {
                alert(data.message || 'Could not remove item.');
            }
        }).catch(function () {
            alert('Could not remove item.');
        });
    }

    function attachRemoveButtonHandlers() {
        var removeButtons = document.getElementsByClassName('cart-remove-btn');

        for (var i = 0; i < removeButtons.length; i++) {
            removeButtons[i].addEventListener('click', function (event) {
                event.preventDefault();

                var cartItemId = Number(this.getAttribute('data-cart-item-id') || 0);
                removeCartItem(cartItemId);
            });
        }
    }

    function attachQuantityButtonHandlers() {
        var quantityButtons = document.getElementsByClassName('cart-qty-btn');

        for (var i = 0; i < quantityButtons.length; i++) {
            quantityButtons[i].addEventListener('click', function (event) {
                event.preventDefault();

                var cartItemId = Number(this.getAttribute('data-cart-item-id') || 0);
                var currentQty = Number(this.getAttribute('data-current-qty') || 1);
                var action = this.getAttribute('data-action');
                var quantity = currentQty - 1;

                if (action === 'increase') {
                    quantity = currentQty + 1;
                }

                updateCartItem(cartItemId, quantity);
            });
        }
    }

    function renderCart(cart) {
        var itemCount = Number(cart.item_count || 0);
        var total = Number(cart.total || 0);
        var items = [];
        var html = '';

        if (Array.isArray(cart.items)) {
            items = cart.items;
        }

        if (cartBadge) {
            cartBadge.innerHTML = String(itemCount);
        }
        if (cartCountLabel) {
            cartCountLabel.innerHTML = String(itemCount);
        }
        if (cartTotalLabel) {
            cartTotalLabel.innerHTML = money(total);
        }
        if (!cartItems || !cartEmpty) {
            return;
        }

        if (items.length === 0) {
            cartItems.innerHTML = '';
            cartEmpty.classList.remove('d-none');
            return;
        }

        cartEmpty.classList.add('d-none');

        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var description = '';

            if (item.description) {
                description = `<div class="text-muted small mb-1">${escapeHtml(item.description)}</div>`;
            }

            var priceText = `EUR ${money(item.price)} each`;
            if (item.is_pay_as_you_like) {
                priceText = `Pay as you like contribution: EUR ${money(item.line_total)} total`;
            }

            html += `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1">${escapeHtml(item.name)}</h6>
                                ${description}
                                <div class="small mt-2">${priceText}</div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger cart-remove-btn" type="button" data-cart-item-id="${Number(item.cart_item_id || 0)}">Remove</button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button class="btn btn-outline-secondary cart-qty-btn" type="button" data-action="decrease" data-cart-item-id="${Number(item.cart_item_id || 0)}" data-current-qty="${Number(item.quantity || 0)}">-</button>
                                <input type="text" class="form-control text-center" value="${Number(item.quantity || 0)}" readonly>
                                <button class="btn btn-outline-secondary cart-qty-btn" type="button" data-action="increase" data-cart-item-id="${Number(item.cart_item_id || 0)}" data-current-qty="${Number(item.quantity || 0)}">+</button>
                            </div>
                            <strong>EUR ${money(item.line_total)}</strong>
                        </div>
                    </div>
                </div>
            `;
        }

        cartItems.innerHTML = html;
        attachRemoveButtonHandlers();
        attachQuantityButtonHandlers();
    }

    function attachAddButtonHandlersToCollection(buttons) {
        // Loops over the buy buttons and adds a click listener to each one.
        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i].getAttribute('data-cart-handler-bound') === '1') {
                continue;
            }

            buttons[i].setAttribute('data-cart-handler-bound', '1');
            buttons[i].addEventListener('click', function (event) {
                event.preventDefault();

                var ticketDetailsId = Number(this.getAttribute('data-ticket-details-id') || 0);
                var contributionTotal = readContributionTotalForButton(this);
                addToCart(ticketDetailsId, 1, contributionTotal);
            });
        }
    }

    function moneyFromText(value) {
        var cleaned = String(value || '').replace('EUR', '').replace('€', '').replace(',', '.').trim();
        var amount = Number(cleaned);

        if (isNaN(amount) || amount < 0) {
            return 0;
        }

        return Math.round(amount * 100) / 100;
    }

    function showBuurderijContribution(amount) {
        var total = document.getElementsByClassName('buurderij-contribution-total')[0];

        if (total) {
            total.innerHTML = 'EUR ' + money(amount);
        }
    }

    function readBuurderijContribution() {
        var input = document.getElementsByClassName('buurderij-custom-amount')[0];

        if (!input) {
            return 0;
        }

        return moneyFromText(input.value);
    }

    function readContributionTotalForButton(button) {
        var source = button.getAttribute('data-contribution-source');
        var directAmount = button.getAttribute('data-contribution-total');

        if (source === 'buurderij') {
            return readBuurderijContribution();
        }

        if (directAmount !== null) {
            return moneyFromText(directAmount);
        }

        return null;
    }

    function attachBuurderijContributionHandlers() {
        var amountButtons = document.getElementsByClassName('buurderij-amount-btn');
        var customInputs = document.getElementsByClassName('buurderij-custom-amount');

        for (var i = 0; i < amountButtons.length; i++) {
            amountButtons[i].addEventListener('click', function () {
                for (var j = 0; j < amountButtons.length; j++) {
                    amountButtons[j].classList.remove('active');
                }

                this.classList.add('active');
                var amount = moneyFromText(this.getAttribute('data-contribution-amount'));
                var input = document.getElementsByClassName('buurderij-custom-amount')[0];

                if (input) {
                    input.value = money(amount);
                }

                showBuurderijContribution(amount);
            });
        }

        for (var k = 0; k < customInputs.length; k++) {
            customInputs[k].addEventListener('input', function () {
                for (var j = 0; j < amountButtons.length; j++) {
                    amountButtons[j].classList.remove('active');
                }

                showBuurderijContribution(moneyFromText(this.value));
            });
        }
    }

    function attachAddButtonHandlers() {
        var addButtons = document.getElementsByClassName('add-to-cart-button');

        attachAddButtonHandlersToCollection(addButtons);
    }

    // NOTE: not in course slides (lets pages that create buttons later connect them to the cart).
    window.attachCartButtonHandlers = attachAddButtonHandlers;

    attachBuurderijContributionHandlers();
    attachAddButtonHandlers();
    refreshCart();
});
