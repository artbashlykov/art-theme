<?php
/**
 * Theme bootstrap.
 *
 * @package Art_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ART_THEME_VERSION', '1.0.1' );
define( 'ART_THEME_SLUG', 'art-theme' );
define( 'ART_THEME_DIR', get_template_directory() );
define( 'ART_THEME_URL', get_template_directory_uri() );

add_filter( 'puc_view_details_link-' . ART_THEME_SLUG, '__return_empty_string' );

require_once ART_THEME_DIR . '/inc/setup.php';
require_once ART_THEME_DIR . '/inc/block-editor.php';
require_once ART_THEME_DIR . '/inc/fonts.php';
require_once ART_THEME_DIR . '/inc/enqueue.php';
require_once ART_THEME_DIR . '/inc/admin/class-blog-settings.php';
require_once ART_THEME_DIR . '/inc/admin/class-single-settings.php';
require_once ART_THEME_DIR . '/inc/admin/class-page-settings.php';
require_once ART_THEME_DIR . '/inc/admin/class-header-settings.php';
require_once ART_THEME_DIR . '/inc/admin/class-footer-settings.php';
require_once ART_THEME_DIR . '/inc/admin/class-not-found-settings.php';
require_once ART_THEME_DIR . '/inc/class-social-icons.php';
require_once ART_THEME_DIR . '/inc/site-header.php';
require_once ART_THEME_DIR . '/inc/site-footer.php';
require_once ART_THEME_DIR . '/inc/not-found.php';
require_once ART_THEME_DIR . '/inc/post-performance.php';
require_once ART_THEME_DIR . '/inc/template-tags.php';
require_once ART_THEME_DIR . '/inc/class-theme-styles.php';
require_once ART_THEME_DIR . '/inc/customizer/class-customizer.php';
require_once ART_THEME_DIR . '/inc/class-updater.php';

add_action( 'after_setup_theme', 'art_theme_setup' );

Art_Theme_Blog_Settings::init();
Art_Theme_Single_Settings::init();
Art_Theme_Page_Settings::init();
Art_Theme_Header_Settings::init();
Art_Theme_Footer_Settings::init();
Art_Theme_Not_Found_Settings::init();
Art_Theme_Customizer::init();

if ( is_admin() ) {
	Art_Theme_Updater::init();
}
