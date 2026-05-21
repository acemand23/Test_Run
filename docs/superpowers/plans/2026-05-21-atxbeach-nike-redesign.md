# ATX Beach Nike-Style Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the static ATX Beach site (`atxbeach_site/`) into a Nike-structured, ATX-Beach-colored experience: full-bleed real photography, oversized condensed type, minimal text, whitespace, squared UI — across the homepage and all four sub-pages.

**Architecture:** One shared `styles.css` holds the design system; five HTML pages consume it. We re-weight the existing color tokens toward a near-black/white base with brand colors as accents, swap SVG placeholders for web-optimized real photos, add Oswald (display) + Inter (body) via Google Fonts, and rebuild the homepage hero/tiles plus every sub-page hero. Work proceeds in dependency order (assets → fonts → global shell → homepage → sub-pages → QA) so the site renders correctly at every commit.

**Tech Stack:** Plain HTML5, CSS3 (custom properties, grid, `clamp()`), Google Fonts (Oswald + Inter), `sips` for image optimization. No build step. Committing directly to `master`.

**No-test-runner note:** This is a static site. "Verify" steps use `sips`/`ls` for images, `grep` for markup/CSS presence, and browser visual confirmation (open `file://` path or screenshot via the Chrome MCP tools).

---

## File Structure

- `atxbeach_site/styles.css` — **(modify, heavy)** the entire design system: tokens, type, nav, buttons, hero, tiles, sections, footer, mobile CTA, responsive.
- `atxbeach_site/index.html` — **(modify, heavy)** rebuild hero + tiles, add venue-scale band, restyle routing strip / policies / contact, swap nav brand to logo image, add font links.
- `atxbeach_site/play.html` / `train.html` / `juniors.html` / `events.html` — **(modify)** add font links, swap nav brand to logo image, give each a full-bleed photo hero.
- `atxbeach_site/images/photos/` — **(modify)** existing downloaded originals get optimized in place into web-ready files; final filenames referenced by CSS/HTML.
- `atxbeach_site/images/*.svg` — **(possibly remove)** old placeholders, once unused.

Design boundary: all cross-page styling lives in `styles.css`; HTML files hold only structure/content. Per-page hero photo is selected by the existing `body.page-*` class hook in CSS, so HTML hero markup stays identical across pages.

---

## Task 1: Optimize image assets for web

**Files:**
- Modify (in place): `atxbeach_site/images/photos/*.jpg`

Originals are up to ~17MB — unusable on a bandwidth-flagged host. Downscale + recompress to web sizes. Keep the same filenames so later tasks reference stable names.

- [ ] **Step 1: Confirm the originals are present**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site/images/photos && ls -1 *.jpg *.png
```
Expected: includes `action-87.jpg action-43.jpg hero-16x9.jpg venue-9468.jpg venue-9469.jpg venue-9471.jpg venue-9467.jpg league-coed3s.jpg play-4142.jpg logo-white.png logo-dark.png` (plus the other downloaded candidates).

- [ ] **Step 2: Optimize the photos used by the design**

Run (macOS `sips`; resize longest dimension, then set JPEG quality):
```bash
cd /Users/aduke/code/beta/atxbeach_site/images/photos
# hero: wide
sips -Z 2200 -s format jpeg -s formatOptions 80 action-87.jpg --out action-87.jpg
sips -Z 2200 -s format jpeg -s formatOptions 80 hero-16x9.jpg --out hero-16x9.jpg
# tiles + band: medium
for f in action-43 venue-9468 venue-9469 venue-9471 venue-9467 league-coed3s play-4142; do
  sips -Z 1400 -s format jpeg -s formatOptions 80 "$f.jpg" --out "$f.jpg"
done
```

- [ ] **Step 3: Verify every used image is small enough**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site/images/photos
for f in action-87 hero-16x9 action-43 venue-9468 venue-9469 venue-9471 venue-9467 league-coed3s play-4142; do
  printf "%-16s " "$f.jpg"; du -h "$f.jpg" | cut -f1; done
```
Expected: each file ≤ ~400KB (most well under 300KB). If any exceeds, re-run that file at quality 70.

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site/images/photos
git commit -m "Optimize ATX Beach photos for web (downscale + recompress)"
```

---

## Task 2: Add web fonts and base typography

**Files:**
- Modify: `atxbeach_site/index.html`, `play.html`, `train.html`, `juniors.html`, `events.html` (each `<head>`)
- Modify: `atxbeach_site/styles.css` (body font, `--font-display`, headline utility)

- [ ] **Step 1: Add the Google Fonts link to every page `<head>`**

In each of the 5 HTML files, immediately above the existing `<link rel="stylesheet" href="styles.css">` line, add:
```html
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

- [ ] **Step 2: Wire fonts into `styles.css`**

In `:root` add a display-font token (next to the existing tokens):
```css
    --font-display: 'Oswald', 'Arial Narrow', system-ui, sans-serif;
    --font-body: 'Inter', system-ui, -apple-system, Helvetica, Arial, sans-serif;
```
Change the `body` rule's `font-family` to `var(--font-body)`.
Add a shared display rule after the `body` rule:
```css
h1, h2, h3, .display { font-family: var(--font-display); font-weight: 700; }
```

- [ ] **Step 3: Verify**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -l "fonts.googleapis.com/css2?family=Oswald" *.html | sort
grep -n "font-display\|var(--font-body)" styles.css
```
Expected: all 5 HTML files listed; `--font-display` defined and `body` uses `var(--font-body)`.
Then open `index.html` in the browser and confirm headings render in a condensed font (Oswald), body in Inter.

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Add Oswald + Inter web fonts across all pages"
```

---

## Task 3: Re-weight color tokens and squared geometry

**Files:**
- Modify: `atxbeach_site/styles.css:1-30` (`:root` tokens)

Shift the dominant fields toward near-black/white; keep brand colors as accents; sharpen corners (Nike is squared).

- [ ] **Step 1: Update `:root` tokens**

In `:root`, add/replace these tokens (keep existing brand colors `--teal --coral --sun --navy --sand` etc.):
```css
    --black: #0d0d0f;
    --paper: #f7f7f5;
    --ink: #15191c;       /* near-black body text */
    --muted: #6b7378;
    --line: #e6e4df;      /* hairline borders on light */
    --radius: 4px;        /* squared, Nike-like (was 18px) */
    --radius-sm: 2px;
```
Change the `body` `background` to `var(--paper)`.

- [ ] **Step 2: Verify nothing references a removed token**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "var(--sand-soft)" styles.css
```
Expected: any remaining `--sand-soft` uses still resolve (keep the `--sand-soft` token defined). If `body` previously used `--sand-soft`, it now uses `--paper`. Open `index.html`; page background should be near-white, text near-black.

- [ ] **Step 3: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site/styles.css
git commit -m "Re-weight palette to near-black/white base with squared geometry"
```

---

## Task 4: Restyle the global shell (nav, buttons, footer, mobile CTA)

**Files:**
- Modify: `atxbeach_site/styles.css` (`.nav*`, `.btn*`, `footer`, `.mobile-cta`)
- Modify: nav markup in all 5 HTML files (logo image)

- [ ] **Step 1: Swap the text brand for the white logo in all 5 pages**

In each HTML file, replace:
```html
        <a href="index.html" class="brand"><span class="dot"></span> ATX BEACH</a>
```
with:
```html
        <a href="index.html" class="brand"><img src="images/photos/logo-white.png" alt="ATX Beach" class="brand-logo"></a>
```

- [ ] **Step 2: Restyle nav + brand logo in `styles.css`**

Replace the `.nav` background with solid near-black and add a `.brand-logo` rule:
```css
.nav { position: sticky; top: 0; z-index: 50; background: var(--black); border-bottom: 1px solid rgba(255,255,255,0.08); }
.brand-logo { height: 30px; width: auto; display: block; }
```
Update `.nav-links a` to uppercase, tracked, smaller:
```css
.nav-links a { color: rgba(255,255,255,0.82); font-weight: 600; font-size: 0.78rem; letter-spacing: 1.5px; text-transform: uppercase; padding: 8px 12px; border-radius: var(--radius-sm); transition: color .2s, background .2s; }
```
Keep `.nav-cta` but squared: add `border-radius: var(--radius-sm);`.

- [ ] **Step 3: Square the buttons**

Change `.btn` `border-radius` from `999px` to `var(--radius-sm)`, reduce the hover lift to `translateY(-1px)`, and make `.btn-primary` use `--coral`. Add a solid-dark variant:
```css
.btn-dark { background: var(--black); color: #fff; }
.btn-dark:hover { background: #000; }
```

- [ ] **Step 4: Restyle footer + mobile CTA to near-black**

Set `footer { background: var(--black); }` and `.mobile-cta { background: var(--black); }`. Square `.mobile-cta a` (`border-radius: var(--radius-sm)`), keep `.mobile-cta a.hot` coral.

- [ ] **Step 5: Verify**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -c "brand-logo" *.html
grep -n "border-radius: var(--radius-sm)" styles.css | head
```
Expected: each HTML file reports `1` brand-logo; buttons/nav-cta show squared radius. Open `index.html`: nav shows the white logo on near-black, uppercase links; buttons are squared.

- [ ] **Step 6: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Restyle global shell: logo nav, squared buttons, near-black footer/CTA"
```

---

## Task 5: Rebuild the homepage hero

**Files:**
- Modify: `atxbeach_site/index.html:31-41` (hero markup)
- Modify: `atxbeach_site/styles.css` (`.hero*`, `.page-home .hero::before`)

Nike-style: near-viewport full-bleed photo, oversized condensed headline, minimal subtext, two CTAs.

- [ ] **Step 1: Point the home hero at the real photo + make it full-bleed**

In `styles.css`, change the home hero background image and add height/treatment:
```css
.page-home .hero::before { background-image: url(images/photos/action-87.jpg); }
.hero { min-height: 82vh; display: flex; align-items: flex-end; }
.hero-inner { padding: 0 22px 64px; width: 100%; }
```
Make the headline huge + condensed uppercase:
```css
.hero h1 { font-family: var(--font-display); font-size: clamp(2.8rem, 9vw, 6rem); line-height: 0.95; font-weight: 700; letter-spacing: -0.5px; text-transform: uppercase; max-width: 16ch; }
```

- [ ] **Step 2: Tighten the hero copy**

In `index.html`, set the `<h1>` to `PLAY. TRAIN. COMPETE.` and shorten the paragraph to one line, e.g. "Austin's largest sand volleyball venue — 8 pro courts, open play, training, juniors, and events." Keep the two existing CTA buttons (`Get Started`, `Plan an Event`).

- [ ] **Step 3: Verify**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "action-87.jpg\|82vh\|PLAY. TRAIN. COMPETE." styles.css index.html
```
Expected: hero references `action-87.jpg`, `min-height: 82vh`, and the new headline. Open `index.html`: hero is a tall full-bleed action photo with a large uppercase headline anchored bottom-left.

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Rebuild homepage hero: full-bleed photo + oversized condensed headline"
```

---

## Task 6: Rebuild the homepage tiles

**Files:**
- Modify: `atxbeach_site/styles.css` (`.tiles*`, `.tile*`)
- Modify: `atxbeach_site/index.html:44-82` (tile copy trim, optional)

- [ ] **Step 1: Point each tile at its real photo + square them**

In `styles.css` replace the four `.tile-*::before` background rules:
```css
.tile-play::before    { background-image: url(images/photos/action-43.jpg); }
.tile-train::before   { background-image: url(images/photos/venue-9468.jpg); }
.tile-juniors::before { background-image: url(images/photos/venue-9471.jpg); }
.tile-events::before  { background-image: url(images/photos/league-coed3s.jpg); }
```
Square the tiles and remove the negative top margin so they read as a clean grid:
```css
.tiles-section { margin-top: 0; padding: 0; }
.tiles { gap: 4px; }
.tile { border-radius: 0; min-height: 420px; box-shadow: none; }
.tile:hover { transform: none; box-shadow: none; }
```

- [ ] **Step 2: Make the tile label condensed + loud**

```css
.tile h2 { font-family: var(--font-display); font-size: clamp(1.8rem, 4vw, 2.8rem); text-transform: uppercase; font-weight: 700; letter-spacing: 0; }
.tile .kicker { font-size: 0.7rem; letter-spacing: 2px; }
```
Keep the `.tile-cta` arrow + hover image-zoom (`.tile:hover::before { transform: scale(1.06); }`) already present.

- [ ] **Step 3: Verify**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "tile-play::before\|tile-train::before\|tile-juniors::before\|tile-events::before" styles.css
```
Expected: each maps to its real photo (`action-43`, `venue-9468`, `venue-9471`, `league-coed3s`). Open `index.html`: a tight 2×2 grid of full-bleed photo tiles with big uppercase labels; hover zooms the photo.

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Rebuild homepage tiles: full-bleed real photos, squared, condensed labels"
```

---

## Task 7: Add the venue-scale band + restyle routing strip, policies, contact

**Files:**
- Modify: `atxbeach_site/index.html` (insert venue band after tiles; restyle hooks)
- Modify: `atxbeach_site/styles.css` (`.quick*`, `.policies`, `.contact`, new `.venue-band`)

- [ ] **Step 1: Add the venue-scale band markup**

In `index.html`, immediately after the closing `</section>` of the tiles section (line ~82) and before the Quick Actions section, insert:
```html
<!-- ============ VENUE SCALE BAND ============ -->
<section class="venue-band">
    <div class="venue-band-inner">
        <span class="eyebrow">The Venue</span>
        <h2>Austin's largest sand volleyball venue</h2>
        <p>8 pro courts under 200-ft shade runways, plus the Turtle Shack bar — 7 TVs, cold drinks, and concessions.</p>
        <a href="#policies" class="btn btn-ghost">Venue Info</a>
    </div>
</section>
```

- [ ] **Step 2: Style the venue band (full-bleed aerial)**

In `styles.css` add:
```css
.venue-band { position: relative; color: #fff; padding: 96px 22px; text-align: center; background: linear-gradient(rgba(13,13,15,0.55), rgba(13,13,15,0.65)), url(images/photos/play-4142.jpg) center / cover no-repeat; }
.venue-band-inner { max-width: 760px; margin: 0 auto; }
.venue-band h2 { font-family: var(--font-display); font-size: clamp(1.8rem, 5vw, 3.2rem); text-transform: uppercase; line-height: 1; }
.venue-band p { margin: 14px 0 22px; color: rgba(255,255,255,0.88); }
```

- [ ] **Step 3: Square + flatten the routing strip and contact form**

In `styles.css`: set `.quick { border-radius: var(--radius); box-shadow: none; border: 1px solid var(--line); }`, `.quick:hover { transform: none; border-color: var(--black); }`, `.lead-form { border-radius: var(--radius); }`, and squared inputs (`border-radius: var(--radius-sm)`). Set `.contact { background: var(--paper); }`. Leave the policies section's dark night-court treatment but swap its background image to `images/photos/league-coed3s.jpg` if `courts-night.svg` is being removed.

- [ ] **Step 4: Verify**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "venue-band" index.html styles.css
```
Expected: band markup in HTML and `.venue-band` rule in CSS referencing `play-4142.jpg`. Open `index.html`: full-width aerial band with centered condensed headline sits between tiles and the routing strip; routing cards and form are squared/flat.

- [ ] **Step 5: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Add venue-scale band; flatten routing strip, policies, and contact"
```

---

## Task 8: Restyle the four sub-page heroes

**Files:**
- Modify: `atxbeach_site/styles.css` (`.page-* .hero::before` image paths; shared sub-hero sizing)
- Modify: `play.html`, `train.html`, `juniors.html`, `events.html` (only if hero copy needs trimming — markup/classes already shared)

The sub-pages already use `body.page-*` + `.hero`, so CSS alone re-skins their heroes. Confirm each page's `<body>` class first.

- [ ] **Step 1: Confirm each sub-page's body class**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "<body" play.html train.html juniors.html events.html
```
Expected: `page-play`, `page-train`, `page-juniors`, `page-events` respectively. (If any differ, use the actual class in Step 2.)

- [ ] **Step 2: Point each sub-page hero at a real photo**

In `styles.css` replace the per-page hero image rules:
```css
.page-play    .hero::before { background-image: url(images/photos/action-43.jpg); }
.page-train   .hero::before { background-image: url(images/photos/venue-9469.jpg); }
.page-juniors .hero::before { background-image: url(images/photos/venue-9471.jpg); }
.page-events  .hero::before { background-image: url(images/photos/league-coed3s.jpg); }
```
Give sub-page heroes a contained-but-bold height (shorter than the home hero):
```css
.page-play .hero, .page-train .hero, .page-juniors .hero, .page-events .hero { min-height: 52vh; }
```
(The home hero stays 82vh from Task 5.)

- [ ] **Step 3: Verify each sub-page**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
grep -n "page-play .hero::before\|page-train .hero::before\|page-juniors .hero::before\|page-events .hero::before" styles.css
```
Expected: each maps to the intended real photo. Open all four sub-pages in the browser: each shows a full-bleed photo hero with the condensed uppercase header and breadcrumb; nav/footer/buttons match the homepage.

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add atxbeach_site
git commit -m "Restyle sub-page heroes with real photos and shared shell"
```

---

## Task 9: Responsive + cross-page QA, remove dead placeholders

**Files:**
- Modify: `atxbeach_site/styles.css` (`@media` blocks if needed)
- Remove: unused `atxbeach_site/images/*.svg`

- [ ] **Step 1: Check responsive behavior on every page**

Open each of the 5 pages at ~375px and ~1280px widths (browser devtools or Chrome MCP resize). Confirm: tiles collapse 2×2 → 1 column; nav links hide and the mobile CTA bar appears < 720px; hero text stays legible; no horizontal scroll. Fix any breakpoint in the existing `@media (max-width: 900px)` / `(max-width: 720px)` blocks.

- [ ] **Step 2: Find and remove unused SVG placeholders**

Run:
```bash
cd /Users/aduke/code/beta/atxbeach_site
for f in images/*.svg; do n=$(basename "$f"); c=$(grep -rl "$n" *.html styles.css | wc -l | tr -d ' '); echo "$c  $f"; done
```
For any file with count `0`, remove it:
```bash
git rm atxbeach_site/images/<unused>.svg
```
(Keep `lines-bg.svg`-style files only if still referenced.)

- [ ] **Step 3: Final visual confirmation**

Open `index.html` and all four sub-pages. Confirm the success criteria from the spec: Nike-structured + ATX-Beach-colored, cohesive shell, real photos everywhere, all routing CTAs intact (YourCourts / TeamUp / VolleyballLife / TicketSocket).

- [ ] **Step 4: Commit**

```bash
cd /Users/aduke/code/beta
git add -A atxbeach_site
git commit -m "Responsive QA pass and remove unused SVG placeholders"
```

---

## Self-Review

**Spec coverage:**
- Nike structure + beach palette → Tasks 3–8. ✔
- Whole-site cohesive (5 pages) → Tasks 2, 4, 8 (shared shell), 5–7 (home), 8 (subs). ✔
- Real photos replace SVGs → Tasks 1, 5, 6, 7, 8, 9. ✔
- Condensed display typography → Task 2. ✔
- Homepage hero → tiles → routing → venue band → policies → contact → footer → Tasks 5, 6, 7. ✔
- Image optimization / bandwidth note → Task 1. ✔
- Routing CTAs preserved → not modified (verified in Task 9). ✔
- Out of scope (backend, new pages, btm/, video) → not touched. ✔

**Placeholder scan:** No "TBD/TODO"; every code step shows real CSS/HTML and exact commands. ✔

**Type/name consistency:** Filenames (`action-87`, `action-43`, `venue-9468`, `venue-9469`, `venue-9471`, `league-coed3s`, `play-4142`, `logo-white.png`) match Task 1 outputs and the spec asset table; tokens (`--black`, `--paper`, `--radius`, `--radius-sm`, `--font-display`, `--font-body`) defined in Tasks 2–3 before use in Tasks 4–8. ✔
