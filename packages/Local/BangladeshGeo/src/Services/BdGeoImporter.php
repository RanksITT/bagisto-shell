<?php

namespace Local\BangladeshGeo\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BdGeoImporter
{
    /**
     * Bangladesh in the `countries` table.
     */
    public const COUNTRY_CODE = 'BD';

    /**
     * Locally curated rows (metro thanas) start here. The highest upstream upazila id is 494,
     * so an upstream refresh can never collide with a curated row.
     */
    public const LOCAL_ID_BASE = 900000;

    protected string $dataPath;

    protected bool $dryRun = false;

    /** @var array<string, int> */
    protected array $stats = [];

    /** @var list<string> */
    protected array $notes = [];

    public function __construct(?string $dataPath = null)
    {
        $this->dataPath = $dataPath ?: __DIR__.'/../Resources/data';
    }

    public function dryRun(bool $dryRun = true): static
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        return $this->stats;
    }

    /** @return list<string> */
    public function notes(): array
    {
        return $this->notes;
    }

    /**
     * Import everything. Idempotent: a second run reports zero changes.
     */
    public function run(bool $prune = false): void
    {
        $divisions = $this->readUpstream('divisions');
        $districts = $this->readUpstream('districts');
        $upazilas  = $this->readUpstream('upazilas');
        $unions    = $this->readUpstream('unions');

        $this->assertIntegrity($divisions, $districts, $upazilas, $unions);

        if ($this->dryRun) {
            $this->stats = [
                'divisions' => count($divisions),
                'districts' => count($districts),
                'upazilas'  => count($upazilas),
                'unions'    => count($unions),
            ];
            $this->notes[] = 'dry-run: nothing written';

            return;
        }

        DB::transaction(function () use ($divisions, $districts, $upazilas, $unions, $prune) {
            $this->syncDivisions($divisions);
            $this->syncDistricts($districts);
            $this->syncCountryStates();
            $this->syncUpazilas($upazilas);
            $this->syncUnions($unions);
            $this->syncMetroThanas();
            $this->recomputeDerived();

            if ($prune) {
                $this->prune($divisions, $districts, $upazilas, $unions);
            }
        });

        $this->flushCache();
    }

    /**
     * Read a phpMyAdmin JSON export.
     *
     * The records live in the node whose `type` is `table`. Never index positionally —
     * index 2 is a coincidence of one particular export and a re-export can reorder it.
     *
     * @return list<array<string, mixed>>
     */
    protected function readUpstream(string $name): array
    {
        $path = "{$this->dataPath}/upstream/{$name}.json";

        if (! is_file($path)) {
            throw new RuntimeException("Missing dataset: {$path}");
        }

        $decoded = json_decode(file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Could not decode JSON: {$path}");
        }

        foreach ($decoded as $node) {
            if (($node['type'] ?? null) === 'table' && isset($node['data']) && is_array($node['data'])) {
                return $node['data'];
            }
        }

        throw new RuntimeException("No node with type=table containing data in {$path}");
    }

    /**
     * Fail loudly on a bad dataset rather than importing orphans.
     */
    protected function assertIntegrity(array $divisions, array $districts, array $upazilas, array $unions): void
    {
        $divIds = array_flip(array_map(fn ($r) => (int) $r['id'], $divisions));
        $disIds = array_flip(array_map(fn ($r) => (int) $r['id'], $districts));
        $upaIds = array_flip(array_map(fn ($r) => (int) $r['id'], $upazilas));

        foreach ($districts as $r) {
            if (! isset($divIds[(int) $r['division_id']])) {
                throw new RuntimeException("District {$r['id']} references unknown division {$r['division_id']}");
            }
        }

        foreach ($upazilas as $r) {
            if (! isset($disIds[(int) $r['district_id']])) {
                throw new RuntimeException("Upazila {$r['id']} references unknown district {$r['district_id']}");
            }
        }

        foreach ($unions as $r) {
            if (! isset($upaIds[$this->unionParentId($r)])) {
                throw new RuntimeException("Union {$r['id']} references unknown upazila");
            }
        }
    }

    /**
     * Upstream misspells the union foreign key as `upazilla_id`. Normalise it here, at the
     * boundary, so the misspelling never reaches a column, model, route or variable.
     */
    protected function unionParentId(array $row): int
    {
        $id = $row['upazilla_id'] ?? $row['upazila_id'] ?? null;

        if ($id === null) {
            throw new RuntimeException('Union row has no upazila foreign key: '.json_encode($row));
        }

        return (int) $id;
    }

    protected function syncDivisions(array $rows): void
    {
        $now = now();

        $payload = array_map(fn ($r) => [
            'id'      => (int) $r['id'],
            'code'    => $r['name'],
            'name'    => $r['name'],
            'bn_name' => $r['bn_name'] ?? null,
            'url'     => $r['url'] ?: null,
            'source'  => 'upstream',
            'status'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows);

        // `code` is deliberately absent from the update list: it is INSERT-ONLY.
        DB::table('bd_divisions')->upsert($payload, ['id'], ['name', 'bn_name', 'url', 'updated_at']);

        $this->stats['divisions'] = count($payload);
    }

    protected function syncDistricts(array $rows): void
    {
        $now = now();

        $payload = array_map(fn ($r) => [
            'id'          => (int) $r['id'],
            'division_id' => (int) $r['division_id'],
            // code == the exact English name, and it is written into addresses.state.
            'code'        => $r['name'],
            'name'        => $r['name'],
            'bn_name'     => $r['bn_name'] ?? null,
            'lat'         => $this->decimalOrNull($r['lat'] ?? null),
            'lon'         => $this->decimalOrNull($r['lon'] ?? null),
            'url'         => $r['url'] ?: null,
            'source'      => 'upstream',
            'status'      => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $rows);

        // `code` never updates — a Chittagong->Chattogram rename upstream must not
        // invalidate addresses.state on historical orders.
        DB::table('bd_districts')->upsert(
            $payload,
            ['id'],
            ['division_id', 'name', 'bn_name', 'lat', 'lon', 'url', 'updated_at']
        );

        $this->stats['districts'] = count($payload);
    }

    /**
     * Mirror the 64 districts into `country_states` so Bagisto's native state dropdown,
     * tax rates and inventory sources all inherit them with no form changes.
     */
    protected function syncCountryStates(): void
    {
        $country = DB::table('countries')->where('code', self::COUNTRY_CODE)->first();

        if (! $country) {
            throw new RuntimeException('Bangladesh is missing from the countries table.');
        }

        $hasTranslations = DB::getSchemaBuilder()->hasTable('country_state_translations');
        $written = 0;

        foreach (DB::table('bd_districts')->orderBy('name')->get() as $district) {
            $stateId = DB::table('country_states')
                ->where('country_code', self::COUNTRY_CODE)
                ->where('code', $district->code)
                ->value('id');

            if (! $stateId) {
                $stateId = DB::table('country_states')->insertGetId([
                    'country_id'   => $country->id,
                    'country_code' => self::COUNTRY_CODE,
                    'code'         => $district->code,
                    'default_name' => $district->name,
                ]);
                $written++;
            } else {
                DB::table('country_states')->where('id', $stateId)->update(['default_name' => $district->name]);
            }

            // Bind the two tables by an explicit FK written in this same transaction, rather
            // than matching on `code` at runtime.
            DB::table('bd_districts')->where('id', $district->id)->update(['country_state_id' => $stateId]);

            if ($hasTranslations && $district->bn_name) {
                $exists = DB::table('country_state_translations')
                    ->where('country_state_id', $stateId)->where('locale', 'bn')->exists();

                if (! $exists) {
                    DB::table('country_state_translations')->insert([
                        'country_state_id' => $stateId,
                        'locale'           => 'bn',
                        'default_name'     => $district->bn_name,
                    ]);
                }
            }
        }

        $this->stats['country_states_created'] = $written;
    }

    protected function syncUpazilas(array $rows): void
    {
        $now = now();

        $payload = array_map(fn ($r) => [
            'id'          => (int) $r['id'],
            'district_id' => (int) $r['district_id'],
            'name'        => $r['name'],
            'bn_name'     => $r['bn_name'] ?? null,
            'url'         => $r['url'] ?: null,
            'type'        => 'upazila',
            'source'      => 'upstream',
            'status'      => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $rows);

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('bd_upazilas')->upsert($chunk, ['id'], ['district_id', 'name', 'bn_name', 'url', 'updated_at']);
        }

        $this->stats['upazilas'] = count($payload);
    }

    protected function syncUnions(array $rows): void
    {
        $now = now();

        // Upstream contains a handful of exact (upazila, name) duplicates — e.g. "Natai"
        // twice in Brahmanbaria Sadar (ids 242/243). Two identically named unions in one
        // upazila are indistinguishable in a dropdown, so we collapse them to the lowest id.
        // Done explicitly and reported, rather than left to the unique index to swallow.
        $seen = [];
        $deduped = [];
        $collapsed = 0;

        foreach ($rows as $r) {
            $key = $this->unionParentId($r).'|'.mb_strtolower(trim($r['name']));

            if (isset($seen[$key])) {
                $collapsed++;

                continue;
            }

            $seen[$key] = true;
            $deduped[] = $r;
        }

        if ($collapsed > 0) {
            $this->notes[] = "{$collapsed} duplicate (upazila, union name) pair(s) collapsed";
        }

        $this->stats['unions_collapsed'] = $collapsed;

        $payload = array_map(fn ($r) => [
            'id'         => (int) $r['id'],
            'upazila_id' => $this->unionParentId($r),
            'name'       => $r['name'],
            'bn_name'    => $r['bn_name'] ?? null,
            'url'        => $r['url'] ?: null,
            'source'     => 'upstream',
            'status'     => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $deduped);

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('bd_unions')->upsert($chunk, ['id'], ['upazila_id', 'name', 'bn_name', 'url', 'updated_at']);
        }

        $this->stats['unions'] = count($payload);
    }

    /**
     * Curated city-corporation thanas. The upstream dataset is rural administrative geography
     * only: selecting district Dhaka otherwise offers just Savar, Dhamrai, Keraniganj,
     * Nawabganj and Dohar, so most urban buyers could not find where they live.
     *
     * These rows are matched to districts BY NAME, never by id — the donor dataset uses a
     * different id space entirely (0 of 64 districts match name-for-id).
     */
    protected function syncMetroThanas(): void
    {
        $path = "{$this->dataPath}/local/dhaka-city.json";

        if (! is_file($path)) {
            $this->notes[] = 'no local metro thana file; skipped';

            return;
        }

        $decoded = json_decode(file_get_contents($path), true);
        $rows = $decoded['dhaka'] ?? (is_array($decoded) ? $decoded : []);

        if (! $rows) {
            $this->notes[] = 'local metro thana file is empty; skipped';

            return;
        }

        // Every row in this file is Dhaka city (Dhaka North / Dhaka South city corporations).
        $district = DB::table('bd_districts')->where('name', 'Dhaka')->first();

        if (! $district) {
            throw new RuntimeException('Cannot place metro thanas: no district named Dhaka.');
        }

        // Names already present as upstream upazilas in this district (Savar, Keraniganj, ...)
        // must not be duplicated — the (district_id, name) unique key would reject them.
        $existing = DB::table('bd_upazilas')
            ->where('district_id', $district->id)
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower($n))
            ->flip();

        $names = [];
        foreach ($rows as $r) {
            $name = trim($r['name'] ?? '');
            if ($name !== '') {
                $names[$name] = $r['bn_name'] ?? null;
            }
        }
        ksort($names, SORT_NATURAL | SORT_FLAG_CASE);

        $now = now();
        $payload = [];
        $offset = 0;
        $skipped = 0;

        foreach ($names as $name => $bnName) {
            if ($existing->has(mb_strtolower($name))) {
                $skipped++;

                continue;
            }

            // Deterministic ids, assigned once by sorted name so a re-run is stable.
            $payload[] = [
                'id'          => self::LOCAL_ID_BASE + (++$offset),
                'district_id' => $district->id,
                'name'        => $name,
                'bn_name'     => $bnName,
                'url'         => null,
                'type'        => 'thana',
                'source'      => 'local',
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        if ($payload) {
            DB::table('bd_upazilas')->upsert($payload, ['id'], ['district_id', 'name', 'bn_name', 'updated_at']);
        }

        $this->stats['metro_thanas'] = count($payload);

        if ($skipped) {
            $this->notes[] = "{$skipped} metro name(s) already existed as upazilas in Dhaka; skipped";
        }
    }

    /**
     * unions_count drives the adaptive level-4 renderer, and has_city_corporation drives the
     * urban hint. Both are derived, never hand-maintained.
     */
    protected function recomputeDerived(): void
    {
        DB::statement('
            UPDATE bd_upazilas u
            LEFT JOIN (
                SELECT upazila_id, COUNT(*) AS c FROM bd_unions WHERE status = 1 GROUP BY upazila_id
            ) t ON t.upazila_id = u.id
            SET u.unions_count = COALESCE(t.c, 0)
        ');

        DB::statement("
            UPDATE bd_districts d
            SET d.has_city_corporation = EXISTS (
                SELECT 1 FROM bd_upazilas u WHERE u.district_id = d.id AND u.type = 'thana' AND u.status = 1
            )
        ");
    }

    /**
     * Retire upstream rows that vanished from the dataset. Never deletes (an address may
     * reference the row) and never touches locally curated rows.
     */
    protected function prune(array $divisions, array $districts, array $upazilas, array $unions): void
    {
        $map = [
            'bd_divisions' => array_map(fn ($r) => (int) $r['id'], $divisions),
            'bd_districts' => array_map(fn ($r) => (int) $r['id'], $districts),
            'bd_upazilas'  => array_map(fn ($r) => (int) $r['id'], $upazilas),
            'bd_unions'    => array_map(fn ($r) => (int) $r['id'], $unions),
        ];

        $total = 0;
        foreach ($map as $table => $ids) {
            $total += DB::table($table)
                ->where('source', 'upstream')
                ->whereNotIn('id', $ids)
                ->update(['status' => 0, 'updated_at' => now()]);
        }

        $this->stats['pruned'] = $total;
    }

    protected function decimalOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    protected function flushCache(): void
    {
        Cache::forget('bd_geo.bootstrap');

        foreach (DB::table('bd_districts')->pluck('id') as $id) {
            Cache::forget("bd_geo.upazilas.{$id}");
        }

        foreach (DB::table('bd_upazilas')->pluck('id') as $id) {
            Cache::forget("bd_geo.unions.{$id}");
        }
    }
}
