<?php
/**
 * Social network icons — uses ART Starter registry when available.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Social icon helpers for the site footer.
 */
class Art_Theme_Social_Icons {

	/**
	 * Footer icons loaded from assets/icons/social/ (slug => filename).
	 *
	 * @var array<string, string>
	 */
	private static $social_icon_files = array(
		'vk' => 'vk.svg',
	);

	/**
	 * Available social networks for the footer picker.
	 *
	 * @return array<string, string>
	 */
	public static function get_networks() {
		if ( class_exists( 'Art_Starter_Icons' ) ) {
			return Art_Starter_Icons::get_social_networks();
		}

		return self::get_fallback_networks();
	}

	/**
	 * Render a social icon or letter fallback.
	 *
	 * @param string $network       Network slug.
	 * @param string $wrapper_class Wrapper CSS class.
	 * @return string
	 */
	public static function render_icon( $network, $wrapper_class = 'art-theme-site-footer__social-icon' ) {
		$network = self::sanitize_network( $network );

		if ( '' === $network ) {
			return '';
		}

		$theme_icon = self::get_theme_icon_svg( $network );
		if ( '' !== $theme_icon ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from internal registry.
			return '<span class="' . esc_attr( $wrapper_class ) . '" aria-hidden="true">' . $theme_icon . '</span>';
		}

		if ( class_exists( 'Art_Starter_Icons' ) ) {
			$icon  = Art_Starter_Icons::get( $network );
			$label = is_array( $icon ) ? (string) ( $icon['label'] ?? '' ) : '';

			return Art_Starter_Icons::render_or_letter( $network, $label, $wrapper_class );
		}

		$label = self::get_fallback_networks()[ $network ] ?? $network;

		return '<span class="' . esc_attr( trim( $wrapper_class . ' art-theme-site-footer__social-icon--letter' ) ) . '">' . esc_html( mb_substr( $label, 0, 1 ) ) . '</span>';
	}

	/**
	 * Sanitize a social network slug.
	 *
	 * @param mixed $network Raw value.
	 * @return string
	 */
	public static function sanitize_network( $network ) {
		$network = sanitize_key( (string) $network );

		if ( '' === $network ) {
			return '';
		}

		if ( class_exists( 'Art_Starter_Icons' ) ) {
			return Art_Starter_Icons::sanitize_slug( $network, array( Art_Starter_Icons::CATEGORY_SOCIAL ), false );
		}

		return array_key_exists( $network, self::get_fallback_networks() ) ? $network : '';
	}

	/**
	 * Build a social link href.
	 *
	 * @param array<string, mixed> $item Social item.
	 * @return string
	 */
	public static function get_href( $item ) {
		if ( ! is_array( $item ) ) {
			return '';
		}

		$network = self::sanitize_network( $item['network'] ?? '' );
		$url     = trim( (string) ( $item['url'] ?? '' ) );

		if ( '' === $network || '' === $url ) {
			return '';
		}

		if ( 'mail' === $network ) {
			if ( preg_match( '#^mailto:#i', $url ) ) {
				return $url;
			}

			$email = sanitize_email( $url );

			return $email ? 'mailto:' . $email : '';
		}

		return esc_url_raw( $url );
	}

	/**
	 * Sanitize a social item URL for storage.
	 *
	 * @param string $network Network slug.
	 * @param mixed  $url     Raw URL or email.
	 * @return string
	 */
	public static function sanitize_item_url( $network, $url ) {
		$network = self::sanitize_network( $network );
		$url     = trim( (string) $url );

		if ( '' === $network || '' === $url ) {
			return '';
		}

		if ( 'mail' === $network ) {
			if ( preg_match( '#^mailto:#i', $url ) ) {
				$email = sanitize_email( (string) preg_replace( '#^mailto:#i', '', $url ) );

				return $email ?: '';
			}

			$email = sanitize_email( $url );

			return $email ?: '';
		}

		return esc_url_raw( $url );
	}

	/**
	 * Theme-owned SVG for the footer (file or inline registry).
	 *
	 * @param string $network Network slug.
	 * @return string
	 */
	private static function get_theme_icon_svg( $network ) {
		if ( isset( self::$social_icon_files[ $network ] ) ) {
			$file_svg = self::load_social_icon_file( self::$social_icon_files[ $network ] );
			if ( '' !== $file_svg ) {
				return $file_svg;
			}
		}

		$icons = self::get_fallback_icons();

		return $icons[ $network ] ?? '';
	}

	/**
	 * Load SVG icon from assets/icons/social/.
	 *
	 * @param string $filename SVG filename.
	 * @return string
	 */
	private static function load_social_icon_file( $filename ) {
		$filename = basename( (string) $filename );
		if ( ! preg_match( '/\.svg$/i', $filename ) ) {
			return '';
		}

		$path = ART_THEME_DIR . '/assets/icons/social/' . $filename;
		if ( ! is_readable( $path ) ) {
			return '';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
		$svg = (string) file_get_contents( $path );
		if ( '' === trim( $svg ) ) {
			return '';
		}

		$svg = preg_replace( '/<\?xml.*?\?>\s*/is', '', $svg );
		$svg = preg_replace( '/<!DOCTYPE.*?>\s*/is', '', $svg );
		$svg = preg_replace( '/<!--.*?-->\s*/is', '', $svg );
		$svg = preg_replace( '/<title>.*?<\/title>\s*/is', '', $svg );
		$svg = preg_replace( '/\s(width|height)="[^"]*"/i', '', $svg );

		if ( ! preg_match( '/aria-hidden/i', $svg ) ) {
			$svg = preg_replace( '/<svg/i', '<svg aria-hidden="true"', $svg, 1 );
		}

		return trim( (string) $svg );
	}

	/**
	 * @return array<string, string>
	 */
	private static function get_fallback_networks() {
		return array(
			'telegram'  => 'Telegram',
			'vk'        => __( 'ВКонтакте', 'art-theme' ),
			'youtube'   => 'YouTube',
			'instagram' => 'Instagram',
			'mail'      => 'Email',
			'whatsapp'  => 'WhatsApp',
			'facebook'  => 'Facebook',
			'zen'       => __( 'Яндекс Дзен', 'art-theme' ),
			'tiktok'    => 'TikTok',
			'x'         => 'X (Twitter)',
			'linkedin'  => 'LinkedIn',
			'ok'        => __( 'Одноклассники', 'art-theme' ),
		);
	}

	/**
	 * @return array<string, string>
	 */
	private static function get_fallback_icons() {
		return array(
			'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.8 4.2 2.7 11.1c-1.2.5-1.2 1.2-.2 1.5l4.9 1.5 1.9 5.8c.2.7.6.9 1.1.9.4 0 .6-.2.9-.7l2.7-2.6 4.8 3.5c.9.5 1.5.2 1.7-1.1L23.5 5.5c.3-1.3-.5-1.9-1.7-1.3Z"/></svg>',
			'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18 5 12 5 12 5s-6 0-7.8.4a2.5 2.5 0 0 0-1.8 1.8C2 9 2 12 2 12s0 3 .4 4.8a2.5 2.5 0 0 0 1.8 1.8C6 19 12 19 12 19s6 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.8.4-4.8.4-4.8s0-3-.4-4.8ZM10 15.5V8.5l5.5 3.5L10 15.5Z"/></svg>',
			'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="4"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>',
			'mail'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
			'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>',
			'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 4.99 3.66 9.13 8.44 9.93v-6.99H7.9v-2.94h2.54V9.41c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.94h-2.34v6.99c4.78-.8 8.44-4.94 8.44-9.93z"/></svg>',
			'zen'       => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.48 2.04a.74.74 0 0 1 .74-.04l7.78 4.49a.74.74 0 0 1 .37.64v8.98a.74.74 0 0 1-.37.64l-7.78 4.49a.74.74 0 0 1-.74-.04.74.74 0 0 1-.33-.6V2.64a.74.74 0 0 1 .33-.6zm.37 2.17-6.42 3.7 6.42 3.7 6.42-3.7-6.42-3.7zm-1.11 5.18v7.4l5.68 3.27v-7.4L10.74 9.39z"/></svg>',
			'tiktok'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.6 5.82s.51.5 0 0A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6 0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64 0 3.33 2.76 5.7 5.69 5.7 3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3a4.1 4.1 0 0 1-1.04-.14z"/></svg>',
			'x'         => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.3 3h3.2l-7 8.01 8.2 9.99h-6.4l-5.01-6.55-5.73 6.55H1.35l7.48-8.55L1 3h6.57l4.53 5.99L17.3 3zm-1.12 16.2h1.77L7.03 4.74H5.14l11.04 14.46z"/></svg>',
			'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5C4.98 4.88 3.86 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.22 8.25h4.56V23H.22V8.25zM8.09 8.25h4.37v2.01h.06c.61-1.16 2.1-2.38 4.32-2.38 4.62 0 5.47 3.04 5.47 6.99V23h-4.56v-7.1c0-1.69-.03-3.87-2.36-3.87-2.36 0-2.72 1.84-2.72 3.75V23H8.09V8.25z"/></svg>',
			'ok'        => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 4.2c2.1 0 3.8 1.7 3.8 3.8S14.1 11.8 12 11.8 8.2 10.1 8.2 8 9.9 4.2 12 4.2zm0 10.3c3.1 0 5.9 1.6 7.5 4.1l-1.7 1.1a8.2 8.2 0 0 0-11.6 0L5.5 18.6c1.6-2.5 4.4-4.1 7.5-4.1zm-4.1 1.9c.8 0 1.4.6 1.4 1.4s-.6 1.4-1.4 1.4-1.4-.6-1.4-1.4.6-1.4 1.4-1.4zm8.2 0c.8 0 1.4.6 1.4 1.4s-.6 1.4-1.4 1.4-1.4-.6-1.4-1.4.6-1.4 1.4-1.4z"/></svg>',
			'max'       => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5.1l-3.55 3.55a1 1 0 0 1-1.7-.7V16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm2.2 5.4h1.45l1.55 2.55 1.55-2.55H14v5.2h-1.55v-3.1l-1.7 2.8h-.95l-1.7-2.8v3.1H7.2V9.4zm8.1 0H18v5.2h-1.55V9.4z"/></svg>',
		);
	}
}
