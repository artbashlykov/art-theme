<?php
/**
 * Block editor layout — content width matches singular front-end settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve editor / content width in pixels for a post.
 *
 * @param WP_Post|int|null $post Post object or ID.
 * @return int
 */
function art_theme_get_editor_content_width_for_post( $post = null ) {
	if ( null === $post ) {
		$post = get_post();
	} elseif ( is_numeric( $post ) ) {
		$post = get_post( (int) $post );
	}

	if ( ! $post instanceof WP_Post ) {
		return (int) Art_Theme_Single_Settings::get()['post_width'];
	}

	if ( 'post' === $post->post_type ) {
		return (int) Art_Theme_Single_Settings::get()['post_width'];
	}

	if ( 'page' === $post->post_type || art_theme_uses_page_template_layout( $post->post_type ) ) {
		return (int) Art_Theme_Page_Settings::get_for_singular( $post )['page_width'];
	}

	return (int) Art_Theme_Single_Settings::get()['post_width'];
}

/**
 * Align Gutenberg layout sizes with the theme content column.
 *
 * @param array                   $editor_settings      Editor settings.
 * @param WP_Block_Editor_Context $block_editor_context Editor context.
 * @return array
 */
function art_theme_filter_block_editor_settings( $editor_settings, $block_editor_context ) {
	$post = $block_editor_context->post ?? null;

	if ( ! $post instanceof WP_Post ) {
		return $editor_settings;
	}

	$width = art_theme_get_editor_content_width_for_post( $post ) . 'px';

	if ( ! isset( $editor_settings['__experimentalFeatures'] ) || ! is_array( $editor_settings['__experimentalFeatures'] ) ) {
		$editor_settings['__experimentalFeatures'] = array();
	}

	if ( ! isset( $editor_settings['__experimentalFeatures']['layout'] ) || ! is_array( $editor_settings['__experimentalFeatures']['layout'] ) ) {
		$editor_settings['__experimentalFeatures']['layout'] = array();
	}

	$editor_settings['__experimentalFeatures']['layout']['contentSize'] = $width;
	$editor_settings['__experimentalFeatures']['layout']['wideSize']    = $width;

	return $editor_settings;
}
add_filter( 'block_editor_settings_all', 'art_theme_filter_block_editor_settings', 10, 2 );

/**
 * CSS variable fallback for editor stylesheet rules.
 */
function art_theme_enqueue_block_editor_content_width() {
	global $post;

	$width = $post instanceof WP_Post ? art_theme_get_editor_content_width_for_post( $post ) : (int) Art_Theme_Single_Settings::get()['post_width'];

	if ( ! wp_style_is( 'art-theme-fonts', 'enqueued' ) ) {
		return;
	}

	wp_add_inline_style(
		'art-theme-fonts',
		sprintf(
			':root { --art-theme-editor-content-width: %1$dpx; }',
			$width
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'art_theme_enqueue_block_editor_content_width', 20 );
