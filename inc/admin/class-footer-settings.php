<?php
/**
 * Site footer settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Footer layout and content settings.
 */
class Art_Theme_Footer_Settings {

	const OPTION_KEY = 'art_theme_footer_settings';

	const TEMPLATE_CLASSIC  = 'classic';
	const TEMPLATE_FLOATING = 'floating';
	const TEMPLATE_MINIMAL  = 'minimal';

	const STRUCTURE_COLUMNS = 'columns';
	const STRUCTURE_STACK     = 'stack';

	const WIDTH_MODE_FIXED = 'fixed';
	const WIDTH_MODE_FULL  = 'full';

	const FOOTER_FIXED_EXTRA_INLINE_DEFAULT = 0;
	const FOOTER_TOP_SPACING_DEFAULT        = 32;
	const FOOTER_BOTTOM_SPACING_DEFAULT     = 32;

	const MAX_SOCIAL_ITEMS = 10;
	const MAX_LINK_ITEMS   = 10;

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'customize_save_after', array( __CLASS__, 'normalize_after_customizer_save' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'footer_template'           => self::TEMPLATE_CLASSIC,
			'footer_structure'          => self::STRUCTURE_COLUMNS,
			'footer_top_spacing'        => self::FOOTER_TOP_SPACING_DEFAULT,
			'footer_bottom_spacing'     => self::FOOTER_BOTTOM_SPACING_DEFAULT,
			'footer_width_mode'         => self::WIDTH_MODE_FIXED,
			'footer_fixed_extra_inline' => self::FOOTER_FIXED_EXTRA_INLINE_DEFAULT,
			'footer_border_radius'      => 10,
			'show_title'                => true,
			'show_tagline'              => true,
			'show_socials'              => true,
			'show_links'                => true,
			'show_copyright'            => true,
			'socials'                   => array(),
			'custom_links'              => array(),
			'copyright_text'            => '',
		);
	}

	/**
	 * Get merged settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		static $cached = null;

		if ( null !== $cached ) {
			return $cached;
		}

		$stored   = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = wp_parse_args( $stored, $defaults );

		$settings['footer_template']           = self::sanitize_footer_template( $settings['footer_template'] ?? $defaults['footer_template'] );
		$settings['footer_structure']          = self::sanitize_footer_structure( $settings['footer_structure'] ?? $defaults['footer_structure'] );
		$settings['footer_top_spacing']        = self::sanitize_footer_top_spacing( $settings['footer_top_spacing'] ?? $defaults['footer_top_spacing'] );
		$settings['footer_bottom_spacing']     = self::sanitize_footer_bottom_spacing( $settings['footer_bottom_spacing'] ?? $defaults['footer_bottom_spacing'] );
		$settings['footer_width_mode']         = self::sanitize_footer_width_mode( $settings['footer_width_mode'] ?? $defaults['footer_width_mode'] );
		$settings['footer_fixed_extra_inline'] = self::sanitize_footer_fixed_extra_inline( $settings['footer_fixed_extra_inline'] ?? $defaults['footer_fixed_extra_inline'] );
		$settings['footer_border_radius']      = self::sanitize_footer_border_radius( $settings['footer_border_radius'] ?? $defaults['footer_border_radius'] );
		$settings['socials']                   = self::sanitize_socials( $settings['socials'] ?? array() );
		$settings['custom_links']              = self::sanitize_custom_links( $settings['custom_links'] ?? array() );
		$settings['copyright_text']            = sanitize_text_field( (string) ( $settings['copyright_text'] ?? '' ) );

		foreach ( array( 'show_title', 'show_tagline', 'show_socials', 'show_links', 'show_copyright' ) as $bool_key ) {
			if ( ! array_key_exists( $bool_key, $stored ) ) {
				$settings[ $bool_key ] = $defaults[ $bool_key ];
			} else {
				$settings[ $bool_key ] = wp_validate_boolean( $stored[ $bool_key ] );
			}
		}

		$cached = $settings;

		return $cached;
	}

	/**
	 * Resolved copyright label (defaults to site name).
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_copyright_text( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$text = trim( (string) ( $settings['copyright_text'] ?? '' ) );

		if ( '' === $text ) {
			return (string) get_bloginfo( 'name', 'display' );
		}

		return $text;
	}

	/**
	 * Full copyright line with symbol and year.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_copyright_line( $settings = null ) {
		return sprintf(
			'© %1$s %2$s',
			gmdate( 'Y' ),
			self::get_copyright_text( $settings )
		);
	}

	/**
	 * Whether the footer has visible content.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return bool
	 */
	public static function has_visible_content( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		if ( ! empty( $settings['show_title'] ) && '' !== get_bloginfo( 'name', 'display' ) ) {
			return true;
		}

		if ( ! empty( $settings['show_tagline'] ) && '' !== get_bloginfo( 'description', 'display' ) ) {
			return true;
		}

		if ( ! empty( $settings['show_socials'] ) && ! empty( $settings['socials'] ) ) {
			return true;
		}

		if ( ! empty( $settings['show_links'] ) && ! empty( $settings['custom_links'] ) ) {
			return true;
		}

		if ( ! empty( $settings['show_copyright'] ) && '' !== self::get_copyright_text( $settings ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_template_choices() {
		return array(
			self::TEMPLATE_CLASSIC  => __( 'Классический минимализм', 'art-theme' ),
			self::TEMPLATE_FLOATING => __( 'Полупрозрачный плавающий подвал', 'art-theme' ),
			self::TEMPLATE_MINIMAL  => __( 'Почти невидимый', 'art-theme' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_structure_choices() {
		return array(
			self::STRUCTURE_COLUMNS => __( 'Две колонки', 'art-theme' ),
			self::STRUCTURE_STACK   => __( 'Одна колонка (по центру)', 'art-theme' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_width_mode_choices() {
		return array(
			self::WIDTH_MODE_FIXED => __( 'Фиксированная', 'art-theme' ),
			self::WIDTH_MODE_FULL  => __( 'На всю ширину', 'art-theme' ),
		);
	}

	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_footer_template( $value ) {
		$value = sanitize_key( (string) $value );

		return array_key_exists( $value, self::get_template_choices() ) ? $value : self::TEMPLATE_CLASSIC;
	}

	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_footer_structure( $value ) {
		$value = sanitize_key( (string) $value );

		return array_key_exists( $value, self::get_structure_choices() ) ? $value : self::STRUCTURE_COLUMNS;
	}

	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_footer_width_mode( $value ) {
		$value = sanitize_key( (string) $value );

		return array_key_exists( $value, self::get_width_mode_choices() ) ? $value : self::WIDTH_MODE_FIXED;
	}

	/**
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_footer_top_spacing( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_footer_bottom_spacing( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_footer_fixed_extra_inline( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_footer_border_radius( $value ) {
		return max( 0, min( 999, (int) $value ) );
	}

	/**
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return int
	 */
	public static function get_fixed_extra_inline_px( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		return self::sanitize_footer_fixed_extra_inline( $settings['footer_fixed_extra_inline'] ?? self::FOOTER_FIXED_EXTRA_INLINE_DEFAULT );
	}

	/**
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_width_mode_class( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		return 'art-theme-site-footer--width-' . self::sanitize_footer_width_mode( $settings['footer_width_mode'] ?? self::WIDTH_MODE_FIXED );
	}

	/**
	 * @param mixed $input Raw socials list.
	 * @return array<int, array{network: string, url: string}>
	 */
	public static function sanitize_socials( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$socials = array();

		foreach ( $input as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$network = Art_Theme_Social_Icons::sanitize_network( $item['network'] ?? '' );
			$url     = Art_Theme_Social_Icons::sanitize_item_url( $network, $item['url'] ?? '' );

			if ( '' === $network || '' === $url ) {
				continue;
			}

			$socials[] = array(
				'network' => $network,
				'url'     => $url,
			);

			if ( count( $socials ) >= self::MAX_SOCIAL_ITEMS ) {
				break;
			}
		}

		return $socials;
	}

	/**
	 * @param mixed $input Raw custom links list.
	 * @return array<int, array{label: string, url: string, open_new_tab: bool}>
	 */
	public static function sanitize_custom_links( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$links = array();

		foreach ( $input as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$label = sanitize_text_field( (string) ( $item['label'] ?? '' ) );
			$url   = esc_url_raw( (string) ( $item['url'] ?? '' ) );

			if ( '' === $label || '' === $url ) {
				continue;
			}

			$links[] = array(
				'label'        => $label,
				'url'          => $url,
				'open_new_tab' => ! empty( $item['open_new_tab'] ),
			);

			if ( count( $links ) >= self::MAX_LINK_ITEMS ) {
				break;
			}
		}

		return $links;
	}

	/**
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		$defaults = self::get_defaults();
		$existing = wp_parse_args( get_option( self::OPTION_KEY, array() ), $defaults );
		$merged   = wp_parse_args( $input, $existing );

		$checkboxes = array( 'show_title', 'show_tagline', 'show_socials', 'show_links', 'show_copyright' );

		foreach ( $checkboxes as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$merged[ $key ] = ! empty( $input[ $key ] );
			}
		}

		return array(
			'footer_template'           => self::sanitize_footer_template( $merged['footer_template'] ?? $defaults['footer_template'] ),
			'footer_structure'          => self::sanitize_footer_structure( $merged['footer_structure'] ?? $defaults['footer_structure'] ),
			'footer_top_spacing'        => self::sanitize_footer_top_spacing( $merged['footer_top_spacing'] ?? $defaults['footer_top_spacing'] ),
			'footer_bottom_spacing'     => self::sanitize_footer_bottom_spacing( $merged['footer_bottom_spacing'] ?? $defaults['footer_bottom_spacing'] ),
			'footer_width_mode'         => self::sanitize_footer_width_mode( $merged['footer_width_mode'] ?? $defaults['footer_width_mode'] ),
			'footer_fixed_extra_inline' => self::sanitize_footer_fixed_extra_inline( $merged['footer_fixed_extra_inline'] ?? $defaults['footer_fixed_extra_inline'] ),
			'footer_border_radius'      => self::sanitize_footer_border_radius( $merged['footer_border_radius'] ?? $defaults['footer_border_radius'] ),
			'show_title'                => ! empty( $merged['show_title'] ),
			'show_tagline'              => ! empty( $merged['show_tagline'] ),
			'show_socials'              => ! empty( $merged['show_socials'] ),
			'show_links'                => ! empty( $merged['show_links'] ),
			'show_copyright'            => ! empty( $merged['show_copyright'] ),
			'socials'                   => self::sanitize_socials( $merged['socials'] ?? array() ),
			'custom_links'              => self::sanitize_custom_links( $merged['custom_links'] ?? array() ),
			'copyright_text'            => sanitize_text_field( (string) ( $merged['copyright_text'] ?? '' ) ),
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
