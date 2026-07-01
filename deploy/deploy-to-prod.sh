#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Surgical production deploy for the Digaball V4 flagship.
#
# Copies ONLY V4's files from this repo checkout into public_html:
#     digaball_v4/index.html   ->  public_html/index.html   (asset paths namespaced)
#     digaball_v4/assets/*     ->  public_html/dgassets/*
#
# It never uses --delete on public_html, so every OTHER folder that shares that
# directory (beta/, your other subsites, ...) is left completely untouched.
# Idempotent — safe to run on every push.
#
# Runs ON THE SERVER (cPanel). Triggered by:
#   • .cpanel.yml  (cPanel Git Version Control "Deploy"), or
#   • cron:  cd ~/repos/digaball && git pull --ff-only && bash deploy/deploy-to-prod.sh
#
# Overridable env: PUBLIC_HTML (default ~/public_html), NS (default dgassets).
# NOTE: this script does NOT touch .htaccess — that's a one-time setup (see
#       deploy/README.md) so repeated deploys can't clobber the domain's rules.
# ---------------------------------------------------------------------------
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC="$REPO_DIR/digaball_v4"
PUBLIC_HTML="${PUBLIC_HTML:-$HOME/public_html}"
NS="${NS:-dgassets}"

[ -d "$SRC" ]          || { echo "ERROR: source '$SRC' not found";        exit 1; }
[ -d "$PUBLIC_HTML" ]  || { echo "ERROR: PUBLIC_HTML '$PUBLIC_HTML' not found"; exit 1; }

mkdir -p "$PUBLIC_HTML/$NS"

# 1) homepage — rewrite asset paths  assets/ -> $NS/  (atomic replace)
sed "s#assets/#$NS/#g" "$SRC/index.html" > "$PUBLIC_HTML/.index.html.tmp"
mv -f "$PUBLIC_HTML/.index.html.tmp" "$PUBLIC_HTML/index.html"

# 2) assets — mirror ONLY into the namespaced folder. --delete here is safe:
#    it can only affect files inside $PUBLIC_HTML/$NS, nothing else.
if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete --exclude 'Title.gif' "$SRC/assets/" "$PUBLIC_HTML/$NS/"
else
  cp -f "$SRC/assets/"*.png "$PUBLIC_HTML/$NS/" 2>/dev/null || true
fi

echo "[$(date -u +%FT%TZ)] Deployed V4 -> $PUBLIC_HTML (index.html + $NS/). Siblings untouched."
