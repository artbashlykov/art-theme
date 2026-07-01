<?php
/**
 * Blog settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Blog listing settings.
 */
class Art_Theme_Blog_Settings {

	const OPTION_KEY = 'art_theme_blog_settings';

	const BLOG_BOTTOM_SPACING_DEFAULT = Art_Theme_Spacing::SECTION_SPACING_DEFAULT;

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_posts_per_page' ) );
		add_filter( 'excerpt_length', array( __CLASS__, 'filter_excerpt_length' ), 20 );
		add_filter( 'excerpt_more', array( __CLASS__, 'filter_excerpt_more' ), 20 );
		add_filter( 'the_excerpt', array( __CLASS__, 'filter_the_excerpt' ), 20 );
		add_action( 'customize_save_after', array( __CLASS__, 'normalize_after_customizer_save' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'blog_width'            => 850,
			'blog_columns'          => 2,
			'posts_per_page'        => 10,
			'blog_bottom_spacing'   => self::BLOG_BOTTOM_SPACING_DEFAULT,
			'hide_blog_header'      => false,
			'hide_category_filter'  => false,
			'blog_title'            => __( 'Блог', 'art-theme' ),
			'blog_description'      => '',
			'all_categories_label'    => __( 'Все категории', 'art-theme' ),
			'cover_aspect_ratio'      => '2-1',
			'show_thumbnail'        => true,
			'show_category'         => true,
			'show_date'             => true,
			'show_reading_time'     => true,
			'show_excerpt'          => true,
			'excerpt_chars'         => 80,
			'show_read_button'      => true,
			'read_button_text'      => __( 'Читать статью →', 'art-theme' ),
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

		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = wp_parse_args( $stored, self::get_defaults() );

		if ( '' === $settings['blog_title'] && ! empty( $stored['archive_title'] ) ) {
			$settings['blog_title'] = (string) $stored['archive_title'];
		}

		if ( '' === trim( (string) $settings['blog_title'] ) ) {
			$settings['blog_title'] = self::get_defaults()['blog_title'];
		}

		if ( '' === $settings['blog_description'] && ! empty( $stored['archive_description'] ) ) {
			$settings['blog_description'] = (string) $stored['archive_description'];
		}

		$settings['blog_width']     = max( 600, min( 1400, (int) $settings['blog_width'] ) );
		$settings['blog_columns']   = max( 1, min( 3, (int) $settings['blog_columns'] ) );
		$settings['posts_per_page'] = max( 1, min( 50, (int) $settings['posts_per_page'] ) );
		$settings['blog_bottom_spacing'] = self::sanitize_blog_bottom_spacing( $settings['blog_bottom_spacing'] ?? self::BLOG_BOTTOM_SPACING_DEFAULT );
		if ( ! array_key_exists( 'excerpt_chars', $stored ) && array_key_exists( 'excerpt_length', $stored ) ) {
			$settings['excerpt_chars'] = 80;
		}

		$settings['excerpt_chars'] = self::sanitize_excerpt_chars( $settings['excerpt_chars'] ?? 80 );

		$checkboxes = array(
			'hide_blog_header',
			'hide_category_filter',
			'show_thumbnail',
			'show_category',
			'show_date',
			'show_reading_time',
			'show_excerpt',
			'show_read_button',
		);

		foreach ( $checkboxes as $bool_key ) {
			if ( ! array_key_exists( $bool_key, $stored ) ) {
				$settings[ $bool_key ] = self::get_defaults()[ $bool_key ];
			} else {
				$settings[ $bool_key ] = wp_validate_boolean( $stored[ $bool_key ] );
			}
		}

		if ( '' === trim( (string) $settings['all_categories_label'] ) ) {
			$settings['all_categories_label'] = self::get_defaults()['all_categories_label'];
		}

		if ( '' === trim( (string) $settings['read_button_text'] ) ) {
			$settings['read_button_text'] = self::get_defaults()['read_button_text'];
		}

		$settings['cover_aspect_ratio'] = self::sanitize_cover_aspect_ratio( $settings['cover_aspect_ratio'] ?? '2-1' );

		$cached = $settings;

		return $cached;
	}

	/**
	 * Allowed cover aspect ratio choices (slug => label).
	 *
	 * @return array<string, string>
	 */
	public static function get_cover_aspect_ratio_choices() {
		return array(
			'2-1'  => '2:1',
			'16-9' => '16:9',
			'4-3'  => '4:3',
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
			return '2-1';
		}

		return $value;
	}

	/**
	 * CSS aspect-ratio value for card covers.
	 *
	 * @param string|null $slug Optional ratio slug.
	 * @return string
	 */
	public static function get_cover_aspect_ratio_css( $slug = null ) {
		$map = array(
			'2-1'  => '2 / 1',
			'16-9' => '16 / 9',
			'4-3'  => '4 / 3',
		);

		if ( null === $slug ) {
			$slug = self::get()['cover_aspect_ratio'];
		}

		$slug = self::sanitize_cover_aspect_ratio( $slug );

		return $map[ $slug ];
	}

	/**
	 * Whether the category filter should render on blog views.
	 *
	 * @return bool
	 */
	public static function should_show_category_filter() {
		$settings = self::get();

		if ( ! empty( $settings['hide_category_filter'] ) ) {
			return false;
		}

		return art_theme_has_archive_categories();
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

		$checkboxes = array(
			'hide_blog_header',
			'hide_category_filter',
			'show_thumbnail',
			'show_category',
			'show_date',
			'show_reading_time',
			'show_excerpt',
			'show_read_button',
		);

		foreach ( $checkboxes as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$merged[ $key ] = ! empty( $input[ $key ] );
			}
		}

		$blog_title = $merged['blog_title'] ?? ( $merged['archive_title'] ?? '' );

		return array(
			'blog_width'            => max( 600, min( 1400, (int) ( $merged['blog_width'] ?? 850 ) ) ),
			'blog_columns'          => max( 1, min( 3, (int) ( $merged['blog_columns'] ?? 2 ) ) ),
			'posts_per_page'        => max( 1, min( 50, (int) ( $merged['posts_per_page'] ?? 10 ) ) ),
			'blog_bottom_spacing'   => self::sanitize_blog_bottom_spacing( $merged['blog_bottom_spacing'] ?? self::BLOG_BOTTOM_SPACING_DEFAULT ),
			'hide_blog_header'      => ! empty( $merged['hide_blog_header'] ),
			'hide_category_filter'  => ! empty( $merged['hide_category_filter'] ),
			'blog_title'            => sanitize_text_field( $blog_title ),
			'blog_description'      => sanitize_textarea_field( $merged['blog_description'] ?? ( $merged['archive_description'] ?? '' ) ),
			'all_categories_label'  => sanitize_text_field( $merged['all_categories_label'] ?? __( 'Все категории', 'art-theme' ) ),
			'cover_aspect_ratio'    => self::sanitize_cover_aspect_ratio( $merged['cover_aspect_ratio'] ?? '2-1' ),
			'show_thumbnail'        => ! empty( $merged['show_thumbnail'] ),
			'show_category'         => ! empty( $merged['show_category'] ),
			'show_date'             => ! empty( $merged['show_date'] ),
			'show_reading_time'     => ! empty( $merged['show_reading_time'] ),
			'show_excerpt'          => ! empty( $merged['show_excerpt'] ),
			'excerpt_chars'         => self::sanitize_excerpt_chars( $merged['excerpt_chars'] ?? ( $merged['excerpt_length'] ?? 80 ) ),
			'show_read_button'      => ! empty( $merged['show_read_button'] ),
			'read_button_text'      => sanitize_text_field( $merged['read_button_text'] ?? __( 'Читать статью →', 'art-theme' ) ),
		);
	}

	/**
	 * Set posts per page on blog views.
	 *
	 * @param WP_Query $query Main query.
	 */
	public static function filter_posts_per_page( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! art_theme_is_blog_archive_view() ) {
			return;
		}

		$settings = self::get();
		$query->set( 'posts_per_page', (int) $settings['posts_per_page'] );
	}

	/**
	 * Sanitize bottom spacing for blog archive pages.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_blog_bottom_spacing( $value ) {
		return max( 0, min( 160, (int) $value ) );
	}

	/**
	 * Disable word-based trimming on blog cards — length is applied by characters.
	 *
	 * @param int $length Default length.
	 * @return int
	 */
	public static function filter_excerpt_length( $length ) {
		if ( is_admin() || ! art_theme_is_blog_archive_view() ) {
			return $length;
		}

		$settings = self::get();

		if ( empty( $settings['show_excerpt'] ) ) {
			return $length;
		}

		return 999;
	}

	/**
	 * Trim archive card excerpts to a character limit.
	 *
	 * @param string $excerpt Post excerpt.
	 * @return string
	 */
	public static function filter_the_excerpt( $excerpt ) {
		if ( is_admin() || ! art_theme_is_blog_archive_view() ) {
			return $excerpt;
		}

		$settings = self::get();

		if ( empty( $settings['show_excerpt'] ) ) {
			return $excerpt;
		}

		return self::trim_excerpt_chars( $excerpt, (int) $settings['excerpt_chars'] );
	}

	/**
	 * Sanitize excerpt character limit.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_excerpt_chars( $value ) {
		return max( 20, min( 500, (int) $value ) );
	}

	/**
	 * Trim plain text to a maximum number of UTF-8 characters.
	 *
	 * @param string $text  Source text.
	 * @param int    $limit Character limit.
	 * @return string
	 */
	public static function trim_excerpt_chars( $text, $limit ) {
		$text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', trim( $text ) );

		if ( '' === $text || mb_strlen( $text ) <= $limit ) {
			return $text;
		}

		$trimmed = mb_substr( $text, 0, $limit );
		$space   = mb_strrpos( $trimmed, ' ' );

		if ( false !== $space && $space > (int) ( $limit * 0.6 ) ) {
			$trimmed = mb_substr( $trimmed, 0, $space );
		}

		return rtrim( $trimmed ) . '...';
	}

	/**
	 * Use plain ellipsis for trimmed card excerpts.
	 *
	 * @param string $more Default more string.
	 * @return string
	 */
	public static function filter_excerpt_more( $more ) {
		if ( is_admin() || ! art_theme_is_blog_archive_view() ) {
			return $more;
		}

		return '...';
	}

	/**
	 * Normalize and sanitize option after Customizer save.
	 */
	public static function normalize_after_customizer_save() {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		update_option( self::OPTION_KEY, self::sanitize( wp_parse_args( $stored, self::get_defaults() ) ) );
	}
}
