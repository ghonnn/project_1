#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/nex-oss-bss}"
BRANCH="${BRANCH:-feat/mvp-phase-1-3-backend}"
APP_PORT="${APP_PORT:-18000}"
POSTGRES_BIND="${POSTGRES_BIND:-10.20.1.32}"
POSTGRES_PORT="${POSTGRES_PORT:-5432}"

cd "$APP_DIR"

if ! git diff --quiet || ! git diff --cached --quiet || [ -n "$(git ls-files --others --exclude-standard)" ]; then
    git stash push -u -m "pre-deploy $(date +%Y%m%d-%H%M%S)"
fi

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

if [ -f .env ]; then
    if grep -q '^APP_PORT=' .env; then
        sed -i "s/^APP_PORT=.*/APP_PORT=${APP_PORT}/" .env
    else
        printf '\nAPP_PORT=%s\n' "$APP_PORT" >> .env
    fi
else
    printf 'APP_PORT=%s\n' "$APP_PORT" > .env
fi

if grep -q '^POSTGRES_BIND=' .env; then
    sed -i "s/^POSTGRES_BIND=.*/POSTGRES_BIND=${POSTGRES_BIND}/" .env
else
    printf 'POSTGRES_BIND=%s\n' "$POSTGRES_BIND" >> .env
fi

if grep -q '^POSTGRES_PORT=' .env; then
    sed -i "s/^POSTGRES_PORT=.*/POSTGRES_PORT=${POSTGRES_PORT}/" .env
else
    printf 'POSTGRES_PORT=%s\n' "$POSTGRES_PORT" >> .env
fi

docker compose build api
docker compose up -d
docker compose exec -T api php artisan migrate --force
docker compose exec -T api php artisan optimize:clear
curl -fsS "http://127.0.0.1:${APP_PORT}/api/v1/health"

printf '\nDeploy done: http://10.20.1.32:%s/admin/login\n' "$APP_PORT"
