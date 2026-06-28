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

		if ( class_exists( 'Art_Starter_Icons' ) ) {
			$icon  = Art_Starter_Icons::get( $network );
			$label = is_array( $icon ) ? (string) ( $icon['label'] ?? '' ) : '';

			return Art_Starter_Icons::render_or_letter( $network, $label, $wrapper_class );
		}

		$fallback = self::get_fallback_icons();

		if ( isset( $fallback[ $network ] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from internal registry.
			return '<span class="' . esc_attr( $wrapper_class ) . '" aria-hidden="true">' . $fallback[ $network ] . '</span>';
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
			'vk'        => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.785 16.241s.336-.039.508-.233c.158-.172.154-.497.154-.753 0-.41.006-8.335.006-8.335s0-.223.056-.342c.056-.119.158-.204.28-.255.221-.097 1.185-1.097 1.185-1.097s.098-.073.098-.17 0-.119-.098-.17l-1.652-.006s-.374-.006-.547.113c-.113.079-.19.259-.19.259s-.34.904-.792 1.672c-.956 1.583-1.339 1.666-1.495 1.666-.113 0-.224-.079-.224-.602v-3.104c0-.511.015-.813-.224-.98-.171-.117-.491-.154-1.286-.164-.985-.015-1.821.006-2.292.196-.158.068-.28.22-.205.229.092.012.301.056.411.205.143.196.137.638.137.638s.083 2.451-.19 2.754c-.19.226-.563.237-.563.237H5.252s-.855-.012-1.012.393c-.073.196-.056 1.512-.056 1.512h2.667s.399-.006.564.226c.393.533.393 1.581.393 1.581s.025 2.335-.184 2.626c-.178.246-.508.207-.508.207H4.587s-1.215-.037-1.711-1.067L2.59 10.44s-.263-.56.184-.823c.363-.203.854-.135.854-.135l3.014-.019s.22-.037.38.073c.16.111.258.369.258.369s.491 1.243 1.148 2.363c.694 1.161 1.557 2.162 1.557 2.162s.135.111.307.073c.184-.037 0-1.056 0-2.066 0-1.111-.079-1.581-.282-1.8-.215-.233-.614-.307-.614-.307s.491-.037 1.262-.056c.971-.025 1.697.019 2.188.215.331.135.589.429.779.834.196.411.147 1.808.147 1.808s.086 2.521-.196 2.86c-.196.227-.564.171-.564.171h-2.03s-1.826.115-4.083-1.659c-1.48-1.237-3.21-5.041-3.21-5.041s-.301-.632.209-.97c.363-.227 1.619-1.056 1.619-1.056s.122-.073.196-.233c.062-.135.037-.331.037-.331V6.926s-.006-.749.564-.97c.429-.171 1.52-.331 3.345-.429 1.263-.073 2.621-.056 2.621-.056h.627s.467-.031.712.147c.171.135.258.429.258.429s.049 1.243-.098 2.066c-.122.737-.429 1.193-.429 1.193s-.037.171 0 .282c.092.288.429.374.429.374s1.544.515 3.295 2.004c1.006.883 1.773 1.974 1.773 1.974s.129.22.037.442c-.062.147-.282.196-.282.196l-2.056.013z"/></svg>',
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
		);
	}
}
