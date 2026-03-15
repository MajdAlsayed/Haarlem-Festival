<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="cartOffcanvasLabel">Your Cart</h5>
            <small><span id="cartCountLabel">0</span> Item(s)</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <div id="cartEmpty" class="text-center py-5">
            <div class="fs-1 mb-3">🛒</div>
            <div>No items in your cart</div>
        </div>

        <div id="cartItems" class="flex-grow-1"></div>

        <div class="border-top pt-3 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-semibold">TOTAL</span>
                <strong>€ <span id="cartTotalLabel">0.00</span></strong>
            </div>

            <a href="/checkout" class="btn btn-dark w-100">PROCEED TO CHECKOUT</a>
        </div>
    </div>
</div>