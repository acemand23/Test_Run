# ATX Beach — Nike-Style Redesign (Design Spec)

**Date:** 2026-05-21
**Scope target:** `atxbeach_site/` (the `btm/` folder is out of scope)

## Goal

Restyle the existing ATX Beach site so it adopts Nike.com's structural design
language — huge full-bleed imagery, minimal text, oversized bold type, generous
negative space, restrained UI — while keeping ATX Beach's warm brand palette.
Per the client thread: a homepage built around **4 large clickable tiles**
(Play, Train, ATX Juniors, Events) and a normal contact section at the bottom.

The site is plain static HTML + one shared `styles.css`. No build step.

## Locked Decisions

1. **Aesthetic:** Nike *structure*, ATX Beach *palette*. Near-black/white carries
   the layout (like Nike); teal/coral/sand become sharp accents (CTAs, hovers,
   brand marks) rather than dominant background fields.
2. **Scope:** Whole site, cohesive — homepage + all 4 sub-pages
   (Play, Train, ATX Juniors, Events). Sub-pages keep their content and routing
   CTAs; they get the new global shell + full-bleed photo heroes.
3. **Imagery:** Real ATX Beach photos pulled from the live atxbeach.com (already
   downloaded to `atxbeach_site/images/photos/`). Replace the SVG placeholders.
4. **Typography:** "Condensed & loud" — condensed heavy display headlines in
   ALL CAPS (Archivo / Oswald family) + Inter for body. One Google Fonts load.

## Design System

### Color
Reuse existing tokens in `styles.css` but re-weight them:
- **Base/neutral (new dominant):** near-black `#0d0d0f` / off-white `#f7f7f5` / greys.
- **Accents (existing brand):** `--coral #ff6b4a` (primary CTA), `--teal #1aa6b7`
  (secondary/links/hover), `--sun #ffc14d` (eyebrows/highlights), `--navy` retained
  for deep sections. Per-page accent variables (`.page-play` etc.) are kept.
- Sharp corners or very small radii (Nike is squared, not rounded) — reduce
  `--radius` from 18px toward ~2–4px for tiles/buttons; keep some softness only
  where it reads better on mobile.

### Typography
- Headlines: condensed heavy display (e.g. **Archivo Narrow / Oswald**),
  uppercase, tight letter-spacing, very large `clamp()` sizes.
- Body/UI: **Inter** (system fallback). Quiet, neutral.
- Load via a single Google Fonts `<link>` with `display=swap` on every page.

### Components (in `styles.css`)
- **Nav:** thin, sticky, dark; white logo image (`logo-white.png`) replaces the
  text "● ATX BEACH"; small uppercase links; one accent CTA pill.
- **Buttons:** squared (low radius); solid black or coral primary, outline/ghost
  variants on photos. Subtle hover (no big lift).
- **Tile:** full-bleed photo, dark bottom-gradient, big uppercase label + arrow,
  hover image-zoom. 2×2 on desktop, single column on mobile.
- **Section headers:** oversized condensed uppercase, lots of whitespace.
- **Footer / mobile CTA bar:** dark, minimal; carried across all pages.

## Homepage Layout (`index.html`)

1. **Hero** — near-viewport full-bleed photo (`action-87`, jump serve with open
   sky), big overlaid headline "PLAY. TRAIN. COMPETE.", one line of subtext,
   two CTAs (Get Started / Plan an Event).
2. **4 tiles** (2×2 full-bleed): Play (`action-43`), Train (`venue-9468`),
   ATX Juniors (`venue-9471`), Events (`league-coed3s`). Big label + arrow.
3. **Jump-straight-in routing strip** — minimal grid of the quick actions, each
   tagged with its system: Book a Court / Open Play → YourCourts; Adult Training /
   Memberships → TeamUp; Tournaments → VolleyballLife; Major Events → TicketSocket;
   Private Events → inquiry form; Turtle Shack → on-site.
4. **Venue-scale band** — drone aerial (`play-4142`): "Austin's largest — 8 pro
   courts" + Turtle Shack mention.
5. **Policies / Venue Info** — restyled minimal, content unchanged.
6. **Contact** — info + lead form (kept; LJ explicitly wants this at the bottom).
7. **Footer** + mobile CTA bar.

## Sub-Page Layout (`play/train/juniors/events.html`)

Each keeps its existing body content, sections, and routing CTAs. Changes:
- New global shell (nav, type, buttons, footer, mobile CTA) via shared CSS.
- Full-bleed photo hero using the relevant real photo + breadcrumb + bold header.
- Section styling inherits the new system automatically.

## Image Assets

Downloaded to `atxbeach_site/images/photos/` (originals, some up to ~17MB):

| Role | File |
|------|------|
| Hero | `action-87.jpg` (alt: `hero-16x9.jpg`) |
| Play tile | `action-43.jpg` |
| Train tile | `venue-9468.jpg` (alt: `venue-9469.jpg`) |
| Juniors tile | `venue-9471.jpg` (alt: `venue-9467.jpg`) |
| Events tile | `league-coed3s.jpg` |
| Venue band | `play-4142.jpg` |
| Logo (dark bg) | `logo-white.png` |
| Logo (light bg) | `logo-dark.png` |
| Events page content | `venue-9378.jpg` (Triple Crown), `venue-9465.jpg` (Open Play promo) |

**Optimization (required — WP Engine bandwidth note):** downscale + recompress
all photos to web sizes before use — hero ~2000px wide, tiles ~1280px, band
~1600px, JPEG quality ~80, target < ~300KB each. Use `sips`. Keep optimized
copies in `images/photos/`. Existing SVG placeholders may be removed once unused.

## Out of Scope (this pass)

- No backend, CRM (Copper), Mailchimp, or live booking-system integration — tile
  CTAs remain links/placeholders pointing at the right systems by name.
- No new pages beyond the existing five (Memberships, Private Events, Sponsors,
  Turtle Shack, Policies as standalone pages are future work).
- No video embeds yet.
- `btm/` folder untouched.

## Success Criteria

- Homepage reads as Nike-structured (full-bleed imagery, minimal text, bold
  condensed type, whitespace) but unmistakably ATX Beach (palette, logo, photos).
- All 5 pages share one cohesive restyled system; nothing visually orphaned.
- Real photos replace all SVG placeholders; every image is web-optimized.
- Responsive: 2×2 tiles collapse to one column; nav collapses; mobile CTA bar shows.
- All existing routing CTAs (YourCourts/TeamUp/VolleyballLife/TicketSocket) preserved.
