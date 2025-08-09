# start.sh
#!/usr/bin/env bash
set -euo pipefail

# Choose docker compose command
if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE="docker-compose"
else
  echo "Docker Compose is not installed. Install Docker Desktop (with Compose) first."
  exit 1
fi

# Run from repo root (where docker-compose.yml is)
cd "$(dirname "$0")"

# Pull/build and start services
$COMPOSE pull || true
$COMPOSE build
$COMPOSE up -d --remove-orphans

echo "Waiting for services to initialize..."
sleep 5

# Run Laravel migrations inside backend (container: forex_backend)
if $COMPOSE ps | grep -q "forex_backend"; then
  $COMPOSE exec -T forex_backend php artisan migrate --force || true
fi

echo
echo "Services are up:"
$COMPOSE ps