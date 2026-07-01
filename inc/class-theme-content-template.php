<?php
/**
 * Shared boxed content template defaults for singular views.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Unified "Контентный блок" defaults for posts, pages, and CPT singular views.
 */
class Art_Theme_Content_Template {

	const TEMPLATE_VARIANT_BOXED      = 'boxed';
	const TEMPLATE_VARIANT_FULL_WIDTH = 'full-width';

	const TEMPLATE_VARIANTS = array(
		self::TEMPLATE_VARIANT_BOXED,
		self::TEMPLATE_VARIANT_FULL_WIDTH,
	);

	const CONTENT_WIDTH_DEFAULT         = 850;
	const BOXED_BORDER_RADIUS_DEFAULT     = 10;
	const BOXED_SHADOW_DEFAULT          = 'medium';
	const BOXED_PADDING_BLOCK_DEFAULT   = 32;
	const BOXED_PADDING_INLINE_DEFAULT    = 24;

	/**
	 * Shared boxed layout defaults (without content-width key name).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_boxed_layout_defaults() {
		return array(
			'template_variant'     => self::TEMPLATE_VARIANT_BOXED,
			'boxed_border_radius'  => self::BOXED_BORDER_RADIUS_DEFAULT,
			'boxed_shadow'         => self::BOXED_SHADOW_DEFAULT,
			'boxed_padding_block'  => self::BOXED_PADDING_BLOCK_DEFAULT,
			'boxed_padding_inline' => self::BOXED_PADDING_INLINE_DEFAULT,
		);
	}

	/**
	 * Template variant labels for Customizer and admin UI.
	 *
	 * @return array<string, string>
	 */
	public static function get_template_variant_choices() {
		return array(
			self::TEMPLATE_VARIANT_BOXED      => __( 'Контентный блок', 'art-theme' ),
			self::TEMPLATE_VARIANT_FULL_WIDTH => __( 'Фон на всю ширину', 'art-theme' ),
		);
	}

	/**
	 * Per-item template override choices (block editor sidebar).
	 *
	 * @return array<string, string>
	 */
	public static function get_template_override_choices() {
		return array(
			'default'                         => __( 'По умолчанию', 'art-theme' ),
			self::TEMPLATE_VARIANT_BOXED      => __( 'Контентный блок', 'art-theme' ),
			self::TEMPLATE_VARIANT_FULL_WIDTH => __( 'Фон на всю ширину', 'art-theme' ),
		);
	}

	/**
	 * Sanitize template variant slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_template_variant( $value ) {
		$value = sanitize_key( (string) $value );

		if ( in_array( $value, self::TEMPLATE_VARIANTS, true ) ) {
			return $value;
		}

		return self::TEMPLATE_VARIANT_BOXED;
	}

	/**
	 * Sanitize per-item template override slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_template_override( $value ) {
		$value = sanitize_key( (string) $value );

		if ( array_key_exists( $value, self::get_template_override_choices() ) ) {
			return $value;
		}

		return 'default';
	}

	/**
	 * Whether settings use the full-width background template.
	 *
	 * @param array<string, mixed> $settings Settings array with template_variant.
	 * @return bool
	 */
	public static function is_full_width_template( $settings ) {
		return self::TEMPLATE_VARIANT_FULL_WIDTH === self::sanitize_template_variant( $settings['template_variant'] ?? self::TEMPLATE_VARIANT_BOXED );
	}
}
