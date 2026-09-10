<?php

namespace Local\BangladeshGeo\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Local\BangladeshGeo\Models\BdDistrict;
use Local\BangladeshGeo\Models\BdDivision;
use Local\BangladeshGeo\Models\BdUnion;
use Local\BangladeshGeo\Models\BdUpazila;

/**
 * Read-only lookups for the address cascade.
 *
 * Payload strategy: divisions + districts are ~11 KB and get inlined into the page, so the
 * first two selects work with no network at all. Upazilas are fetched per district (5-20
 * rows) and unions per upazila. The full union set is 574 KB - roughly 60x everything else
 * combined - so it is never shipped whole to a mobile-first market.
 */
class GeoController extends Controller
{
    /**
     * Divisions and districts together: what the page needs before the customer touches anything.
     */
    public function bootstrap(): JsonResponse
    {
        $data = Cache::rememberForever('bd_geo.bootstrap', fn () => [
            'divisions' => BdDivision::active()
                ->orderBy('name')
                ->get(['id', 'name', 'bn_name'])
                ->toArray(),
            'districts' => BdDistrict::active()
                ->orderBy('name')
                ->get(['id', 'division_id', 'code', 'name', 'bn_name', 'has_city_corporation'])
                ->toArray(),
        ]);

        return $this->cached($data);
    }

    /**
     * Level 3 for a district: rural upazilas and curated metro thanas together, ordered so
     * city thanas surface first in districts that have them.
     *
     * `type` and `unions_count` travel with every row so the front end can choose the level-4
     * renderer without a second request - and so it never fetches an empty union list just to
     * discover the list is empty.
     */
    public function upazilas(Request $request): JsonResponse
    {
        $districtId = $this->intParam($request, 'district_id');

        if (! $districtId) {
            return response()->json(['message' => 'district_id is required'], 422);
        }

        $data = Cache::rememberForever("bd_geo.upazilas.{$districtId}", fn () => BdUpazila::active()
            ->where('district_id', $districtId)
            ->orderByRaw("FIELD(type, 'thana', 'upazila')")
            ->orderBy('name')
            ->get(['id', 'district_id', 'name', 'bn_name', 'type', 'unions_count'])
            ->toArray());

        return $this->cached(['data' => $data]);
    }

    public function unions(Request $request): JsonResponse
    {
        $upazilaId = $this->intParam($request, 'upazila_id');

        if (! $upazilaId) {
            return response()->json(['message' => 'upazila_id is required'], 422);
        }

        $data = Cache::rememberForever("bd_geo.unions.{$upazilaId}", fn () => BdUnion::active()
            ->where('upazila_id', $upazilaId)
            ->orderBy('name')
            ->get(['id', 'upazila_id', 'name', 'bn_name'])
            ->toArray());

        return $this->cached(['data' => $data]);
    }

    /**
     * Both dependent lists in one call. Exists purely to kill the request waterfall when
     * EDITING a saved address, where the selects would otherwise have to chain.
     */
    public function hydrate(Request $request): JsonResponse
    {
        $districtId = $this->intParam($request, 'district_id');
        $upazilaId = $this->intParam($request, 'upazila_id');

        $upazilas = $districtId
            ? Cache::rememberForever("bd_geo.upazilas.{$districtId}", fn () => BdUpazila::active()
                ->where('district_id', $districtId)
                ->orderByRaw("FIELD(type, 'thana', 'upazila')")
                ->orderBy('name')
                ->get(['id', 'district_id', 'name', 'bn_name', 'type', 'unions_count'])
                ->toArray())
            : [];

        $unions = $upazilaId
            ? Cache::rememberForever("bd_geo.unions.{$upazilaId}", fn () => BdUnion::active()
                ->where('upazila_id', $upazilaId)
                ->orderBy('name')
                ->get(['id', 'upazila_id', 'name', 'bn_name'])
                ->toArray())
            : [];

        return $this->cached(['upazilas' => $upazilas, 'unions' => $unions]);
    }

    /**
     * Reject anything that is not a plain positive integer, so a garbage or unbounded id
     * cannot spray the cache with junk keys.
     */
    protected function intParam(Request $request, string $key): ?int
    {
        $value = $request->query($key);

        if ($value === null || ! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        if (! preg_match('/^[1-9][0-9]{0,9}$/', (string) $value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * This data changes only when bd-geo:sync runs, so it is safe to cache hard and to
     * answer repeat visits with a 304.
     */
    protected function cached(array $payload): JsonResponse
    {
        $body = json_encode($payload);
        $etag = '"'.md5($body).'"';

        if (trim(request()->header('If-None-Match', ''), 'W/') === $etag) {
            return response()->json(null, 304)->setEtag($etag);
        }

        return response()
            ->json($payload)
            ->setEtag($etag)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
