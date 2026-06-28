<?php
/**
 * Site footer rendering.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render site footer when it has visible content.
 */
function art_theme_render_site_footer() {
	$settings = Art_Theme_Footer_Settings::get();

	if ( ! Art_Theme_Footer_Settings::has_visible_content( $settings ) ) {
		return;
	}

	$template   = Art_Theme_Footer_Settings::sanitize_footer_template( $settings['footer_template'] ?? Art_Theme_Footer_Settings::TEMPLATE_CLASSIC );
	$structure  = Art_Theme_Footer_Settings::sanitize_footer_structure( $settings['footer_structure'] ?? Art_Theme_Footer_Settings::STRUCTURE_COLUMNS );
	$footer_class = 'art-theme-site-footer art-theme-site-footer--template-' . $template . ' art-theme-site-footer--structure-' . $structure . ' ' . Art_Theme_Footer_Settings::get_width_mode_class( $settings );
	?>
	<footer class="<?php echo esc_attr( $footer_class ); ?>">
		<div class="art-theme-site-footer__inner">
			<?php
			switch ( $structure ) {
				case Art_Theme_Footer_Settings::STRUCTURE_STACK:
					art_theme_render_footer_stack_layout( $settings );
					break;
				default:
					art_theme_render_footer_columns_layout( $settings );
					break;
			}
			?>
		</div>
	</footer>
	<?php
}

/**
 * Two-column footer layout.
 *
 * @param array<string, mixed> $settings Footer settings.
 */
function art_theme_render_footer_columns_layout( $settings ) {
	$has_brand_column = art_theme_footer_has_brand_block( $settings ) || art_theme_footer_has_socials( $settings );
	$has_links_column = art_theme_footer_has_links( $settings );

	if ( $has_brand_column || $has_links_column ) {
		$main_class = 'art-theme-site-footer__main';

		if ( $has_brand_column && ! $has_links_column ) {
			$main_class .= ' art-theme-site-footer__main--brand-only';
		} elseif ( ! $has_brand_column && $has_links_column ) {
			$main_class .= ' art-theme-site-footer__main--links-only';
		}

		echo '<div class="' . esc_attr( $main_class ) . '">';

		if ( $has_brand_column ) {
			echo '<div class="art-theme-site-footer__column art-theme-site-footer__column--brand">';
			echo '<div class="art-theme-site-footer__brand-block">';
			art_theme_render_footer_brand( $settings );
			art_theme_render_footer_socials( $settings );
			echo '</div>';
			echo '</div>';
		}

		if ( $has_links_column ) {
			echo '<div class="art-theme-site-footer__column art-theme-site-footer__column--links">';
			art_theme_render_footer_links( $settings, 'columns' );
			echo '</div>';
		}

		echo '</div>';
	}

	art_theme_render_footer_copyright( $settings, 'columns' );
}

/**
 * Centered stack footer layout.
 *
 * @param array<string, mixed> $settings Footer settings.
 */
function art_theme_render_footer_stack_layout( $settings ) {
	echo '<div class="art-theme-site-footer__stack">';
	art_theme_render_footer_brand( $settings );
	art_theme_render_footer_socials( $settings );
	art_theme_render_footer_links( $settings, 'stack' );
	art_theme_render_footer_copyright( $settings, 'stack' );
	echo '</div>';
}

/**
 * @param array<string, mixed> $settings Footer settings.
 * @return bool
 */
function art_theme_footer_has_brand_block( $settings ) {
	return ( ! empty( $settings['show_title'] ) && '' !== get_bloginfo( 'name', 'display' ) )
		|| ( ! empty( $settings['show_tagline'] ) && '' !== get_bloginfo( 'description', 'display' ) );
}

/**
 * @param array<string, mixed> $settings Footer settings.
 * @return bool
 */
function art_theme_footer_has_socials( $settings ) {
	return ! empty( $settings['show_socials'] ) && ! empty( $settings['socials'] );
}

/**
 * @param array<string, mixed> $settings Footer settings.
 * @return bool
 */
function art_theme_footer_has_links( $settings ) {
	return ! empty( $settings['show_links'] ) && ! empty( $settings['custom_links'] );
}

/**
 * @param array<string, mixed> $settings Footer settings.
 */
function art_theme_render_footer_brand( $settings ) {
	if ( ! art_theme_footer_has_brand_block( $settings ) ) {
		return;
	}

	echo '<div class="art-theme-site-footer__brand">';

	if ( ! empty( $settings['show_title'] ) ) {
		$name = get_bloginfo( 'name', 'display' );

		if ( '' !== $name ) {
			echo '<div class="art-theme-site-footer__title">' . esc_html( $name ) . '</div>';
		}
	}

	if ( ! empty( $settings['show_tagline'] ) ) {
		$description = get_bloginfo( 'description', 'display' );

		if ( '' !== $description ) {
			echo '<div class="art-theme-site-footer__tagline">' . esc_html( $description ) . '</div>';
		}
	}

	echo '</div>';
}

/**
 * @param array<string, mixed> $settings Footer settings.
 */
function art_theme_render_footer_socials( $settings ) {
	if ( ! art_theme_footer_has_socials( $settings ) ) {
		return;
	}

	echo '<div class="art-theme-site-footer__socials">';

	foreach ( $settings['socials'] as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$network = Art_Theme_Social_Icons::sanitize_network( $item['network'] ?? '' );
		$href    = Art_Theme_Social_Icons::get_href( $item );

		if ( '' === $network || '' === $href ) {
			continue;
		}

		$networks = Art_Theme_Social_Icons::get_networks();
		$label    = $networks[ $network ] ?? $network;

		printf(
			'<a class="art-theme-site-footer__social-item art-theme-site-footer__social-item--%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%4$s</a>',
			esc_attr( $network ),
			esc_url( $href ),
			esc_attr( $label ),
			Art_Theme_Social_Icons::render_icon( $network ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG escaped in helper.
		);
	}

	echo '</div>';
}

/**
 * @param array<string, mixed> $settings Footer settings.
 * @param string               $context  Layout context.
 */
function art_theme_render_footer_links( $settings, $context ) {
	if ( ! art_theme_footer_has_links( $settings ) ) {
		return;
	}

	$list_class = 'art-theme-site-footer__links art-theme-site-footer__links--' . sanitize_html_class( $context );

	echo '<nav class="' . esc_attr( $list_class ) . '" aria-label="' . esc_attr__( 'Ссылки подвала', 'art-theme' ) . '">';

	foreach ( $settings['custom_links'] as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = trim( (string) ( $item['label'] ?? '' ) );
		$url   = esc_url( (string) ( $item['url'] ?? '' ) );

		if ( '' === $label || '' === $url ) {
			continue;
		}

		$attrs = array(
			'class' => 'art-theme-site-footer__link',
			'href'  => $url,
		);

		if ( ! empty( $item['open_new_tab'] ) ) {
			$attrs['target'] = '_blank';
			$attrs['rel']    = 'noopener noreferrer';
		}

		$attr_string = '';

		foreach ( $attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		printf( '<a%1$s>%2$s</a>', $attr_string, esc_html( $label ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attrs built with esc_attr.
	}

	echo '</nav>';
}

/**
 * @param array<string, mixed> $settings Footer settings.
 * @param string               $context  Layout context.
 */
function art_theme_render_footer_copyright( $settings, $context ) {
	if ( empty( $settings['show_copyright'] ) ) {
		return;
	}

	$line = Art_Theme_Footer_Settings::get_copyright_line( $settings );

	if ( '' === trim( $line ) ) {
		return;
	}

	printf(
		'<div class="art-theme-site-footer__copyright art-theme-site-footer__copyright--%1$s">%2$s</div>',
		esc_attr( sanitize_html_class( $context ) ),
		esc_html( $line )
	);
}
