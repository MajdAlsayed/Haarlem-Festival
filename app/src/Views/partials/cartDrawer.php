<div id="cartDrawerBackdrop" class="cart-backdrop" aria-hidden="true"></div>

<aside id="cartDrawer" class="cart-drawer" aria-label="Shopping cart" aria-hidden="true">
    <div class="cart-header">
        <div>
            <strong>Your Cart</strong>
            <div class="cart-subtitle">
                <span id="cartCountLabel">0</span> Item(s)
            </div>
        </div>

        <button id="cartCloseBtn" class="cart-close" type="button" aria-label="Close cart">×</button>
    </div>

    <div class="cart-body">
        <div id="cartEmpty" class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <div>No items in your cart</div>
        </div>

        <div id="cartItems" class="cart-items"></div>
    </div>

    <div class="cart-footer">
        <div class="cart-total">
            <span>TOTAL</span>
            <strong>€ <span id="cartTotalLabel">0.00</span></strong>
        </div>

        <a href="/checkout" class="cart-checkout">PROCEED TO CHECKOUT ›</a>
    </div>
</aside>
