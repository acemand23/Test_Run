# tbdvolleyball.com — Blind Draw 4s dual-tournament update

**Date:** 2026-05-27
**Folder:** `TBD/` (deploys to `digaball.com/beta/TBD/` → live `www.tbdvolleyball.com`)
**Baseline:** faithful mirror of current live site committed in `c2a2612`.

## Goal
Convert the existing **Blind Draw 6v6** event into a **Blind Draw 4s** run as a **dual
tournament** (Rec + Competitive). Venue (Aussie's Grill & Beach Bar, Austin) and charity
(Big Brother Big Sister of Central Texas) unchanged.

## Confirmed details
- **Date:** November 1, 2026.
- **Divisions (all blind draw):**
  - **Rec** — 9am–1pm. Includes BBBS **Matches** = a "Big" paired with their "Little" (Little 16+).
  - **Competitive** — 12pm–7pm. (Overlap noon–1; separate courts.)
- **Player entry fees** (register via vballmanager.com): BBBS Matches **$25**, everyone else **$50**.
- **Sponsorships:** Presenting **$6,000** · Court **$1,500** · Team **$500** · In-Kind (keep) ·
  Lunch **available** · DJ **available**.
- Swag hats = visual inspiration only (no swag section). Palette cue: site green `#48BB8C` + warm multicolor.

## Per-file changes
- **`tour_index.php`** — title → "Blind Draw 4s"; date → Nov 1 2026; add two division blocks
  (Rec / Competitive with times, blind-draw note, Matches eligibility, fees); change format
  **6v6 → 4v4** and fix every "six players" rule reference (team composition, mission); add a
  **Register** button → vballmanager.com (placeholder link).
- **`spon_index.php`** — update the text list to the confirmed tiers; keep Blackbaud payment iframe.
- **`index.php`** — headline → 4s; event line → "November 1st, 2026 / Aussie's Grill & Beach Bar /
  9am–7pm"; replace the 2025 countdown + `register.php` JS with a **Register Now** link to
  vballmanager.com; fix "randomly pairing 6 players" → 4.
- **`images/sp1.png`** — **regenerate** the homepage "Sponsorship Packages" graphic with the new
  prices/tiers (Presenting $6,000, Court $1,500, Team $500, In-Kind, + Lunch & DJ "available"),
  matching the original teal/numbered-card style. Built as HTML/CSS, rendered to PNG, replacing
  the file. Fixes the old $5,000-vs-$6,000 inconsistency.

## Open / placeholders
- **vballmanager.com registration link** not yet provided → Register buttons point to a clearly
  marked placeholder for the owner to swap in.

## Noted, not in scope (flagged to owner)
- `images/sand.jpg` is **19 MB** — should be compressed for page speed.
- `script.js` 404s and `css/menu-styles.css` is empty on live — the hamburger menu may be broken;
  offer to fix separately.
