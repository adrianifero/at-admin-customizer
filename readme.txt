=== AT Admin Palette ===
Contributors: adrianifero
Tags: admin, dashboard, colors, branding, widgets
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Brand your WordPress admin with your own colors, and curate dashboard widgets. Lightweight, no white-label bloat.

== Description ==

https://www.youtube.com/watch?v=7mr7VNq7BGo

AT Admin Palette helps you make the WordPress admin feel like *your* workspace.

* **Admin colors** - open a floating color customizer on any admin screen. Pick a starter palette or choose your own colors. Preview live, then save.
* **Dashboard widgets** - add up to two custom dashboard boxes, choose which roles see them, and optionally hide the default WordPress widgets.

On install, the admin looks like normal WordPress until you choose a palette.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install the zip via Plugins → Add New.
2. Activate **AT Admin Palette**.
3. Look for the color button in the bottom-right of any admin screen, or go to **Settings → AT Admin Palette**.

== Frequently Asked Questions ==

= Does this change my public site? =

No. Colors apply to the WordPress admin. Your front-end theme is unchanged.

= Are there default brand colors? =

On install, the admin looks like normal WordPress. Starter palettes (Slate, Ocean, Forest, Warm) are optional starting points you can customize further.

= Who can change colors? =

Users with the `manage_options` capability (typically Administrators).

== Screenshots ==

1. Floating color customizer on the dashboard.
2. Live preview while adjusting palette colors.
3. Settings screen for custom dashboard widgets.

== Changelog ==

= 1.1.0 =
* First public release as AT Admin Palette.
* New floating admin color customizer with live preview.
* Starter palettes and per-token color pickers (menu, admin bar, buttons, links).
* Settings screen cleaned up; widget options use nonces and a single options array.
* Prefixed internals (atac_); admin colors via wp_add_inline_style.
* Migrates previous Customize Admin Dashboard / AT Admin Customizer widget options when present.
* Verified for WordPress 6.x / 7.x; Tested up to 7.1.

= 1.0.2a =
* Custom dashboard boxes and option to remove default metaboxes (as Customize Admin Dashboard).

= 1.0.1 =
* Localization-ready; one or two custom boxes.

== Upgrade Notice ==

= 1.1.0 =
Adds a live admin color customizer. Existing custom dashboard boxes are migrated automatically.
