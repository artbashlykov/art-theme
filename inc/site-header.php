<?php
/**
 * Site header rendering.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render sortable layout order list markup.
 *
 * @param array<int, string>    $order  Item slugs.
 * @param array<string, string> $labels Item labels.
 */
function art_theme_render_layout_order_list( $order, $labels ) {
	?>
	<ul class="art-theme-layout-order">
		<?php foreach ( $order as $item ) : ?>
			<li class="art-theme-layout-order__item" data-item="<?php echo esc_attr( $item ); ?>">
				<span class="art-theme-layout-order__handle dashicons dashicons-menu" aria-hidden="true"></span>
				<span class="art-theme-layout-order__label"><?php echo esc_html( $labels[ $item ] ?? $item ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Whether the site header has any visible content.
 *
 * @param array<string, mixed> $settings Header settings.
 * @return bool
 */
function art_theme_header_has_visible_content( $settings ) {
	return ! empty( Art_Theme_Header_Settings::get_visible_order( $settings ) );
}

/**
 * Render site header when at least one item is visible.
 */
function art_theme_render_site_header() {
	$settings = Art_Theme_Header_Settings::get();

	if ( ! art_theme_header_has_visible_content( $settings ) ) {
		return;
	}

	$template     = Art_Theme_Header_Settings::sanitize_header_template( $settings['header_template'] ?? Art_Theme_Header_Settings::TEMPLATE_CLASSIC );
	$order        = Art_Theme_Header_Settings::get_visible_order( $settings );
	$mobile_brand = art_theme_get_header_mobile_brand_items( $order, $settings );
	$panel_items  = art_theme_get_header_panel_items( $order, $settings );
	$panel_id     = 'art-theme-site-header-panel';
	$needs_panel  = ! empty( $panel_items );
	$has_button   = Art_Theme_Header_Settings::is_item_enabled( 'button', $settings ) && Art_Theme_Header_Settings::can_render_item( 'button', $settings );
	$header_class = 'art-theme-site-header art-theme-site-header--template-' . $template . ' ' . Art_Theme_Header_Settings::get_width_mode_class( $settings );
	$header_class .= $has_button ? ' art-theme-site-header--has-button' : ' art-theme-site-header--no-button';

	if ( ! $needs_panel ) {
		$header_class .= ' art-theme-site-header--no-panel';
	}
	?>
	<header class="<?php echo esc_attr( $header_class ); ?>" data-art-theme-header>
		<div class="art-theme-site-header__desktop">
			<div class="art-theme-site-header__inner">
				<div class="art-theme-site-header__brand">
					<?php art_theme_render_header_zone_items( array( 'logo' ), $settings, 'desktop' ); ?>
					<?php if ( art_theme_header_has_visible_brand_identity( $settings ) ) : ?>
					<div class="art-theme-site-header__brand-identity">
						<?php art_theme_render_header_zone_items( array( 'title', 'tagline' ), $settings, 'desktop' ); ?>
					</div>
					<?php endif; ?>
				</div>
				<div class="art-theme-site-header__nav">
					<?php art_theme_render_header_zone_items( array( 'menu' ), $settings, 'desktop' ); ?>
				</div>
				<?php if ( $has_button ) : ?>
				<div class="art-theme-site-header__actions">
					<?php art_theme_render_header_zone_items( array( 'button' ), $settings, 'desktop' ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $needs_panel ) : ?>
		<div class="art-theme-site-header__mobile-bar">
			<div class="art-theme-site-header__mobile-brand">
				<?php art_theme_render_header_mobile_brand( $mobile_brand, $settings ); ?>
			</div>
			<div class="art-theme-site-header__mobile-actions">
				<button
					type="button"
					class="art-theme-site-header__toggle"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				>
					<span class="art-theme-site-header__toggle-icon" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Меню', 'art-theme' ); ?></span>
				</button>
			</div>
		</div>

		<div id="<?php echo esc_attr( $panel_id ); ?>" class="art-theme-site-header__panel" hidden>
			<?php art_theme_render_header_items( $order, $settings, 'mobile-panel' ); ?>
		</div>
		<?php else : ?>
		<div class="art-theme-site-header__mobile-inline">
			<?php art_theme_render_header_items( $order, $settings, 'mobile-inline' ); ?>
		</div>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Render items that belong to a header zone.
 *
 * @param array<int, string>   $zone_items Zone item slugs.
 * @param array<string, mixed> $settings   Header settings.
 * @param string               $context    Render context.
 */
function art_theme_render_header_zone_items( $zone_items, $settings, $context ) {
	foreach ( Art_Theme_Header_Settings::LAYOUT_ITEMS as $item ) {
		if ( ! in_array( $item, $zone_items, true ) ) {
			continue;
		}

		art_theme_render_header_item( $item, $settings, $context );
	}
}

/**
 * Whether title or tagline is visible in the header brand block.
 *
 * @param array<string, mixed> $settings Header settings.
 * @return bool
 */
function art_theme_header_has_visible_brand_identity( $settings ) {
	foreach ( array( 'title', 'tagline' ) as $item ) {
		if ( Art_Theme_Header_Settings::is_item_enabled( $item, $settings ) && Art_Theme_Header_Settings::can_render_item( $item, $settings ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render logo and site identity for the mobile header bar.
 *
 * @param array<int, string>   $brand_items Brand item slugs.
 * @param array<string, mixed> $settings    Header settings.
 */
function art_theme_render_header_mobile_brand( $brand_items, $settings ) {
	foreach ( $brand_items as $item ) {
		if ( 'logo' === $item ) {
			art_theme_render_header_item( $item, $settings, 'mobile-brand' );
		}
	}

	$identity_items = array();

	foreach ( $brand_items as $item ) {
		if ( in_array( $item, array( 'title', 'tagline' ), true ) ) {
			$identity_items[] = $item;
		}
	}

	if ( empty( $identity_items ) ) {
		return;
	}

	echo '<div class="art-theme-site-header__brand-identity">';

	foreach ( $identity_items as $item ) {
		art_theme_render_header_item( $item, $settings, 'mobile-brand' );
	}

	echo '</div>';
}

/**
 * Items shown beside the mobile menu toggle (logo and/or title).
 *
 * @param array<int, string>   $order    Visible item order.
 * @param array<string, mixed> $settings Header settings.
 * @return array<int, string>
 */
function art_theme_get_header_mobile_brand_items( $order, $settings = null ) {
	$brand = array();

	foreach ( $order as $item ) {
		if ( in_array( $item, array( 'logo', 'title', 'tagline' ), true ) && ! in_array( $item, $brand, true ) ) {
			$brand[] = $item;
		}
	}

	return $brand;
}

/**
 * Items rendered inside the mobile dropdown panel.
 *
 * @param array<int, string>   $order    Visible item order.
 * @param array<string, mixed> $settings Header settings.
 * @return array<int, string>
 */
function art_theme_get_header_panel_items( $order, $settings = null ) {
	if ( null === $settings ) {
		$settings = Art_Theme_Header_Settings::get();
	}

	$brand = art_theme_get_header_mobile_brand_items( $order, $settings );
	$panel = array();

	foreach ( $order as $item ) {
		if ( ! in_array( $item, $brand, true ) && ! in_array( $item, $panel, true ) ) {
			$panel[] = $item;
		}
	}

	return $panel;
}

/**
 * Render header items in configured order.
 *
 * @param array<int, string>   $order    Visible item slugs.
 * @param array<string, mixed> $settings Header settings.
 * @param string               $context  Render context.
 */
function art_theme_render_header_items( $order, $settings, $context ) {
	$skip = array();

	if ( 'mobile-panel' === $context ) {
		$skip = art_theme_get_header_mobile_brand_items( $order, $settings );
	}

	foreach ( $order as $item ) {
		if ( in_array( $item, $skip, true ) ) {
			continue;
		}

		art_theme_render_header_item( $item, $settings, $context );
	}
}

/**
 * Render one header item.
 *
 * @param string               $item     Item slug.
 * @param array<string, mixed> $settings Header settings.
 * @param string               $context  Render context.
 */
function art_theme_render_header_item( $item, $settings, $context ) {
	$item = sanitize_key( $item );

	if ( ! Art_Theme_Header_Settings::is_item_enabled( $item, $settings ) || ! Art_Theme_Header_Settings::can_render_item( $item, $settings ) ) {
		return;
	}

	echo '<div class="art-theme-site-header__item art-theme-site-header__item--' . esc_attr( $item ) . ' art-theme-site-header__item--' . esc_attr( sanitize_html_class( $context ) ) . '">';

	switch ( $item ) {
		case 'logo':
			if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
				the_custom_logo();
			}
			break;

		case 'title':
			echo '<a class="art-theme-site-header__title" href="' . esc_url( home_url( '/' ) ) . '">';
			echo esc_html( get_bloginfo( 'name' ) );
			echo '</a>';
			break;

		case 'tagline':
			echo '<p class="art-theme-site-header__tagline">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
			break;

		case 'menu':
			if ( ! has_nav_menu( Art_Theme_Header_Settings::MENU_LOCATION ) ) {
				break;
			}

			wp_nav_menu(
				array(
					'theme_location' => Art_Theme_Header_Settings::MENU_LOCATION,
					'container'      => false,
					'menu_class'     => 'art-theme-site-header__menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			break;

		case 'button':
			$label        = (string) ( $settings['button_label'] ?? '' );
			$url          = (string) ( $settings['button_url'] ?? '' );
			$open_new_tab = ! empty( $settings['button_open_new_tab'] );

			if ( '' !== trim( $label ) && '' !== trim( $url ) ) {
				if ( $open_new_tab ) {
					printf(
						'<a class="art-theme-site-header__button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
						esc_url( $url ),
						esc_html( $label )
					);
				} else {
					printf(
						'<a class="art-theme-site-header__button" href="%1$s">%2$s</a>',
						esc_url( $url ),
						esc_html( $label )
					);
				}
			}
			break;
	}

	echo '</div>';
}
