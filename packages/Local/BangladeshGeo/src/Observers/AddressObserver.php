<?php

namespace Local\BangladeshGeo\Observers;

use Local\BangladeshGeo\Models\BdDistrict;
use Local\BangladeshGeo\Models\BdUpazila;

/**
 * Derives the native Bagisto address columns from the Bangladesh geo ids.
 *
 * Everything downstream - invoices, packing slips, the ERP export, tax matching - reads
 * `country`, `state` and `city`. Deriving them here, on save, means they can never
 * disagree with the selected ids, and it covers checkout, the account address book,
 * the admin panel and guest checkout in one place rather than four.
 *
 * Level 4 is not collected. Unions are rural-only and absent for every metro thana, and the
 * house, road and area detail already lives in the street address, so a union id or area
 * name arriving from a stale form is discarded rather than persisted.
 */
class AddressObserver
{
    /**
     * Districts already looked up in this request, keyed by id.
     *
     * @var array<int, BdDistrict|null>
     */
    protected array $districts = [];

    /**
     * Upazilas already looked up in this request, keyed by id.
     *
     * @var array<int, BdUpazila|null>
     */
    protected array $upazilas = [];

    /**
     * Fill `country`, `state` and `city` from the geo ids before the address is written.
     *
     * `state` takes the district's code, which is its exact English name, so every raw
     * `$address->state` display already renders correctly. `city` takes the upazila or thana
     * name, but only when that upazila really belongs to the district: a mismatched pair is
     * refused rather than turned into a plausible lie.
     */
    public function saving($address): void
    {
        if (empty($address->bd_district_id)) {
            return;
        }

        $district = $this->district((int) $address->bd_district_id);

        if (! $district) {
            return;
        }

        $address->country = 'BD';
        $address->state = $district->code;
        $address->bd_division_id = $district->division_id;
        $address->bd_union_id = null;
        $address->bd_area_name = null;

        if (empty($address->bd_upazila_id)) {
            return;
        }

        $upazila = $this->upazila((int) $address->bd_upazila_id);

        if (! $upazila || $upazila->district_id !== $district->id) {
            return;
        }

        $address->city = $upazila->name;
    }

    /**
     * Find a district, once per id per request.
     */
    protected function district(int $id): ?BdDistrict
    {
        return $this->districts[$id] ??= BdDistrict::find($id);
    }

    /**
     * Find an upazila or thana, once per id per request.
     */
    protected function upazila(int $id): ?BdUpazila
    {
        return $this->upazilas[$id] ??= BdUpazila::find($id);
    }
}
