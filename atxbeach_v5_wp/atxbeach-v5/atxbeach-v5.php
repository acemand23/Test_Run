<?php
/**
 * Plugin Name:       ATX Beach v5 (Aerial)
 * Description:        Renders the approved ATX Beach "aerial court" homepage and its five sub-pages (Play, Train, ATX Juniors, Events, Leagues) as standalone, full-bleed page templates — no theme chrome. Assign a template to a Page under Page Attributes → Template. Deactivating the plugin instantly removes the templates (a built-in rollback lever); the pages themselves are never deleted.
 * Version:           1.0.0
 * Author:            Digaball
 * License:           GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

if (!defined('ABSPATH')) exit;

define('ATXBEACH_V5_DIR', plugin_dir_path(__FILE__));
define('ATXBEACH_ASSETS', plugins_url('assets/', __FILE__)); // note: trailing slash

/**
 * Registered templates: key => [ menu label, template file ].
 * The key is what WordPress stores on the page (_wp_page_template).
 */
function atxbeach_v5_templates(): array {
    return [
        'atxb-home'    => ['ATX Beach — Home',        'templates/home.php'],
        'atxb-play'    => ['ATX Beach — Play',        'templates/play.php'],
        'atxb-train'   => ['ATX Beach — Train',       'templates/train.php'],
        'atxb-juniors' => ['ATX Beach — ATX Juniors', 'templates/juniors.php'],
        'atxb-events'  => ['ATX Beach — Events',      'templates/events.php'],
        'atxb-leagues' => ['ATX Beach — Leagues',     'templates/leagues.php'],
    ];
}

/**
 * Page slugs for the ATX Beach set — kept distinct so they never collide with the
 * existing live site (e.g. /train/, /events/ already exist on atxbeach.com). Change
 * here (or via the 'atxb_v5_slugs' filter) and rename the pages to match.
 */
function atxb_v5_slugs(): array {
    return apply_filters('atxb_v5_slugs', [
        'home'    => 'sand-home',
        'play'    => 'sand-play',
        'train'   => 'sand-train',
        'juniors' => 'sand-juniors',
        'events'  => 'sand-events',
        'leagues' => 'sand-leagues',
    ]);
}

/** Resolve an inter-page link by key. 'home' points to '/' once it's the static front page. */
function atxb_v5_url(string $key): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $slug = atxb_v5_slugs()[$key] ?? '';
    if ($slug === '') return $cache[$key] = home_url('/');
    $page = get_page_by_path($slug);
    return $cache[$key] = $page ? get_permalink($page) : home_url('/' . $slug . '/');
}

/** Offer our templates in the Page → Template dropdown (block + classic editors). */
add_filter('theme_page_templates', function (array $templates): array {
    foreach (atxbeach_v5_templates() as $key => $t) $templates[$key] = $t[0];
    return $templates;
});

/** When a page uses one of our templates, render it from the plugin instead of the theme. */
add_filter('template_include', function (string $template): string {
    if (!is_page()) return $template;
    $assigned = get_page_template_slug(get_queried_object_id());
    $map = atxbeach_v5_templates();
    if (isset($map[$assigned])) {
        $file = ATXBEACH_V5_DIR . $map[$assigned][1];
        if (is_file($file)) return $file;
    }
    return $template;
});

/**
 * On our pages only: hide the admin bar (it offsets the full-viewport layout) and drop the
 * theme's global/block CSS so nothing competes with the mockup's own styling.
 */
add_action('template_redirect', function (): void {
    if (!is_page()) return;
    if (!isset(atxbeach_v5_templates()[(string) get_page_template_slug(get_queried_object_id())])) return;
    add_filter('show_admin_bar', '__return_false');
    // Our template supplies its own <title>; drop WordPress's (classic + block-theme renderers).
    remove_action('wp_head', '_wp_render_title_tag', 1);
    remove_action('wp_head', '_block_template_render_title_tag', 1);
    add_action('wp_enqueue_scripts', function (): void {
        foreach (['wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles'] as $h) {
            wp_dequeue_style($h);
        }
    }, 100);
});

/* ---- Host-event inquiry form (Events page) --------------------------------- */

/** Where host inquiries are emailed: the atxb_host_email option, else the site admin. */
function atxb_host_inquiry_recipient(): string {
    $opt = get_option('atxb_host_email');
    if (is_email($opt)) return $opt;      // optional override via the atxb_host_email option
    return 'lj@atxbeach.com';             // default recipient on prod (no WP-CLI needed)
}

/** Handle an Events-page inquiry submission and email it (POST -> admin-post.php). */
function atxb_handle_host_inquiry(): void {
    $back = home_url('/events/');
    if (!isset($_POST['atxb_nonce']) || !wp_verify_nonce($_POST['atxb_nonce'], 'atxb_host_inquiry')) {
        wp_safe_redirect($back . '?sent=err#inquiry'); exit;
    }
    if (!empty($_POST['website'])) { wp_safe_redirect($back . '?sent=1#inquiry'); exit; } // honeypot

    $name   = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email  = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone  = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $type   = sanitize_text_field(wp_unslash($_POST['event_type'] ?? ''));
    $date   = sanitize_text_field(wp_unslash($_POST['event_date'] ?? ''));
    $guests = sanitize_text_field(wp_unslash($_POST['guests'] ?? ''));
    $msg    = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

    if ($name === '' || !is_email($email)) { wp_safe_redirect($back . '?sent=err#inquiry'); exit; }

    $body = "New host / private-event inquiry from the ATX Beach website:\n\n"
          . "Name:             $name\n"
          . "Email:            $email\n"
          . "Phone:            $phone\n"
          . "Event type:       $type\n"
          . "Preferred date:   $date\n"
          . "Estimated guests: $guests\n\n"
          . "Message:\n$msg\n";
    wp_mail(
        atxb_host_inquiry_recipient(),
        'ATX Beach — Host Event Inquiry from ' . $name,
        $body,
        ['Reply-To: ' . $name . ' <' . $email . '>']
    );
    wp_safe_redirect($back . '?sent=1#inquiry'); exit;
}
add_action('admin_post_nopriv_atxb_host_inquiry', 'atxb_handle_host_inquiry');
add_action('admin_post_atxb_host_inquiry', 'atxb_handle_host_inquiry');

/** Success/error banner rendered on the Events page after a submission (token in the template). */
function atxb_host_notice(): void {
    $s = $_GET['sent'] ?? '';
    if ($s === '1') {
        echo '<p class="notice notice-ok">Thanks — your inquiry is on its way. Our events team will follow up soon.</p>';
    } elseif ($s === 'err') {
        echo '<p class="notice notice-err">Please add your name and a valid email, then try again.</p>';
    }
}
