<?php
/**
 * Theme Customizer — blog settings.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Customizer sections and controls.
 */
class Art_Theme_Customizer {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'customize_register', array( __CLASS__, 'register' ) );
		add_action( 'customize_register', array( __CLASS__, 'reorder_sections' ), 999 );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'enqueue_controls_assets' ) );
		add_action( 'customize_preview_init', array( __CLASS__, 'enqueue_preview_assets' ) );
		add_action( 'customize_preview_init', array( __CLASS__, 'hide_admin_bar_in_preview' ) );
		add_action( 'customize_controls_init', array( __CLASS__, 'prefer_blog_preview_from_admin' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'filter_admin_bar_customize_link' ), 100 );
		add_action( 'admin_menu', array( __CLASS__, 'filter_appearance_customize_submenu' ), 999 );
	}

	/**
	 * Hide the admin bar inside the Customizer preview iframe.
	 */
	public static function hide_admin_bar_in_preview() {
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * When Customizer is opened from wp-admin on the front page (e.g. ART Starter),
	 * switch the preview to the posts page so theme settings are visible.
	 */
	public static function prefer_blog_preview_from_admin() {
		global $wp_customize;

		if ( ! $wp_customize instanceof WP_Customize_Manager ) {
			return;
		}

		$blog_url = self::get_blog_customizer_preview_url();
		$home_url = home_url( '/' );

		if ( '' === $blog_url || self::urls_match( $blog_url, $home_url ) ) {
			return;
		}

		$preview_url = $wp_customize->get_preview_url();
		$return_url  = $wp_customize->get_return_url();

		if ( ! self::urls_match( $preview_url, $home_url ) ) {
			return;
		}

		// Only redirect when returning to wp-admin (Appearance → Customize), not when
		// the user opened Customizer from the front page via the admin bar.
		if ( ! is_string( $return_url ) || false === strpos( $return_url, '/wp-admin/' ) ) {
			return;
		}

		$wp_customize->set_preview_url( $blog_url );
	}

	/**
	 * Compare two URLs ignoring trailing slashes.
	 *
	 * @param string $left  First URL.
	 * @param string $right Second URL.
	 * @return bool
	 */
	private static function urls_match( $left, $right ) {
		return untrailingslashit( (string) $left ) === untrailingslashit( (string) $right );
	}

	/**
	 * Enqueue Customizer preview assets.
	 */
	public static function enqueue_preview_assets() {
		$script_path = ART_THEME_DIR . '/assets/js/customize-preview.js';

		wp_enqueue_script(
			'art-theme-customize-preview',
			ART_THEME_URL . '/assets/js/customize-preview.js',
			array( 'customize-preview' ),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : ART_THEME_VERSION,
			true
		);

		$defaults = Art_Theme_Appearance_Settings::get_defaults();

		wp_localize_script(
			'art-theme-customize-preview',
			'artThemeCustomizePreview',
			array(
				'copyrightTextSettingId' => Art_Theme_Footer_Settings::OPTION_KEY . '[copyright_text]',
				'showCopyrightSettingId' => Art_Theme_Footer_Settings::OPTION_KEY . '[show_copyright]',
				'yearShortcode'          => Art_Theme_Footer_Settings::COPYRIGHT_YEAR_SHORTCODE,
				'currentYear'            => (string) date_i18n( 'Y' ),
				'siteName'               => (string) get_bloginfo( 'name', 'display' ),
				'appearance'             => array(
					'colors' => array(
						array(
							'id'      => Art_Theme_Appearance_Settings::OPTION_KEY . '[color_canvas]',
							'var'     => '--art-theme-canvas',
							'default' => $defaults['color_canvas'],
						),
						array(
							'id'      => Art_Theme_Appearance_Settings::OPTION_KEY . '[color_surface]',
							'var'     => '--art-theme-surface',
							'default' => $defaults['color_surface'],
						),
						array(
							'id'      => Art_Theme_Appearance_Settings::OPTION_KEY . '[color_text]',
							'var'     => '--art-theme-fg',
							'default' => $defaults['color_text'],
						),
						array(
							'id'      => Art_Theme_Appearance_Settings::OPTION_KEY . '[color_accent]',
							'var'     => '--art-theme-accent',
							'default' => $defaults['color_accent'],
						),
					),
				),
			)
		);
	}

	/**
	 * Enqueue Customizer control assets.
	 */
	public static function enqueue_controls_assets() {
		$style_path       = ART_THEME_DIR . '/assets/css/customize-controls.css';
		$script_path      = ART_THEME_DIR . '/assets/js/customize-reset.js';
		$order_path       = ART_THEME_DIR . '/assets/js/layout-order.js';
		$single_path      = ART_THEME_DIR . '/assets/js/single-template-settings.js';
		$header_menu_path = ART_THEME_DIR . '/assets/js/header-menu-select.js';
		$footer_path      = ART_THEME_DIR . '/assets/js/footer-customize.js';

		wp_enqueue_style(
			'art-theme-customize-controls',
			ART_THEME_URL . '/assets/css/customize-controls.css',
			array( 'customize-controls' ),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : ART_THEME_VERSION
		);

		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script(
			'art-theme-layout-order',
			ART_THEME_URL . '/assets/js/layout-order.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			file_exists( $order_path ) ? (string) filemtime( $order_path ) : ART_THEME_VERSION,
			true
		);

		wp_enqueue_script(
			'art-theme-single-template-settings',
			ART_THEME_URL . '/assets/js/single-template-settings.js',
			array( 'jquery', 'customize-controls' ),
			file_exists( $single_path ) ? (string) filemtime( $single_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-single-template-settings',
			'artThemeSingleTemplateSettings',
			array(
				'templateVariantSettingId'     => Art_Theme_Single_Settings::OPTION_KEY . '[template_variant]',
				'pageTemplateVariantSettingId' => Art_Theme_Page_Settings::OPTION_KEY . '[template_variant]',
				'headerWidthModeSettingId'     => Art_Theme_Header_Settings::OPTION_KEY . '[header_width_mode]',
				'headerWidthModeFixed'         => Art_Theme_Header_Settings::WIDTH_MODE_FIXED,
				'footerWidthModeSettingId'     => Art_Theme_Footer_Settings::OPTION_KEY . '[footer_width_mode]',
				'footerWidthModeFixed'         => Art_Theme_Footer_Settings::WIDTH_MODE_FIXED,
			)
		);

		wp_enqueue_script(
			'art-theme-customize-reset',
			ART_THEME_URL . '/assets/js/customize-reset.js',
			array( 'customize-controls', 'jquery' ),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-customize-reset',
			'artThemeCustomizeReset',
			array(
				'label'    => __( 'Сбросить', 'art-theme' ),
				'confirm'  => __( 'Сбросить это поле к значению по умолчанию?', 'art-theme' ),
				'defaults' => self::get_setting_defaults_map(),
			)
		);

		wp_enqueue_script(
			'art-theme-header-menu-select',
			ART_THEME_URL . '/assets/js/header-menu-select.js',
			array( 'jquery', 'customize-controls', 'customize-nav-menus' ),
			file_exists( $header_menu_path ) ? (string) filemtime( $header_menu_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-header-menu-select',
			'artThemeHeaderMenuSelect',
			array(
				'controlId'    => 'art_theme_header_menu_id',
				'createValue'  => Art_Theme_Header_Settings::HEADER_MENU_CREATE_VALUE,
				'menuLocation' => Art_Theme_Header_Settings::MENU_LOCATION,
			)
		);

		wp_enqueue_script(
			'art-theme-footer-customize',
			ART_THEME_URL . '/assets/js/footer-customize.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			file_exists( $footer_path ) ? (string) filemtime( $footer_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-footer-customize',
			'artThemeFooterCustomize',
			array(
				'socialNetworks'       => Art_Theme_Social_Icons::get_networks(),
				'removeLabel'          => __( 'Удалить', 'art-theme' ),
				'reorderLabel'         => __( 'Перетащить', 'art-theme' ),
				'linkLabelPlaceholder' => __( 'Текст ссылки', 'art-theme' ),
				'openNewTabLabel'      => __( 'Открывать в новой вкладке', 'art-theme' ),
			)
		);

		$not_found_path = ART_THEME_DIR . '/assets/js/not-found-customize.js';

		wp_enqueue_script(
			'art-theme-not-found-customize',
			ART_THEME_URL . '/assets/js/not-found-customize.js',
			array( 'jquery', 'customize-controls' ),
			file_exists( $not_found_path ) ? (string) filemtime( $not_found_path ) : ART_THEME_VERSION,
			true
		);

		wp_localize_script(
			'art-theme-not-found-customize',
			'artThemeNotFoundCustomize',
			array(
				'previewUrl' => Art_Theme_Not_Found_Settings::get_preview_url(),
			)
		);

		$groups_path = ART_THEME_DIR . '/assets/js/customize-groups.js';

		wp_enqueue_script(
			'art-theme-customize-groups',
			ART_THEME_URL . '/assets/js/customize-groups.js',
			array( 'jquery', 'customize-controls' ),
			file_exists( $groups_path ) ? (string) filemtime( $groups_path ) : ART_THEME_VERSION,
			true
		);
	}

	/**
	 * Default values for Customizer settings (setting ID => value).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_setting_defaults_map() {
		$map = array();

		foreach ( self::build_defaults_map_for_option( Art_Theme_Blog_Settings::OPTION_KEY, Art_Theme_Blog_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Single_Settings::OPTION_KEY, Art_Theme_Single_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Page_Settings::OPTION_KEY, Art_Theme_Page_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Header_Settings::OPTION_KEY, Art_Theme_Header_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Footer_Settings::OPTION_KEY, Art_Theme_Footer_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Not_Found_Settings::OPTION_KEY, Art_Theme_Not_Found_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		foreach ( self::build_defaults_map_for_option( Art_Theme_Appearance_Settings::OPTION_KEY, Art_Theme_Appearance_Settings::get_defaults() ) as $setting_id => $value ) {
			$map[ $setting_id ] = $value;
		}

		$map[ Art_Theme_Not_Found_Settings::OPTION_KEY . '[button_url]' ] = Art_Theme_Not_Found_Settings::get_default_button_url();

		return $map;
	}

	/**
	 * Build Customizer setting IDs map from option defaults.
	 *
	 * @param string               $option_key Option name.
	 * @param array<string, mixed> $defaults   Default values.
	 * @return array<string, mixed>
	 */
	private static function build_defaults_map_for_option( $option_key, $defaults ) {
		$map = array();

		foreach ( $defaults as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( 'meta_order' === $key ) {
					$map[ $option_key . '[' . $key . ']' ] = $value;
					continue;
				}

				foreach ( $value as $index => $item_value ) {
					$map[ $option_key . '[' . $key . '][' . $index . ']' ] = $item_value;
				}
				continue;
			}

			if ( is_bool( $value ) ) {
				$value = $value ? 1 : 0;
			}

			$map[ $option_key . '[' . $key . ']' ] = $value;
		}

		return $map;
	}

	/**
	 * Register Customizer settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public static function register( $wp_customize ) {
		require_once ART_THEME_DIR . '/inc/customizer/class-layout-order-control.php';
		require_once ART_THEME_DIR . '/inc/customizer/class-customize-group-section.php';

		$wp_customize->register_section_type( 'Art_Theme_Customize_Group_Section' );

		self::register_root_groups( $wp_customize );

		self::register_appearance( $wp_customize );

		$wp_customize->add_panel(
			'art_theme_header',
			array(
				'title'       => __( 'Настройки шапки', 'art-theme' ),
				'description' => __( 'Элементы шапки и их видимость.', 'art-theme' ),
				'priority'    => 100,
			)
		);

		self::register_site_header( $wp_customize );

		$wp_customize->add_panel(
			'art_theme_footer',
			array(
				'title'       => __( 'Настройки подвала', 'art-theme' ),
				'description' => __( 'Структура, оформление и содержимое подвала.', 'art-theme' ),
				'priority'    => 105,
			)
		);

		self::register_site_footer( $wp_customize );

		$wp_customize->add_panel(
			'art_theme_page',
			array(
				'title'       => __( 'Настройка страницы', 'art-theme' ),
				'description' => __( 'Шаблон статических страниц и произвольных типов записей с макетом страницы: вариант оформления, ширина и внутренние отступы.', 'art-theme' ),
				'priority'    => 115,
			)
		);

		self::register_page_template( $wp_customize );

		$wp_customize->add_panel(
			'art_theme_blog',
			array(
				'title'       => __( 'Настройка блога', 'art-theme' ),
				'description' => __( 'Шаблон страницы блога, шапка и карточки записей.', 'art-theme' ),
				'priority'    => 120,
			)
		);

		self::register_blog_template( $wp_customize );
		self::register_blog_header( $wp_customize );
		self::register_blog_card( $wp_customize );

		$wp_customize->add_panel(
			'art_theme_single',
			array(
				'title'       => __( 'Настройка записи', 'art-theme' ),
				'description' => __( 'Шаблон одиночной записи и элементы мета-блока.', 'art-theme' ),
				'priority'    => 130,
			)
		);

		self::register_single_template( $wp_customize );
		self::register_single_meta( $wp_customize );

		self::register_not_found_page( $wp_customize );
	}

	/**
	 * Colors and fonts.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_appearance( $wp_customize ) {
		$option_key = Art_Theme_Appearance_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Appearance_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_appearance',
			array(
				'title'       => __( 'Цвета и шрифты', 'art-theme' ),
				'description' => __( 'Общий вид страниц темы (блог, записи, страницы). На главной ART Starter эти цвета не применяются — у плагина свой шаблон.', 'art-theme' ),
				'priority'    => 45,
			)
		);

		$colors = array(
			'color_canvas'  => __( 'Фон страницы', 'art-theme' ),
			'color_surface' => __( 'Фон контента', 'art-theme' ),
			'color_text'    => __( 'Текст', 'art-theme' ),
			'color_accent'  => __( 'Акцент', 'art-theme' ),
		);

		foreach ( $colors as $key => $label ) {
			$setting_id = $option_key . '[' . $key . ']';

			$wp_customize->add_setting(
				$setting_id,
				array(
					'type'              => 'option',
					'default'           => $defaults[ $key ],
					'transport'         => 'postMessage',
					'sanitize_callback' => array( 'Art_Theme_Appearance_Settings', 'sanitize_color' ),
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'art_theme_appearance_' . $key,
					array(
						'label'    => $label,
						'section'  => 'art_theme_appearance',
						'settings' => $setting_id,
					)
				)
			);
		}

		self::add_select_control(
			$wp_customize,
			$option_key . '[font_pack]',
			'art_theme_appearance_font_pack',
			'art_theme_appearance',
			__( 'Набор шрифтов', 'art-theme' ),
			$defaults['font_pack'],
			Art_Theme_Appearance_Settings::get_font_pack_choices(),
			array( 'Art_Theme_Appearance_Settings', 'sanitize_font_pack' )
		);
	}

	/**
	 * Site header builder settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_site_header( $wp_customize ) {
		self::register_header_template( $wp_customize );
		self::register_header_layout( $wp_customize );
	}

	/**
	 * Header template variant settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_header_template( $wp_customize ) {
		$option_key = Art_Theme_Header_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Header_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_header_template',
			array(
				'title'       => __( 'Шаблон шапки', 'art-theme' ),
				'description' => __( 'Вариант оформления и размеры шапки. Отступы сверху и снизу действуют на всех страницах сайта.', 'art-theme' ),
				'panel'       => 'art_theme_header',
				'priority'    => 10,
			)
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[header_template]',
			'art_theme_header_template_variant',
			'art_theme_header_template',
			__( 'Вариант шаблона', 'art-theme' ),
			$defaults['header_template'],
			Art_Theme_Header_Settings::get_template_choices(),
			array( 'Art_Theme_Header_Settings', 'sanitize_header_template' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[header_top_spacing]',
			'art_theme_header_top_spacing',
			'art_theme_header_template',
			__( 'Отступ сверху (px)', 'art-theme' ),
			(int) $defaults['header_top_spacing'],
			0,
			120,
			array( 'Art_Theme_Header_Settings', 'sanitize_header_top_spacing' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[header_bottom_spacing]',
			'art_theme_header_bottom_spacing',
			'art_theme_header_template',
			__( 'Отступ снизу (px)', 'art-theme' ),
			(int) $defaults['header_bottom_spacing'],
			0,
			160,
			array( 'Art_Theme_Header_Settings', 'sanitize_header_bottom_spacing' )
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[header_width_mode]',
			'art_theme_header_width_mode',
			'art_theme_header_template',
			__( 'Ширина шапки', 'art-theme' ),
			$defaults['header_width_mode'],
			Art_Theme_Header_Settings::get_width_mode_choices(),
			array( 'Art_Theme_Header_Settings', 'sanitize_header_width_mode' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[header_fixed_extra_inline]',
			'art_theme_header_fixed_extra_inline',
			'art_theme_header_template',
			__( 'Доп. отступ слева и справа (px)', 'art-theme' ),
			(int) $defaults['header_fixed_extra_inline'],
			0,
			120,
			array( 'Art_Theme_Header_Settings', 'sanitize_header_fixed_extra_inline' ),
			array(
				'description' => __( 'При фиксированной ширине шапка шире контентной колонки на это значение с каждой стороны.', 'art-theme' ),
				'classes'     => 'art-theme-header-fixed-only',
			)
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[header_border_radius]',
			'art_theme_header_border_radius',
			'art_theme_header_template',
			__( 'Скругление углов (px)', 'art-theme' ),
			(int) $defaults['header_border_radius'],
			0,
			999,
			array( 'Art_Theme_Header_Settings', 'sanitize_header_border_radius' )
		);
	}

	/**
	 * Header element visibility and content settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_header_layout( $wp_customize ) {
		$option_key = Art_Theme_Header_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Header_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_header_layout',
			array(
				'title'       => __( 'Конструктор шапки', 'art-theme' ),
				'description' => __( 'Логотип и название сайта задаются в разделе «Идентичность сайта». Выберите меню для шапки или создайте новое в разделе «Меню».', 'art-theme' ),
				'panel'       => 'art_theme_header',
				'priority'    => 20,
			)
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_logo]',
			'art_theme_header_show_logo',
			'art_theme_header_layout',
			__( 'Показывать логотип', 'art-theme' ),
			! empty( $defaults['show_logo'] )
		);

		$logo_control = $wp_customize->get_control( 'art_theme_header_show_logo' );

		if ( $logo_control ) {
			$logo_control->description = __( 'Сам файл логотипа загружается в разделе «Идентичность сайта».', 'art-theme' );
		}

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_title]',
			'art_theme_header_show_title',
			'art_theme_header_layout',
			__( 'Показывать название', 'art-theme' ),
			! empty( $defaults['show_title'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_tagline]',
			'art_theme_header_show_tagline',
			'art_theme_header_layout',
			__( 'Показывать короткое описание', 'art-theme' ),
			! empty( $defaults['show_tagline'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_menu]',
			'art_theme_header_show_menu',
			'art_theme_header_layout',
			__( 'Показывать меню', 'art-theme' ),
			! empty( $defaults['show_menu'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[menu_collapse_desktop]',
			'art_theme_header_menu_collapse_desktop',
			'art_theme_header_layout',
			__( 'Сворачивать меню в бургер на ПК, если не помещается', 'art-theme' ),
			! empty( $defaults['menu_collapse_desktop'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_button]',
			'art_theme_header_show_button',
			'art_theme_header_layout',
			__( 'Показывать кнопку', 'art-theme' ),
			! empty( $defaults['show_button'] )
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[button_label]',
			'art_theme_header_button_label',
			'art_theme_header_layout',
			__( 'Текст кнопки', 'art-theme' ),
			$defaults['button_label'],
			'sanitize_text_field'
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[button_url]',
			'art_theme_header_button_url',
			'art_theme_header_layout',
			__( 'Ссылка кнопки', 'art-theme' ),
			$defaults['button_url'],
			array( 'Art_Theme_Header_Settings', 'sanitize_button_url' )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[button_open_new_tab]',
			'art_theme_header_button_open_new_tab',
			'art_theme_header_layout',
			__( 'Открывать в новой вкладке', 'art-theme' ),
			$defaults['button_open_new_tab']
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[header_menu_id]',
			'art_theme_header_menu_id',
			'art_theme_header_layout',
			__( 'Выбрать меню', 'art-theme' ),
			Art_Theme_Header_Settings::get_effective_header_menu_id_for_ui(),
			Art_Theme_Header_Settings::get_header_menu_choices(),
			array( 'Art_Theme_Header_Settings', 'sanitize_header_menu_id' )
		);
	}

	/**
	 * Root group headings between core and theme Customizer items.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_root_groups( $wp_customize ) {
		$wp_customize->add_section(
			new Art_Theme_Customize_Group_Section(
				$wp_customize,
				'art_theme_group_general',
				array(
					'title'          => __( 'Общие настройки', 'art-theme' ),
					'priority'       => 35,
					'divider_before' => false,
				)
			)
		);

		$wp_customize->add_section(
			new Art_Theme_Customize_Group_Section(
				$wp_customize,
				'art_theme_group_chrome',
				array(
					'title'          => __( 'Шапка и подвал сайта', 'art-theme' ),
					'priority'       => 95,
					'divider_before' => false,
				)
			)
		);

		$wp_customize->add_section(
			new Art_Theme_Customize_Group_Section(
				$wp_customize,
				'art_theme_group_parts',
				array(
					'title'          => __( 'Внешний вид страниц', 'art-theme' ),
					'priority'       => 110,
					'divider_before' => false,
				)
			)
		);

		$wp_customize->add_section(
			new Art_Theme_Customize_Group_Section(
				$wp_customize,
				'art_theme_group_other',
				array(
					'title'          => __( 'Прочие настройки', 'art-theme' ),
					'priority'       => 195,
					'divider_before' => false,
				)
			)
		);
	}

	/**
	 * Group and reorder root Customizer panels/sections.
	 *
	 * Group headings are real sections with priorities (see register_root_groups).
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public static function reorder_sections( $wp_customize ) {
		// Общие настройки.
		$general_group = $wp_customize->get_section( 'art_theme_group_general' );

		if ( $general_group instanceof WP_Customize_Section ) {
			$general_group->priority = 35;
		}

		$title_tagline = $wp_customize->get_section( 'title_tagline' );

		if ( $title_tagline instanceof WP_Customize_Section ) {
			$title_tagline->title    = __( 'Свойства сайта', 'art-theme' );
			$title_tagline->priority = 40;
		}

		$appearance = $wp_customize->get_section( 'art_theme_appearance' );

		if ( $appearance instanceof WP_Customize_Section ) {
			$appearance->priority = 45;
		}

		$static_front_page = $wp_customize->get_section( 'static_front_page' );

		if ( $static_front_page instanceof WP_Customize_Section ) {
			$static_front_page->title    = __( 'Настройка главной', 'art-theme' );
			$static_front_page->priority = 50;
		}

		$nav_menus = $wp_customize->get_panel( 'nav_menus' );

		if ( $nav_menus instanceof WP_Customize_Panel ) {
			$nav_menus->title    = __( 'Меню', 'art-theme' );
			$nav_menus->priority = 60;
		}

		self::configure_site_icon_control( $wp_customize );

		// Шапка и подвал сайта.
		$chrome_group = $wp_customize->get_section( 'art_theme_group_chrome' );

		if ( $chrome_group instanceof WP_Customize_Section ) {
			$chrome_group->priority = 95;
		}

		$header = $wp_customize->get_panel( 'art_theme_header' );

		if ( $header instanceof WP_Customize_Panel ) {
			$header->title    = __( 'Настройки шапки', 'art-theme' );
			$header->priority = 100;
		}

		$footer = $wp_customize->get_panel( 'art_theme_footer' );

		if ( $footer instanceof WP_Customize_Panel ) {
			$footer->title    = __( 'Настройки подвала', 'art-theme' );
			$footer->priority = 105;
		}

		// Внешний вид страниц.
		$parts_group = $wp_customize->get_section( 'art_theme_group_parts' );

		if ( $parts_group instanceof WP_Customize_Section ) {
			$parts_group->priority = 110;
		}

		$parts_order = array(
			'art_theme_page'      => 115,
			'art_theme_blog'      => 120,
			'art_theme_single'    => 130,
			'art_theme_not_found' => 140,
		);

		foreach ( $parts_order as $id => $priority ) {
			$item = $wp_customize->get_panel( $id );

			if ( ! $item ) {
				$item = $wp_customize->get_section( $id );
			}

			if ( $item ) {
				$item->priority = $priority;
			}
		}

		// Прочие настройки.
		$other_group = $wp_customize->get_section( 'art_theme_group_other' );

		if ( $other_group instanceof WP_Customize_Section ) {
			$other_group->priority = 195;
		}

		$custom_css = $wp_customize->get_section( 'custom_css' );

		if ( $custom_css instanceof WP_Customize_Section ) {
			$custom_css->title    = __( 'CSS стили', 'art-theme' );
			$custom_css->priority = 200;
		}

		$widgets = $wp_customize->get_panel( 'widgets' );

		if ( $widgets instanceof WP_Customize_Panel ) {
			$widgets->priority = 210;
		}
	}

	/**
	 * Lower Site Icon minimum size (core default is 512×512).
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function configure_site_icon_control( $wp_customize ) {
		$site_icon = $wp_customize->get_control( 'site_icon' );

		if ( ! $site_icon instanceof WP_Customize_Site_Icon_Control ) {
			return;
		}

		$site_icon->width  = 128;
		$site_icon->height = 128;
		$site_icon->description = sprintf(
			/* translators: 1: width in pixels, 2: height in pixels */
			'<p>' . __( 'Иконка сайта отображается во вкладках браузера и в закладках. Она должна быть квадратной формы с разрешением не менее <code>%1$s на %2$s</code> пикселей.', 'art-theme' ) . '</p>',
			128,
			128
		);
	}

	/**
	 * 404 page settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_not_found_page( $wp_customize ) {
		$option_key = Art_Theme_Not_Found_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Not_Found_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_not_found',
			array(
				'title'       => __( 'Страница 404', 'art-theme' ),
				'description' => __( 'Текст и кнопка на странице «Страница не найдена». При открытии этого раздела в превью показывается страница 404.', 'art-theme' ),
				'priority'    => 140,
			)
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[error_code]',
			'art_theme_not_found_error_code',
			'art_theme_not_found',
			__( 'Код ошибки', 'art-theme' ),
			$defaults['error_code'],
			'sanitize_text_field'
		);

		self::add_textarea_control(
			$wp_customize,
			$option_key . '[error_message]',
			'art_theme_not_found_error_message',
			'art_theme_not_found',
			__( 'Текст под кодом', 'art-theme' ),
			$defaults['error_message']
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[button_label]',
			'art_theme_not_found_button_label',
			'art_theme_not_found',
			__( 'Текст кнопки', 'art-theme' ),
			$defaults['button_label'],
			'sanitize_text_field'
		);

		$wp_customize->add_setting(
			$option_key . '[button_url]',
			array(
				'type'              => 'option',
				'default'           => $defaults['button_url'],
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			'art_theme_not_found_button_url',
			array(
				'label'       => __( 'Ссылка кнопки', 'art-theme' ),
				'description' => __( 'Пустое значение — ссылка на главную страницу.', 'art-theme' ),
				'section'     => 'art_theme_not_found',
				'settings'    => $option_key . '[button_url]',
				'type'        => 'url',
			)
		);
	}

	/**
	 * Site footer settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_site_footer( $wp_customize ) {
		require_once ART_THEME_DIR . '/inc/customizer/class-footer-repeater-control.php';

		self::register_footer_template( $wp_customize );
		self::register_footer_content( $wp_customize );
	}

	/**
	 * Footer template variant settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_footer_template( $wp_customize ) {
		$option_key = Art_Theme_Footer_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Footer_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_footer_template',
			array(
				'title'       => __( 'Шаблон подвала', 'art-theme' ),
				'description' => __( 'Вариант оформления, структура и размеры подвала.', 'art-theme' ),
				'panel'       => 'art_theme_footer',
				'priority'    => 10,
			)
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[footer_template]',
			'art_theme_footer_template_variant',
			'art_theme_footer_template',
			__( 'Вариант шаблона', 'art-theme' ),
			$defaults['footer_template'],
			Art_Theme_Footer_Settings::get_template_choices(),
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_template' )
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[footer_structure]',
			'art_theme_footer_structure',
			'art_theme_footer_template',
			__( 'Структура подвала', 'art-theme' ),
			$defaults['footer_structure'],
			Art_Theme_Footer_Settings::get_structure_choices(),
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_structure' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[footer_top_spacing]',
			'art_theme_footer_top_spacing',
			'art_theme_footer_template',
			__( 'Отступ сверху (px)', 'art-theme' ),
			(int) $defaults['footer_top_spacing'],
			0,
			120,
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_top_spacing' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[footer_bottom_spacing]',
			'art_theme_footer_bottom_spacing',
			'art_theme_footer_template',
			__( 'Отступ снизу (px)', 'art-theme' ),
			(int) $defaults['footer_bottom_spacing'],
			0,
			120,
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_bottom_spacing' )
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[footer_width_mode]',
			'art_theme_footer_width_mode',
			'art_theme_footer_template',
			__( 'Ширина подвала', 'art-theme' ),
			$defaults['footer_width_mode'],
			Art_Theme_Footer_Settings::get_width_mode_choices(),
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_width_mode' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[footer_fixed_extra_inline]',
			'art_theme_footer_fixed_extra_inline',
			'art_theme_footer_template',
			__( 'Доп. отступ слева и справа (px)', 'art-theme' ),
			(int) $defaults['footer_fixed_extra_inline'],
			0,
			120,
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_fixed_extra_inline' ),
			array(
				'description' => __( 'При фиксированной ширине подвал шире контентной колонки на это значение с каждой стороны.', 'art-theme' ),
				'classes'     => 'art-theme-footer-fixed-only',
			)
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[footer_border_radius]',
			'art_theme_footer_border_radius',
			'art_theme_footer_template',
			__( 'Скругление углов (px)', 'art-theme' ),
			(int) $defaults['footer_border_radius'],
			0,
			999,
			array( 'Art_Theme_Footer_Settings', 'sanitize_footer_border_radius' )
		);
	}

	/**
	 * Footer content settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_footer_content( $wp_customize ) {
		$option_key = Art_Theme_Footer_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Footer_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_footer_content',
			array(
				'title'       => __( 'Содержимое подвала', 'art-theme' ),
				'description' => __( 'Название и описание сайта берутся из раздела «Идентичность сайта». Копирайт выводится как «© год текст»; если поле пустое — подставляется название сайта. В тексте копирайта можно использовать шорткод [current_year] — тогда строка выводится как есть, например: (c) 2018-[current_year].', 'art-theme' ),
				'panel'       => 'art_theme_footer',
				'priority'    => 20,
			)
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_title]',
			'art_theme_footer_show_title',
			'art_theme_footer_content',
			__( 'Показывать название сайта', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_tagline]',
			'art_theme_footer_show_tagline',
			'art_theme_footer_content',
			__( 'Показывать короткое описание', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_socials]',
			'art_theme_footer_show_socials',
			'art_theme_footer_content',
			__( 'Показывать соцсети', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_links]',
			'art_theme_footer_show_links',
			'art_theme_footer_content',
			__( 'Показывать произвольные ссылки', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_copyright]',
			'art_theme_footer_show_copyright',
			'art_theme_footer_content',
			__( 'Показывать копирайт', 'art-theme' ),
			1,
			'postMessage'
		);

		$wp_customize->add_setting(
			$option_key . '[socials]',
			array(
				'type'              => 'option',
				'default'           => $defaults['socials'],
				'transport'         => 'refresh',
				'sanitize_callback' => array( 'Art_Theme_Footer_Settings', 'sanitize_socials' ),
			)
		);

		$wp_customize->add_control(
			new Art_Theme_Customize_Footer_Repeater_Control(
				$wp_customize,
				'art_theme_footer_socials',
				array(
					'label'         => __( 'Соцсети', 'art-theme' ),
					'description'   => __( 'Выберите сеть и укажите ссылку. До 10 элементов. Порядок меняется перетаскиванием.', 'art-theme' ),
					'section'       => 'art_theme_footer_content',
					'settings'      => $option_key . '[socials]',
					'repeater_type' => 'socials',
				)
			)
		);

		$wp_customize->add_setting(
			$option_key . '[custom_links]',
			array(
				'type'              => 'option',
				'default'           => $defaults['custom_links'],
				'transport'         => 'refresh',
				'sanitize_callback' => array( 'Art_Theme_Footer_Settings', 'sanitize_custom_links' ),
			)
		);

		$wp_customize->add_control(
			new Art_Theme_Customize_Footer_Repeater_Control(
				$wp_customize,
				'art_theme_footer_custom_links',
				array(
					'label'         => __( 'Произвольные ссылки', 'art-theme' ),
					'description'   => __( 'Добавьте политику, оферту и другие ссылки. До 10 элементов. Порядок меняется перетаскиванием.', 'art-theme' ),
					'section'       => 'art_theme_footer_content',
					'settings'      => $option_key . '[custom_links]',
					'repeater_type' => 'links',
				)
			)
		);

		$wp_customize->add_setting(
			$option_key . '[copyright_text]',
			array(
				'type'              => 'option',
				'default'           => $defaults['copyright_text'],
				'transport'         => 'postMessage',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'art_theme_footer_copyright_text',
			array(
				'label'       => __( 'Текст копирайта', 'art-theme' ),
				'description' => __( 'Шорткод [current_year] подставляет текущий год. Пример: (c) 2018-[current_year]. Если шорткод указан, строка выводится целиком без автопрефикса «© год».', 'art-theme' ),
				'section'     => 'art_theme_footer_content',
				'settings'    => $option_key . '[copyright_text]',
				'type'        => 'text',
			)
		);
	}

	/**
	 * Blog template settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_blog_template( $wp_customize ) {
		$option_key = Art_Theme_Blog_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Blog_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_blog_template',
			array(
				'title'       => __( 'Шаблон блога', 'art-theme' ),
				'description' => __( 'Сетка карточек и ширина страницы блога. Для предпросмотра откройте страницу блога.', 'art-theme' ),
				'panel'       => 'art_theme_blog',
				'priority'    => 10,
			)
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[blog_width]',
			'art_theme_blog_width',
			'art_theme_blog_template',
			__( 'Ширина страницы блога (px)', 'art-theme' ),
			(int) $defaults['blog_width'],
			600,
			1400
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[blog_columns]',
			'art_theme_blog_columns',
			'art_theme_blog_template',
			__( 'Количество колонок', 'art-theme' ),
			(int) $defaults['blog_columns'],
			1,
			3
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[posts_per_page]',
			'art_theme_blog_posts_per_page',
			'art_theme_blog_template',
			__( 'Количество карточек до пагинации', 'art-theme' ),
			(int) $defaults['posts_per_page'],
			1,
			50
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[blog_bottom_spacing]',
			'art_theme_blog_bottom_spacing',
			'art_theme_blog_template',
			__( 'Нижний отступ (px)', 'art-theme' ),
			(int) $defaults['blog_bottom_spacing'],
			0,
			160,
			array( 'Art_Theme_Blog_Settings', 'sanitize_blog_bottom_spacing' )
		);
	}

	/**
	 * Blog header settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_blog_header( $wp_customize ) {
		$option_key = Art_Theme_Blog_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Blog_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_blog_header',
			array(
				'title'       => __( 'Шапка блога', 'art-theme' ),
				'description' => __( 'Заголовок, описание и фильтр рубрик на странице блога.', 'art-theme' ),
				'panel'       => 'art_theme_blog',
				'priority'    => 20,
			)
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[hide_blog_header]',
			'art_theme_blog_hide_header',
			'art_theme_blog_header',
			__( 'Скрыть шапку блога', 'art-theme' ),
			0
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[hide_category_filter]',
			'art_theme_blog_hide_category_filter',
			'art_theme_blog_header',
			__( 'Скрыть фильтр рубрик', 'art-theme' ),
			0
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[blog_title]',
			'art_theme_blog_title',
			'art_theme_blog_header',
			__( 'Заголовок блога', 'art-theme' ),
			$defaults['blog_title'],
			'sanitize_text_field'
		);

		self::add_textarea_control(
			$wp_customize,
			$option_key . '[blog_description]',
			'art_theme_blog_description',
			'art_theme_blog_header',
			__( 'Описание блога', 'art-theme' ),
			$defaults['blog_description']
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[all_categories_label]',
			'art_theme_blog_all_categories_label',
			'art_theme_blog_header',
			__( 'Текст для «Все категории»', 'art-theme' ),
			$defaults['all_categories_label'],
			'sanitize_text_field'
		);
	}

	/**
	 * Blog card settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_blog_card( $wp_customize ) {
		$option_key = Art_Theme_Blog_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Blog_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_blog_card',
			array(
				'title'       => __( 'Карточка записи', 'art-theme' ),
				'description' => __( 'Элементы карточки записи в списке блога.', 'art-theme' ),
				'panel'       => 'art_theme_blog',
				'priority'    => 30,
			)
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[cover_aspect_ratio]',
			'art_theme_blog_cover_aspect_ratio',
			'art_theme_blog_card',
			__( 'Соотношение сторон обложки', 'art-theme' ),
			$defaults['cover_aspect_ratio'],
			Art_Theme_Blog_Settings::get_cover_aspect_ratio_choices(),
			array( 'Art_Theme_Blog_Settings', 'sanitize_cover_aspect_ratio' )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_thumbnail]',
			'art_theme_blog_show_thumbnail',
			'art_theme_blog_card',
			__( 'Показывать миниатюру', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_category]',
			'art_theme_blog_show_category',
			'art_theme_blog_card',
			__( 'Показывать рубрику', 'art-theme' ),
			! empty( $defaults['show_category'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_date]',
			'art_theme_blog_show_date',
			'art_theme_blog_card',
			__( 'Показывать дату', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_reading_time]',
			'art_theme_blog_show_reading_time',
			'art_theme_blog_card',
			__( 'Показывать время чтения', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_excerpt]',
			'art_theme_blog_show_excerpt',
			'art_theme_blog_card',
			__( 'Показывать отрывок', 'art-theme' ),
			1
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[excerpt_chars]',
			'art_theme_blog_excerpt_chars',
			'art_theme_blog_card',
			__( 'Длина отрывка (символов)', 'art-theme' ),
			(int) $defaults['excerpt_chars'],
			20,
			500,
			array( 'Art_Theme_Blog_Settings', 'sanitize_excerpt_chars' )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_read_button]',
			'art_theme_blog_show_read_button',
			'art_theme_blog_card',
			__( 'Показывать кнопку «Читать далее»', 'art-theme' ),
			1
		);

		self::add_text_control(
			$wp_customize,
			$option_key . '[read_button_text]',
			'art_theme_blog_read_button_text',
			'art_theme_blog_card',
			__( 'Текст кнопки «Читать далее»', 'art-theme' ),
			$defaults['read_button_text'],
			'sanitize_text_field'
		);
	}

	/**
	 * Register page template settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_page_template( $wp_customize ) {
		$option_key = Art_Theme_Page_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Page_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_page_template',
			array(
				'title'       => __( 'Шаблон страницы', 'art-theme' ),
				'description' => __( 'Вариант оформления, ширина и внутренние отступы страниц и произвольных типов записей. По умолчанию — «Контентный блок» с теми же параметрами, что у записей блога.', 'art-theme' ),
				'panel'       => 'art_theme_page',
				'priority'    => 10,
			)
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[template_variant]',
			'art_theme_page_template_variant',
			'art_theme_page_template',
			__( 'Вариант шаблона', 'art-theme' ),
			$defaults['template_variant'],
			Art_Theme_Page_Settings::get_template_variant_choices(),
			array( 'Art_Theme_Page_Settings', 'sanitize_template_variant' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[page_width]',
			'art_theme_page_width',
			'art_theme_page_template',
			__( 'Ширина содержимого контента (px)', 'art-theme' ),
			(int) $defaults['page_width'],
			600,
			1400
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_border_radius]',
			'art_theme_page_boxed_border_radius',
			'art_theme_page_template',
			__( 'Скругление контентного блока (px)', 'art-theme' ),
			(int) $defaults['boxed_border_radius'],
			0,
			32,
			array( 'Art_Theme_Page_Settings', 'sanitize_boxed_border_radius' ),
			array( 'classes' => 'art-theme-page-boxed-only' )
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[boxed_shadow]',
			'art_theme_page_boxed_shadow',
			'art_theme_page_template',
			__( 'Тень контентного блока', 'art-theme' ),
			$defaults['boxed_shadow'],
			Art_Theme_Page_Settings::get_boxed_shadow_choices(),
			array( 'Art_Theme_Page_Settings', 'sanitize_boxed_shadow' ),
			array( 'classes' => 'art-theme-page-boxed-only' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_padding_block]',
			'art_theme_page_boxed_padding_block',
			'art_theme_page_template',
			__( 'Отступ сверху и снизу (px)', 'art-theme' ),
			(int) $defaults['boxed_padding_block'],
			0,
			120,
			array( 'Art_Theme_Page_Settings', 'sanitize_boxed_padding_block' ),
			array( 'classes' => 'art-theme-page-boxed-only' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_padding_inline]',
			'art_theme_page_boxed_padding_inline',
			'art_theme_page_template',
			__( 'Отступ слева и справа (px)', 'art-theme' ),
			(int) $defaults['boxed_padding_inline'],
			0,
			120,
			array( 'Art_Theme_Page_Settings', 'sanitize_boxed_padding_inline' ),
			array( 'classes' => 'art-theme-page-boxed-only' )
		);
	}

	/**
	 * Register single post template settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_single_template( $wp_customize ) {
		$option_key = Art_Theme_Single_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Single_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_single_template',
			array(
				'title'       => __( 'Шаблон записи', 'art-theme' ),
				'description' => __( 'Вариант оформления, ширина и внутренние отступы одиночной записи. По умолчанию — «Контентный блок» с теми же параметрами, что у страниц и произвольных типов записей.', 'art-theme' ),
				'panel'       => 'art_theme_single',
				'priority'    => 10,
			)
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[template_variant]',
			'art_theme_single_template_variant',
			'art_theme_single_template',
			__( 'Вариант шаблона', 'art-theme' ),
			$defaults['template_variant'],
			Art_Theme_Single_Settings::get_template_variant_choices(),
			array( 'Art_Theme_Single_Settings', 'sanitize_template_variant' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[post_width]',
			'art_theme_single_post_width',
			'art_theme_single_template',
			__( 'Ширина содержимого контента (px)', 'art-theme' ),
			(int) $defaults['post_width'],
			600,
			1400
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_border_radius]',
			'art_theme_single_boxed_border_radius',
			'art_theme_single_template',
			__( 'Скругление контентного блока (px)', 'art-theme' ),
			(int) $defaults['boxed_border_radius'],
			0,
			32,
			array( 'Art_Theme_Single_Settings', 'sanitize_boxed_border_radius' ),
			array( 'classes' => 'art-theme-single-boxed-only' )
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[boxed_shadow]',
			'art_theme_single_boxed_shadow',
			'art_theme_single_template',
			__( 'Тень контентного блока', 'art-theme' ),
			$defaults['boxed_shadow'],
			Art_Theme_Single_Settings::get_boxed_shadow_choices(),
			array( 'Art_Theme_Single_Settings', 'sanitize_boxed_shadow' ),
			array( 'classes' => 'art-theme-single-boxed-only' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_padding_block]',
			'art_theme_single_boxed_padding_block',
			'art_theme_single_template',
			__( 'Отступ сверху и снизу (px)', 'art-theme' ),
			(int) $defaults['boxed_padding_block'],
			0,
			120,
			array( 'Art_Theme_Single_Settings', 'sanitize_boxed_padding_block' ),
			array( 'classes' => 'art-theme-single-boxed-only' )
		);

		self::add_number_control(
			$wp_customize,
			$option_key . '[boxed_padding_inline]',
			'art_theme_single_boxed_padding_inline',
			'art_theme_single_template',
			__( 'Отступ слева и справа (px)', 'art-theme' ),
			(int) $defaults['boxed_padding_inline'],
			0,
			120,
			array( 'Art_Theme_Single_Settings', 'sanitize_boxed_padding_inline' ),
			array( 'classes' => 'art-theme-single-boxed-only' )
		);
	}

	/**
	 * Register single post meta block settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private static function register_single_meta( $wp_customize ) {
		$option_key = Art_Theme_Single_Settings::OPTION_KEY;
		$defaults   = Art_Theme_Single_Settings::get_defaults();

		$wp_customize->add_section(
			'art_theme_single_meta',
			array(
				'title'       => __( 'Мета-блок записи', 'art-theme' ),
				'description' => __( 'Изображение, заголовок и мета-данные на странице записи.', 'art-theme' ),
				'panel'       => 'art_theme_single',
				'priority'    => 20,
			)
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_thumbnail]',
			'art_theme_single_show_thumbnail',
			'art_theme_single_meta',
			__( 'Показывать изображение', 'art-theme' ),
			1
		);

		self::add_select_control(
			$wp_customize,
			$option_key . '[cover_aspect_ratio]',
			'art_theme_single_cover_aspect_ratio',
			'art_theme_single_meta',
			__( 'Соотношение сторон обложки', 'art-theme' ),
			$defaults['cover_aspect_ratio'],
			Art_Theme_Single_Settings::get_cover_aspect_ratio_choices(),
			array( 'Art_Theme_Single_Settings', 'sanitize_cover_aspect_ratio' )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_category]',
			'art_theme_single_show_category',
			'art_theme_single_meta',
			__( 'Показывать рубрику', 'art-theme' ),
			! empty( $defaults['show_category'] )
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_date]',
			'art_theme_single_show_date',
			'art_theme_single_meta',
			__( 'Показывать дату', 'art-theme' ),
			1
		);

		self::add_checkbox_control(
			$wp_customize,
			$option_key . '[show_reading_time]',
			'art_theme_single_show_reading_time',
			'art_theme_single_meta',
			__( 'Показывать время чтения', 'art-theme' ),
			1
		);

		$wp_customize->add_setting(
			$option_key . '[meta_order]',
			array(
				'type'              => 'option',
				'default'           => $defaults['meta_order'],
				'sanitize_callback' => array( 'Art_Theme_Single_Settings', 'sanitize_meta_order' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new Art_Theme_Customize_Layout_Order_Control(
				$wp_customize,
				'art_theme_single_meta_order',
				array(
					'label'       => __( 'Порядок элементов', 'art-theme' ),
					'description' => __( 'Перетащите элементы, чтобы изменить порядок.', 'art-theme' ),
					'section'     => 'art_theme_single_meta',
					'settings'    => $option_key . '[meta_order]',
				)
			)
		);
	}

	/**
	 * Add a select Customizer control.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @param string               $setting_id    Setting ID.
	 * @param string               $control_id    Control ID.
	 * @param string               $section       Section ID.
	 * @param string               $label         Control label.
	 * @param string               $default       Default value.
	 * @param array<string, string> $choices      Select choices.
	 */
	private static function add_select_control( $wp_customize, $setting_id, $control_id, $section, $label, $default, $choices, $sanitize = null, $control_args = array() ) {
		if ( null === $sanitize ) {
			$sanitize = array( 'Art_Theme_Blog_Settings', 'sanitize_cover_aspect_ratio' );
		}

		$wp_customize->add_setting(
			$setting_id,
			array(
				'type'              => 'option',
				'default'           => $default,
				'transport'         => 'refresh',
				'sanitize_callback' => $sanitize,
			)
		);

		$wp_customize->add_control(
			$control_id,
			array_merge(
				array(
					'label'    => $label,
					'section'  => $section,
					'settings' => $setting_id,
					'type'     => 'select',
					'choices'  => $choices,
				),
				$control_args
			)
		);
	}

	/**
	 * Add a text Customizer control.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @param string               $setting_id    Setting ID.
	 * @param string               $control_id    Control ID.
	 * @param string               $section       Section ID.
	 * @param string               $label         Control label.
	 * @param string               $default       Default value.
	 * @param callable             $sanitize      Sanitize callback.
	 */
	private static function add_text_control( $wp_customize, $setting_id, $control_id, $section, $label, $default, $sanitize ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'type'              => 'option',
				'default'           => $default,
				'transport'         => 'refresh',
				'sanitize_callback' => $sanitize,
			)
		);

		$wp_customize->add_control(
			$control_id,
			array(
				'label'    => $label,
				'section'  => $section,
				'settings' => $setting_id,
				'type'     => 'text',
			)
		);
	}

	/**
	 * Add a textarea Customizer control.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @param string               $setting_id    Setting ID.
	 * @param string               $control_id    Control ID.
	 * @param string               $section       Section ID.
	 * @param string               $label         Control label.
	 * @param string               $default       Default value.
	 */
	private static function add_textarea_control( $wp_customize, $setting_id, $control_id, $section, $label, $default ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'type'              => 'option',
				'default'           => $default,
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);

		$wp_customize->add_control(
			$control_id,
			array(
				'label'    => $label,
				'section'  => $section,
				'settings' => $setting_id,
				'type'     => 'textarea',
			)
		);
	}

	/**
	 * Add a number Customizer control.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @param string               $setting_id    Setting ID.
	 * @param string               $control_id    Control ID.
	 * @param string               $section       Section ID.
	 * @param string               $label         Control label.
	 * @param int                  $default       Default value.
	 * @param int                  $min           Minimum value.
	 * @param int                  $max           Maximum value.
	 */
	private static function add_number_control( $wp_customize, $setting_id, $control_id, $section, $label, $default, $min, $max, $sanitize = null, $control_args = array() ) {
		if ( null === $sanitize ) {
			$sanitize = function ( $value ) use ( $min, $max ) {
				return max( $min, min( $max, (int) $value ) );
			};
		}

		$wp_customize->add_setting(
			$setting_id,
			array(
				'type'              => 'option',
				'default'           => $default,
				'transport'         => 'refresh',
				'sanitize_callback' => $sanitize,
			)
		);

		$wp_customize->add_control(
			$control_id,
			array_merge(
				array(
					'label'       => $label,
					'section'     => $section,
					'settings'    => $setting_id,
					'type'        => 'number',
					'input_attrs' => array(
						'min'  => $min,
						'max'  => $max,
						'step' => 1,
					),
				),
				$control_args
			)
		);
	}

	/**
	 * Add a checkbox Customizer control.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @param string               $setting_id    Setting ID.
	 * @param string               $control_id    Control ID.
	 * @param string               $section       Section ID.
	 * @param string               $label         Control label.
	 * @param bool                 $default       Default value.
	 * @param string               $transport     Optional transport. Default refresh.
	 */
	private static function add_checkbox_control( $wp_customize, $setting_id, $control_id, $section, $label, $default, $transport = 'refresh' ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'type'              => 'option',
				'default'           => $default,
				'transport'         => $transport,
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);

		$wp_customize->add_control(
			$control_id,
			array(
				'label'    => $label,
				'section'  => $section,
				'settings' => $setting_id,
				'type'     => 'checkbox',
			)
		);
	}

	/**
	 * Resolve the front-end URL that the Customizer should preview for the current context.
	 *
	 * @return string
	 */
	public static function get_preview_url_for_context() {
		if ( is_admin() ) {
			return self::get_admin_customizer_preview_url();
		}

		return self::get_frontend_customizer_preview_url();
	}

	/**
	 * Preview URL when opening the Customizer from wp-admin.
	 *
	 * Prefers the post/page being edited; otherwise the blog listing
	 * (avoids ART Starter front-page takeover when using Appearance → Customize).
	 *
	 * @return string
	 */
	private static function get_admin_customizer_preview_url() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && 'post' === $screen->base ) {
			$post = get_post();

			if ( $post instanceof WP_Post ) {
				$post_url = self::get_post_customizer_preview_url( $post );

				if ( '' !== $post_url ) {
					return $post_url;
				}
			}
		}

		return self::get_blog_customizer_preview_url();
	}

	/**
	 * Blog archive URL for Customizer preview (posts page or home).
	 *
	 * @return string
	 */
	private static function get_blog_customizer_preview_url() {
		$url = art_theme_get_blog_posts_url();

		return is_string( $url ) ? $url : '';
	}

	/**
	 * Preview URL when opening the Customizer from the front end.
	 *
	 * @return string
	 */
	private static function get_frontend_customizer_preview_url() {
		if ( ! is_singular() ) {
			return '';
		}

		$post = get_queried_object();

		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		return self::get_post_customizer_preview_url( $post );
	}

	/**
	 * Canonical preview URL for a public post type.
	 *
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	private static function get_post_customizer_preview_url( WP_Post $post ) {
		$post_type_object = get_post_type_object( $post->post_type );

		if ( ! $post_type_object || empty( $post_type_object->public ) ) {
			return '';
		}

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ), true ) ) {
			$preview_url = get_preview_post_link( $post );

			return is_string( $preview_url ) ? $preview_url : '';
		}

		if ( in_array( $post->post_status, array( 'publish', 'private' ), true ) ) {
			$permalink = get_permalink( $post );

			return is_string( $permalink ) ? $permalink : '';
		}

		return '';
	}

	/**
	 * Build a Customizer URL that previews the current page/post when possible.
	 *
	 * Uses the same relative `customize.php?…` shape as core menu.php so submenu
	 * links stay valid after admin menu rendering.
	 *
	 * Important: `add_query_arg()` / `build_query()` do not encode values. The
	 * preview URL must be urlencoded before add_query_arg (as in core admin-bar.php).
	 * Otherwise `esc_url( 'customize.php?url=https://…' )` returns empty and the
	 * Appearance → Customize link becomes a no-op page refresh.
	 *
	 * @param string $preview_url Front-end URL to preview.
	 * @return string
	 */
	private static function build_customize_url( $preview_url ) {
		$args = array();

		if ( is_string( $preview_url ) && '' !== $preview_url ) {
			$args['url'] = rawurlencode( $preview_url );
		}

		if ( is_admin() && ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$return_path = remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) );

			if ( is_string( $return_path ) && '' !== $return_path ) {
				$args['return'] = rawurlencode( $return_path );
			}
		}

		if ( empty( $args ) ) {
			return 'customize.php';
		}

		return add_query_arg( $args, 'customize.php' );
	}

	/**
	 * Point the admin bar "Customize" link at the current page instead of the home page.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function filter_admin_bar_customize_link( $wp_admin_bar ) {
		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		$preview_url = self::get_preview_url_for_context();

		if ( '' === $preview_url ) {
			return;
		}

		$customize_url = self::build_customize_url( $preview_url );

		// Admin bar links need an absolute URL (may appear on the front end).
		if ( ! preg_match( '#^https?://#i', $customize_url ) ) {
			$customize_url = admin_url( $customize_url );
		}

		$node = $wp_admin_bar->get_node( 'customize' );

		if ( $node ) {
			$wp_admin_bar->add_node(
				array(
					'id'   => 'customize',
					'href' => $customize_url,
				)
			);

			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'customize',
				'title' => __( 'Customize', 'art-theme' ),
				'href'  => $customize_url,
				'meta'  => array(
					'class' => 'hide-if-no-customize',
				),
			)
		);
	}

	/**
	 * Point Appearance → Customize at the current post (when editing) or the blog listing.
	 */
	public static function filter_appearance_customize_submenu() {
		global $submenu;

		$preview_url = self::get_admin_customizer_preview_url();

		if ( '' === $preview_url || empty( $submenu['themes.php'] ) || ! is_array( $submenu['themes.php'] ) ) {
			return;
		}

		$relative = self::build_customize_url( $preview_url );
		$escaped  = esc_url( $relative );

		// Fallback if esc_url rejects the relative link (must never blank the menu href).
		$customize_url = ( is_string( $escaped ) && '' !== $escaped ) ? $escaped : $relative;

		foreach ( $submenu['themes.php'] as &$item ) {
			if ( empty( $item[2] ) || ! is_string( $item[2] ) ) {
				continue;
			}

			if ( false === strpos( $item[2], 'customize.php' ) ) {
				continue;
			}

			$item[2] = $customize_url;
		}

		unset( $item );
	}
}
