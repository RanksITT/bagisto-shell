<?php

namespace Local\BangladeshGeo\Http\Requests;

use Local\BangladeshGeo\Http\Requests\Concerns\ValidatesBdGeo;
use Webkul\Shop\Http\Requests\CartAddressRequest as BaseRequest;

/**
 * Checkout. Bagisto namespaces these fields by address type, and shipping is only validated
 * when the customer did not tick "use billing for shipping" - mirror that exactly, or a
 * same-as-billing checkout fails on shipping fields that were never rendered.
 */
class ShopCartAddressRequest extends BaseRequest
{
    use ValidatesBdGeo;

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->has('billing')) {
            $rules = array_merge($rules, $this->bdGeoRules('billing.'));
        }

        if (! $this->input('billing.use_for_shipping')) {
            $rules = array_merge($rules, $this->bdGeoRules('shipping.'));
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(
            method_exists(get_parent_class($this), 'messages') ? parent::messages() : [],
            $this->bdGeoMessages('billing.'),
            $this->bdGeoMessages('shipping.')
        );
    }
}
