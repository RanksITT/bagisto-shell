<?php

namespace Local\BangladeshGeo\Http\Requests;

use Local\BangladeshGeo\Http\Requests\Concerns\ValidatesBdGeo;
use Webkul\Shop\Http\Requests\Customer\AddressRequest as BaseRequest;

/**
 * The customer address book form. Bound over Webkul's class in the service provider, so
 * Laravel resolves this wherever a controller type-hints the base - no Webkul file edited.
 */
class ShopCustomerAddressRequest extends BaseRequest
{
    use ValidatesBdGeo;

    public function rules()
    {
        return array_merge(parent::rules(), $this->bdGeoRules());
    }

    public function messages()
    {
        return array_merge(
            method_exists(get_parent_class($this), 'messages') ? parent::messages() : [],
            $this->bdGeoMessages()
        );
    }
}
