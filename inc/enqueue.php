<?php
/**
 * Front-end assets.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme stylesheet after WordPress block/global styles.
 */
function art_theme_enqueue_assets() {
	wp_enqueue_style( 'art-theme-fonts' );

	/*
	 * Block library loads in parallel — never as a dependency of theme bundles.
	 * A missing or late block stylesheet must not block theme-base.css from printing.
	 */
	if ( Art_Theme_Styles::should_enqueue_block_library() ) {
		wp_enqueue_style( 'wp-block-library' );
	}

	Art_Theme_Styles::enqueue_bundles( array( 'art-theme-fonts' ) );

	$style_handle = Art_Theme_Styles::get_primary_handle();

	if ( ! empty( Art_Theme_Header_Settings::get_visible_order() ) ) {
		$header_settings = Art_Theme_Header_Settings::get();
		$header_css_vars = array(
			sprintf( '--art-theme-header-top-spacing: %1$dpx', (int) $header_settings['header_top_spacing'] ),
			sprintf( '--art-theme-header-bottom-spacing: %1$dpx', (int) $header_settings['header_bottom_spacing'] ),
			sprintf( '--art-theme-header-content-width: %1$dpx', Art_Theme_Header_Settings::get_content_width_px() ),
			sprintf( '--art-theme-header-fixed-extra-inline: %1$dpx', Art_Theme_Header_Settings::get_fixed_extra_inline_px( $header_settings ) ),
			sprintf( '--art-theme-header-radius: %1$dpx', (int) $header_settings['header_border_radius'] ),
		);

		wp_add_inline_style(
			$style_handle,
			':root { ' . implode( '; ', $header_css_vars ) . '; }'
		);

		$header_script_path = ART_THEME_DIR . '/assets/js/header-nav.js';

		wp_enqueue_script(
			'art-theme-header-nav',
			ART_THEME_URL . '/assets/js/header-nav.js',
			array(),
			file_exists( $header_script_path ) ? (string) filemtime( $header_script_path ) : ART_THEME_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		wp_localize_script(
			'art-theme-header-nav',
			'artThemeHeaderNav',
			array(
				'toggleLabel' => __( 'Раскрыть подменю', 'art-theme' ),
			)
		);
	}

	$footer_settings = Art_Theme_Footer_Settings::get();
	$footer_css_vars = array(
		sprintf( '--art-theme-footer-top-spacing: %1$dpx', (int) $footer_settings['footer_top_spacing'] ),
		sprintf( '--art-theme-footer-bottom-spacing: %1$dpx', (int) $footer_settings['footer_bottom_spacing'] ),
	);

	if ( Art_Theme_Footer_Settings::has_visible_content( $footer_settings ) ) {
		$footer_css_vars[] = sprintf( '--art-theme-footer-content-width: %1$dpx', Art_Theme_Header_Settings::get_content_width_px() );
		$footer_css_vars[] = sprintf( '--art-theme-footer-fixed-extra-inline: %1$dpx', Art_Theme_Footer_Settings::get_fixed_extra_inline_px( $footer_settings ) );
		$footer_css_vars[] = sprintf( '--art-theme-footer-radius: %1$dpx', (int) $footer_settings['footer_border_radius'] );
	}

	wp_add_inline_style(
		$style_handle,
		':root { ' . implode( '; ', $footer_css_vars ) . '; }'
	);

	if ( art_theme_is_blog_archive_view() ) {
		$blog_settings = Art_Theme_Blog_Settings::get();

		wp_add_inline_style(
			$style_handle,
			sprintf(
				':root { --art-theme-archive-width: %1$dpx; --art-theme-archive-columns: %2$d; --art-theme-cover-aspect-ratio: %3$s; --art-theme-blog-bottom-spacing: %4$dpx; }',
				(int) $blog_settings['blog_width'],
				(int) $blog_settings['blog_columns'],
				Art_Theme_Blog_Settings::get_cover_aspect_ratio_css( $blog_settings['cover_aspect_ratio'] ),
				(int) $blog_settings['blog_bottom_spacing']
			)
		);

		if ( Art_Theme_Blog_Settings::should_show_category_filter() ) {
			$script_path = ART_THEME_DIR . '/assets/js/archive-category-filter.js';

			wp_enqueue_script(
				'art-theme-archive-filter',
				ART_THEME_URL . '/assets/js/archive-category-filter.js',
				array(),
				file_exists( $script_path ) ? (string) filemtime( $script_path ) : ART_THEME_VERSION,
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
		}
	}

	if ( art_theme_is_single_post_view() ) {
		$single_settings = Art_Theme_Single_Settings::get();
		$aspect_css      = Art_Theme_Single_Settings::get_cover_aspect_ratio_css( $single_settings['cover_aspect_ratio'] );
		$css_vars        = array(
			sprintf( '--art-theme-single-width: %1$dpx', (int) $single_settings['post_width'] ),
			sprintf( '--art-theme-single-cover-aspect-ratio: %1$s', esc_attr( $aspect_css ) ),
			sprintf(
				'--art-theme-single-gutter: %1$s',
				Art_Theme_Single_Settings::is_full_width_template( $single_settings ) ? '1.5rem' : '0px'
			),
			sprintf( '--art-theme-single-top-spacing: %1$dpx', (int) $single_settings['post_top_spacing'] ),
		);

		if ( ! Art_Theme_Single_Settings::is_full_width_template( $single_settings ) ) {
			$boxed_padding = Art_Theme_Single_Settings::get_boxed_padding_css( $single_settings );

			$css_vars[] = sprintf( '--art-theme-single-boxed-radius: %1$dpx', (int) $single_settings['boxed_border_radius'] );
			$css_vars[] = sprintf(
				'--art-theme-single-boxed-shadow: %1$s',
				esc_attr( Art_Theme_Single_Settings::get_boxed_shadow_css( null, $single_settings ) )
			);
			$css_vars[] = sprintf( '--art-theme-single-boxed-padding-block: %1$s', esc_attr( $boxed_padding['block'] ) );
			$css_vars[] = sprintf( '--art-theme-single-boxed-padding-inline: %1$s', esc_attr( $boxed_padding['inline'] ) );
		}

		wp_add_inline_style(
			$style_handle,
			':root { ' . implode( '; ', $css_vars ) . '; }'
		);
	}

	if ( art_theme_is_page_template_view() ) {
		$page_settings = Art_Theme_Page_Settings::get_for_singular();
		$css_vars      = array(
			sprintf( '--art-theme-page-width: %1$dpx', (int) $page_settings['page_width'] ),
			sprintf(
				'--art-theme-page-gutter: %1$s',
				Art_Theme_Page_Settings::is_full_width_template( $page_settings ) ? '1.5rem' : '0px'
			),
			sprintf( '--art-theme-page-top-spacing: %1$dpx', (int) $page_settings['page_top_spacing'] ),
		);

		if ( ! Art_Theme_Page_Settings::is_full_width_template( $page_settings ) ) {
			$boxed_padding = Art_Theme_Page_Settings::get_boxed_padding_css( $page_settings );

			$css_vars[] = sprintf( '--art-theme-page-boxed-radius: %1$dpx', (int) $page_settings['boxed_border_radius'] );
			$css_vars[] = sprintf(
				'--art-theme-page-boxed-shadow: %1$s',
				esc_attr( Art_Theme_Page_Settings::get_boxed_shadow_css( null, $page_settings ) )
			);
			$css_vars[] = sprintf( '--art-theme-page-boxed-padding-block: %1$s', esc_attr( $boxed_padding['block'] ) );
			$css_vars[] = sprintf( '--art-theme-page-boxed-padding-inline: %1$s', esc_attr( $boxed_padding['inline'] ) );
		}

		wp_add_inline_style(
			$style_handle,
			':root { ' . implode( '; ', $css_vars ) . '; }'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'art_theme_enqueue_assets', 999 );

/**
 * Remove core block styles on views that never render block HTML (archives, 404).
 */
function art_theme_dequeue_unused_block_styles() {
	if ( Art_Theme_Styles::should_enqueue_block_library() ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'art_theme_dequeue_unused_block_styles', 1000 );
