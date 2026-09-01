<?php

namespace Webkul\CartRule\Listeners;

use Webkul\CartRule\Helpers\CartRule;
use Webkul\CartRule\Helpers\FreeGift;

class Cart
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRule $cartRuleHelper,
        protected FreeGift $freeGiftHelper
    ) {}

    /**
     * Apply valid cart rules to cart
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function applyCartRules($cart)
    {
        /**
         * Withdrawn before discounts are collected, so a gift the cart no longer earns is
         * gone by the time totals are worked out rather than being priced into them.
         */
        $this->freeGiftHelper->revokeUnearnedGifts($cart);

        $this->cartRuleHelper->collect($cart);
    }
}
