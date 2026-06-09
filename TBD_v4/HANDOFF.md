# The Big Draw — v4 site · Handoff / Continue notes

_Last updated: 2026-06-09 (contact form wired to mailto; mobile menu outside-tap/Esc close; gallery still network-blocked)_

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
| `index.php` | (logo) / About | ✅ done | Top of poster (hero + mission). Header chopped off the image (5.5%) and replaced by the shared nav. Register-Now hotspot over the button. |
| `sponsor.php` | Sponsor / Sponsor Now | ✅ done | Bottom of poster (Sponsorship Packages + footer art, 57.5%→100%). "Become a sponsor" mailto hotspot. |
| `tournament.php` | Tournament | ✅ done | "Rules of the Game" — gradient header + 8-rule grid + CTAs. (Menu says **Tournament**, singular — deliberate.) |
| `contact.php` | Contact | ✅ done | Contact form (name/email/topic/message). On submit opens a `mailto:` to `questions@tbdvolleyball.com` (like volunteer.php). Swap to a backend if real capture is needed. |
| `volunteer.php` | Get Involved | ✅ done | Volunteer signup form (see below). Emails the director via `mailto:`. |
| `gallery.php` | Gallery | ⏳ built, **needs photos** | Replaces "Shop". Past-Years tabs + grid + lightbox; auto-loads `assets/gallery/<year>/`. Currently empty → "Photos coming soon." |

### Nav (shared, in `includes/header.php`)
`About → index.php` · `Tournament` · `Get Involved → volunteer.php` · `Sponsor` ·
`Gallery` · `Contact` · **Sponsor Now** pill → sponsor.php.
- Mobile: collapses to a **hamburger** dropdown (≤680px), animates to an X.
- Active page is highlighted (orange).

---

## Key technical decisions / how things work

- **Poster slicing (home & sponsor):** one image `assets/poster.png`, shown twice
  via CSS in `css/site.css`. `.slice.home` shows poster 5.5%→57.5%; `.slice.spon`
  shows 57.5%→100%. Done with `aspect-ratio` + a negative `margin-top` percentage
  on the `<img>`. No image cropping needed; scales cleanly on mobile.
  - Trade-off: text in those two slices is **not selectable / not screen-reader
    text** (alt text added). Tournament/Contact/Volunteer/Gallery are real HTML.
    Future work: progressively replace sliced regions with real HTML.
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
- **CSS cache-busting:** `header.php` links `css/site.css?v=3`. **Bump the `?v=`
  when you change CSS** so browsers reload it.

---

## OPEN ITEMS — do these next

1. **Gallery photos (STILL BLOCKED on network allowlist — re-checked 2026-06-09).**
   - This environment cannot reach `tbdvolleyball.com` → `Host not in allowlist`
     (both `/` and `/photo_index.php` return 403 from the allowlist proxy).
   - User chose: **allowlist the domain.** Network policy is fixed per session,
     so it must be added in the environment settings **and a new session started**.
   - **To do once reachable:** fetch `https://tbdvolleyball.com/photo_index.php?year=2022`
     and `?year=2023`, find the real photo file URLs, download them into
     `TBD_v4/assets/gallery/2022/` and `.../2023/`, commit. Grid auto-populates.
   - ❓ Photos may be served from a **separate image host/CDN** — allowlist that
     host too. Confirm where photos are served from.
   - Docs: https://code.claude.com/docs/en/claude-code-on-the-web

2. **Director email (placeholder).** `volunteer.php` uses
   `$director_email = 'director@tbdvolleyball.com'` (marked TODO). **Replace with
   the real address.**

3. **Contact form — DONE (mailto).** `contact.php` now opens a `mailto:` to
   `questions@tbdvolleyball.com` on submit (mirrors volunteer.php). If you want
   real server-side capture instead, swap for PHP `mail()` or a form service
   (Formspree) — the `$contact_email` var + submit handler are the only touch points.

4. **Registration link placeholder.** `header.php` sets
   `$register_url = 'https://vballmanager.com/'` (TODO: the specific event reg URL).
   Used by the Register-Now hotspot and Tournament CTA.

5. **Nav placeholders now resolved:** About→index (done). All nav items point
   somewhere real now.

---

## Possible future polish (not requested / optional)
- ~~Close the mobile menu when tapping outside it.~~ ✅ done (also closes on Esc).
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
