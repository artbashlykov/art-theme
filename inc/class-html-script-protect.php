<?php
/**
 * Protect inline <script> in post content from the_content filters.
 *
 * WordPress may encode & as &#038; / &amp; inside scripts (breaking && and similar).
 * Scripts are stashed after early filters, then restored at the end of the_content.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Art_Theme_Html_Script_Protect
 */
class Art_Theme_Html_Script_Protect {

	/**
	 * Scripts parked while remaining the_content filters run.
	 *
	 * @var string[]
	 */
	private static $stashed_scripts = array();

	/**
	 * Register hooks.
	 */
	public static function init() {
		// After do_blocks (9) / wptexturize (10) / do_shortcode (11).
		add_filter( 'the_content', array( __CLASS__, 'stash_scripts' ), 12 );
		add_filter( 'the_content', array( __CLASS__, 'restore_scripts' ), 99999 );
	}

	/**
	 * Whether protection should run for this request.
	 *
	 * @return bool
	 */
	private static function should_run() {
		if ( is_admin() || wp_doing_ajax() ) {
			return false;
		}

		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return false;
		}

		return true;
	}

	/**
	 * Comment token used while a script is parked outside the content string.
	 *
	 * @param int $index Script index.
	 * @return string
	 */
	private static function placeholder( $index ) {
		return '<!--art-theme-protected-script:' . (int) $index . '-->';
	}

	/**
	 * Decode ampersand entities that break JavaScript operators.
	 *
	 * @param string $script Raw script tag markup.
	 * @return string
	 */
	private static function normalize_ampersands( $script ) {
		$script = (string) $script;

		if ( '' === $script ) {
			return '';
		}

		return str_replace(
			array( '&#038;', '&#38;', '&amp;' ),
			'&',
			$script
		);
	}

	/**
	 * Extract <script> tags and replace them with placeholders.
	 *
	 * @param string $html HTML that may contain scripts.
	 * @return array{0:string,1:string[]} HTML without scripts, list of raw script tags.
	 */
	private static function extract_scripts( $html ) {
		$html    = (string) $html;
		$scripts = array();

		if ( '' === $html || false === stripos( $html, '<script' ) ) {
			return array( $html, $scripts );
		}

		$replaced = preg_replace_callback(
			'/<script\b[^>]*>[\s\S]*?<\/script>/i',
			static function ( $matches ) use ( &$scripts ) {
				$index     = count( $scripts );
				$scripts[] = self::normalize_ampersands( $matches[0] );

				return self::placeholder( $index );
			},
			$html
		);

		if ( ! is_string( $replaced ) ) {
			return array( $html, array() );
		}

		return array( $replaced, $scripts );
	}

	/**
	 * Pull inline scripts out of the_content after early filters.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function stash_scripts( $content ) {
		self::$stashed_scripts = array();

		if ( ! self::should_run() ) {
			return $content;
		}

		if ( false === stripos( (string) $content, '<script' ) ) {
			return $content;
		}

		list( $content, $scripts ) = self::extract_scripts( (string) $content );

		self::$stashed_scripts = $scripts;

		return $content;
	}

	/**
	 * Put protected scripts back after remaining the_content filters.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function restore_scripts( $content ) {
		$content = (string) $content;

		if ( empty( self::$stashed_scripts ) ) {
			// Safety net: decode entities even if stash was skipped.
			if ( self::should_run() && false !== stripos( $content, '<script' ) ) {
				$content = preg_replace_callback(
					'/<script\b[^>]*>[\s\S]*?<\/script>/i',
					static function ( $matches ) {
						return self::normalize_ampersands( $matches[0] );
					},
					$content
				);
			}

			return is_string( $content ) ? $content : '';
		}

		foreach ( self::$stashed_scripts as $index => $script_tag ) {
			$placeholder = self::placeholder( $index );
			$normalized  = self::normalize_ampersands( $script_tag );

			if ( false !== strpos( $content, $placeholder ) ) {
				$content = str_replace( $placeholder, $normalized, $content );
				continue;
			}

			// Filters may insert a space inside the HTML comment.
			$pattern = '/<!--\s*art-theme-protected-script:' . (int) $index . '\s*-->/';
			$content = preg_replace( $pattern, $normalized, $content, 1 );
		}

		self::$stashed_scripts = array();

		return is_string( $content ) ? $content : '';
	}
}
