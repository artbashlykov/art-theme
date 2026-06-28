# ART Theme — performance notes

This document explains speed-related architecture in the theme. Read it before adding templates, CSS, or post loops.

## CSS bundles (split styles)

Front-end CSS lives in **partials**, not in one monolithic file:

| File | Loaded when |
|------|-------------|
| `theme-base.css` | Always |
| `theme-header.css` | Header has visible items |
| `theme-single.css` | Any singular view (posts) + search with posts |
| `theme-page.css` | Any singular view (pages, public CPTs) + search with pages |

On **every singular URL** (post, page, public CPT), both `single` and `page` bundles load. This matches `single.php` / `page.php` and avoids missing layout CSS when post type routing differs.
| `theme-archive.css` | Blog home, category, tag, date, author archives |
| `theme-not-found.css` | 404 |
| `theme-footer.css` | Footer has visible content |

**Logic:** `Art_Theme_Styles::get_active_bundles()` in `inc/class-theme-styles.php`.

### When you change CSS

1. Edit the matching **`theme-*.css`** partial for the view you are changing.
2. If you add a **new template/view** (e.g. new archive type), update `get_active_bundles()` so the right partial loads.
3. In the block editor, **all** partials load via `Art_Theme_Styles::get_editor_stylesheets()` — preview stays complete.

### Debug: force full CSS

```php
add_filter( 'art_theme_force_full_styles', '__return_true' );
```

Use temporarily if a page looks unstyled after changes.

## wp-block-library (conditional)

`Art_Theme_Styles::should_enqueue_block_library()` loads WordPress block styles when:

- **Any singular** post, page, or public CPT (`the_content()`)
- **Search results**
- Customizer preview (always)

**Not loaded** on blog archives and 404 (excerpts only; block styles dequeued after core registers them).

Do not list `global-styles` as a theme CSS dependency unless it is explicitly enqueued — an un-enqueued dependency can prevent theme bundles from printing on some posts.

### When you add templates

If a new template outputs **`the_content()`** with Gutenberg blocks, ensure block library loads:

- Extend `should_enqueue_block_library()`, or
- Use `add_filter( 'art_theme_force_full_styles', '__return_true' )` while developing, then fix properly.

## Archive performance (post meta)

`inc/post-performance.php` caches expensive per-post data:

| Meta key | Purpose |
|----------|---------|
| `_art_theme_reading_minutes` | Reading time on archive cards |
| `_art_theme_list_image_id` | Card image when no featured image |

- Recomputed on **`save_post_post`** only (no front-end writes to post meta)
- If meta is missing, archive helpers compute values on read without persisting; re-save the post to populate cache

**Archive images:** `art_theme_get_archive_post_image_id()` — featured image, then cached meta, then read-only fallback via `art_theme_resolve_list_image_id()`.

**Do not** call `art_theme_resolve_list_image_id()` inside archive loops — use archive helper or featured image only.

## Theme settings cache

All `Art_*_Settings::get()` methods cache in a **static variable** for the request. Safe for repeated calls in templates.

If you add long-running CLI/queue code in-process, do not assume options changed mid-request without clearing cache.

## Inline CSS variables

Customizer spacing/width variables attach to handle `art-theme-base` (`Art_Theme_Styles::get_primary_handle()`). If you change the base handle, update `inc/enqueue.php` too.

## Checklist for new features

- [ ] New UI styles → correct `theme-*.css` partial
- [ ] New front-end route → `get_active_bundles()` / filter `art_theme_style_bundles`
- [ ] Template renders blocks → block library rule updated
- [ ] Archive loop → pass settings **once** (see `archive.php` / `index.php`)
- [ ] Archive loop → use `art_theme_get_archive_post_image_id()` and cached reading time
- [ ] Test: archive, single post with blocks, page, 404, search, Customizer preview
