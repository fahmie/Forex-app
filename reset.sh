# reset.sh
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

# Stop and remove containers, networks, and volumes
$COMPOSE down -v --remove-orphans

# Optional: prune dangling images if env var set
if [ "${PRUNE_IMAGES:-0}" = "1" ]; then
  docker image prune -f
fi

echo "Environment reset complete."