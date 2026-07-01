#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Build a SURGICAL "root overlay" bundle for deploying the Digaball V4 flagship
# to the digaball.com document root (public_html) WITHOUT disturbing the other
# folders that share that directory (beta/, other subsites, Joomla, ...).
#
# It emits just the files V4 needs, with assets namespaced into a unique folder
# so nothing at the root gets clobbered:
#
#     digaball-root.zip
#       ├── index.html          (asset paths rewritten assets/ -> dgassets/)
#       ├── dgassets/           (logo.png, aduke.png, jduke.png — optimized)
#       └── .htaccess           (DirectoryIndex so index.html wins; + gzip/cache)
#
# Upload the zip into public_html via cPanel File Manager and "Extract" — it
# MERGES (adds/overwrites only these files), leaving sibling folders untouched.
#
# Usage:  bash deploy/build-root-bundle.sh
# Output: deploy/dist/digaball-root/  and  deploy/dist/digaball-root.zip
# ---------------------------------------------------------------------------
set -euo pipefail
cd "$(dirname "$0")/.."                 # repo root

SRC="digaball_v4"
NS="dgassets"                           # namespaced asset folder — confirm it does NOT already exist in public_html
OUT="deploy/dist/digaball-root"
MAXW=1400                               # max px (long side) for the big ink illustrations (they display ~600px)

command -v zip >/dev/null || { echo "ERROR: 'zip' not found"; exit 1; }
rm -rf "$OUT"; mkdir -p "$OUT/$NS"

# 1) index.html with asset paths rewritten  assets/ -> $NS/
sed "s#assets/#$NS/#g" "$SRC/index.html" > "$OUT/index.html"

# 2) copy + optimize the three referenced assets (sips ships with macOS)
optimize(){ # src dest maxw
  if command -v sips >/dev/null 2>&1; then
    sips -Z "$3" "$1" --out "$2" >/dev/null 2>&1 || cp "$1" "$2"
  else
    cp "$1" "$2"
  fi
}
optimize "$SRC/assets/logo.png"  "$OUT/$NS/logo.png"  640
optimize "$SRC/assets/aduke.png" "$OUT/$NS/aduke.png" "$MAXW"
optimize "$SRC/assets/jduke.png" "$OUT/$NS/jduke.png" "$MAXW"

# 3) minimal root .htaccess — BACK UP the existing public_html/.htaccess before replacing.
#    Only sets the homepage + light caching; no rewrites, so sibling folders serve normally.
cat > "$OUT/.htaccess" <<'HT'
# Digaball flagship (V4) — static homepage. No rewrites; sibling folders (beta/, etc.) unaffected.
DirectoryIndex index.html index.php
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css application/javascript image/svg+xml
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/png  "access plus 1 month"
  ExpiresByType text/css   "access plus 7 days"
  ExpiresByType text/html  "access plus 0 seconds"
</IfModule>
HT

# 4) zip (dotfiles included), excluding cruft
( cd "$OUT" && zip -rqX ../digaball-root.zip . -x '.DS_Store' )

echo "Bundle contents:"
( cd "$OUT" && find . -type f | sort | while read -r f; do printf "  %-22s %s\n" "$f" "$(du -h "$f" | cut -f1)"; done )
echo "Zip: deploy/dist/digaball-root.zip ($(du -h deploy/dist/digaball-root.zip | cut -f1))"
