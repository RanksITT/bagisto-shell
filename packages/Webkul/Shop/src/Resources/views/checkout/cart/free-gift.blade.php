@php
    $cart = \Webkul\Checkout\Facades\Cart::getCart();

    $claimable = $cart
        ? app(\Webkul\CartRule\Helpers\FreeGift::class)->claimableRules($cart)
        : collect();
@endphp

@foreach ($claimable as $rule)
    @php
        $giftProduct = $rule->gift_product;

        $variants = $giftProduct->variants
            ->filter(fn ($variant) => $variant->isSaleable())
            ->mapWithKeys(function ($variant) use ($giftProduct) {
                $label = $giftProduct->super_attributes
                    ->map(fn ($attribute) => optional(
                        $attribute->options->firstWhere('id', $variant->{$attribute->code})
                    )->admin_name)
                    ->filter()
                    ->implode(' / ');

                return [$variant->id => $label];
            })
            ->filter();
    @endphp

    <div class="mt-5 flex flex-wrap items-center justify-between gap-5 rounded-xl border border-darkBlue bg-navySurface p-5 max-sm:p-4">
        <div class="flex items-center gap-4">
            <span
                class="icon-heart-fill text-3xl text-darkBlue"
                role="presentation"
            ></span>

            <div>
                <p class="text-lg font-medium text-offWhite max-sm:text-base">
                    @lang('shop::app.checkout.cart.free-gift.earned')
                </p>

                <p class="text-sm text-mutedBlue" v-pre>
                    {{ $giftProduct->name }}
                </p>
            </div>
        </div>

        <x-shop::form
            :action="route('shop.checkout.cart.free_gift.store')"
            class="flex flex-wrap items-center gap-3"
        >
            <input
                type="hidden"
                name="cart_rule_id"
                value="{{ $rule->id }}"
            >

            @if ($variants->isNotEmpty())
                <label
                    for="gift-variant-{{ $rule->id }}"
                    class="sr-only"
                >
                    @lang('shop::app.checkout.cart.free-gift.choose-size')
                </label>

                <select
                    id="gift-variant-{{ $rule->id }}"
                    name="selected_configurable_option"
                    class="custom-select rounded-lg border border-navyBorder bg-navyBlue px-4 py-2.5 text-base text-offWhite"
                    required
                >
                    <option value="">@lang('shop::app.checkout.cart.free-gift.choose-size')</option>

                    @foreach ($variants as $variantId => $label)
                        <option value="{{ $variantId }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif

            <button
                type="submit"
                class="primary-button rounded-lg px-6 py-2.5 text-base"
            >
                @lang('shop::app.checkout.cart.free-gift.claim')
            </button>
        </x-shop::form>
    </div>
@endforeach
