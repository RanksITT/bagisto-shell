<?php

namespace Local\BangladeshGeo\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesBdGeo
{
    /**
     * Validation for the Bangladesh address cascade.
     *
     * The scoped Rule::exists IS the parent-child check: an upazila that does not belong to
     * the submitted district simply fails to exist for that query, so no custom rule object
     * is needed. This is what stops a tampered or stale form saving an incoherent address.
     *
     * `state` and `city` are relaxed because AddressObserver derives them from the ids on
     * save - validating what the client sent would be validating a value we overwrite.
     *
     * @param  string  $prefix  '' for the flat account form, 'billing.'/'shipping.' at checkout
     * @return array<string, mixed>
     */
    protected function bdGeoRules(string $prefix = ''): array
    {
        $divisionId = (int) $this->input($prefix.'bd_division_id');
        $districtId = (int) $this->input($prefix.'bd_district_id');

        return [
            $prefix.'bd_division_id' => [
                'required', 'integer',
                Rule::exists('bd_divisions', 'id')->where('status', 1),
            ],

            $prefix.'bd_district_id' => [
                'required', 'integer',
                Rule::exists('bd_districts', 'id')
                    ->where('division_id', $divisionId)
                    ->where('status', 1),
            ],

            $prefix.'bd_upazila_id' => [
                'required', 'integer',
                Rule::exists('bd_upazilas', 'id')
                    ->where('district_id', $districtId)
                    ->where('status', 1),
            ],


            // Optional: in Bangladesh the mobile number is the contact that matters, and
            // plenty of buyers have no email they actually read. Requiring one just invites
            // invented addresses and lost orders. Still validated when given.
            $prefix.'email' => ['nullable', 'email'],

            // Derived server-side by AddressObserver.
            $prefix.'state' => ['nullable'],
            $prefix.'city'  => ['nullable'],
        ];
    }

    /**
     * A generic "the bd area name field is required" is a checkout-abandonment bug.
     *
     * @return array<string, string>
     */
    protected function bdGeoMessages(string $prefix = ''): array
    {
        return [
            $prefix.'bd_division_id.required' => trans('bdgeo::app.validation.division-required'),
            $prefix.'bd_division_id.exists'   => trans('bdgeo::app.validation.division-required'),
            $prefix.'bd_district_id.required' => trans('bdgeo::app.validation.district-required'),
            $prefix.'bd_district_id.exists'   => trans('bdgeo::app.validation.district-invalid'),
            $prefix.'bd_upazila_id.required'  => trans('bdgeo::app.validation.upazila-required'),
            $prefix.'bd_upazila_id.exists'    => trans('bdgeo::app.validation.upazila-invalid'),
        ];
    }
}
