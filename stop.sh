# stop.sh
#!/usr/bin/env bash
set -euo pipefail

if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
  COMPOSE="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE="docker-compose"
else
  echo "Docker Compose is not installed."
  exit 1
fi

cd "$(dirname "$0")"
$COMPOSE stop

echo "All services stopped. Use ./start.sh to start again."