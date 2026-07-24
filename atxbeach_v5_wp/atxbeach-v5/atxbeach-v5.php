<?php
/**
 * Plugin Name:       ATX Beach v5 (Aerial)
 * Description:        Renders the approved ATX Beach "aerial court" homepage and its four sub-pages (Play, Train, ATX Juniors, Events) as standalone, full-bleed page templates — no theme chrome. Assign a template to a Page under Page Attributes → Template. Deactivating the plugin instantly removes the templates (a built-in rollback lever); the pages themselves are never deleted.
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
    ];
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
