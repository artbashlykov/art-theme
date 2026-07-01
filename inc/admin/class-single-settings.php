<?php
/**
 * Single post template settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Single post (запись) appearance settings.
 */
class Art_Theme_Single_Settings {

	const OPTION_KEY = 'art_theme_single_settings';

	/**
	 * Single post header layout item slugs.
	 */
	const LAYOUT_ITEMS = array( 'image', 'title', 'meta' );

	/**
	 * Single post template variant slugs.
	 */
	const TEMPLATE_VARIANTS = array( 'boxed', 'full-width' );

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
		return array_merge(
			Art_Theme_Content_Template::get_boxed_layout_defaults(),
			array(
				'post_width'         => Art_Theme_Content_Template::CONTENT_WIDTH_DEFAULT,
				'cover_aspect_ratio' => '16-9',
				'show_thumbnail'     => true,
				'show_date'          => true,
				'show_category'      => false,
				'show_reading_time'  => true,
				'meta_order'         => array( 'image', 'title', 'meta' ),
			)
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

		if ( ! empty( $stored['show_categories'] ) && empty( $stored['show_category'] ) ) {
			$settings['show_category'] = wp_validate_boolean( $stored['show_categories'] );
		}

		$settings['post_width']           = max( 600, min( 1400, (int) $settings['post_width'] ) );
		$settings['template_variant']     = self::sanitize_template_variant( $settings['template_variant'] ?? 'boxed' );
		if ( is_array( $stored ) && isset( $stored['boxed_padding'] ) && ! array_key_exists( 'boxed_padding_block', $stored ) && ! array_key_exists( 'boxed_padding_inline', $stored ) ) {
			$legacy_padding                   = self::map_legacy_boxed_padding( $stored['boxed_padding'] );
			$settings['boxed_padding_block']  = $legacy_padding['block'];
			$settings['boxed_padding_inline'] = $legacy_padding['inline'];
		}

		$settings['boxed_border_radius']  = self::sanitize_boxed_border_radius( $settings['boxed_border_radius'] ?? 10 );
		$settings['boxed_shadow']         = self::sanitize_boxed_shadow( $settings['boxed_shadow'] ?? 'medium' );
		$settings['boxed_padding_block']  = self::sanitize_boxed_padding_block( $settings['boxed_padding_block'] ?? 32 );
		$settings['boxed_padding_inline'] = self::sanitize_boxed_padding_inline( $settings['boxed_padding_inline'] ?? 24 );
		$settings['cover_aspect_ratio']   = self::sanitize_cover_aspect_ratio( $settings['cover_aspect_ratio'] ?? '16-9' );
		$settings['meta_order']         = self::sanitize_meta_order( $settings['meta_order'] ?? $defaults['meta_order'] );

		foreach ( array( 'show_thumbnail', 'show_date', 'show_category', 'show_reading_time' ) as $bool_key ) {
			if ( ! array_key_exists( $bool_key, $stored ) && 'show_category' === $bool_key && ! empty( $stored['show_categories'] ) ) {
				$settings[ $bool_key ] = true;
				continue;
			}

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
	 * Template variant labels for admin and Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function get_template_variant_choices() {
		return Art_Theme_Content_Template::get_template_variant_choices();
	}

	/**
	 * Sanitize single post template variant slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_template_variant( $value ) {
		return Art_Theme_Content_Template::sanitize_template_variant( $value );
	}

	/**
	 * Whether the single post uses the full-width background template.
	 *
	 * @param array<string, mixed>|null $settings Optional settings array.
	 * @return bool
	 */
	public static function is_full_width_template( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		return Art_Theme_Content_Template::is_full_width_template( $settings );
	}

	/**
	 * Boxed template shadow choices.
	 *
	 * @return array<string, string>
	 */
	public static function get_boxed_shadow_choices() {
		return array(
			'none'   => __( 'Без тени', 'art-theme' ),
			'light'  => __( 'Лёгкая', 'art-theme' ),
			'medium' => __( 'Средняя', 'art-theme' ),
		);
	}

	/**
	 * Map legacy boxed padding preset slug to pixel values.
	 *
	 * @param mixed $value Legacy preset slug.
	 * @return array{block: int, inline: int}
	 */
	private static function map_legacy_boxed_padding( $value ) {
		$value = sanitize_key( (string) $value );

		$map = array(
			'compact'  => array(
				'block'  => 20,
				'inline' => 16,
			),
			'standard' => array(
				'block'  => 32,
				'inline' => 24,
			),
			'spacious' => array(
				'block'  => 40,
				'inline' => 32,
			),
		);

		if ( array_key_exists( $value, $map ) ) {
			return $map[ $value ];
		}

		return $map['standard'];
	}

	/**
	 * Sanitize boxed border radius in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_border_radius( $value ) {
		return max( 0, min( 32, (int) $value ) );
	}

	/**
	 * Sanitize boxed shadow slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_boxed_shadow( $value ) {
		$value = sanitize_key( (string) $value );

		if ( array_key_exists( $value, self::get_boxed_shadow_choices() ) ) {
			return $value;
		}

		return 'medium';
	}

	/**
	 * Sanitize boxed vertical padding in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_padding_block( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * Sanitize boxed horizontal padding in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_padding_inline( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * CSS box-shadow for boxed template.
	 *
	 * @param string|null        $slug     Optional shadow slug.
	 * @param array<string,mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_boxed_shadow_css( $slug = null, $settings = null ) {
		if ( null === $slug ) {
			if ( null === $settings ) {
				$settings = self::get();
			}

			$slug = self::sanitize_boxed_shadow( $settings['boxed_shadow'] ?? 'medium' );
		} else {
			$slug = self::sanitize_boxed_shadow( $slug );
		}

		$map = array(
			'none'   => 'none',
			'light'  => '0 2px 12px rgba(0, 0, 0, 0.04)',
			'medium' => '0 2px 24px rgba(0, 0, 0, 0.06)',
		);

		return $map[ $slug ];
	}

	/**
	 * CSS padding values for boxed template shell.
	 *
	 * @param array<string,mixed>|null $settings Optional settings.
	 * @return array{block: string, inline: string}
	 */
	public static function get_boxed_padding_css( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$block  = self::sanitize_boxed_padding_block( $settings['boxed_padding_block'] ?? 32 );
		$inline = self::sanitize_boxed_padding_inline( $settings['boxed_padding_inline'] ?? 24 );

		return array(
			'block'  => sprintf( '%dpx', $block ),
			'inline' => sprintf( '%dpx', $inline ),
		);
	}

	/**
	 * Allowed cover aspect ratio choices (slug => label).
	 *
	 * @return array<string, string>
	 */
	public static function get_cover_aspect_ratio_choices() {
		return array(
			'16-9'     => '16:9',
			'4-3'      => '4:3',
			'2-1'      => '2:1',
			'original' => __( 'Как в оригинале', 'art-theme' ),
		);
	}

	/**
	 * Layout item labels for admin and Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function get_layout_item_labels() {
		return array(
			'image' => __( 'Изображение', 'art-theme' ),
			'title' => __( 'Заголовок', 'art-theme' ),
			'meta'  => __( 'Мета-данные', 'art-theme' ),
		);
	}

	/**
	 * Sanitize cover aspect ratio slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_cover_aspect_ratio( $value ) {
		$value = sanitize_key( (string) $value );

		if ( ! array_key_exists( $value, self::get_cover_aspect_ratio_choices() ) ) {
			return '16-9';
		}

		return $value;
	}

	/**
	 * Sanitize meta block order.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int, string>
	 */
	public static function sanitize_meta_order( $value ) {
		if ( is_string( $value ) ) {
			$value = array_map( 'trim', explode( ',', $value ) );
		}

		if ( ! is_array( $value ) ) {
			return self::get_defaults()['meta_order'];
		}

		$raw_order = array();

		foreach ( $value as $item ) {
			$item = sanitize_key( (string) $item );

			if ( '' !== $item ) {
				$raw_order[] = $item;
			}
		}

		if ( self::is_legacy_meta_order( $raw_order ) ) {
			$raw_order = self::migrate_legacy_meta_order( $raw_order );
		}

		$order = array();

		foreach ( $raw_order as $item ) {
			if ( in_array( $item, self::LAYOUT_ITEMS, true ) && ! in_array( $item, $order, true ) ) {
				$order[] = $item;
			}
		}

		foreach ( self::LAYOUT_ITEMS as $item ) {
			if ( ! in_array( $item, $order, true ) ) {
				$order[] = $item;
			}
		}

		return $order;
	}

	/**
	 * Whether stored order uses legacy date/category slugs.
	 *
	 * @param array<int, string> $order Raw order slugs.
	 * @return bool
	 */
	private static function is_legacy_meta_order( $order ) {
		foreach ( $order as $item ) {
			if ( in_array( $item, array( 'date', 'category' ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert legacy image/date/category order to image/title/meta.
	 *
	 * @param array<int, string> $order Legacy order slugs.
	 * @return array<int, string>
	 */
	private static function migrate_legacy_meta_order( $order ) {
		$migrated = array();

		foreach ( $order as $item ) {
			if ( 'date' === $item || 'category' === $item ) {
				if ( ! in_array( 'meta', $migrated, true ) ) {
					$migrated[] = 'meta';
				}
				continue;
			}

			if ( in_array( $item, self::LAYOUT_ITEMS, true ) && ! in_array( $item, $migrated, true ) ) {
				$migrated[] = $item;
			}
		}

		if ( ! in_array( 'title', $migrated, true ) ) {
			$image_index = array_search( 'image', $migrated, true );

			if ( false !== $image_index ) {
				array_splice( $migrated, $image_index + 1, 0, array( 'title' ) );
			} else {
				array_unshift( $migrated, 'title' );
			}
		}

		return $migrated;
	}

	/**
	 * CSS aspect-ratio value for single post covers.
	 *
	 * @param string|null $slug Optional ratio slug.
	 * @return string
	 */
	public static function get_cover_aspect_ratio_css( $slug = null ) {
		if ( null === $slug ) {
			$slug = self::get()['cover_aspect_ratio'];
		}

		$slug = self::sanitize_cover_aspect_ratio( $slug );

		if ( 'original' === $slug ) {
			return 'auto';
		}

		$map = array(
			'16-9' => '16 / 9',
			'4-3'  => '4 / 3',
			'2-1'  => '2 / 1',
		);

		return $map[ $slug ];
	}

	/**
	 * Sanitize saved settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		unset( $input['_form'] );

		$existing = wp_parse_args( get_option( self::OPTION_KEY, array() ), self::get_defaults() );
		$merged   = wp_parse_args( $input, $existing );

		$checkboxes = array( 'show_thumbnail', 'show_date', 'show_category', 'show_reading_time' );

		foreach ( $checkboxes as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$merged[ $key ] = ! empty( $input[ $key ] );
			}
		}

		return array(
			'template_variant'    => self::sanitize_template_variant( $merged['template_variant'] ?? 'boxed' ),
			'post_width'          => max( 600, min( 1400, (int) ( $merged['post_width'] ?? Art_Theme_Content_Template::CONTENT_WIDTH_DEFAULT ) ) ),
			'boxed_border_radius' => self::sanitize_boxed_border_radius( $merged['boxed_border_radius'] ?? 10 ),
			'boxed_shadow'         => self::sanitize_boxed_shadow( $merged['boxed_shadow'] ?? 'medium' ),
			'boxed_padding_block'  => self::sanitize_boxed_padding_block( $merged['boxed_padding_block'] ?? 32 ),
			'boxed_padding_inline' => self::sanitize_boxed_padding_inline( $merged['boxed_padding_inline'] ?? 24 ),
			'cover_aspect_ratio'  => self::sanitize_cover_aspect_ratio( $merged['cover_aspect_ratio'] ?? '16-9' ),
			'show_thumbnail'     => ! empty( $merged['show_thumbnail'] ),
			'show_date'          => ! empty( $merged['show_date'] ),
			'show_category'      => ! empty( $merged['show_category'] ?? ( $merged['show_categories'] ?? false ) ),
			'show_reading_time'  => ! empty( $merged['show_reading_time'] ),
			'meta_order'         => self::sanitize_meta_order( $merged['meta_order'] ?? self::get_defaults()['meta_order'] ),
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
