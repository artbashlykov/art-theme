<?php
/**
 * Customizer control — footer socials and custom links repeaters.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Repeater list for footer socials or custom links.
 */
class Art_Theme_Customize_Footer_Repeater_Control extends WP_Customize_Control {

	/**
	 * Control type slug.
	 *
	 * @var string
	 */
	public $type = 'art_theme_footer_repeater';

	/**
	 * Repeater mode: socials|links.
	 *
	 * @var string
	 */
	public $repeater_type = 'socials';

	/**
	 * Render control markup.
	 */
	protected function render_content() {
		if ( empty( $this->label ) ) {
			return;
		}

		$value = $this->value();

		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			$value   = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		if ( 'links' === $this->repeater_type ) {
			$value = Art_Theme_Footer_Settings::sanitize_custom_links( $value );
		} else {
			$value = Art_Theme_Footer_Settings::sanitize_socials( $value );
		}

		$max_items = 'links' === $this->repeater_type
			? Art_Theme_Footer_Settings::MAX_LINK_ITEMS
			: Art_Theme_Footer_Settings::MAX_SOCIAL_ITEMS;
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<div
			class="art-theme-footer-repeater"
			data-repeater-type="<?php echo esc_attr( $this->repeater_type ); ?>"
			data-setting-id="<?php echo esc_attr( $this->setting->id ); ?>"
			data-max-items="<?php echo esc_attr( (string) $max_items ); ?>"
		>
			<div class="art-theme-footer-repeater__list"></div>
			<button type="button" class="button art-theme-footer-repeater__add"><?php esc_html_e( 'Добавить', 'art-theme' ); ?></button>
			<input type="hidden" class="art-theme-footer-repeater__value" value="<?php echo esc_attr( wp_json_encode( $value ) ); ?>" />
		</div>
		<?php
	}
}
