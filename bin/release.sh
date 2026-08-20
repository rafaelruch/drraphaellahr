#!/bin/sh
# Lança uma nova versão do tema — bump da versão + commit + push + GitHub Release.
# Depois disso, o WordPress do cliente mostra "Atualização disponível" para o tema.
#
# Uso:
#   ./bin/release.sh 4.1.1 "O que mudou nesta versão"
#
# Requisitos: rodar de dentro da pasta do tema; git + gh (GitHub CLI) autenticado.

set -e

VER="$1"
NOTES="${2:-Nova versão $1}"
REPO="rafaelruch/drraphaellahr"

if [ -z "$VER" ]; then
  echo "Uso: ./bin/release.sh <versao> [notas]   ex.: ./bin/release.sh 4.1.1 \"Ajustes na hero\""
  exit 1
fi

cd "$(dirname "$0")/.."

# 1) Atualiza a versão no cabeçalho do style.css
sed -i '' "s/^Version: .*/Version: $VER/" style.css
echo "→ style.css agora em Version: $VER"

# 2) Commit + push
git add style.css
git commit -m "release: v$VER — $NOTES"
git push origin main
echo "→ commit enviado ao GitHub"

# 3) Cria o Release (tag vX.Y.Z apontando para o commit atual)
gh release create "v$VER" -R "$REPO" --title "v$VER" --notes "$NOTES"
echo "✅ Release v$VER publicado. O WordPress do cliente já pode clicar em Atualizar."
