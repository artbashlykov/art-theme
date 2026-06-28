<?php
/**
 * Front-end CSS bundles — split by layout context for PageSpeed.
 *
 * When adding new UI, update the matching theme-*.css partial AND the rules
 * in get_active_bundles() if the styles are view-specific.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and enqueues split theme stylesheets.
 */
class Art_Theme_Styles {

	const HANDLE_PREFIX = 'art-theme';

	/**
	 * Bundle definitions (order matters for cascade when multiple bundles load).
	 *
	 * @return array<string, array{file: string, label: string}>
	 */
	public static function get_bundle_definitions() {
		return array(
			'base'      => array(
				'file'  => 'theme-base.css',
				'label' => 'Base layout and shared content',
			),
			'header'    => array(
				'file'  => 'theme-header.css',
				'label' => 'Site header',
			),
			'single'    => array(
				'file'  => 'theme-single.css',
				'label' => 'Single post',
			),
			'page'      => array(
				'file'  => 'theme-page.css',
				'label' => 'Static page',
			),
			'archive'   => array(
				'file'  => 'theme-archive.css',
				'label' => 'Blog archive and cards',
			),
			'not-found' => array(
				'file'  => 'theme-not-found.css',
				'label' => '404 page',
			),
			'footer'    => array(
				'file'  => 'theme-footer.css',
				'label' => 'Site footer',
			),
		);
	}

	/**
	 * Relative paths for the block editor (loads every bundle).
	 *
	 * @return array<int, string>
	 */
	public static function get_editor_stylesheets() {
		$paths = array();

		foreach ( self::get_bundle_definitions() as $bundle ) {
			$paths[] = 'assets/css/' . $bundle['file'];
		}

		return $paths;
	}

	/**
	 * Primary style handle used for inline CSS variables.
	 *
	 * @return string
	 */
	public static function get_primary_handle() {
		return self::HANDLE_PREFIX . '-base';
	}

	/**
	 * Resolve bundle slugs for the current front-end request.
	 *
	 * @return array<int, string>
	 */
	public static function get_active_bundles() {
		if ( is_customize_preview() || self::should_force_full_bundles() ) {
			return array_keys( self::get_bundle_definitions() );
		}

		$bundles = array( 'base' );

		if ( self::should_load_header_bundle() ) {
			$bundles[] = 'header';
		}

		if ( is_singular() ) {
			/*
			 * Singular views always need both layout bundles: posts use single
			 * classes, pages and public CPTs use page classes. Loading both avoids
			 * missing styles when templates or post types do not match a narrow check.
			 */
			$bundles[] = 'single';
			$bundles[] = 'page';
		} elseif ( is_search() ) {
			if ( self::search_results_include_posts() ) {
				$bundles[] = 'single';
			}

			if ( self::search_results_include_pages() ) {
				$bundles[] = 'page';
			}
		}

		if ( art_theme_is_blog_archive_view() ) {
			$bundles[] = 'archive';
		}

		if ( is_404() ) {
			$bundles[] = 'not-found';
		}

		if ( self::should_load_footer_bundle() ) {
			$bundles[] = 'footer';
		}

		/**
		 * Filter CSS bundle slugs enqueued on the current request.
		 *
		 * @param array<int, string> $bundles Active bundle slugs.
		 */
		$bundles = apply_filters( 'art_theme_style_bundles', $bundles );

		return array_values( array_unique( array_filter( $bundles ) ) );
	}

	/**
	 * Whether wp-block-library should load on this request.
	 *
	 * Block styles are only needed when Gutenberg block HTML is rendered.
	 *
	 * @return bool
	 */
	public static function should_enqueue_block_library() {
		if ( is_customize_preview() || self::should_force_full_bundles() ) {
			return true;
		}

		/*
		 * Singular templates always call the_content() — keep block styles so
		 * galleries, lists, and embeds render consistently across all posts.
		 */
		if ( is_singular() ) {
			return true;
		}

		if ( is_search() ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue active CSS bundles.
	 *
	 * @param array<int, string> $dependencies Base style dependencies (fonts, block library, etc.).
	 */
	public static function enqueue_bundles( $dependencies = array() ) {
		$definitions = self::get_bundle_definitions();
		$active      = self::get_active_bundles();
		$parent      = $dependencies;

		foreach ( $active as $slug ) {
			if ( ! isset( $definitions[ $slug ] ) ) {
				continue;
			}

			$file   = $definitions[ $slug ]['file'];
			$path   = ART_THEME_DIR . '/assets/css/' . $file;
			$handle = self::HANDLE_PREFIX . '-' . str_replace( '_', '-', $slug );

			wp_enqueue_style(
				$handle,
				ART_THEME_URL . '/assets/css/' . $file,
				$parent,
				file_exists( $path ) ? (string) filemtime( $path ) : ART_THEME_VERSION
			);

			$parent = array( $handle );
		}
	}

	/**
	 * @return bool
	 */
	private static function should_force_full_bundles() {
		/**
		 * Force every theme CSS bundle (debug or plugins that render unknown templates).
		 *
		 * @param bool $force Full bundle load.
		 */
		return (bool) apply_filters( 'art_theme_force_full_styles', false );
	}

	/**
	 * @return bool
	 */
	private static function should_load_header_bundle() {
		return ! empty( Art_Theme_Header_Settings::get_visible_order() );
	}

	/**
	 * @return bool
	 */
	private static function should_load_footer_bundle() {
		return Art_Theme_Footer_Settings::has_visible_content();
	}

	/**
	 * @return bool
	 */
	private static function search_results_include_posts() {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query || empty( $wp_query->posts ) ) {
			return false;
		}

		foreach ( $wp_query->posts as $post ) {
			if ( $post instanceof WP_Post && 'post' === $post->post_type ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private static function search_results_include_pages() {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query || empty( $wp_query->posts ) ) {
			return false;
		}

		foreach ( $wp_query->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			if ( 'page' === $post->post_type || art_theme_uses_page_template_layout( $post->post_type ) ) {
				return true;
			}
		}

		return false;
	}
}
