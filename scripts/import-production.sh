#!/usr/bin/env bash
# Importação em lotes para produção (WordPress.com via SSH ou servidor próprio).
#
# Uso:
#   ./scripts/import-production.sh              # lote 0–99
#   ./scripts/import-production.sh 3            # lote 300–399
#   ./scripts/import-production.sh 3 --dry-run
#
# Cada lote importa BATCH_SIZE registros. Ajuste abaixo se necessário.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

BATCH_SIZE="${BATCH_SIZE:-100}"
BATCH="${1:-0}"
DRY_RUN="${2:-}"

if ! [[ "$BATCH" =~ ^[0-9]+$ ]]; then
  echo "Uso: $0 [numero_do_lote] [--dry-run]" >&2
  echo "Exemplo: $0 0   → registros 0–99" >&2
  echo "         $0 5   → registros 500–599" >&2
  exit 1
fi

OFFSET=$(( BATCH * BATCH_SIZE ))
LIMIT=$BATCH_SIZE

PHP="${PHP:-php}"
if [[ -x /opt/homebrew/bin/php ]]; then
  PHP=/opt/homebrew/bin/php
fi

CMD=( "$PHP" scripts/import-posts.php "--offset=$OFFSET" "--limit=$LIMIT" )
if [[ "$DRY_RUN" == "--dry-run" ]]; then
  CMD+=( --dry-run )
fi

echo "→ Lote $BATCH (offset $OFFSET, limit $LIMIT)"
exec "${CMD[@]}"
