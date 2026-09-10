<?php

namespace Local\BangladeshGeo\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyBdGeoCommand extends Command
{
    protected $signature = 'bd-geo:verify';

    protected $description = 'Assert the Bangladesh geo data is complete and internally consistent.';

    /**
     * Minimum expected upstream row counts. Deliberately a floor, not an equality:
     * upazila and union totals drift upstream over time, and a refresh that adds rows
     * must not fail the build.
     */
    protected const EXPECTED = [
        'bd_divisions' => 8,
        'bd_districts' => 64,
        'bd_upazilas'  => 494,
        // 4540 rows upstream minus 4 exact (upazila, name) duplicates the importer
        // deliberately collapses. See BdGeoImporter::syncUnions().
        'bd_unions'    => 4536,
    ];

    protected int $failures = 0;

    public function handle(): int
    {
        $this->info('bd-geo:verify');

        foreach (self::EXPECTED as $table => $min) {
            $count = DB::table($table)->where('source', 'upstream')->count();
            $this->check("{$table} >= {$min}", $count >= $min, "{$count} rows");
        }

        $metro = DB::table('bd_upazilas')->where('source', 'local')->count();
        $this->check('curated metro thanas present', $metro > 0, "{$metro} rows");

        $badIds = DB::table('bd_upazilas')
            ->where('source', 'local')
            ->where('id', '<=', 900000)
            ->count();
        $this->check('curated rows use the reserved id range', $badIds === 0, "{$badIds} outside 900001+");

        $retiredLocal = DB::table('bd_upazilas')->where('source', 'local')->where('status', 0)->count();
        $this->check('no curated row has been retired', $retiredLocal === 0, "{$retiredLocal} retired");

        $mapped = DB::table('bd_districts')->whereNotNull('country_state_id')->count();
        $this->check('districts mapped to country_states', $mapped === 64, "{$mapped}/64");

        $bdStates = DB::table('country_states')->where('country_code', 'BD')->count();
        $this->check('country_states has the districts', $bdStates >= 64, "{$bdStates} rows");

        // The code/name contract: code is insert-only and must still equal the name.
        $drift = DB::table('bd_districts')->whereColumn('code', '!=', 'name')->count();
        $this->check('district code has not drifted from name', $drift === 0, "{$drift} drifted");

        // Every stored address state must resolve, or an order becomes unreadable.
        $orphanStates = DB::table('addresses')
            ->where('country', 'BD')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->whereNotIn('state', DB::table('country_states')->where('country_code', 'BD')->pluck('code'))
            ->count();
        $this->check('every BD address state resolves', $orphanStates === 0, "{$orphanStates} unresolvable");

        // Referential integrity.
        $this->check('no orphan districts', $this->orphans('bd_districts', 'division_id', 'bd_divisions') === 0);
        $this->check('no orphan upazilas', $this->orphans('bd_upazilas', 'district_id', 'bd_districts') === 0);
        $this->check('no orphan unions', $this->orphans('bd_unions', 'upazila_id', 'bd_upazilas') === 0);

        $zeroUnion = DB::table('bd_upazilas')->where('type', 'upazila')->where('unions_count', 0)->count();
        $this->line(sprintf('  %-44s %s', 'upazilas with no unions (expected 5)', $zeroUnion));

        $badMetro = DB::table('addresses as a')
            ->join('bd_upazilas as u', 'u.id', '=', 'a.bd_upazila_id')
            ->where('u.type', 'thana')
            ->whereNotNull('a.bd_union_id')
            ->count();
        $this->check('no metro address carries a union', $badMetro === 0, "{$badMetro} bad rows");

        if ($this->failures > 0) {
            $this->error("{$this->failures} check(s) failed");

            return self::FAILURE;
        }

        $this->info('all checks passed');

        return self::SUCCESS;
    }

    protected function orphans(string $table, string $fk, string $parent): int
    {
        return DB::table($table.' as c')
            ->leftJoin($parent.' as p', 'p.id', '=', 'c.'.$fk)
            ->whereNull('p.id')
            ->count();
    }

    protected function check(string $label, bool $ok, string $detail = ''): void
    {
        if (! $ok) {
            $this->failures++;
        }

        $this->line(sprintf('  [%s] %-44s %s', $ok ? 'ok' : 'FAIL', $label, $detail));
    }
}
