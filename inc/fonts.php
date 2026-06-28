<?php
/**
 * Theme fonts — Manrope (body) and Rubik (headings), bundled locally.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute path to the local fonts stylesheet.
 *
 * @return string
 */
function art_theme_get_fonts_stylesheet_path() {
	return ART_THEME_DIR . '/assets/css/fonts.css';
}

/**
 * Public URL to the local fonts stylesheet.
 *
 * @return string
 */
function art_theme_get_fonts_stylesheet_uri() {
	return ART_THEME_URL . '/assets/css/fonts.css';
}

/**
 * Relative path for add_editor_style().
 *
 * @return string
 */
function art_theme_get_fonts_editor_stylesheet() {
	return 'assets/css/fonts.css';
}

/**
 * Cache-busting version for the fonts stylesheet.
 *
 * @return string
 */
function art_theme_get_fonts_stylesheet_version() {
	$path = art_theme_get_fonts_stylesheet_path();

	return file_exists( $path ) ? (string) filemtime( $path ) : ART_THEME_VERSION;
}

/**
 * Register front-end font stylesheet.
 */
function art_theme_register_fonts() {
	wp_register_style(
		'art-theme-fonts',
		art_theme_get_fonts_stylesheet_uri(),
		array(),
		art_theme_get_fonts_stylesheet_version()
	);
}
add_action( 'wp_enqueue_scripts', 'art_theme_register_fonts', 5 );

/**
 * Enqueue fonts in block editor preview.
 */
function art_theme_enqueue_editor_fonts() {
	wp_enqueue_style(
		'art-theme-fonts',
		art_theme_get_fonts_stylesheet_uri(),
		array(),
		art_theme_get_fonts_stylesheet_version()
	);
}
add_action( 'enqueue_block_editor_assets', 'art_theme_enqueue_editor_fonts' );
