<?php
/**
 * 404 page settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Not found page content settings.
 */
class Art_Theme_Not_Found_Settings {

	const OPTION_KEY = 'art_theme_not_found_settings';

	const PREVIEW_SLUG = 'art-theme-404-preview';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'customize_save_after', array( __CLASS__, 'normalize_after_customizer_save' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, string>
	 */
	public static function get_defaults() {
		return array(
			'error_code'    => '404',
			'error_message' => __( 'Страница не найдена', 'art-theme' ),
			'button_label'  => __( 'На главную', 'art-theme' ),
			'button_url'    => '',
		);
	}

	/**
	 * Get merged settings.
	 *
	 * @return array<string, string>
	 */
	public static function get() {
		static $cached = null;

		if ( null !== $cached && ! is_customize_preview() ) {
			return $cached;
		}

		$stored   = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stored = art_theme_overlay_customizer_option_values( self::OPTION_KEY, $stored, array_keys( $defaults ) );

		$settings = wp_parse_args( $stored, $defaults );

		$settings['error_code']    = sanitize_text_field( (string) ( $settings['error_code'] ?? $defaults['error_code'] ) );
		$settings['error_message'] = sanitize_textarea_field( (string) ( $settings['error_message'] ?? $defaults['error_message'] ) );
		$settings['button_label']  = sanitize_text_field( (string) ( $settings['button_label'] ?? $defaults['button_label'] ) );
		$settings['button_url']    = esc_url_raw( (string) ( $settings['button_url'] ?? '' ) );

		if ( '' === trim( $settings['error_code'] ) ) {
			$settings['error_code'] = $defaults['error_code'];
		}

		if ( '' === trim( $settings['error_message'] ) ) {
			$settings['error_message'] = $defaults['error_message'];
		}

		if ( '' === trim( $settings['button_label'] ) ) {
			$settings['button_label'] = $defaults['button_label'];
		}

		if ( '' === trim( $settings['button_url'] ) ) {
			$settings['button_url'] = home_url( '/' );
		}

		if ( ! is_customize_preview() ) {
			$cached = $settings;
		}

		return $settings;
	}

	/**
	 * Front-end URL that triggers the 404 template in the Customizer preview.
	 *
	 * @return string
	 */
	public static function get_preview_url() {
		return home_url( user_trailingslashit( self::PREVIEW_SLUG ) );
	}

	/**
	 * Default button URL for reset controls.
	 *
	 * @return string
	 */
	public static function get_default_button_url() {
		return home_url( '/' );
	}

	/**
	 * @param mixed $input Raw input.
	 * @return array<string, string>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		$defaults = self::get_defaults();
		$merged   = wp_parse_args( $input, $defaults );

		return array(
			'error_code'    => sanitize_text_field( (string) ( $merged['error_code'] ?? $defaults['error_code'] ) ),
			'error_message' => sanitize_textarea_field( (string) ( $merged['error_message'] ?? $defaults['error_message'] ) ),
			'button_label'  => sanitize_text_field( (string) ( $merged['button_label'] ?? $defaults['button_label'] ) ),
			'button_url'    => esc_url_raw( (string) ( $merged['button_url'] ?? '' ) ),
		);
	}

	/**
	 * Merge and sanitize option after Customizer save.
	 */
	public static function normalize_after_customizer_save() {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		update_option( self::OPTION_KEY, self::sanitize( wp_parse_args( $stored, self::get_defaults() ) ) );
	}
}
