=== ATX Beach v5 (Aerial) ===
Contributors: Digaball
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later

The approved ATX Beach "aerial court" homepage + four sub-pages (Play, Train,
ATX Juniors, Events) as standalone, full-bleed WordPress page templates.

== What it is ==

This plugin adds five page templates. It does NOT touch the active theme, so it
is safe to run alongside the existing Hub/Elementor site. Each template renders
its page full-bleed (no theme header/footer), pixel-matched to the sign-off
mockup, and loads only its own CSS/images.

Rollback is built in two ways:
  1. Switch the homepage back (Settings -> Reading) — the old homepage is never
     touched, so this is instant.
  2. Deactivate this plugin — the five templates disappear and the pages fall
     back to the theme default. The Page content itself is never deleted.

== Install ==

1. WP Admin -> Plugins -> Add New -> Upload Plugin -> choose atxbeach-v5.zip -> Install.
2. Activate "ATX Beach v5 (Aerial)".

== One-time setup: create the 5 pages ==

The homepage tiles link to /play/, /train/, /juniors/, /events/, so those pages
must exist with those exact slugs.

For each page below: Pages -> Add New, set the Title and permalink SLUG, then in
Page Attributes -> Template pick the matching template, and Publish.

    Title          Slug       Template
    -----          ----       --------
    Home           home       ATX Beach — Home
    Play           play       ATX Beach — Play
    Train          train      ATX Beach — Train
    ATX Juniors    juniors    ATX Beach — ATX Juniors
    Events         events     ATX Beach — Events

(Leave the page body empty — the template supplies all content.)

Faster, via WP-CLI:
    wp plugin activate atxbeach-v5
    for row in "Home:home:atxb-home" "Play:play:atxb-play" "Train:train:atxb-train" \
               "ATX Juniors:juniors:atxb-juniors" "Events:events:atxb-events"; do
      T="${row%%:*}"; rest="${row#*:}"; S="${rest%%:*}"; TPL="${rest#*:}"
      ID=$(wp post create --post_type=page --post_status=publish --post_title="$T" --post_name="$S" --porcelain)
      wp post meta update "$ID" _wp_page_template "$TPL"
    done
    wp rewrite flush

== THE FLIP (go live) ==

1. Take a restore point first (WP Engine -> Backups -> Back up now), just in case.
2. Settings -> Reading -> "Your homepage displays" -> A static page ->
   Homepage: **Home** (the ATX Beach — Home page). Save.
3. PURGE CACHES — this is the step people forget:
     - WP Engine: caching -> Clear all caches (or `wp page-cache flush`)
     - Cloudflare: Caching -> Configuration -> Purge Everything
4. Open the site in a private/incognito window and confirm.

== ROLLBACK (something looks wrong) ==

1. Settings -> Reading -> set "Homepage" back to the previous homepage. Save.
   (Or simply deactivate this plugin.)
2. Purge WP Engine + Cloudflare caches again.
Under a minute, and the old homepage returns exactly as it was.

== Still to swap in real content ==

These are placeholders in the mockup — update the linked template files (or,
better, wire them to Customizer/ACF fields before launch):
  - "Sign Waiver" button -> real YourCourts waiver URL
  - Events page -> real TicketSocket link
  - Ticker headlines + tournament dates
  - "Book a Court" / open-play buttons -> real YourCourts URLs

== Notes ==

- Assets live in /wp-content/plugins/atxbeach-v5/assets/. Only the images the
  pages actually use are shipped (~3 MB total, down from the 9.8 MB source set).
- The templates hide the admin bar and drop the theme's global CSS on these
  pages so nothing competes with the design. Other pages are unaffected.
