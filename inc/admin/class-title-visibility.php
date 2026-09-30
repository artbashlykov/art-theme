<?php
/**
 * Per-post title visibility (Gutenberg toggle + front-end hide).
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Show/hide the singular entry title for posts and pages.
 */
class Art_Theme_Title_Visibility {

	/**
	 * Canonical meta key (true = hide title on the front end).
	 */
	const META_KEY = 'art_theme_hide_title';

	/**
	 * Legacy page meta (kept for backwards compatibility).
	 */
	const LEGACY_PAGE_META_KEY = 'art_theme_page_hide_title';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_meta' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Post types that get the editor title toggle.
	 *
	 * @return array<int, string>
	 */
	public static function get_supported_post_types() {
		/**
		 * Filter post types that support the Gutenberg title visibility toggle.
		 *
		 * @param array<int, string> $post_types Post type slugs.
		 */
		$post_types = apply_filters( 'art_theme_title_visibility_post_types', array( 'post', 'page' ) );

		if ( ! is_array( $post_types ) ) {
			return array( 'post', 'page' );
		}

		return array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
	}

	/**
	 * Register hide-title meta for supported post types.
	 */
	public static function register_post_meta() {
		foreach ( self::get_supported_post_types() as $post_type ) {
			register_post_meta(
				$post_type,
				self::META_KEY,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'boolean',
					'default'           => false,
					'sanitize_callback' => array( __CLASS__, 'sanitize_hide_title' ),
					'auth_callback'     => array( __CLASS__, 'meta_auth_callback' ),
				)
			);
		}
	}

	/**
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public static function sanitize_hide_title( $value ) {
		return wp_validate_boolean( $value );
	}

	/**
	 * @param bool   $allowed  Whether allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function meta_auth_callback( $allowed, $meta_key, $post_id ) {
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', (int) $post_id );
	}

	/**
	 * Whether the singular title should be hidden on the front end.
	 *
	 * @param int|null $post_id Optional post ID.
	 * @return bool
	 */
	public static function should_hide_title( $post_id = null ) {
		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$post_id = (int) $post_id;

		if ( $post_id <= 0 ) {
			return false;
		}

		$post_type = get_post_type( $post_id );

		if ( ! $post_type || ! in_array( $post_type, self::get_supported_post_types(), true ) ) {
			return false;
		}

		if ( metadata_exists( 'post', $post_id, self::META_KEY ) ) {
			return self::sanitize_hide_title( get_post_meta( $post_id, self::META_KEY, true ) );
		}

		// Back-compat: pages previously used a dedicated meta key.
		if ( 'page' === $post_type && metadata_exists( 'post', $post_id, self::LEGACY_PAGE_META_KEY ) ) {
			return self::sanitize_hide_title( get_post_meta( $post_id, self::LEGACY_PAGE_META_KEY, true ) );
		}

		return false;
	}

	/**
	 * Enqueue Gutenberg title toggle assets.
	 */
	public static function enqueue_block_editor_assets() {
		$post_type = self::get_current_editor_post_type();

		if ( ! $post_type || ! in_array( $post_type, self::get_supported_post_types(), true ) ) {
			return;
		}

		$script_path = ART_THEME_DIR . '/assets/js/title-visibility-editor.js';
		$style_path  = ART_THEME_DIR . '/assets/css/title-visibility-editor.css';

		wp_enqueue_style(
			'art-theme-title-visibility-editor',
			ART_THEME_URL . '/assets/css/title-visibility-editor.css',
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : ART_THEME_VERSION
		);

		wp_enqueue_script(
			'art-theme-title-visibility-editor',
			ART_THEME_URL . '/assets/js/title-visibility-editor.js',
			array( 'wp-data', 'wp-dom-ready', 'wp-edit-post', 'wp-editor', 'wp-element', 'wp-i18n' ),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-title-visibility-editor',
			'artThemeTitleVisibility',
			array(
				'metaKey'           => self::META_KEY,
				'legacyPageMetaKey' => self::LEGACY_PAGE_META_KEY,
				'supportedTypes'    => self::get_supported_post_types(),
				'enableLabel'       => __( 'Показывать заголовок на сайте', 'art-theme' ),
				'disableLabel'      => __( 'Скрыть заголовок на сайте', 'art-theme' ),
			)
		);
	}

	/**
	 * Resolve the post type for the current block editor screen.
	 *
	 * @return string
	 */
	private static function get_current_editor_post_type() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && ! empty( $screen->post_type ) ) {
			return sanitize_key( (string) $screen->post_type );
		}

		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post = get_post( (int) wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $post instanceof WP_Post ) {
				return sanitize_key( (string) $post->post_type );
			}
		}

		if ( isset( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_key( (string) wp_unslash( $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return 'post';
	}
}
