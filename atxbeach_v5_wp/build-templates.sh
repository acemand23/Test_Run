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
    '"'"'index'"'"'   => esc_url(home_url('"'"'/'"'"')),
    '"'"'play'"'"'    => esc_url(home_url('"'"'/play/'"'"')),
    '"'"'train'"'"'   => esc_url(home_url('"'"'/train/'"'"')),
    '"'"'juniors'"'"' => esc_url(home_url('"'"'/juniors/'"'"')),
    '"'"'events'"'"'  => esc_url(home_url('"'"'/events/'"'"')),
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
      s{url\(images/}{url(<?php echo \$ATXB; ?>images/}g;
      s{src="images/}{src="<?php echo \$ATXB; ?>images/}g;
      s{href="pages\.css"}{href="<?php echo \$ATXB; ?>pages.css"}g;
      s{(<link href="https://fonts\.googleapis\.com/css2)}{<?php wp_head(); ?>\n$1};
      s{</body>}{<?php wp_footer(); ?>\n</body>};
    ' "$SRC/$1.html"
  } > "$P/templates/$2.php"
  echo "  built templates/$2.php"
}

transform index   home
transform play     play
transform train    train
transform juniors  juniors
transform events   events
echo "done."
