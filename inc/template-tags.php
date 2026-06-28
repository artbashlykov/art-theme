<?php
/**
 * Template helpers.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current view is a posts archive listing.
 *
 * @return bool
 */
function art_theme_is_blog_archive_view() {
	return is_home() || is_category() || is_tag() || is_date() || is_author();
}

/**
 * Whether a post type should use the page template layout from theme page settings.
 *
 * @param string|null $post_type Optional post type slug.
 * @return bool
 */
function art_theme_uses_page_template_layout( $post_type = null ) {
	if ( null === $post_type ) {
		if ( ! is_singular() ) {
			return false;
		}

		$post_type = get_post_type();
	}

	if ( ! is_string( $post_type ) || '' === $post_type ) {
		return false;
	}

	if ( in_array( $post_type, array( 'post', 'attachment' ), true ) ) {
		return false;
	}

	if ( 'page' === $post_type ) {
		return true;
	}

	$post_type_object = get_post_type_object( $post_type );

	return $post_type_object
		&& ! empty( $post_type_object->public )
		&& ! empty( $post_type_object->publicly_queryable );
}

/**
 * Whether the current view is a single blog post (post type: post).
 *
 * @return bool
 */
function art_theme_is_single_post_view() {
	if ( is_single() ) {
		return true;
	}

	return is_singular( 'post' );
}

/**
 * Whether the current view should use page template canvas/shell/CSS settings.
 *
 * @return bool
 */
function art_theme_is_page_template_view() {
	if ( is_page() ) {
		return true;
	}

	return is_singular() && art_theme_uses_page_template_layout();
}

/**
 * Whether the theme should render the entry title for the current singular item.
 *
 * @param int|null $post_id Optional post ID.
 * @return bool
 */
function art_theme_should_show_singular_entry_title( $post_id = null ) {
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( $post_id && is_page( $post_id ) && Art_Theme_Page_Settings::page_should_hide_title( $post_id ) ) {
		return false;
	}

	/**
	 * Filter whether the theme entry title should render on singular views.
	 *
	 * @param bool $show    Whether to show the title.
	 * @param int  $post_id Post ID.
	 */
	return (bool) apply_filters( 'art_theme_show_singular_entry_title', true, (int) $post_id );
}

add_filter(
	'art_theme_show_singular_entry_title',
	static function ( $show, $post_id ) {
		if ( 'art_lms_material' === get_post_type( $post_id ) ) {
			return false;
		}

		return $show;
	},
	10,
	2
);

/**
 * Render numbered pagination for archive and search listings.
 */
function art_theme_render_pagination() {
	global $wp_query;

	if ( ! isset( $wp_query ) || (int) $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	$links = paginate_links(
		array(
			'total'     => (int) $wp_query->max_num_pages,
			'current'   => $current,
			'type'      => 'list',
			'mid_size'  => 1,
			'end_size'  => 1,
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
		)
	);

	if ( ! $links ) {
		return;
	}

	echo '<nav class="art-theme-pagination" aria-label="' . esc_attr__( 'Навигация по страницам', 'art-theme' ) . '">';
	echo wp_kses_post( $links );
	echo '</nav>';
}

/**
 * Render blog header for listing views.
 */
function art_theme_render_archive_header() {
	$settings = Art_Theme_Blog_Settings::get();

	if ( is_home() && ! empty( $settings['hide_blog_header'] ) ) {
		return;
	}

	$title       = art_theme_get_archive_header_title();
	$description = art_theme_get_archive_header_description();
	$show_filter = Art_Theme_Blog_Settings::should_show_category_filter();

	if ( '' === $title && '' === $description && ! $show_filter ) {
		return;
	}

	echo '<header class="art-theme-archive-header">';
	echo '<div class="art-theme-archive-header__bar">';

	if ( '' !== $title ) {
		echo '<h1 class="art-theme-page-title">' . esc_html( $title ) . '</h1>';
	}

	if ( $show_filter ) {
		art_theme_render_archive_category_filter();
	}

	echo '</div>';

	if ( '' !== $description ) {
		echo '<div class="art-theme-archive-description">' . esc_html( $description ) . '</div>';
	}

	echo '</header>';
}

/**
 * Archive header title.
 *
 * @return string
 */
function art_theme_get_archive_header_title() {
	$settings = Art_Theme_Blog_Settings::get();

	if ( is_category() || is_tag() || is_date() || is_author() ) {
		if ( '' !== $settings['blog_title'] ) {
			return $settings['blog_title'];
		}

		if ( is_category() ) {
			return single_cat_title( '', false );
		}

		if ( is_tag() ) {
			return single_tag_title( '', false );
		}

		if ( is_author() ) {
			$author = get_queried_object();

			if ( $author instanceof WP_User ) {
				return $author->display_name;
			}

			return '';
		}

		return wp_strip_all_tags( get_the_archive_title() );
	}

	if ( is_home() && ! is_front_page() ) {
		if ( '' !== $settings['blog_title'] ) {
			return $settings['blog_title'];
		}

		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id ) {
			return (string) get_the_title( $posts_page_id );
		}

		return __( 'Блог', 'art-theme' );
	}

	if ( is_home() && is_front_page() && '' !== $settings['blog_title'] ) {
		return $settings['blog_title'];
	}

	return '';
}

/**
 * Archive header description.
 *
 * @return string
 */
function art_theme_get_archive_header_description() {
	$settings = Art_Theme_Blog_Settings::get();

	if ( is_category() || is_tag() || is_date() || is_author() ) {
		if ( '' !== $settings['blog_description'] ) {
			return $settings['blog_description'];
		}

		return wp_strip_all_tags( (string) get_the_archive_description() );
	}

	if ( is_home() && ! is_front_page() ) {
		if ( '' !== $settings['blog_description'] ) {
			return $settings['blog_description'];
		}

		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id ) {
			return (string) get_post_field( 'post_excerpt', $posts_page_id );
		}

		return '';
	}

	if ( is_home() && is_front_page() ) {
		return $settings['blog_description'];
	}

	return '';
}

/**
 * Whether the site has categories for the archive filter.
 *
 * @return bool
 */
function art_theme_has_archive_categories() {
	$categories = get_categories(
		array(
			'hide_empty' => true,
		)
	);

	return ! empty( $categories );
}

/**
 * Blog posts page URL for the archive filter.
 *
 * @return string
 */
function art_theme_get_blog_posts_url() {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id ) {
		return (string) get_permalink( $posts_page_id );
	}

	return (string) home_url( '/' );
}

/**
 * Render category filter for archive header.
 */
function art_theme_render_archive_category_filter() {
	if ( ! Art_Theme_Blog_Settings::should_show_category_filter() ) {
		return;
	}

	if ( ! art_theme_has_archive_categories() ) {
		return;
	}

	$categories = get_categories(
		array(
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	$settings     = Art_Theme_Blog_Settings::get();
	$blog_url     = art_theme_get_blog_posts_url();
	$current_cat  = is_category() ? (int) get_queried_object_id() : 0;
	$items        = array(
		array(
			'url'     => $blog_url,
			'label'   => $settings['all_categories_label'],
			'current' => 0 === $current_cat,
		),
	);
	$current_label = $items[0]['label'];

	foreach ( $categories as $category ) {
		if ( ! $category instanceof WP_Term ) {
			continue;
		}

		$is_current = $current_cat === (int) $category->term_id;

		if ( $is_current ) {
			$current_label = $category->name;
		}

		$items[] = array(
			'url'     => get_category_link( $category->term_id ),
			'label'   => $category->name,
			'current' => $is_current,
		);
	}

	echo '<div class="art-theme-archive-header__filter">';
	echo '<div class="art-theme-archive-filter" data-art-theme-archive-filter>';
	echo '<button type="button" class="art-theme-archive-filter__toggle" id="art-theme-archive-category-filter" aria-expanded="false" aria-controls="art-theme-archive-category-list">';
	echo '<span class="art-theme-archive-filter__label">' . esc_html( $current_label ) . '</span>';
	echo '<span class="art-theme-archive-filter__icon" aria-hidden="true"></span>';
	echo '</button>';
	echo '<ul class="art-theme-archive-filter__list" id="art-theme-archive-category-list" role="listbox" aria-labelledby="art-theme-archive-category-filter" hidden>';

	foreach ( $items as $item ) {
		$classes = 'art-theme-archive-filter__option';

		if ( ! empty( $item['current'] ) ) {
			$classes .= ' is-current';
		}

		echo '<li class="art-theme-archive-filter__item" role="none">';
		printf(
			'<a class="%1$s" role="option" href="%2$s"%3$s>%4$s</a>',
			esc_attr( $classes ),
			esc_url( $item['url'] ),
			! empty( $item['current'] ) ? ' aria-current="true"' : '',
			esc_html( $item['label'] )
		);
		echo '</li>';
	}

	echo '</ul>';
	echo '</div>';
	echo '</div>';
}

/**
 * Get image ID for archive list item: featured image or first image in content.
 *
 * @param int $post_id Post ID.
 * @return int Attachment ID or 0.
 */
function art_theme_get_post_image_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return 0;
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id ) {
		return $thumbnail_id;
	}

	return art_theme_resolve_list_image_id( $post_id );
}

/**
 * Estimate reading time in minutes for a post.
 *
 * @param int $post_id Post ID.
 * @return int Minutes, minimum 1.
 */
function art_theme_get_reading_time_minutes( $post_id = 0 ) {
	return art_theme_get_cached_reading_time_minutes( $post_id );
}

/**
 * Localized reading time label for archive cards.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function art_theme_get_reading_time_label( $post_id = 0 ) {
	$minutes = art_theme_get_reading_time_minutes( $post_id );

	return sprintf(
		/* translators: %d: estimated reading time in minutes. */
		_n( '%d мин чтения', '%d мин чтения', $minutes, 'art-theme' ),
		$minutes
	);
}

/**
 * Canvas layout modifier class for the current view.
 *
 * @return string
 */
function art_theme_get_canvas_modifier_class() {
	$classes = array();

	if ( art_theme_is_single_post_view() ) {
		$classes[] = 'art-theme-canvas--single';

		if ( Art_Theme_Single_Settings::is_full_width_template() ) {
			$classes[] = 'art-theme-canvas--single-full';
		}
	} elseif ( is_page() || ( is_singular() && art_theme_uses_page_template_layout() ) ) {
		$page_settings = Art_Theme_Page_Settings::get_for_singular();

		$classes[] = 'art-theme-canvas--page';

		if ( Art_Theme_Page_Settings::is_full_width_template( $page_settings ) ) {
			$classes[] = 'art-theme-canvas--page-full';
		}
	} elseif ( is_404() ) {
		$classes[] = 'art-theme-canvas--not-found';
	} elseif ( art_theme_is_blog_archive_view() ) {
		$classes[] = 'art-theme-canvas--archive';
	}

	if ( Art_Theme_Footer_Settings::has_visible_content() ) {
		$classes[] = 'art-theme-canvas--has-footer';
	}

	if ( empty( $classes ) ) {
		return '';
	}

	return ' ' . implode( ' ', $classes );
}

/**
 * Shell layout modifier class for the current view.
 *
 * @return string
 */
function art_theme_get_shell_modifier_class() {
	if ( art_theme_is_blog_archive_view() ) {
		return ' art-theme-shell--archive';
	}

	if ( art_theme_is_single_post_view() ) {
		$classes = ' art-theme-shell--single';

		if ( Art_Theme_Single_Settings::is_full_width_template() ) {
			$classes .= ' art-theme-shell--single-full';
		} else {
			$classes .= ' art-theme-shell--single-boxed';
		}

		return $classes;
	}

	if ( is_page() || ( is_singular() && art_theme_uses_page_template_layout() ) ) {
		$page_settings = Art_Theme_Page_Settings::get_for_singular();
		$classes       = ' art-theme-shell--page';

		if ( Art_Theme_Page_Settings::is_full_width_template( $page_settings ) ) {
			$classes .= ' art-theme-shell--page-full';
		} else {
			$classes .= ' art-theme-shell--page-boxed';
		}

		return $classes;
	}

	return '';
}

/**
 * Whether a single post layout item should render.
 *
 * @param string               $item     Layout item slug.
 * @param array<string, mixed> $settings Single post settings.
 * @return bool
 */
function art_theme_should_render_single_layout_item( $item, $settings ) {
	if ( 'image' === $item ) {
		return ! empty( $settings['show_thumbnail'] ) && art_theme_get_post_image_id( get_the_ID() );
	}

	if ( 'title' === $item ) {
		return true;
	}

	if ( 'meta' === $item ) {
		if ( ! empty( $settings['show_date'] ) ) {
			return true;
		}

		if ( ! empty( $settings['show_reading_time'] ) ) {
			return true;
		}

		return ! empty( $settings['show_category'] ) && has_category();
	}

	return false;
}

/**
 * Render date and category meta line for a single post.
 *
 * @param array<string, mixed> $settings Single post settings.
 */
function art_theme_render_single_meta_line( $settings ) {
	$parts = array();

	if ( ! empty( $settings['show_category'] ) && has_category() ) {
		$parts[] = '<span class="art-theme-post__categories">' . wp_kses_post( get_the_category_list( ', ' ) ) . '</span>';
	}

	if ( ! empty( $settings['show_date'] ) ) {
		$parts[] = '<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	}

	if ( ! empty( $settings['show_reading_time'] ) ) {
		$parts[] = '<span class="art-theme-post__reading-time">' . esc_html( art_theme_get_reading_time_label( get_the_ID() ) ) . '</span>';
	}

	if ( empty( $parts ) ) {
		return;
	}

	echo '<div class="art-theme-post__meta art-theme-post__meta--single">';

	foreach ( $parts as $index => $part ) {
		if ( $index > 0 ) {
			echo '<span class="art-theme-post__meta-sep" aria-hidden="true">&middot;</span>';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built with esc_* above.
		echo $part;
	}

	echo '</div>';
}

/**
 * Render ordered single post header (image, title, meta).
 *
 * @param array<string, mixed> $settings Single post settings.
 */
function art_theme_render_single_entry_header( $settings ) {
	$order = Art_Theme_Single_Settings::sanitize_meta_order( $settings['meta_order'] ?? array() );

	echo '<header class="art-theme-entry-header">';

	foreach ( $order as $item ) {
		if ( ! art_theme_should_render_single_layout_item( $item, $settings ) ) {
			continue;
		}

		if ( 'image' === $item ) {
			get_template_part( 'template-parts/post', 'thumbnail' );
			continue;
		}

		if ( 'title' === $item ) {
			the_title( '<h1 class="art-theme-entry-title">', '</h1>' );
			continue;
		}

		if ( 'meta' === $item ) {
			art_theme_render_single_meta_line( $settings );
		}
	}

	echo '</header>';
}
