<?php
/**
 * Theme supports and menus.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme features. Layout is fixed in PHP/CSS — not Site Editor.
 */
function art_theme_setup() {
	load_theme_textdomain( 'art-theme', ART_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style(
		array_merge(
			array( art_theme_get_fonts_editor_stylesheet() ),
			Art_Theme_Styles::get_editor_stylesheets(),
			array( 'assets/css/editor.css' )
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Меню шапки', 'art-theme' ),
		)
	);
}
