<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Webkul\CartRule\Contracts\CartRule;
use Webkul\CartRule\Helpers\FreeGift;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

class FreeGiftController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected FreeGift $freeGiftHelper,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Claim a gift the cart has earned, in the variant the shopper picked.
     *
     * Eligibility is re-checked here rather than trusted from the form, so a stale or
     * hand-crafted request cannot conjure a free product.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'cart_rule_id' => 'required|integer',
            'selected_configurable_option' => 'nullable|integer',
        ]);

        $cart = Cart::getCart();

        if (! $cart) {
            return back();
        }

        $rule = $this->freeGiftHelper->claimableRules($cart)->get(request('cart_rule_id'));

        if (! $rule) {
            session()->flash('warning', trans('shop::app.checkout.cart.free-gift.not-available'));

            return back();
        }

        $product = $this->productRepository->find($rule->gift_product_id);

        if (! $product) {
            session()->flash('warning', trans('shop::app.checkout.cart.free-gift.not-available'));

            return back();
        }

        try {
            $result = Cart::addProduct($product, [
                'product_id' => $product->id,
                'quantity' => $rule->gift_qty ?: 1,
                'selected_configurable_option' => request('selected_configurable_option'),
            ]);

            if (is_string($result)) {
                session()->flash('warning', $result);

                return back();
            }

            $this->markAsGift($cart, $rule);
        } catch (\Exception $exception) {
            session()->flash('warning', $exception->getMessage());

            return back();
        }

        session()->flash('success', trans('shop::app.checkout.cart.free-gift.added'));

        return back();
    }

    /**
     * Stamp the newly added line as a gift and zero its price.
     *
     * `custom_price` is the only hook the cart honours for overriding what an item costs,
     * so it is what makes the gift free once totals are collected.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @param  CartRule  $rule
     */
    protected function markAsGift($cart, $rule): void
    {
        $cart->refresh();

        $item = $cart->items
            ->reject(fn ($item) => $this->freeGiftHelper->isGift($item))
            ->where('product_id', $rule->gift_product_id)
            ->last();

        if (! $item) {
            return;
        }

        $item->additional = array_merge($item->additional ?? [], [
            FreeGift::ITEM_FLAG => true,
            FreeGift::ITEM_RULE_KEY => $rule->id,
        ]);

        $item->custom_price = 0;

        $item->save();

        Cart::collectTotals();
    }
}
