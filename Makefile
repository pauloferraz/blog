.PHONY: start stop status

# Atalho: make start  (equivalente a ./start.sh)
start:
	@./start.sh

# Para o servidor PHP na porta padrão (8080); MySQL não é parado
stop:
	@lsof -tiTCP:8080 -sTCP:LISTEN | xargs kill 2>/dev/null || echo "Nenhum servidor na porta 8080."

# MySQL + processo na porta do blog
status:
	@brew services list 2>/dev/null | grep -E '^mysql' || true
	@lsof -nP -iTCP:8080 -sTCP:LISTEN 2>/dev/null || echo "Servidor PHP: parado (porta 8080 livre)."
