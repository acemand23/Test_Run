# Digaball Flagship Revamp — Design Spec

**Date:** 2026-06-30
**Author:** Coach Duke (Anthony Duke) + Claude
**Status:** Approved structure; building mockups
**Deploys to:** `digaball.com/beta/` (GitHub `acemand23/Test_Run`, `master` auto-deploy)

---

## 1. Goal

Today `digaball.com` is a single-purpose "Sand Training & Promotions" site. Revamp it
into the **flagship** for the whole Digaball organization — a **unified, one-page brand
site** where sand training stays the heart and the other ventures appear as sections
within it (not links to separate sites).

Working method: build **3 visual directions** as real, responsive pages in the beta
(`digaball_v1/`, `v2/`, `v3/`), add a card for each to the beta index, and let Coach Duke
pick / mix. Same content in all three; the **visual direction** differs.

## 2. What Digaball is (brand facts — use real copy, not lorem)

**Digaball** — umbrella organization for the **development and marketing of volleyball,
indoor and outdoor**. Founded and led by Coach Duke.

**Mission (verbatim from live site):**
> "Our mission is to train athletes to be the best at our sport, and to promote the sport
> so that our athletes have a future sport to play."

**History arc (verbatim):**
> "Over the last 30 years, we have watched the sport of sand volleyball grow to an amazing
> success in the early 90's, then to collapse in 2000, and now to emerge as the fast
> growing sport in collegiate history in 2015."

**People:**
- **Anthony "Coach Duke" Duke** — Co-Owner, Coach, Trainer, Promoter, DJ. 30+ years.
  Coached the University of Texas Women's Indoor club team to a **Gold National Title
  (2000)** and **Silver National Title (2014)**. **AVP Pro Coach, 2018–2020.**
- **Jayna Duke** — Co-Owner & Trainer. Top-50 US sand volleyball player 2009–2017;
  highest national rank **#35 (2016)**.

**The four ventures (sections within the page):**
1. **Digaball Sand Training (Austin)** — *the heart.* Coach Duke's personal sand
   coaching; "now accepting new training clients." Primary CTA = training inquiry.
2. **Reaction Trainer** — phone app that trains volleyball reaction time & hand-eye
   coordination. This is the site's current "App Coming Soon." CTA = notify / coming soon.
3. **vballmanager** — club-owned league / tournament / facility-management software, in
   beta (live scoring, Stripe Connect payouts). CTA = visit `vballmanager.com`.
4. **Coach Duke AI Music** — Coach Duke's DJ project; AI-generated, sand-volleyball-themed
   music. CTA = listen (streaming links TBD).

**Events (from live site — DATES LIKELY STALE, treat as editable placeholders):**
- Hawaii Dino Tournament (Apr 23–27)
- Aspen Motherlode Tournament (Aug 26–Sep 2)

**Existing brand identity:** black + red (`#ff0000`), Open Sans. Blocky **DIGABALL**
wordmark with red accents on the "I" and "A" and a volleyball oval behind "DIG"; tagline
"Professional Sand Volleyball Training." Logo saved as `Title.gif` (523×88, transparent).

## 3. Decision

**Unified one-page brand site** (chosen over hub/launchpad and modernized-current).
Build **all 3** visual directions. **Reuse the existing DIGABALL wordmark**, but design
fresh wordmarks/lockups per direction where there's an opening (especially on dark
backgrounds where the black wordmark won't read).

## 4. Shared content architecture (identical skeleton in all 3 mockups)

```
HEADER    DIGABALL wordmark · nav anchors (Training · App · Software · Music · About · Contact) · sticky
HERO      "Digaball — the volleyball company." Development & marketing of volleyball,
          indoor & outdoor. 30 years. Coach Duke. Primary CTA: "Train with us."
COACH     Who we are — Coach Duke (30 yrs, UT titles, AVP) + Jayna Duke (#35 US).
          Mission line + the 30-year history arc.
VENTURES  Four sections, each: name · one-liner · CTA · visual.
            1 Sand Training (Austin)  → training inquiry        (heart / largest)
            2 Reaction Trainer (app)  → "coming soon / notify"
            3 vballmanager (software) → vballmanager.com
            4 Coach Duke AI Music     → listen
EVENTS    Upcoming tournaments (editable date/title text, like TBD_v5 overlays)
CONTACT   Training-inquiry block + email (TBD — confirm address)
FOOTER    © Digaball · indoor & outdoor volleyball, Austin TX
```

Event dates and "accepting clients" copy live as **plain editable text** (one-line edits,
no image editing), matching the TBD_v5 pattern.

## 5. The three visual directions

All three: responsive (mobile-first, single breakpoint ~720px), self-contained
(inline `<style>` + minimal assets), reuse the real wordmark where it reads, and keep red
tethered to the heritage brand so the set still feels like Digaball.

### V1 · Coastal Pro — *the coach + sand heritage*
Premium athletic-beach, editorial. Full-bleed sand/court photography, Coach Duke front and
center. Palette: warm sand/cream + deep ocean teal, **red kept as a sharp accent**. Type:
confident grotesk with an editorial display face for headlines. Feels like a high-end
beach-volleyball brand. Sand Training is the hero; other ventures are clean supporting
sections.

### V2 · Court Lines — *the products (app + SaaS)*
Clean, techy, "house of products" within one page — true to the black + red heritage.
Palette: white + near-black, **red as the system accent**, court-line motifs as dividers.
Type: **Hanken Grotesk + JetBrains Mono** (matches the existing beta index system). Crisp
venture cards, structured grid, lots of whitespace. Leans into the app + vballmanager.

### V3 · Coach Duke / Hype — *the personality + music*
Bold, dark, DJ/sunset energy — amps up the existing black + red. Palette: near-black +
neon sunset gradient (red → magenta → orange). Type: heavy high-contrast display. Hero
with motion/gradient, music-forward. Fresh glowing wordmark (the black logo won't read on
dark). Feels like a movement/lifestyle brand; music and personality lead, training and
products follow.

## 6. Build plan

- One shared content partial of truth = this spec. Each mockup is a standalone folder so
  they can diverge freely:
  - `digaball_v1/` (Coastal Pro) — `index.html` + `assets/`
  - `digaball_v2/` (Court Lines) — `index.html` + `assets/`
  - `digaball_v3/` (Coach Duke / Hype) — `index.html` + `assets/`
- Copy `Title.gif` into each mockup's `assets/` for reuse; build CSS/SVG wordmark variants
  where the raster logo won't read (dark backgrounds).
- Add a card per mockup to the root beta `index.html` ("Digaball — Flagship vN").
- Static HTML/CSS (no PHP needed for mockups); responsive; no build step.

## 7. Open items / placeholders (confirm with Coach Duke as we go)

- **Contact email** for training inquiries (the live site obscures it). Placeholder until
  confirmed.
- **Event dates** — Hawaii Dino / Aspen Motherlode dates look stale (it's June 2026).
  Built as editable text; confirm or replace.
- **Reaction Trainer** — app store links / "notify me" target (coming soon for now).
- **Coach Duke AI Music** — streaming links (Spotify / SoundCloud / YouTube?).
- **vballmanager** — confirm the outbound link `https://vballmanager.com`.
- **Photos** — using placeholders / existing assets until real sand-training + Coach Duke
  photos are provided.
- **"Now accepting clients"** — current site says 2025 Spring/Summer; update to 2026.
```
