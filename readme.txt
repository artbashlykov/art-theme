=== ART Theme ===
Contributors: artbashlykov
Tags: one-column, custom-menu, custom-logo, featured-images, translation-ready, editor-style
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.12
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

= 1.0.12 =
* Customizer: текст копирайта и другие настройки подвала обновляются в превью без публикации.
* Исправлен stale cache вложенных option-настроек в превью Customizer.

= 1.0.11 =
* Подвал: шорткод [current_year] в тексте копирайта (например: (c) 2018-[current_year]).

= 1.0.10 =
* Шапка: пункты меню на ПК сворачиваются в бургер, если не помещаются в одну строку.
* Новый переключатель в Customizer → Шапка (включён по умолчанию).
* Определение переполнения по геометрии меню — корректно ловит наложение на кнопку.

= 1.0.9 =
* Исправлена иконка ВКонтакте в подвале: отдельный SVG без рамки приложения.
* Иконки подвала из темы имеют приоритет над ART Starter; центрирование в круглых кнопках.

= 1.0.8 =
* Русские подписи для пустого блога и поиска без результатов.
* Центрирование блока «нет записей» в области контента.

= 1.0.7 =
* Customizer: меню из панели «Меню → Меню шапки» видно в предпросмотре до «Опубликовать».
* Синхронизация области меню и выпадающего списка в настройках шапки; автообновление превью.

= 1.0.6 =
* Исправлены PHP Warning в Customizer при чтении несохранённого меню шапки.
* Архив: у карточек без миниатюры тёмный только блок изображения, не вся карточка.

= 1.0.5 =
* Customizer preview: header menu visible before Publish (unsaved menu assignments).
* Archive cards without a featured image use a dark style so they stand out on the gray canvas.

= 1.0.4 =
* Universal header bottom spacing on all singular and archive views.
* Unified boxed content template defaults for posts, pages, and public CPTs.
* Header defaults: «Личный кабинет» button, all layout checkboxes enabled.
* Improved logo scaling within the header bar (5px inset, aspect ratio preserved).
* CPT template override meta and block editor sidebar support.

= 1.0.3 =
* Исправлено падение Customizer на новых сайтах без назначенного меню.
* Обновлён screenshot.png (превью темы в админке).

= 1.0.2 =
* Обновлено описание темы (скорость загрузки, мобильные устройства).
* Протестировано с WordPress 7.0.

= 1.0.1 =
* Post meta cache writes only on save_post (security).
* Footer and header menu alignment with WordPress theme location standards.
* Social URL sanitization in footer settings.
* Code cleanup and performance documentation.

= 1.0.0 =
* Classic theme: PHP layout shell, Gutenberg for content only.
* Local Manrope and Rubik fonts (latin, cyrillic).
