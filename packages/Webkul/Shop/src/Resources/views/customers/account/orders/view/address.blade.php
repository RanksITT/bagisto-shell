<div class="flex flex-col max-md:hidden" v-pre>
    <p class="font-semibold leading-6 text-offWhite">
        {{ $address->company_name ?? '' }}
    </p>

    <p class="font-semibold leading-6 text-offWhite">
        {{ $address->name }}
    </p>
    
    <p class="!leading-6 text-mutedBlue">
        {{ $address->address }}<br>

        {{ $address->city }}<br>

        {{ $address->state }}<br>

        {{ core()->country_name($address->country) }} @if ($address->postcode) ({{ $address->postcode }}) @endif<br>

        {{ trans('shop::app.customers.account.orders.view.contact') }} : {{ $address->phone }}
    </p>
</div>

<!-- For Mobile View -->
<div class="text-offWhite md:hidden" v-pre>
    <p class="font-semibold">
        {{ $address->company_name ?? '' }}
    </p>

    <p class="text-xs">
        {{ $address->name }}

        {{ $address->address }}

        {{ $address->city }}

        {{ $address->state }}

        {{ core()->country_name($address->country) }} @if ($address->postcode) ({{ $address->postcode }}) @endif <br>

        <span class="no-underline">
            {{ trans('shop::app.customers.account.orders.view.contact') }} : {{ $address->phone }}
        </span>
    </p>
</div>