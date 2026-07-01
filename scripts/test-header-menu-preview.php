<?php
/**
 * CLI smoke test: Customizer preview nav menu filter must not recurse.
 *
 * Usage: php scripts/test-header-menu-preview.php
 *
 * @package Art_Theme
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';

if ( ! is_readable( $wp_load ) ) {
	$wp_load = 'C:/Users/artba/Local Sites/demo/app/public/wp-load.php';
}

if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php\n" );
	exit( 1 );
}

require_once $wp_load;

if ( ! class_exists( 'Art_Theme_Header_Settings' ) ) {
	fwrite( STDERR, "ART Theme is not active.\n" );
	exit( 1 );
}

require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

global $wp_customize;

$wp_customize = new WP_Customize_Manager(
	array(
		'url' => home_url( '/' ),
	)
);

$reflection = new ReflectionClass( $wp_customize );
$previewing = $reflection->getProperty( 'previewing' );
$previewing->setAccessible( true );
$previewing->setValue( $wp_customize, true );

delete_option( 'art_theme_header_settings' );

$filter_depth = 0;

add_filter(
	'theme_mod_nav_menu_locations',
	static function ( $locations ) use ( &$filter_depth ) {
		++$filter_depth;

		if ( $filter_depth > 5 ) {
			throw new RuntimeException( 'Recursion detected in theme_mod_nav_menu_locations filter.' );
		}

		return is_array( $locations ) ? $locations : array();
	},
	5
);

try {
	$locations = get_nav_menu_locations();
	$menu_id   = Art_Theme_Header_Settings::get_preview_header_menu_id();

	if ( ! is_array( $locations ) ) {
		throw new RuntimeException( 'Expected nav menu locations array.' );
	}

	if ( 0 !== (int) $menu_id ) {
		throw new RuntimeException( 'Expected empty preview menu ID on fresh install.' );
	}

	$placeholder_id = -42;
	$resolved       = Art_Theme_Header_Settings::get_preview_header_menu_id(
		array(
			Art_Theme_Header_Settings::MENU_LOCATION => $placeholder_id,
		)
	);

	if ( $placeholder_id !== $resolved ) {
		throw new RuntimeException( 'Expected Customizer placeholder menu ID in preview locations.' );
	}

	echo "OK: customize preview nav menu lookup completed without recursion.\n";
	exit( 0 );
} catch ( Throwable $exception ) {
	fwrite( STDERR, 'FAIL: ' . $exception->getMessage() . "\n" );
	exit( 1 );
}
