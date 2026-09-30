<?php
/**
 * Fresh-install defaults that must not rewrite existing sites.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed theme defaults only for brand-new installs.
 */
class Art_Theme_Install_Defaults {

	/**
	 * Option that stores the completed seed schema version.
	 */
	const OPTION_KEY = 'art_theme_install_defaults_version';

	/**
	 * Current seed schema (bump when adding another one-time defaults migration).
	 *
	 * 1 = floating header/footer + «Главная» button
	 * 2 = blog/single cards hide category by default
	 */
	const VERSION = 2;

	/**
	 * Register hooks.
	 */
	public static function init() {
		// Run before header menu migration so its version flag is not mistaken for an existing install.
		add_action( 'after_switch_theme', array( __CLASS__, 'maybe_seed' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_seed' ), 5 );
	}

	/**
	 * Run once per schema version: lock legacy values on existing installs, seed new defaults on fresh ones.
	 */
	public static function maybe_seed() {
		$current = (int) get_option( self::OPTION_KEY, 0 );

		if ( $current >= self::VERSION ) {
			return;
		}

		if ( 0 === $current && ! self::has_existing_theme_footprint() ) {
			self::seed_fresh_install();
		} else {
			if ( 0 === $current ) {
				self::lock_legacy_header_footer();
			}

			if ( $current < 2 ) {
				if ( self::should_apply_fresh_category_defaults() ) {
					self::seed_fresh_blog_single_if_missing();
				} else {
					self::lock_legacy_blog_category();
					self::lock_legacy_single_category();
				}
			}
		}

		update_option( self::OPTION_KEY, self::VERSION, true );
	}

	/**
	 * Fresh v1 installs already have floating header + «Главная»; keep category hidden for them too.
	 *
	 * @return bool
	 */
	private static function should_apply_fresh_category_defaults() {
		$header = get_option( Art_Theme_Header_Settings::OPTION_KEY, false );

		if ( ! is_array( $header ) ) {
			return false;
		}

		if ( Art_Theme_Header_Settings::TEMPLATE_FLOATING !== ( $header['header_template'] ?? '' ) ) {
			return false;
		}

		if ( 'Главная' !== trim( (string) ( $header['button_label'] ?? '' ) ) ) {
			return false;
		}

		return empty( $header['button_open_new_tab'] );
	}

	/**
	 * Seed blog/single options for installs that already received fresh header defaults in v1.
	 */
	private static function seed_fresh_blog_single_if_missing() {
		if ( false === get_option( Art_Theme_Blog_Settings::OPTION_KEY, false ) ) {
			add_option(
				Art_Theme_Blog_Settings::OPTION_KEY,
				Art_Theme_Blog_Settings::sanitize( Art_Theme_Blog_Settings::get_defaults() ),
				'',
				false
			);
		}

		if ( false === get_option( Art_Theme_Single_Settings::OPTION_KEY, false ) ) {
			add_option(
				Art_Theme_Single_Settings::OPTION_KEY,
				Art_Theme_Single_Settings::sanitize( Art_Theme_Single_Settings::get_defaults() ),
				'',
				false
			);
		}
	}

	/**
	 * Whether this site already used ART Theme settings (treat as existing install).
	 *
	 * Intentionally ignores menu-migration flags and empty theme_mods — those appear on
	 * brand-new activations and must not force legacy defaults.
	 *
	 * @return bool
	 */
	private static function has_existing_theme_footprint() {
		$option_keys = array(
			Art_Theme_Header_Settings::OPTION_KEY,
			Art_Theme_Footer_Settings::OPTION_KEY,
			Art_Theme_Appearance_Settings::OPTION_KEY,
			Art_Theme_Blog_Settings::OPTION_KEY,
			Art_Theme_Single_Settings::OPTION_KEY,
			Art_Theme_Page_Settings::OPTION_KEY,
			Art_Theme_Not_Found_Settings::OPTION_KEY,
		);

		foreach ( $option_keys as $option_key ) {
			if ( false !== get_option( $option_key, false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Persist previous header/footer defaults for sites that relied on implicit classic / «Личный кабинет».
	 */
	private static function lock_legacy_header_footer() {
		$header = get_option( Art_Theme_Header_Settings::OPTION_KEY, false );

		if ( false === $header ) {
			add_option(
				Art_Theme_Header_Settings::OPTION_KEY,
				Art_Theme_Header_Settings::sanitize( Art_Theme_Header_Settings::get_legacy_install_defaults() ),
				'',
				false
			);
		} else {
			self::ensure_keys(
				Art_Theme_Header_Settings::OPTION_KEY,
				is_array( $header ) ? $header : array(),
				Art_Theme_Header_Settings::get_legacy_install_defaults(),
				array( 'header_template', 'button_label', 'button_url', 'button_open_new_tab' ),
				array( 'Art_Theme_Header_Settings', 'sanitize' )
			);
		}

		$footer = get_option( Art_Theme_Footer_Settings::OPTION_KEY, false );

		if ( false === $footer ) {
			add_option(
				Art_Theme_Footer_Settings::OPTION_KEY,
				Art_Theme_Footer_Settings::sanitize( Art_Theme_Footer_Settings::get_legacy_install_defaults() ),
				'',
				false
			);
		} else {
			self::ensure_keys(
				Art_Theme_Footer_Settings::OPTION_KEY,
				is_array( $footer ) ? $footer : array(),
				Art_Theme_Footer_Settings::get_legacy_install_defaults(),
				array( 'footer_template' ),
				array( 'Art_Theme_Footer_Settings', 'sanitize' )
			);
		}
	}

	/**
	 * Keep blog cards showing category on existing sites that relied on the old default.
	 */
	private static function lock_legacy_blog_category() {
		$blog = get_option( Art_Theme_Blog_Settings::OPTION_KEY, false );

		if ( false === $blog ) {
			add_option(
				Art_Theme_Blog_Settings::OPTION_KEY,
				Art_Theme_Blog_Settings::sanitize( Art_Theme_Blog_Settings::get_legacy_install_defaults() ),
				'',
				false
			);
			return;
		}

		self::ensure_keys(
			Art_Theme_Blog_Settings::OPTION_KEY,
			is_array( $blog ) ? $blog : array(),
			Art_Theme_Blog_Settings::get_legacy_install_defaults(),
			array( 'show_category' ),
			array( 'Art_Theme_Blog_Settings', 'sanitize' )
		);
	}

	/**
	 * Single posts already defaulted to hidden category; lock only if the key is missing
	 * and a legacy plural key is not already enabling it.
	 */
	private static function lock_legacy_single_category() {
		$single = get_option( Art_Theme_Single_Settings::OPTION_KEY, false );

		if ( false === $single ) {
			// Existing sites without saved single settings already used show_category=false.
			add_option(
				Art_Theme_Single_Settings::OPTION_KEY,
				Art_Theme_Single_Settings::sanitize( Art_Theme_Single_Settings::get_defaults() ),
				'',
				false
			);
			return;
		}

		if ( ! is_array( $single ) ) {
			return;
		}

		if ( array_key_exists( 'show_category', $single ) || ! empty( $single['show_categories'] ) ) {
			return;
		}

		$single['show_category'] = false;
		update_option( Art_Theme_Single_Settings::OPTION_KEY, Art_Theme_Single_Settings::sanitize( $single ) );
	}

	/**
	 * Fill missing keys from a legacy defaults map without overwriting saved values.
	 *
	 * @param string               $option_key Option name.
	 * @param array<string, mixed> $stored     Stored option array.
	 * @param array<string, mixed> $legacy     Legacy defaults.
	 * @param array<int, string>   $keys       Keys to backfill.
	 * @param callable             $sanitize   Sanitize callback.
	 */
	private static function ensure_keys( $option_key, array $stored, array $legacy, array $keys, $sanitize ) {
		$changed = false;

		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $stored ) ) {
				$stored[ $key ] = $legacy[ $key ];
				$changed        = true;
			}
		}

		if ( $changed ) {
			update_option( $option_key, call_user_func( $sanitize, $stored ) );
		}
	}

	/**
	 * Write current (new-site) defaults for a brand-new theme install.
	 */
	private static function seed_fresh_install() {
		add_option(
			Art_Theme_Header_Settings::OPTION_KEY,
			Art_Theme_Header_Settings::sanitize( Art_Theme_Header_Settings::get_defaults() ),
			'',
			false
		);

		add_option(
			Art_Theme_Footer_Settings::OPTION_KEY,
			Art_Theme_Footer_Settings::sanitize( Art_Theme_Footer_Settings::get_defaults() ),
			'',
			false
		);

		add_option(
			Art_Theme_Blog_Settings::OPTION_KEY,
			Art_Theme_Blog_Settings::sanitize( Art_Theme_Blog_Settings::get_defaults() ),
			'',
			false
		);

		add_option(
			Art_Theme_Single_Settings::OPTION_KEY,
			Art_Theme_Single_Settings::sanitize( Art_Theme_Single_Settings::get_defaults() ),
			'',
			false
		);
	}
}
