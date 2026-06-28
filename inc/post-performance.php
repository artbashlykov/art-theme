<?php
/**
 * Post meta caches for archive performance (reading time, list image).
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta key: cached reading time in minutes.
 */
const ART_THEME_READING_TIME_META_KEY = '_art_theme_reading_minutes';

/**
 * Meta key: cached attachment ID for archive cards (0 = none).
 */
const ART_THEME_LIST_IMAGE_META_KEY = '_art_theme_list_image_id';

/**
 * Register save hooks.
 */
function art_theme_post_performance_init() {
	add_action( 'save_post_post', 'art_theme_sync_post_performance_meta', 20, 2 );
}
add_action( 'init', 'art_theme_post_performance_init' );

/**
 * Recompute performance meta when a post is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function art_theme_sync_post_performance_meta( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return;
	}

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, ART_THEME_READING_TIME_META_KEY, art_theme_calculate_reading_time_minutes( $post_id ) );
	update_post_meta( $post_id, ART_THEME_LIST_IMAGE_META_KEY, art_theme_resolve_list_image_id( $post_id ) );
}

/**
 * Calculate reading time without reading post meta.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function art_theme_calculate_reading_time_minutes( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return 1;
	}

	$content = get_post_field( 'post_content', $post_id );
	$text    = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $content ) ) );

	if ( '' === $text ) {
		return 1;
	}

	$words   = count( preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) );
	$minutes = (int) ceil( max( 1, $words ) / 200 );

	return max( 1, $minutes );
}

/**
 * Resolve list/card image ID using the full fallback chain (save_post only).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function art_theme_resolve_list_image_id( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 ) {
		return 0;
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id ) {
		return $thumbnail_id;
	}

	$content = get_post_field( 'post_content', $post_id );

	if ( is_string( $content ) && preg_match( '/wp-image-(\d+)/', $content, $matches ) ) {
		return (int) $matches[1];
	}

	$attachments = get_children(
		array(
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => 1,
			'orderby'        => 'menu_order ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $attachments ) ) {
		return (int) reset( $attachments );
	}

	return 0;
}

/**
 * Image ID for archive cards: featured image, cached meta, or computed fallback (read-only).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function art_theme_get_archive_post_image_id( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( $post_id <= 0 ) {
		return 0;
	}

	$thumbnail_id = (int) get_post_thumbnail_id( $post_id );

	if ( $thumbnail_id ) {
		return $thumbnail_id;
	}

	$cached = get_post_meta( $post_id, ART_THEME_LIST_IMAGE_META_KEY, true );

	if ( '' !== $cached && false !== $cached ) {
		return max( 0, (int) $cached );
	}

	return art_theme_resolve_list_image_id( $post_id );
}

/**
 * Reading time for archive cards: cached meta or computed fallback (read-only).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function art_theme_get_cached_reading_time_minutes( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( $post_id <= 0 ) {
		return 1;
	}

	$cached = get_post_meta( $post_id, ART_THEME_READING_TIME_META_KEY, true );

	if ( '' !== $cached && false !== $cached ) {
		return max( 1, (int) $cached );
	}

	return art_theme_calculate_reading_time_minutes( $post_id );
}
