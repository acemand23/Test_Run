# The Big Draw — v4 site · Handoff / Continue notes

_Last updated: 2026-06-10_

Load this file at the start of a new session to pick up exactly where we left off.

---

## What this is

`TBD_v4/` is a **multi-page PHP site** for **The Big Draw** — a Blind Draw 6-vs-6
beach volleyball tournament (Nov 1, 2026, Aussie's Grill & Beach Bar, Austin TX),
benefiting Big Brothers Big Sisters of Central Texas. It is intended to
**eventually replace the live site at tbdvolleyball.com**.

It was built from `TBD_v3/` — a single illustrated **poster** (`assets/poster.png`,
1024×1536) — by "carving" the poster into pages and adding a shared, standard
header/nav so every page is consistent.

### Branch & deploy
- **Work branch:** `claude/big-draw-v2-mobile-xXOG2` (also pushed to `master`).
- Repo: `acemand23/test_run`. GitHub access is via MCP tools only (no `gh`).
- Push pattern: `git push -u origin <branch>` with retry/backoff on network errors.

### Run locally
```bash
cd TBD_v4 && php -S 127.0.0.1:8896
# visit http://127.0.0.1:8896/index.php
```
Requires PHP (uses includes + a little server-side glob). Pages: index, sponsor,
tournament, contact, volunteer, gallery.

---

## Page map & status

| Page | Nav label | Status | Notes |
|------|-----------|--------|-------|
| `index.php` | (logo) | ✅ done | Top of poster (hero + mission) + an **"Our Sponsors"** section after the poster / before the footer. Header chopped off the image (5.5%) and replaced by the shared nav. Register-Now hotspot over the button (→ register.php). Reachable via the logo (no "About" nav item). |
| `register.php` | Register | ✅ holder | "Coming Soon" placeholder (gradient header + centered notice + "Notify me" mailto). All register actions route here while sign-ups aren't open. |
| `sponsor.php` | Sponsor / Sponsor Now | ✅ done | Gradient page header + **editable HTML tier cards** (number, name, price, perks, availability pill) driven by a `$tiers` array at the top of the file. "Become a sponsor" mailto CTA. (Was a poster slice; rebuilt so text/prices/availability are editable without touching the image.) |
| `tournament.php` | Tournament | ✅ done | "Rules of the Game" — gradient header + 8-rule grid + CTAs. (Menu says **Tournament**, singular — deliberate.) |
| `contact.php` | Contact | ✅ done | Sample contact form (name/email/topic/message). JS-only confirmation, no real send yet. |
| `volunteer.php` | Get Involved | ✅ done | Volunteer signup form (see below). Emails the director via `mailto:`. |
| `gallery.php` | Gallery | ✅ done | Replaces "Shop". Past-Years tabs + grid + lightbox; auto-loads `assets/gallery/<year>/`. Photos added for **2022 (7)** and **2023 (8 + a thank-you graphic)**. |

### Nav (shared, in `includes/header.php`)
`Register → register.php` · `Tournament` · `Get Involved → volunteer.php` ·
`Sponsor` · `Gallery` · `Contact` · **Sponsor Now** pill → sponsor.php.
- The logo links to `index.php` (the home/About page); there is no separate
  "About" nav item — it was replaced by "Register".
- Mobile: collapses to a **hamburger** dropdown (≤680px), animates to an X.
- Active page is highlighted (orange).

---

## Key technical decisions / how things work

- **Poster slicing (home only):** one image `assets/poster.png`. `.slice.home`
  shows poster 5.5%→57.5% via `aspect-ratio` + a negative `margin-top` percentage
  on the `<img>`. No image cropping needed; scales cleanly on mobile.
  - Trade-off: text in the home slice is **not selectable / not screen-reader
    text** (alt text added). Every other page is real HTML.
  - `sponsor.php` used to show the bottom slice (57.5%→100%) but was **rebuilt as
    HTML tier cards** so the package text/prices/availability are editable — see the
    `$tiers` array at the top of `sponsor.php`. `.slice.spon` CSS is now unused but
    left in place. Future work: do the same for the home hero/mission.
- **Hotspots:** transparent `<a class="hot">` boxes positioned with %-based
  inline styles over the artwork. Add `#edit` to any URL (e.g.
  `…/index.php#edit`) to reveal the boxes for fine-tuning. Toggle JS is in
  `includes/footer.php`.
- **Date fix (2025→2026):** the date is baked into the poster pixels. Fixed by
  copying the "6" glyph from "6PM" on the same line and pasting it over the "5"
  (identical font/color/paper texture). Poster now reads "NOVEMBER 1ST, 2026".
  Tooling: Python + Pillow (installed via pip in-session).
- **Gallery:** `gallery.php` globs `assets/gallery/*/` subfolders (each = a year),
  builds year tabs (newest first) + a responsive square-thumb grid + a
  click-to-enlarge lightbox (Esc/click to close). Mirrors the old
  `TBD/photo_index.php` UX. Drop photos into `assets/gallery/2022/`,
  `assets/gallery/2023/`, etc. and they appear automatically.
- **Volunteer form:** captures Name, Email, Phone, role checkboxes
  (Pre-event work · Morning-of setup · Run of show · Tear down · Wherever you
  need me), and a notes box. On submit it builds a `mailto:` to the director
  with all fields formatted in the body. No server backend required.
- **Home sponsors:** `index.php` has a `$sponsors` array (each: `name`, `logo`,
  optional `url`, `level` = presenting|court|team|inkind). The "Our Sponsors"
  section groups logos by level and **sizes them by level** (presenting largest →
  inkind smallest; `.lvl-*` in `css/site.css`). While the array is empty it shows
  the **2025 sponsor wall faded** (`assets/sponsors/sponsors-2025.png`, pulled from
  the old site's `ch1.png`) + a "Become a sponsor" CTA. Add 2026 sponsors to the
  array (drop logos in `assets/sponsors/`) and the leveled wall replaces the fade.
- **Sponsor tiers:** `sponsor.php` renders the `$tiers` array (name, price, perks,
  `avail` label, `state` = `open` orange pill / `closed` teal pill). Edit that
  array to change any package — no image editing. Styles: `.tiers/.tier/.perks/
  .pillstate` in `css/site.css`.
- **CSS cache-busting:** `header.php` links `css/site.css?v=4`. **Bump the `?v=`
  when you change CSS** so browsers reload it.

---

## OPEN ITEMS — do these next

1. **Gallery photos — ✅ DONE (2026-06-10).** The session was teleported to a local
   machine (no network allowlist), then fetched `tbdvolleyball.com/photo_index.php`
   for 2022 & 2023 (needs a browser User-Agent — the WAF returns 406 otherwise),
   downloaded the photos into `TBD_v4/assets/gallery/2022|2023/`, downscaled the
   2023 originals to 1600px, and deployed. Photos are on the **same host** — no
   separate CDN.

2. **Director email (placeholder).** `volunteer.php` uses
   `$director_email = 'director@tbdvolleyball.com'` (marked TODO). **Replace with
   the real address.**

3. **Contact form is sample-only.** `contact.php` doesn't actually send. Decide:
   `mailto:` (like volunteer.php), PHP `mail()`, or a form service (Formspree).

4. **Registration is a "coming soon" holder.** `register.php` is a placeholder;
   `header.php` sets `$register_url = 'register.php'`, so the nav "Register" link,
   the home poster Register hotspot, and the Tournament "Register Now" button all
   route there. When sign-ups open, point `$register_url` at the real
   vballmanager.com event URL (or build the form into `register.php`).

5. **Nav:** "About" was removed and replaced with "Register" (→ register.php).
   All nav items point somewhere real.

---

## Possible future polish (not requested / optional)
- Close the mobile menu when tapping outside it.
- Convert the home hero + mission (and sponsor tiers) from poster-slice images to
  real HTML/text for accessibility & SEO.
- Apply the same 2026 date fix to `TBD_v3/assets/poster.png` (still says 2025).

---

## Other folders in this repo (context)
- `TBD/` — copy of the **existing** tbdvolleyball.com PHP site (the one v4 replaces).
  Only UI chrome images present; real event photos are not in the repo.
- `TBD_v2/` — earlier multi-page concept (several "look" variants).
- `TBD_v3/` — the single-page poster version (source of v4's poster.png).
- `atxbeach_site/` — a different reference site (ATX Beach). **Not** Big Draw;
  its photos were used only as throwaway test images and removed.
- root `index.html` — a landing page linking to all versions (TBD, v2, v3, v4).
