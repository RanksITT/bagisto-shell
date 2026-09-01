<?php

namespace Webkul\CartRule\Helpers;

use Illuminate\Support\Collection;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\Checkout\Contracts\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Rule\Helpers\Validator;

class FreeGift
{
    /**
     * Action type identifying a rule that hands over a product rather than a discount.
     */
    const ACTION_TYPE = 'free_gift';

    /**
     * Marks a cart item as something the shopper was given rather than chose.
     */
    const ITEM_FLAG = 'is_free_gift';

    /**
     * Records which rule earned the gift, so it can be withdrawn when that rule stops
     * matching and so a shopper cannot claim the same offer twice.
     */
    const ITEM_RULE_KEY = 'cart_rule_id';

    /**
     * Create a new helper instance.
     */
    public function __construct(
        protected CartRuleRepository $cartRuleRepository,
        protected Validator $validator
    ) {}

    /**
     * Gift rules the cart currently earns, keyed by rule id.
     *
     * A rule qualifies when at least one item the shopper actually paid for satisfies its
     * conditions; gift items are excluded so that one gift can never qualify for another.
     *
     * @param  Cart  $cart
     */
    public function qualifyingRules($cart): Collection
    {
        $payingItems = $cart->items->reject(fn ($item) => $this->isGift($item));

        if ($payingItems->isEmpty()) {
            return collect();
        }

        return $this->giftRules($cart)
            ->filter(fn ($rule) => $payingItems->contains(
                fn ($item) => $this->validator->validate($rule, $item)
            ))
            ->keyBy('id');
    }

    /**
     * Gift rules the shopper has not already claimed.
     *
     * @param  Cart  $cart
     */
    public function claimableRules($cart): Collection
    {
        $claimed = $cart->items
            ->filter(fn ($item) => $this->isGift($item))
            ->map(fn ($item) => $this->ruleIdOf($item))
            ->filter()
            ->all();

        return $this->qualifyingRules($cart)
            ->reject(fn ($rule) => in_array($rule->id, $claimed))
            ->filter(fn ($rule) => (bool) $rule->gift_product);
    }

    /**
     * Withdraw gifts the cart no longer earns.
     *
     * Runs on every totals collection, so it must only ever delete - adding here would
     * re-add a gift the shopper deliberately removed, and would have no size to add it with.
     *
     * @param  Cart  $cart
     */
    public function revokeUnearnedGifts($cart): void
    {
        $gifts = $cart->items->filter(fn ($item) => $this->isGift($item));

        if ($gifts->isEmpty()) {
            return;
        }

        $qualifying = $this->qualifyingRules($cart);

        $revoked = $gifts->reject(fn ($item) => $qualifying->has($this->ruleIdOf($item)));

        if ($revoked->isEmpty()) {
            return;
        }

        CartItem::destroy($revoked->pluck('id')->all());

        $cart->load('items');
    }

    /**
     * Whether a cart item was given rather than bought.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     */
    public function isGift($item): bool
    {
        return (bool) ($item->additional[self::ITEM_FLAG] ?? false);
    }

    /**
     * The rule that earned a gift item, if any.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     */
    public function ruleIdOf($item): ?int
    {
        $ruleId = $item->additional[self::ITEM_RULE_KEY] ?? null;

        return $ruleId === null ? null : (int) $ruleId;
    }

    /**
     * Active gift rules for the cart's channel and customer group.
     *
     * @param  Cart  $cart
     */
    protected function giftRules($cart): Collection
    {
        $customerGroupId = $cart->customer
            ? $cart->customer->customer_group_id
            : app(CustomerGroupRepository::class)
                ->findOneWhere(['code' => 'guest'])?->id;

        return $this->cartRuleRepository
            ->scopeQuery(function ($query) use ($cart, $customerGroupId) {
                return $query
                    ->where('cart_rules.status', 1)
                    ->where('cart_rules.action_type', self::ACTION_TYPE)
                    ->whereNotNull('cart_rules.gift_product_id')
                    ->where(fn ($q) => $q->whereNull('cart_rules.starts_from')->orWhereDate('cart_rules.starts_from', '<=', now()))
                    ->where(fn ($q) => $q->whereNull('cart_rules.ends_till')->orWhereDate('cart_rules.ends_till', '>=', now()))
                    ->leftJoin('cart_rule_channels', 'cart_rules.id', '=', 'cart_rule_channels.cart_rule_id')
                    ->leftJoin('cart_rule_customer_groups', 'cart_rules.id', '=', 'cart_rule_customer_groups.cart_rule_id')
                    ->where('cart_rule_channels.channel_id', $cart->channel_id)
                    ->where('cart_rule_customer_groups.customer_group_id', $customerGroupId)
                    ->orderBy('sort_order')
                    ->select('cart_rules.*')
                    ->distinct();
            })
            ->all();
    }
}
