<?php
/**
 * Page template settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static page appearance settings.
 */
class Art_Theme_Page_Settings {

	const OPTION_KEY = 'art_theme_page_settings';

	const META_PAGE_TEMPLATE = 'art_theme_page_template_variant';

	const META_HIDE_TITLE = 'art_theme_page_hide_title';

	/**
	 * Page template variant slugs (Customizer / global).
	 */
	const TEMPLATE_VARIANTS = array( 'boxed', 'full-width' );

	/**
	 * Per-page template override slugs (block editor).
	 */
	const PAGE_TEMPLATE_OVERRIDE_VARIANTS = array( 'default', 'boxed', 'full-width' );

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_meta' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
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
				'page_width' => Art_Theme_Content_Template::CONTENT_WIDTH_DEFAULT,
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

		$settings['page_width']           = max( 600, min( 1400, (int) $settings['page_width'] ) );
		$settings['template_variant']     = self::sanitize_template_variant( $settings['template_variant'] ?? 'boxed' );
		$settings['boxed_border_radius']  = self::sanitize_boxed_border_radius( $settings['boxed_border_radius'] ?? 10 );
		$settings['boxed_shadow']         = self::sanitize_boxed_shadow( $settings['boxed_shadow'] ?? 'medium' );
		$settings['boxed_padding_block']  = self::sanitize_boxed_padding_block( $settings['boxed_padding_block'] ?? 32 );
		$settings['boxed_padding_inline'] = self::sanitize_boxed_padding_inline( $settings['boxed_padding_inline'] ?? 24 );

		$cached = $settings;

		return $cached;
	}

	/**
	 * Get layout settings for the current singular view (page or CPT using page template).
	 *
	 * @param int|WP_Post|null $post_id Optional post ID or object.
	 * @return array<string, mixed>
	 */
	public static function get_for_singular( $post_id = null ) {
		if ( null === $post_id ) {
			if ( is_singular() ) {
				$post_id = (int) get_queried_object_id();
			} else {
				return self::get();
			}
		} elseif ( $post_id instanceof WP_Post ) {
			$post_id = (int) $post_id->ID;
		}

		$post_id  = (int) $post_id;
		$settings = self::get();
		$post     = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $settings;
		}

		if ( 'post' === $post->post_type ) {
			return $settings;
		}

		$override = self::get_page_template_override( $post_id );

		if ( 'default' !== $override ) {
			$settings['template_variant'] = self::sanitize_template_variant( $override );
		}

		return $settings;
	}

	/**
	 * Template variant labels for Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function get_template_variant_choices() {
		return Art_Theme_Content_Template::get_template_variant_choices();
	}

	/**
	 * Per-page template choices for the block editor.
	 *
	 * @return array<string, string>
	 */
	public static function get_page_template_override_choices() {
		return Art_Theme_Content_Template::get_template_override_choices();
	}

	/**
	 * Sanitize per-page template override slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_page_template_override( $value ) {
		return Art_Theme_Content_Template::sanitize_template_override( $value );
	}

	/**
	 * Get per-page template override for a page.
	 *
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public static function get_page_template_override( $post_id ) {
		$value = get_post_meta( (int) $post_id, self::META_PAGE_TEMPLATE, true );

		return self::sanitize_page_template_override( $value );
	}

	/**
	 * Get merged settings for a page, including per-page template override.
	 *
	 * @param int|null $post_id Optional page ID; defaults to the current queried page.
	 * @return array<string, mixed>
	 */
	public static function get_for_page( $post_id = null ) {
		return self::get_for_singular( $post_id );
	}

	/**
	 * Whether the page title should be hidden on the front end.
	 *
	 * @param int|null $post_id Optional page ID; defaults to the current queried page.
	 * @return bool
	 */
	public static function page_should_hide_title( $post_id = null ) {
		if ( null === $post_id ) {
			if ( ! is_singular( 'page' ) ) {
				return false;
			}

			$post_id = (int) get_queried_object_id();
		}

		return self::sanitize_page_hide_title( get_post_meta( (int) $post_id, self::META_HIDE_TITLE, true ) );
	}

	/**
	 * Sanitize hide-title flag for a page.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public static function sanitize_page_hide_title( $value ) {
		return wp_validate_boolean( $value );
	}

	/**
	 * Register page template meta for the block editor REST API.
	 */
	public static function register_post_meta() {
		foreach ( self::get_page_layout_post_types() as $post_type ) {
			register_post_meta(
				$post_type,
				self::META_PAGE_TEMPLATE,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'string',
					'default'           => 'default',
					'sanitize_callback' => array( __CLASS__, 'sanitize_page_template_override' ),
					'auth_callback'     => array( __CLASS__, 'page_meta_auth_callback' ),
				)
			);
		}

		register_post_meta(
			'page',
			self::META_HIDE_TITLE,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( __CLASS__, 'sanitize_page_hide_title' ),
				'auth_callback'     => array( __CLASS__, 'page_meta_auth_callback' ),
			)
		);
	}

	/**
	 * Whether the current user may edit page meta.
	 *
	 * @param bool   $allowed  Whether the user can add the meta.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function page_meta_auth_callback( $allowed, $meta_key, $post_id ) {
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Enqueue block editor sidebar controls for page templates.
	 */
	public static function enqueue_block_editor_assets() {
		if ( ! self::is_page_layout_block_editor_screen() ) {
			return;
		}

		$script_path = ART_THEME_DIR . '/assets/js/page-template-sidebar.js';
		$style_path  = ART_THEME_DIR . '/assets/css/page-settings-sidebar.css';
		$editor_deps = array(
			'wp-plugins',
			'wp-editor',
			'wp-edit-post',
			'wp-components',
			'wp-data',
			'wp-core-data',
			'wp-element',
		);

		wp_enqueue_style(
			'art-theme-page-settings-sidebar',
			ART_THEME_URL . '/assets/css/page-settings-sidebar.css',
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : ART_THEME_VERSION
		);

		wp_enqueue_script(
			'art-theme-page-template-sidebar',
			ART_THEME_URL . '/assets/js/page-template-sidebar.js',
			$editor_deps,
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : ART_THEME_VERSION,
			true
		);

		$choices = array();

		foreach ( self::get_page_template_override_choices() as $value => $label ) {
			$choices[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		wp_localize_script(
			'art-theme-page-template-sidebar',
			'artThemePageTemplate',
			array(
				'choices'              => $choices,
				'supportedPostTypes'   => self::get_page_layout_post_types(),
				'defaultHelp'          => __( 'Наследует шаблон «Контентный блок» из настроек темы.', 'art-theme' ),
				'panelTitle'           => __( 'Шаблон страницы', 'art-theme' ),
				'controlLabel'         => __( 'Шаблон', 'art-theme' ),
				'hideTitleLabel'       => __( 'Скрыть заголовок страницы', 'art-theme' ),
				'hideTitleHelp'        => __( 'Заголовок не выводится на сайте, но остаётся в редакторе.', 'art-theme' ),
				'hideTitlePostTypes'   => array( 'page' ),
			)
		);
	}

	/**
	 * Post types that use the shared page-style boxed template on singular views.
	 *
	 * @return array<int, string>
	 */
	public static function get_page_layout_post_types() {
		$post_types = array( 'page' );

		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $post_type ) {
			if ( art_theme_uses_page_template_layout( $post_type ) ) {
				$post_types[] = $post_type;
			}
		}

		return array_values( array_unique( $post_types ) );
	}

	/**
	 * Whether the current admin screen is the block editor for a page-layout post type.
	 *
	 * @return bool
	 */
	private static function is_page_layout_block_editor_screen() {
		if ( ! is_admin() ) {
			return false;
		}

		if ( isset( $_GET['post'] ) ) {
			$post = get_post( (int) wp_unslash( $_GET['post'] ) );

			if ( $post instanceof WP_Post && in_array( $post->post_type, self::get_page_layout_post_types(), true ) ) {
				return use_block_editor_for_post( $post );
			}
		}

		if ( isset( $_GET['post_type'] ) ) {
			$post_type = sanitize_key( wp_unslash( $_GET['post_type'] ) );

			if ( in_array( $post_type, self::get_page_layout_post_types(), true ) ) {
				return use_block_editor_for_post_type( $post_type );
			}
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		return $screen && in_array( $screen->post_type, self::get_page_layout_post_types(), true );
	}

	/**
	 * @deprecated 1.0.4 Use is_page_layout_block_editor_screen().
	 * @return bool
	 */
	private static function is_page_block_editor_screen() {
		return self::is_page_layout_block_editor_screen();
	}

	/**
	 * Sanitize page template variant slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_template_variant( $value ) {
		return Art_Theme_Content_Template::sanitize_template_variant( $value );
	}

	/**
	 * Whether the page uses the full-width background template.
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
		return Art_Theme_Single_Settings::get_boxed_shadow_choices();
	}

	/**
	 * Sanitize boxed border radius in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_border_radius( $value ) {
		return Art_Theme_Single_Settings::sanitize_boxed_border_radius( $value );
	}

	/**
	 * Sanitize boxed shadow slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_boxed_shadow( $value ) {
		return Art_Theme_Single_Settings::sanitize_boxed_shadow( $value );
	}

	/**
	 * Sanitize boxed vertical padding in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_padding_block( $value ) {
		return Art_Theme_Single_Settings::sanitize_boxed_padding_block( $value );
	}

	/**
	 * Sanitize boxed horizontal padding in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_boxed_padding_inline( $value ) {
		return Art_Theme_Single_Settings::sanitize_boxed_padding_inline( $value );
	}

	/**
	 * CSS box-shadow for boxed template.
	 *
	 * @param string|null              $slug     Optional shadow slug.
	 * @param array<string,mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_boxed_shadow_css( $slug = null, $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		if ( null === $slug ) {
			$slug = self::sanitize_boxed_shadow( $settings['boxed_shadow'] ?? 'medium' );
		}

		return Art_Theme_Single_Settings::get_boxed_shadow_css( $slug, $settings );
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
	 * Sanitize saved settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		$existing = wp_parse_args( get_option( self::OPTION_KEY, array() ), self::get_defaults() );
		$merged   = wp_parse_args( $input, $existing );

		return array(
			'template_variant'     => self::sanitize_template_variant( $merged['template_variant'] ?? 'boxed' ),
			'page_width'           => max( 600, min( 1400, (int) ( $merged['page_width'] ?? Art_Theme_Content_Template::CONTENT_WIDTH_DEFAULT ) ) ),
			'boxed_border_radius'  => self::sanitize_boxed_border_radius( $merged['boxed_border_radius'] ?? 10 ),
			'boxed_shadow'         => self::sanitize_boxed_shadow( $merged['boxed_shadow'] ?? 'medium' ),
			'boxed_padding_block'  => self::sanitize_boxed_padding_block( $merged['boxed_padding_block'] ?? 32 ),
			'boxed_padding_inline' => self::sanitize_boxed_padding_inline( $merged['boxed_padding_inline'] ?? 24 ),
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
