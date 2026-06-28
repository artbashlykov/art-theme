=== ART Theme ===
Contributors: artbashlykov
Tags: one-column, custom-menu, custom-logo, featured-images, translation-ready, editor-style
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Minimal classic theme: customizable header, footer, blog, posts, and pages via Customizer. Gray canvas, white content blocks, Gutenberg for post and page content.

== Description ==

ART Theme is a lightweight classic WordPress theme. Header, footer, blog archive, single posts, and pages are configured in the Customizer — not the Site Editor.

Main features:

* Customizable site header (templates, menu, logo, spacing)
* Customizable footer (structure, social links, copyright)
* Blog archive with cards, columns, and category filter
* Single post and page templates (boxed and full-width)
* One-column layout on a gray canvas — no sidebar
* Gutenberg block editor for post and page content
* Bundled Manrope and Rubik web fonts (latin, cyrillic) — no external font CDN
* Optional integration with ART Starter social icon registry when the plugin is active

== Installation ==

1. Upload the `art-theme` folder to `/wp-content/themes/`.
2. Activate **ART Theme** under Appearance → Themes.
3. Assign menus under Appearance → Menus (header menu location).

== Frequently Asked Questions ==

= Can I edit the site layout in the Site Editor? =

No. ART Theme is a classic theme. The layout is defined in theme files. Use Gutenberg only for post and page content.

= Does ART Theme include a sidebar? =

No. The theme is intentionally one-column.

= Does ART Theme require ART Starter? =

No. The theme works standalone. If ART Starter is active, footer social icons can use the shared icon registry.

== Changelog ==

= 1.0.1 =
* Post meta cache writes only on save_post (security).
* Footer and header menu alignment with WordPress theme location standards.
* Social URL sanitization in footer settings.
* Code cleanup and performance documentation.

= 1.0.0 =
* Classic theme: PHP layout shell, Gutenberg for content only.
* Local Manrope and Rubik fonts (latin, cyrillic).
