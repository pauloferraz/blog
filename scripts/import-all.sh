#!/usr/bin/env bash
# Roda todos os lotes de import-production.sh em sequência até o CSV acabar.
#
# Uso:
#   ./scripts/import-all.sh              # importação real, todos os lotes
#   ./scripts/import-all.sh --dry-run    # simulação, todos os lotes
#
# Para na primeira falha (exit code != 0) de um lote, sem tocar nos seguintes.
# Logs de cada lote e um resumo geral ficam em scripts/logs/.

set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

DRY_RUN="${1:-}"
if [[ -n "$DRY_RUN" && "$DRY_RUN" != "--dry-run" ]]; then
  echo "Uso: $0 [--dry-run]" >&2
  exit 1
fi

LOG_DIR="$ROOT/scripts/logs"
mkdir -p "$LOG_DIR"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
SUMMARY_LOG="$LOG_DIR/import-all-$TIMESTAMP.log"

BATCH=0
while true; do
  echo "==================================================" | tee -a "$SUMMARY_LOG"
  echo "Lote $BATCH ($(date '+%H:%M:%S'))" | tee -a "$SUMMARY_LOG"

  BATCH_LOG="$LOG_DIR/lote-$BATCH-$TIMESTAMP.log"

  if [[ "$DRY_RUN" == "--dry-run" ]]; then
    ./scripts/import-production.sh "$BATCH" --dry-run > "$BATCH_LOG" 2>&1
  else
    ./scripts/import-production.sh "$BATCH" > "$BATCH_LOG" 2>&1
  fi
  STATUS=$?

  cat "$BATCH_LOG" | tee -a "$SUMMARY_LOG"

  if [[ $STATUS -ne 0 ]]; then
    echo "ERRO no lote $BATCH (exit $STATUS). Interrompendo." | tee -a "$SUMMARY_LOG"
    echo "Log completo: $BATCH_LOG" | tee -a "$SUMMARY_LOG"
    exit 1
  fi

  if grep -q "Registros neste lote: 0" "$BATCH_LOG"; then
    echo "Nenhum registro no lote $BATCH — importação concluída." | tee -a "$SUMMARY_LOG"
    break
  fi

  BATCH=$((BATCH + 1))
done

echo "==================================================" | tee -a "$SUMMARY_LOG"
echo "Importação finalizada. Resumo: $SUMMARY_LOG" | tee -a "$SUMMARY_LOG"
