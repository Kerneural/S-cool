git pull -ff-only origin main


# Stop Web/worker while dependencies are changing

docker compose -p scool stop nginx queue app

#  Prepare update PHP runtime and database
docker compose -p scool build app queue

docker compose -p scool up -d --wait --wait-timeout 120 mysql

docker compose -p scool run --rm --no-deps app \
 composer install --no-interaction --prefer-dist
npm ci



# Reload configuration, validate the local target, then apply pending migraftions.

docker compose -p scool run --rm --no-deps app php artisan config:clear
docker compose -p scool run --rm --no-deps app php scripts/verify-runtime.php config
docker compose -p scool run --rm --no-deps app php artisan migrate  --no-interaction


docker compose -p scool up -d --wait --wait-timeout 120


# Already includes npm run build, runtime/queue checks and tests.
bash scripts/verify-fresh-setup.sh --mode verify

