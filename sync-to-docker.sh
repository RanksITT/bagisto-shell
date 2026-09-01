#!/usr/bin/env bash
#
# Push local packages/ edits into the container's fast volume, then clear the
# caches that would otherwise keep serving the old code.
#
# The container reads packages/ from a Docker volume rather than the Windows
# bind mount, because reading those 2,550 PHP files over the Windows/Linux
# bridge costs ~2s of service-provider boot on every request. The volume is a
# copy, so edits on E:\ are invisible until this runs.
#
# Usage: bash sync.sh
set -euo pipefail

REPO="E:/Projects/RanksITT/bagisto"

echo "==> syncing packages/ into the container"
MSYS_NO_PATHCONV=1 docker run --rm \
  -v "${REPO}/packages:/src:ro" \
  -v bagisto-packages:/dst \
  alpine sh -c 'apk add --no-cache rsync >/dev/null 2>&1 && rsync -a --delete /src/ /dst/'

echo "==> clearing compiled views and cached config"
MSYS_NO_PATHCONV=1 docker exec -w /app bagisto-app php artisan view:clear >/dev/null
MSYS_NO_PATHCONV=1 docker exec -w /app bagisto-app php artisan config:clear >/dev/null

echo "==> rebuilding caches"
MSYS_NO_PATHCONV=1 docker exec -w /app bagisto-app php artisan config:cache >/dev/null
MSYS_NO_PATHCONV=1 docker exec -w /app bagisto-app php artisan view:cache >/dev/null

echo "==> done"
curl -s -o /dev/null -w "    homepage now loads in %{time_total}s\n" http://localhost:8080/
