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
 * This ENFORCES as well as populates: a tampered or stale form that posts a union
 * alongside a metro thana gets the union nulled, not persisted.
 */
class AddressObserver
{
    /** @var array<int, BdDistrict|null> */
    protected array $districts = [];

    /** @var array<int, BdUpazila|null> */
    protected array $upazilas = [];

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
        // country_states.code is the district's exact English name, so this is what every
        // raw `$address->state` display already renders correctly.
        $address->state = $district->code;
        $address->bd_division_id = $district->division_id;

        if (empty($address->bd_upazila_id)) {
            return;
        }

        $upazila = $this->upazila((int) $address->bd_upazila_id);

        if (! $upazila || $upazila->district_id !== $district->id) {
            // Mismatched pair: refuse to derive from it rather than write a plausible lie.
            return;
        }

        $address->city = $upazila->name;

        // Unions are not collected: they are rural-only, absent for every metro thana, and
        // buyers describe where they live by area and road. Level 4 is always free text, and
        // any union id arriving from a stale form is discarded rather than persisted.
        $address->bd_union_id = null;
        $address->bd_area_name = $this->normalise($address->bd_area_name);
    }

    /**
     * Tidy what the customer typed without "correcting" it - no title-casing, no
     * spell-fixing. Just trim, collapse runs of whitespace, drop a trailing comma.
     */
    protected function normalise(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value));
        $value = rtrim($value, " ,;");

        return $value === '' ? null : mb_substr($value, 0, 128);
    }

    protected function district(int $id): ?BdDistrict
    {
        return $this->districts[$id] ??= BdDistrict::find($id);
    }

    protected function upazila(int $id): ?BdUpazila
    {
        return $this->upazilas[$id] ??= BdUpazila::find($id);
    }
}
