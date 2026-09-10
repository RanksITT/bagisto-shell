# Bangladesh geo data — provenance

## upstream/  — nuhil/bangladesh-geocode  (MIT)

https://github.com/nuhil/bangladesh-geocode
Pinned commit: `5622f68bd07a98e076edcf8100bf0db6a75b9854`
Imported: 2026-09-10

Format is a phpMyAdmin JSON export: a top-level ARRAY whose records live in the node with
`"type":"table"`. Do NOT index it positionally — a re-export can reorder the nodes.

| File | Fields | Records |
|---|---|---|
| divisions.json | id, name, bn_name, url | 8 |
| districts.json | id, division_id, name, bn_name, lat, lon, url | 64 |
| upazilas.json | id, district_id, name, bn_name, url | 494 |
| unions.json | id, **upazilla_id** (upstream typo), name, bn_name, url | 4540 |

Verified at import time: referential integrity is clean (0 orphans at every level);
district names are unique; upazila names are NOT (9 collide across districts);
5 upazilas have zero unions (Guimara, Naldanga, Eidgaon, Madhyanagar, Dasar).
All values are JSON strings; some lat/lon are empty strings.

## local/dhaka-city.json — ifahimreza/bangladesh-geojson

142 Dhaka North/South city-corporation areas (Gulshan, Dhanmondi, Uttara, ...) with Bengali names.
Fields: division_id, district_id, city_corporation, name, bn_name.

**LICENCE WARNING:** that repository ships `LICENSE-DATA` as **ODbL** (share-alike), while the
README describes the administrative reference data as MIT. Confirm which applies to this specific
file before any public redistribution of the database.

**ID SPACES ARE NOT COMPATIBLE** with nuhil's: 0 of 64 districts match name-for-id
(nuhil Dhaka = 47, ifahimreza Dhaka = 1). The importer therefore maps these rows to districts
**by name**, never by id, and assigns local ids in the reserved 900001+ range.

## Rules for anyone touching this data

1. `bd_districts.code` is **INSERT-ONLY**. It equals the district English name and is written into
   `addresses.state`. A dataset refresh may update `name` but must never update `code`, or every
   historical order's state becomes unresolvable.
2. **Never delete** a geo row an address references. Retire it with `status = 0`.
3. `bd-geo:sync --prune` only ever touches `source = 'upstream'` rows. Deactivating a curated
   local thana would silently break urban checkout.
4. Curated metro thanas are load-bearing production data with no upstream. They live in
   `local/` in git; `bd-geo:verify` fails if their row count drops.
5. Downstream consumers (courier mapping, shipping rules, BI) must key on `bd_upazila_id`,
   **never** on the `city` string — `city` now holds a mix of upazila and metro thana names.
