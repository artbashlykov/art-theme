<?php
/**
 * Site header builder settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header layout and visibility settings.
 */
class Art_Theme_Header_Settings {

	const OPTION_KEY = 'art_theme_header_settings';

	const TEMPLATE_CLASSIC  = 'classic';
	const TEMPLATE_FLOATING = 'floating';
	const TEMPLATE_MINIMAL  = 'minimal';

	const WIDTH_MODE_FIXED = 'fixed';
	const WIDTH_MODE_FULL  = 'full';

	const HEADER_FIXED_EXTRA_INLINE_DEFAULT = 0;
	const HEADER_TOP_SPACING_DEFAULT    = 32;
	const HEADER_BOTTOM_SPACING_DEFAULT = 32;

	const HEADER_MENU_CREATE_VALUE = 'create';

	const MENU_LOCATION = 'primary';

	const DB_VERSION = 2;

	const DB_VERSION_OPTION = 'art_theme_header_menu_db_version';

	/**
	 * Header element slugs.
	 */
	const LAYOUT_ITEMS = array( 'logo', 'title', 'tagline', 'menu', 'button' );

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_migrate_header_menu_location' ), 20 );
		add_action( 'after_switch_theme', array( __CLASS__, 'maybe_migrate_header_menu_location' ) );
		add_action( 'customize_save_after', array( __CLASS__, 'normalize_after_customizer_save' ) );
		add_filter( 'theme_mod_nav_menu_locations', array( __CLASS__, 'filter_nav_menu_locations_for_preview' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'header_template'       => self::TEMPLATE_CLASSIC,
			'header_top_spacing'    => self::HEADER_TOP_SPACING_DEFAULT,
			'header_bottom_spacing' => self::HEADER_BOTTOM_SPACING_DEFAULT,
			'header_width_mode'          => self::WIDTH_MODE_FIXED,
			'header_fixed_extra_inline'  => self::HEADER_FIXED_EXTRA_INLINE_DEFAULT,
			'header_border_radius'       => 10,
			'show_logo'     => true,
			'show_title'    => true,
			'show_tagline'  => false,
			'show_menu'     => true,
			'show_button'   => false,
			'button_label'         => __( 'Связаться', 'art-theme' ),
			'button_url'           => '',
			'button_open_new_tab'  => true,
			'header_menu_id' => 0,
		);
	}

	/**
	 * Get merged settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		static $cached = null;

		if ( null !== $cached ) {
			return $cached;
		}

		$stored   = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = wp_parse_args( $stored, $defaults );

		if ( is_array( $stored ) && isset( $stored['header_width'] ) && ! array_key_exists( 'header_width_mode', $stored ) ) {
			$settings['header_width_mode'] = (int) $stored['header_width'] > 0 ? self::WIDTH_MODE_FIXED : self::WIDTH_MODE_FULL;
		}

		$settings['header_template']       = self::sanitize_header_template( $settings['header_template'] ?? $defaults['header_template'] );
		$settings['header_top_spacing']    = self::sanitize_header_top_spacing( $settings['header_top_spacing'] ?? $defaults['header_top_spacing'] );
		$settings['header_bottom_spacing'] = self::sanitize_header_bottom_spacing( $settings['header_bottom_spacing'] ?? $defaults['header_bottom_spacing'] );
		$settings['header_width_mode']          = self::sanitize_header_width_mode( $settings['header_width_mode'] ?? $defaults['header_width_mode'] );
		$settings['header_fixed_extra_inline']  = self::sanitize_header_fixed_extra_inline( $settings['header_fixed_extra_inline'] ?? $defaults['header_fixed_extra_inline'] );
		$settings['header_border_radius']       = self::sanitize_header_border_radius( $settings['header_border_radius'] ?? $defaults['header_border_radius'] );
		$settings['button_label'] = sanitize_text_field( (string) ( $settings['button_label'] ?? $defaults['button_label'] ) );
		$settings['button_url']   = esc_url_raw( (string) ( $settings['button_url'] ?? '' ) );
		$settings['header_menu_id'] = self::sanitize_header_menu_id( $settings['header_menu_id'] ?? $defaults['header_menu_id'] );

		if ( '' === trim( $settings['button_label'] ) ) {
			$settings['button_label'] = $defaults['button_label'];
		}

		foreach ( array( 'show_logo', 'show_title', 'show_tagline', 'show_menu', 'show_button', 'button_open_new_tab' ) as $bool_key ) {
			if ( ! array_key_exists( $bool_key, $stored ) ) {
				$settings[ $bool_key ] = $defaults[ $bool_key ];
			} else {
				$settings[ $bool_key ] = wp_validate_boolean( $stored[ $bool_key ] );
			}
		}

		$cached = $settings;

		return $cached;
	}

	/**
	 * Header template choices for Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function get_template_choices() {
		return array(
			self::TEMPLATE_CLASSIC  => __( 'Классический минимализм', 'art-theme' ),
			self::TEMPLATE_FLOATING => __( 'Полупрозрачная плавающая шапка', 'art-theme' ),
			self::TEMPLATE_MINIMAL  => __( 'Почти невидимая', 'art-theme' ),
		);
	}

	/**
	 * Sanitize header template slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_header_template( $value ) {
		$value = sanitize_key( (string) $value );

		if ( array_key_exists( $value, self::get_template_choices() ) ) {
			return $value;
		}

		return self::TEMPLATE_CLASSIC;
	}

	/**
	 * Header width mode choices for Customizer.
	 *
	 * @return array<string, string>
	 */
	public static function get_width_mode_choices() {
		return array(
			self::WIDTH_MODE_FIXED => __( 'Фиксированная', 'art-theme' ),
			self::WIDTH_MODE_FULL  => __( 'На всю ширину', 'art-theme' ),
		);
	}

	/**
	 * Sanitize header width mode slug.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_header_width_mode( $value ) {
		$value = sanitize_key( (string) $value );

		if ( 'full-inset' === $value ) {
			return self::WIDTH_MODE_FULL;
		}

		if ( array_key_exists( $value, self::get_width_mode_choices() ) ) {
			return $value;
		}

		return self::WIDTH_MODE_FIXED;
	}

	/**
	 * Content column width in pixels for the current view.
	 *
	 * @return int
	 */
	public static function get_content_width_px() {
		if ( art_theme_is_blog_archive_view() ) {
			return (int) Art_Theme_Blog_Settings::get()['blog_width'];
		}

		if ( art_theme_is_single_post_view() ) {
			return (int) Art_Theme_Single_Settings::get()['post_width'];
		}

		if ( art_theme_is_page_template_view() ) {
			return (int) Art_Theme_Page_Settings::get_for_singular()['page_width'];
		}

		return 850;
	}

	/**
	 * Body/CSS class suffix for the active width mode.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return string
	 */
	public static function get_width_mode_class( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		return 'art-theme-site-header--width-' . self::sanitize_header_width_mode( $settings['header_width_mode'] ?? self::WIDTH_MODE_FIXED );
	}

	/**
	 * Sanitize top spacing above the site header.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_header_top_spacing( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * Sanitize bottom spacing below the site header.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_header_bottom_spacing( $value ) {
		return max( 0, min( 160, (int) $value ) );
	}

	/**
	 * Sanitize extra horizontal padding for fixed-width header mode.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_header_fixed_extra_inline( $value ) {
		return max( 0, min( 120, (int) $value ) );
	}

	/**
	 * Extra horizontal padding for fixed-width header mode in pixels.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return int
	 */
	public static function get_fixed_extra_inline_px( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		return self::sanitize_header_fixed_extra_inline( $settings['header_fixed_extra_inline'] ?? self::HEADER_FIXED_EXTRA_INLINE_DEFAULT );
	}

	/**
	 * Sanitize header corner radius in pixels.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_header_border_radius( $value ) {
		return max( 0, min( 999, (int) $value ) );
	}

	/**
	 * Nav menu choices for the header Customizer control.
	 *
	 * @return array<int|string, string>
	 */
	public static function get_header_menu_choices() {
		$choices  = array();
		$menus    = wp_get_nav_menus();
		$has_menu = ! is_wp_error( $menus ) && ! empty( $menus );

		if ( $has_menu ) {
			foreach ( $menus as $menu ) {
				if ( empty( $menu->term_id ) ) {
					continue;
				}

				$choices[ (int) $menu->term_id ] = $menu->name;
			}
		}

		$choices[ self::HEADER_MENU_CREATE_VALUE ] = __( 'Создать меню', 'art-theme' );

		return $choices;
	}

	/**
	 * Nav menu locations stored in theme mods (no theme_mod_nav_menu_locations filter).
	 *
	 * Used internally to avoid recursion when the Customizer preview filter is active.
	 *
	 * @return array<string, int>
	 */
	private static function get_raw_nav_menu_locations() {
		$stylesheet = get_stylesheet();

		if ( '' === $stylesheet ) {
			return array();
		}

		$mods = get_option( 'theme_mods_' . $stylesheet );

		if ( ! is_array( $mods ) || ! isset( $mods['nav_menu_locations'] ) ) {
			return array();
		}

		$locations = $mods['nav_menu_locations'];

		return is_array( $locations ) ? $locations : array();
	}

	/**
	 * Menu ID assigned to the Primary Menu theme location.
	 *
	 * @return int
	 */
	public static function get_primary_location_menu_id() {
		$locations = self::get_raw_nav_menu_locations();

		if ( empty( $locations[ self::MENU_LOCATION ] ) ) {
			return 0;
		}

		return self::sanitize_header_menu_id( $locations[ self::MENU_LOCATION ] );
	}

	/**
	 * Menu ID shown in Customizer (option value or Primary Menu location).
	 *
	 * @return int
	 */
	public static function get_effective_header_menu_id_for_ui() {
		$stored   = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$menu_id = self::sanitize_header_menu_id( $stored['header_menu_id'] ?? $defaults['header_menu_id'] );

		if ( $menu_id > 0 ) {
			return $menu_id;
		}

		return self::get_primary_location_menu_id();
	}

	/**
	 * Assign a menu to the header theme location.
	 *
	 * @param int $menu_id Menu term ID.
	 */
	public static function set_primary_menu_location( $menu_id ) {
		$menu_id = self::sanitize_header_menu_id( $menu_id );

		if ( $menu_id <= 0 ) {
			return;
		}

		$locations = self::get_raw_nav_menu_locations();

		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		$locations[ self::MENU_LOCATION ] = $menu_id;

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Remove the menu assignment from the header theme location.
	 */
	public static function clear_primary_menu_location() {
		$locations = self::get_raw_nav_menu_locations();

		if ( ! is_array( $locations ) || ! array_key_exists( self::MENU_LOCATION, $locations ) ) {
			return;
		}

		unset( $locations[ self::MENU_LOCATION ] );

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Sync header_menu_id option value to the Primary Menu theme location.
	 *
	 * @param int $menu_id Menu term ID. Values <= 0 do not clear the location.
	 */
	public static function sync_primary_menu_location( $menu_id ) {
		$menu_id = self::sanitize_header_menu_id( $menu_id );

		if ( $menu_id > 0 ) {
			self::set_primary_menu_location( $menu_id );
		}
	}

	/**
	 * One-time migration: header_menu_id option ↔ Primary Menu location.
	 */
	public static function migrate_header_menu_location() {
		$defaults        = self::get_defaults();
		$stored          = get_option( self::OPTION_KEY, array() );
		$changed_option  = false;

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings        = wp_parse_args( $stored, $defaults );
		$header_menu_id  = self::sanitize_header_menu_id( $settings['header_menu_id'] ?? 0 );
		$primary_menu_id = self::get_primary_location_menu_id();

		if ( $header_menu_id > 0 ) {
			if ( $primary_menu_id !== $header_menu_id ) {
				self::set_primary_menu_location( $header_menu_id );
			}
		} elseif ( $primary_menu_id > 0 ) {
			$settings['header_menu_id'] = $primary_menu_id;
			$changed_option             = true;
		}

		if ( $header_menu_id > 0 && ! wp_get_nav_menu_object( $header_menu_id ) ) {
			$settings['header_menu_id'] = 0;
			$changed_option             = true;

			if ( $primary_menu_id === $header_menu_id ) {
				self::clear_primary_menu_location();
			}
		} elseif ( $primary_menu_id > 0 && ! wp_get_nav_menu_object( $primary_menu_id ) ) {
			self::clear_primary_menu_location();
		}

		if ( $changed_option ) {
			update_option( self::OPTION_KEY, self::sanitize( wp_parse_args( $settings, $defaults ) ) );
		}
	}

	/**
	 * Run header menu migration once per theme version.
	 */
	public static function maybe_migrate_header_menu_location() {
		if ( (int) get_option( self::DB_VERSION_OPTION, 0 ) >= self::DB_VERSION ) {
			return;
		}

		self::migrate_header_menu_location();

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Override Primary Menu location in Customizer preview from header_menu_id.
	 *
	 * @param mixed $locations Nav menu locations theme mod.
	 * @return array<string, int>
	 */
	public static function filter_nav_menu_locations_for_preview( $locations ) {
		if ( ! is_customize_preview() ) {
			return is_array( $locations ) ? $locations : array();
		}

		if ( ! is_array( $locations ) ) {
			$locations = array();
		}

		$menu_id = self::get_preview_header_menu_id();

		if ( $menu_id > 0 ) {
			$locations[ self::MENU_LOCATION ] = $menu_id;
		}

		return $locations;
	}

	/**
	 * Header menu ID from Customizer preview state.
	 *
	 * @return int
	 */
	public static function get_preview_header_menu_id() {
		$stored   = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();

		if ( ! is_array( $stored ) ) {
			return 0;
		}

		$menu_id = self::sanitize_header_menu_id( $stored['header_menu_id'] ?? $defaults['header_menu_id'] );

		if ( $menu_id > 0 ) {
			return $menu_id;
		}

		return self::get_primary_location_menu_id();
	}

	/**
	 * Sanitize selected header menu ID.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_header_menu_id( $value ) {
		if ( self::HEADER_MENU_CREATE_VALUE === (string) $value ) {
			return 0;
		}

		$menu_id = (int) $value;

		if ( $menu_id <= 0 ) {
			return 0;
		}

		$menu = wp_get_nav_menu_object( $menu_id );

		if ( ! $menu || is_wp_error( $menu ) ) {
			return 0;
		}

		return (int) $menu->term_id;
	}

	/**
	 * Whether the configured header menu has items to render.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return bool
	 */
	public static function header_menu_is_available( $settings = null ) {
		unset( $settings );

		if ( ! has_nav_menu( self::MENU_LOCATION ) ) {
			return false;
		}

		$menu_id = self::get_primary_location_menu_id();

		if ( $menu_id <= 0 ) {
			return false;
		}

		$items = wp_get_nav_menu_items( $menu_id );

		return is_array( $items ) && ! empty( $items );
	}

	/**
	 * Whether a header item is enabled in settings.
	 *
	 * @param string               $item     Item slug.
	 * @param array<string, mixed> $settings Header settings.
	 * @return bool
	 */
	public static function is_item_enabled( $item, $settings ) {
		$item = sanitize_key( $item );

		$map = array(
			'logo'    => 'show_logo',
			'title'   => 'show_title',
			'tagline' => 'show_tagline',
			'menu'    => 'show_menu',
			'button'  => 'show_button',
		);

		if ( ! isset( $map[ $item ] ) ) {
			return false;
		}

		return ! empty( $settings[ $map[ $item ] ] );
	}

	/**
	 * Ordered list of enabled header items.
	 *
	 * @param array<string, mixed>|null $settings Optional settings.
	 * @return array<int, string>
	 */
	public static function get_visible_order( $settings = null ) {
		if ( null === $settings ) {
			$settings = self::get();
		}

		$visible = array();

		foreach ( self::LAYOUT_ITEMS as $item ) {
			if ( self::is_item_enabled( $item, $settings ) && self::can_render_item( $item, $settings ) ) {
				$visible[] = $item;
			}
		}

		return $visible;
	}

	/**
	 * Whether an enabled item has content to render.
	 *
	 * @param string               $item     Item slug.
	 * @param array<string, mixed> $settings Header settings.
	 * @return bool
	 */
	public static function can_render_item( $item, $settings ) {
		switch ( $item ) {
			case 'logo':
				return (bool) get_theme_mod( 'custom_logo' );
			case 'title':
				return '' !== get_bloginfo( 'name', 'display' );
			case 'tagline':
				return '' !== get_bloginfo( 'description', 'display' );
			case 'menu':
				return self::header_menu_is_available( $settings );
			case 'button':
				return '' !== trim( (string) ( $settings['button_label'] ?? '' ) ) && '' !== trim( (string) ( $settings['button_url'] ?? '' ) );
		}

		return false;
	}

	/**
	 * Sanitize saved settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		$defaults = self::get_defaults();
		$existing = wp_parse_args( get_option( self::OPTION_KEY, array() ), $defaults );
		$merged   = wp_parse_args( $input, $existing );

		$checkboxes = array( 'show_logo', 'show_title', 'show_tagline', 'show_menu', 'show_button', 'button_open_new_tab' );

		foreach ( $checkboxes as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$merged[ $key ] = ! empty( $input[ $key ] );
			}
		}

		return array(
			'header_template'       => self::sanitize_header_template( $merged['header_template'] ?? $defaults['header_template'] ),
			'header_top_spacing'    => self::sanitize_header_top_spacing( $merged['header_top_spacing'] ?? $defaults['header_top_spacing'] ),
			'header_bottom_spacing' => self::sanitize_header_bottom_spacing( $merged['header_bottom_spacing'] ?? $defaults['header_bottom_spacing'] ),
			'header_width_mode'          => self::sanitize_header_width_mode( $merged['header_width_mode'] ?? $defaults['header_width_mode'] ),
			'header_fixed_extra_inline'  => self::sanitize_header_fixed_extra_inline( $merged['header_fixed_extra_inline'] ?? $defaults['header_fixed_extra_inline'] ),
			'header_border_radius'       => self::sanitize_header_border_radius( $merged['header_border_radius'] ?? $defaults['header_border_radius'] ),
			'show_logo'    => ! empty( $merged['show_logo'] ),
			'show_title'   => ! empty( $merged['show_title'] ),
			'show_tagline' => ! empty( $merged['show_tagline'] ),
			'show_menu'    => ! empty( $merged['show_menu'] ),
			'show_button'  => ! empty( $merged['show_button'] ),
			'button_label'        => sanitize_text_field( (string) ( $merged['button_label'] ?? $defaults['button_label'] ) ),
			'button_url'          => esc_url_raw( (string) ( $merged['button_url'] ?? '' ) ),
			'button_open_new_tab' => ! empty( $merged['button_open_new_tab'] ),
			'header_menu_id'      => self::sanitize_header_menu_id( $merged['header_menu_id'] ?? $defaults['header_menu_id'] ),
		);
	}

	/**
	 * Merge and sanitize option after Customizer save.
	 */
	public static function normalize_after_customizer_save() {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		unset( $stored['header_order'] );

		$sanitized      = self::sanitize( wp_parse_args( $stored, self::get_defaults() ) );
		$header_menu_id = (int) ( $sanitized['header_menu_id'] ?? 0 );

		if ( $header_menu_id > 0 ) {
			self::sync_primary_menu_location( $header_menu_id );
		} else {
			$primary_menu_id = self::get_primary_location_menu_id();

			if ( $primary_menu_id > 0 ) {
				$sanitized['header_menu_id'] = $primary_menu_id;
			}
		}

		update_option( self::OPTION_KEY, $sanitized );
	}
}
