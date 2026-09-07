<div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
    <img
        class="max-md:h-[100px] max-md:w-[100px]"
        src="{{ bagisto_asset('images/thank-you.png') }}"
        alt="{{ trans('shop::app.products.catalog.empty') }}"
        loading="lazy"
        decoding="async"
    />

    <p
        class="text-xl max-md:text-sm"
        role="heading"
    >
        @lang('shop::app.products.catalog.empty')
    </p>

    <a
        class="secondary-button mt-6 w-max rounded-2xl px-11 py-3 text-center text-base max-md:rounded-lg max-sm:px-6 max-sm:py-1.5 max-sm:text-sm"
        href="{{ route('shop.products.index') }}"
    >
        @lang('shop::app.products.catalog.all-products')
    </a>
</div>
