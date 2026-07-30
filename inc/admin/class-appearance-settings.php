<?php
/**
 * Colors and fonts (appearance) settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Brand colors and font pack for Customizer.
 */
class Art_Theme_Appearance_Settings {

	const OPTION_KEY = 'art_theme_appearance_settings';

	const FONT_PACK_MANROPE_RUBIK = 'manrope_rubik';
	const FONT_PACK_MANROPE       = 'manrope';
	const FONT_PACK_RUBIK         = 'rubik';
	const FONT_PACK_SYSTEM        = 'system';
	const FONT_PACK_SYSTEM_SERIF  = 'system_serif';
	const FONT_PACK_SERIF         = 'serif';

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
			'color_canvas'  => '#eeeeee',
			'color_surface' => '#ffffff',
			'color_text'    => '#1a1a1a',
			'color_accent'  => '#2563eb',
			'font_pack'     => self::FONT_PACK_MANROPE_RUBIK,
		);
	}

	/**
	 * Font pack choices for the Customizer select.
	 *
	 * @return array<string, string>
	 */
	public static function get_font_pack_choices() {
		return array(
			self::FONT_PACK_MANROPE_RUBIK => __( 'Manrope + Rubik (по умолчанию)', 'art-theme' ),
			self::FONT_PACK_MANROPE       => __( 'Только Manrope', 'art-theme' ),
			self::FONT_PACK_RUBIK         => __( 'Только Rubik', 'art-theme' ),
			self::FONT_PACK_SYSTEM        => __( 'Системные', 'art-theme' ),
			self::FONT_PACK_SYSTEM_SERIF  => __( 'Системные + заголовки serif', 'art-theme' ),
			self::FONT_PACK_SERIF         => __( 'Классика (serif)', 'art-theme' ),
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

		$settings = self::sanitize( wp_parse_args( $stored, $defaults ) );

		if ( ! is_customize_preview() ) {
			$cached = $settings;
		}

		return $settings;
	}

	/**
	 * Whether local webfonts should be loaded.
	 *
	 * @param array<string, string>|null $settings Settings.
	 * @return bool
	 */
	public static function should_load_local_fonts( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$pack = $settings['font_pack'] ?? self::FONT_PACK_MANROPE_RUBIK;

		return in_array(
			$pack,
			array(
				self::FONT_PACK_MANROPE_RUBIK,
				self::FONT_PACK_MANROPE,
				self::FONT_PACK_RUBIK,
			),
			true
		);
	}

	/**
	 * CSS font stacks for the active pack.
	 *
	 * @param array<string, string>|null $settings Settings.
	 * @return array{body: string, heading: string}
	 */
	public static function get_font_stacks( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$system = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
		$serif  = 'Georgia, "Times New Roman", Times, serif';

		switch ( $settings['font_pack'] ?? self::FONT_PACK_MANROPE_RUBIK ) {
			case self::FONT_PACK_MANROPE:
				return array(
					'body'    => '"Manrope", ' . $system,
					'heading' => '"Manrope", ' . $system,
				);

			case self::FONT_PACK_RUBIK:
				return array(
					'body'    => '"Rubik", ' . $system,
					'heading' => '"Rubik", ' . $system,
				);

			case self::FONT_PACK_SYSTEM:
				return array(
					'body'    => $system,
					'heading' => $system,
				);

			case self::FONT_PACK_SYSTEM_SERIF:
				return array(
					'body'    => $system,
					'heading' => $serif,
				);

			case self::FONT_PACK_SERIF:
				return array(
					'body'    => $serif,
					'heading' => $serif,
				);

			case self::FONT_PACK_MANROPE_RUBIK:
			default:
				return array(
					'body'    => '"Manrope", ' . $system,
					'heading' => '"Rubik", "Manrope", ' . $system,
				);
		}
	}

	/**
	 * CSS custom properties for :root.
	 *
	 * @param array<string, string>|null $settings Settings.
	 * @return array<string, string> Property => value (without trailing semicolon).
	 */
	public static function get_css_variables( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$fonts  = self::get_font_stacks( $settings );
		$accent = $settings['color_accent'];
		$text   = $settings['color_text'];

		return array(
			'--art-theme-canvas'          => $settings['color_canvas'],
			'--art-theme-surface'         => $settings['color_surface'],
			'--art-theme-fg'              => $text,
			'--art-theme-muted'           => 'color-mix(in srgb, ' . $text . ' 55%, #ffffff)',
			'--art-theme-accent'          => $accent,
			'--art-theme-category-fg'     => $accent,
			'--art-theme-category-bg'     => 'color-mix(in srgb, ' . $accent . ' 12%, #ffffff)',
			'--art-theme-category-border' => 'color-mix(in srgb, ' . $accent . ' 35%, #ffffff)',
			'--art-theme-font'            => $fonts['body'],
			'--art-theme-font-heading'    => $fonts['heading'],
		);
	}

	/**
	 * Inline :root { … } CSS block.
	 *
	 * @param array<string, string>|null $settings Settings.
	 * @return string
	 */
	public static function get_inline_css( $settings = null ) {
		$parts = array();

		foreach ( self::get_css_variables( $settings ) as $property => $value ) {
			$parts[] = $property . ': ' . $value;
		}

		return ':root { ' . implode( '; ', $parts ) . '; }';
	}

	/**
	 * @param mixed $color Raw color.
	 * @return string Hex color.
	 */
	public static function sanitize_color( $color ) {
		$sanitized = sanitize_hex_color( (string) $color );

		return is_string( $sanitized ) && '' !== $sanitized ? $sanitized : '#000000';
	}

	/**
	 * @param mixed $pack Raw pack.
	 * @return string
	 */
	public static function sanitize_font_pack( $pack ) {
		$pack     = sanitize_key( (string) $pack );
		$choices  = self::get_font_pack_choices();

		return isset( $choices[ $pack ] ) ? $pack : self::FONT_PACK_MANROPE_RUBIK;
	}

	/**
	 * @param mixed $input Raw input.
	 * @return array<string, string>
	 */
	public static function sanitize( $input ) {
		$defaults = self::get_defaults();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$merged = wp_parse_args( $input, $defaults );

		return array(
			'color_canvas'  => self::sanitize_color( $merged['color_canvas'] ?? $defaults['color_canvas'] ),
			'color_surface' => self::sanitize_color( $merged['color_surface'] ?? $defaults['color_surface'] ),
			'color_text'    => self::sanitize_color( $merged['color_text'] ?? $defaults['color_text'] ),
			'color_accent'  => self::sanitize_color( $merged['color_accent'] ?? $defaults['color_accent'] ),
			'font_pack'     => self::sanitize_font_pack( $merged['font_pack'] ?? $defaults['font_pack'] ),
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
