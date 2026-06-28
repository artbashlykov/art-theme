<?php
/**
 * 404 page rendering.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the theme 404 page content.
 */
function art_theme_render_not_found_content() {
	$settings = Art_Theme_Not_Found_Settings::get();
	?>
	<section class="art-theme-not-found">
		<?php if ( '' !== trim( $settings['error_code'] ) ) : ?>
			<div class="art-theme-not-found__code"><?php echo esc_html( $settings['error_code'] ); ?></div>
		<?php endif; ?>

		<?php if ( '' !== trim( $settings['error_message'] ) ) : ?>
			<p class="art-theme-not-found__message"><?php echo esc_html( $settings['error_message'] ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== trim( $settings['button_label'] ) ) : ?>
			<p class="art-theme-not-found__actions">
				<a class="art-theme-not-found__button" href="<?php echo esc_url( $settings['button_url'] ); ?>">
					<?php echo esc_html( $settings['button_label'] ); ?>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php
}
