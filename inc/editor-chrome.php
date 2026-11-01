<?php
/**
 * ART Editor site chrome (header/footer) consumer helpers.
 *
 * Priority owned by the theme: blank → Editor HTML → Customizer chrome.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current view must hide site chrome entirely.
 *
 * @return bool
 */
function art_theme_should_hide_site_chrome() {
	return function_exists( 'art_theme_is_blank_template_view' ) && art_theme_is_blank_template_view();
}

/**
 * Active ART Editor site header payload, or null.
 *
 * @return array{enabled:bool,html:string,updated?:string}|null
 */
function art_theme_get_editor_site_header() {
	if ( ! function_exists( 'art_editor_get_site_header' ) ) {
		return null;
	}

	$payload = art_editor_get_site_header();

	if ( ! is_array( $payload ) || empty( $payload['enabled'] ) ) {
		return null;
	}

	$html = isset( $payload['html'] ) ? trim( (string) $payload['html'] ) : '';

	if ( '' === $html ) {
		return null;
	}

	$payload['html'] = $html;

	return $payload;
}

/**
 * Active ART Editor site footer payload, or null.
 *
 * @return array{enabled:bool,html:string,updated?:string}|null
 */
function art_theme_get_editor_site_footer() {
	if ( ! function_exists( 'art_editor_get_site_footer' ) ) {
		return null;
	}

	$payload = art_editor_get_site_footer();

	if ( ! is_array( $payload ) || empty( $payload['enabled'] ) ) {
		return null;
	}

	$html = isset( $payload['html'] ) ? trim( (string) $payload['html'] ) : '';

	if ( '' === $html ) {
		return null;
	}

	$payload['html'] = $html;

	return $payload;
}

/**
 * Whether Editor supplies the active site header.
 *
 * @return bool
 */
function art_theme_has_editor_site_header() {
	return null !== art_theme_get_editor_site_header();
}

/**
 * Whether Editor supplies the active site footer.
 *
 * @return bool
 */
function art_theme_has_editor_site_footer() {
	return null !== art_theme_get_editor_site_footer();
}

/**
 * Whether any site footer chrome will render (Editor or Customizer).
 *
 * @return bool
 */
function art_theme_has_site_footer_chrome() {
	if ( art_theme_should_hide_site_chrome() ) {
		return false;
	}

	if ( art_theme_has_editor_site_footer() ) {
		return true;
	}

	return Art_Theme_Footer_Settings::has_visible_content();
}

/**
 * Echo trusted ART Editor chrome HTML (not through the_content).
 *
 * @param string $html Raw HTML from ART Editor.
 */
function art_theme_print_editor_chrome_html( $html ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted admin HTML from ART Editor (same trust model as core/html blocks).
	echo $html;
}
