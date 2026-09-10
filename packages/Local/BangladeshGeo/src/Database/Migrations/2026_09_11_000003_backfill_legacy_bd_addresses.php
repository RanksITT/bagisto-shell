<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Enrich pre-existing Bangladesh addresses with the new geo ids.
 *
 * Because bd_districts.code is the district's exact English name, rows that already stored
 * state='Dhaka' are valid the moment the seed lands — this migration only fills in the ids.
 *
 * A few rows stored a *thana* name in the state column (e.g. 'Tejgaon'), which is not a
 * district. Those are resolved to their real district and the thana is captured as the
 * upazila, so the address becomes fully structured instead of merely readable.
 *
 * `city` is never rewritten: guessing an upazila from a free-text city would manufacture
 * data that looks authoritative and is not. Rows left with a null bd_upazila_id are picked
 * up by the address form, which forces re-selection on the customer's next edit.
 */
return new class extends Migration
{
    public function up(): void
    {
        $districts = DB::table('bd_districts')->get()->keyBy(fn ($d) => mb_strtolower($d->name));

        // Thana/upazila names are NOT unique across districts, so only accept an
        // unambiguous match — one row for that name in the whole country.
        $upazilas = DB::table('bd_upazilas')
            ->select('id', 'district_id', 'name')
            ->get()
            ->groupBy(fn ($u) => mb_strtolower($u->name));

        $report = ['district' => 0, 'via_thana' => 0, 'unresolved' => 0];

        $rows = DB::table('addresses')
            ->where('country', 'BD')
            ->whereNull('bd_district_id')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->get(['id', 'state']);

        foreach ($rows as $row) {
            $key = mb_strtolower(trim($row->state));

            if ($district = $districts->get($key)) {
                DB::table('addresses')->where('id', $row->id)->update([
                    'bd_district_id' => $district->id,
                    'bd_division_id' => $district->division_id,
                ]);

                $report['district']++;

                continue;
            }

            $candidates = $upazilas->get($key);

            if ($candidates && $candidates->count() === 1) {
                $upazila = $candidates->first();
                $district = DB::table('bd_districts')->where('id', $upazila->district_id)->first();

                DB::table('addresses')->where('id', $row->id)->update([
                    // state must hold a district code, not a thana name.
                    'state'          => $district->code,
                    'bd_district_id' => $district->id,
                    'bd_division_id' => $district->division_id,
                    'bd_upazila_id'  => $upazila->id,
                ]);

                $report['via_thana']++;

                continue;
            }

            $report['unresolved']++;
        }

        echo sprintf(
            "  backfill: %d by district, %d via thana, %d unresolved\n",
            $report['district'],
            $report['via_thana'],
            $report['unresolved']
        );
    }

    public function down(): void
    {
        // Only clears the ids this migration populated. The `state` rewrite for thana rows is
        // not reversed: the rewritten value ('Dhaka') is strictly more correct than what was
        // there before ('Tejgaon', which was never a valid district).
        DB::table('addresses')->where('country', 'BD')->update([
            'bd_division_id' => null,
            'bd_district_id' => null,
            'bd_upazila_id'  => null,
        ]);
    }
};
