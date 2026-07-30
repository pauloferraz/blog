#!/usr/bin/env bash
# Inicia MySQL (Homebrew) e o servidor PHP do WordPress em http://127.0.0.1:8080
# Uso: ./start.sh   ou   bash start.sh

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

HOST="${WP_HOST:-127.0.0.1}"
PORT="${WP_PORT:-8080}"

if [[ -x /opt/homebrew/bin/php ]]; then
  PHP="${PHP:-/opt/homebrew/bin/php}"
else
  PHP="${PHP:-php}"
fi

MYSQL_CLIENT="${MYSQL_CLIENT:-}"
if [[ -z "$MYSQL_CLIENT" && -x /opt/homebrew/opt/mysql/bin/mysql ]]; then
  MYSQL_CLIENT=/opt/homebrew/opt/mysql/bin/mysql
elif [[ -z "$MYSQL_CLIENT" ]]; then
  MYSQL_CLIENT=mysql
fi

if ! command -v brew >/dev/null 2>&1; then
  echo "Erro: Homebrew não encontrado. Instale em https://brew.sh" >&2
  exit 1
fi

if ! command -v "$PHP" >/dev/null 2>&1; then
  echo "Erro: PHP não encontrado ($PHP). Instale com: brew install php" >&2
  exit 1
fi

if lsof -nP -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; then
  echo "Erro: a porta $PORT já está em uso." >&2
  echo "Pare o processo anterior ou use: WP_PORT=8888 ./start.sh" >&2
  exit 1
fi

echo "→ Iniciando MySQL..."
brew services start mysql >/dev/null 2>&1 || brew services start mysql

echo "→ Aguardando MySQL..."
for _ in {1..30}; do
  if "$MYSQL_CLIENT" -h 127.0.0.1 -u wpuser -pwp_local_change_me -e "SELECT 1" wordpress_local >/dev/null 2>&1; then
    break
  fi
  sleep 0.5
done

if ! "$MYSQL_CLIENT" -h 127.0.0.1 -u wpuser -pwp_local_change_me -e "SELECT 1" wordpress_local >/dev/null 2>&1; then
  echo "Aviso: não foi possível conectar ao banco wordpress_local. O WordPress pode falhar até o MySQL subir." >&2
fi

URL="http://${HOST}:${PORT}"
echo ""
echo "  Site:  ${URL}"
echo "  Admin: ${URL}/wp-admin"
echo ""
echo "  Ctrl+C para parar o servidor PHP (MySQL continua rodando em background)."
echo ""

exec "$PHP" -S "${HOST}:${PORT}"
