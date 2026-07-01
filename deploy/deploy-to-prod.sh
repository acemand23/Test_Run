#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Deploy the Digaball V4 flagship to production.
#
# It's exactly what you did by hand: copy /beta/digaball_v4/* up to the web root.
#     digaball_v4/index.html  ->  public_html/index.html
#     digaball_v4/assets/*    ->  public_html/assets/*
#
# Plain overlay copy — NO --delete anywhere under public_html — so every other
# folder that shares the root (beta/, your other subsites) is left untouched.
# Idempotent; safe to run on every push.
#
# Runs ON THE SERVER from the beta checkout. Automate via cron (see
# deploy/README.md) or the cPanel .cpanel.yml trigger.
#
# Overridable env: PUBLIC_HTML (default ~/public_html).
# ---------------------------------------------------------------------------
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SRC="$REPO_DIR/digaball_v4"
PUBLIC_HTML="${PUBLIC_HTML:-$HOME/public_html}"

[ -d "$SRC" ]         || { echo "ERROR: source '$SRC' not found";            exit 1; }
[ -d "$PUBLIC_HTML" ] || { echo "ERROR: PUBLIC_HTML '$PUBLIC_HTML' not found"; exit 1; }

# Copy index.html + assets/ into the web root (exclude the dev-only design spec).
if command -v rsync >/dev/null 2>&1; then
  rsync -a --exclude 'DESIGN_SOURCE.md' "$SRC"/ "$PUBLIC_HTML"/
else
  cp -f "$SRC/index.html" "$PUBLIC_HTML/index.html"
  mkdir -p "$PUBLIC_HTML/assets"
  cp -f "$SRC/assets/"* "$PUBLIC_HTML/assets/"
fi

echo "[$(date -u +%FT%TZ)] Deployed V4 -> $PUBLIC_HTML (index.html + assets/). Siblings untouched."
