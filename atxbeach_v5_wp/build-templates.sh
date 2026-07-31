#!/usr/bin/env bash
# Regenerate the 5 WP page templates from the approved static mockup.
# Rewrites: asset paths -> plugin URL, X.html links -> WP permalinks, injects wp_head/wp_footer.
set -euo pipefail
SRC=~/code/beta/atxbeach_v5
P=~/code/beta/atxbeach_v5_wp/atxbeach-v5

PREAMBLE='<?php
if (!defined('"'"'ABSPATH'"'"')) exit;
$ATXB = ATXBEACH_ASSETS;
$LINK = [
    '"'"'index'"'"'   => esc_url(atxb_v5_url('"'"'home'"'"')),
    '"'"'play'"'"'    => esc_url(atxb_v5_url('"'"'play'"'"')),
    '"'"'train'"'"'   => esc_url(atxb_v5_url('"'"'train'"'"')),
    '"'"'juniors'"'"' => esc_url(atxb_v5_url('"'"'juniors'"'"')),
    '"'"'events'"'"'  => esc_url(atxb_v5_url('"'"'events'"'"')),
    '"'"'leagues'"'"' => esc_url(atxb_v5_url('"'"'leagues'"'"')),
];
?>'

transform () {  # $1 = source basename (no ext), $2 = dest basename
  { printf '%s\n' "$PREAMBLE"
    perl -0777 -pe '
      s{href="index\.html}{href="<?php echo \$LINK['"'"'index'"'"']; ?>}g;
      s{href="play\.html}{href="<?php echo \$LINK['"'"'play'"'"']; ?>}g;
      s{href="train\.html}{href="<?php echo \$LINK['"'"'train'"'"']; ?>}g;
      s{href="juniors\.html}{href="<?php echo \$LINK['"'"'juniors'"'"']; ?>}g;
      s{href="events\.html}{href="<?php echo \$LINK['"'"'events'"'"']; ?>}g;
      s{href="leagues\.html}{href="<?php echo \$LINK['"'"'leagues'"'"']; ?>}g;
      s{url\(images/}{url(<?php echo \$ATXB; ?>images/}g;
      s{src="images/}{src="<?php echo \$ATXB; ?>images/}g;
      s{href="pages\.css"}{href="<?php echo \$ATXB; ?>pages.css"}g;
      s{__ATXB_ACTION__}{<?php echo esc_url(admin_url('"'"'admin-post.php'"'"')); ?>}g;
      s{<!--ATXB_NONCE-->}{<?php wp_nonce_field('"'"'atxb_host_inquiry'"'"', '"'"'atxb_nonce'"'"'); ?>}g;
      s{<!--ATXB_NOTICE-->}{<?php atxb_host_notice(); ?>}g;
      s{(<link href="https://fonts\.googleapis\.com/css2)}{<?php wp_head(); ?>\n$1};
      s{</body>}{<?php wp_footer(); ?>\n</body>};
    ' "$SRC/$1.html"
  } > "$P/templates/$2.php"
  echo "  built templates/$2.php"
}

# Sync assets from the mockup: shared CSS + only the images the pages actually reference.
cp "$SRC/pages.css" "$P/assets/pages.css"
rm -rf "$P/assets/images"
grep -rhoE "images/[a-zA-Z0-9/._-]+\.(jpg|jpeg|png)" "$SRC"/*.html | sort -u | while IFS= read -r rel; do
  mkdir -p "$P/assets/$(dirname "$rel")"; cp "$SRC/$rel" "$P/assets/$rel"
done
echo "  synced assets ($(find "$P/assets/images" -type f | wc -l | tr -d ' ') images + pages.css)"

transform index   home
transform play     play
transform train    train
transform juniors  juniors
transform events   events
transform leagues  leagues
echo "done."
