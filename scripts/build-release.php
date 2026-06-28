<?php
/**
 * Build GitHub Release zip for ART Theme.
 *
 * Usage: php scripts/build-release.php [output-path]
 *
 * @package Art_Theme
 */

if ( 'cli' === PHP_SAPI && ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ );
}

defined( 'ABSPATH' ) || exit;

/**
 * Write a message to STDERR in CLI mode.
 *
 * @param string $art_theme_message Message text.
 */
function art_theme_build_release_stderr( $art_theme_message ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI build script only.
	fwrite( STDERR, $art_theme_message );
}

/**
 * Build release zip archive.
 *
 * @param array<int, string> $art_theme_argv CLI arguments.
 * @return int Exit code.
 */
function art_theme_build_release( array $art_theme_argv ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		art_theme_build_release_stderr( "ZipArchive is required.\n" );
		return 1;
	}

	$art_theme_dir    = dirname( __DIR__ );
	$art_theme_slug   = basename( $art_theme_dir );
	$art_theme_output = $art_theme_argv[1] ?? ( sys_get_temp_dir() . DIRECTORY_SEPARATOR . $art_theme_slug . '.zip' );

	$art_theme_exclude_dirs          = array( '.git', '.cursor', '.idea', '.vscode', 'node_modules', 'scripts' );
	$art_theme_exclude_file_patterns = array(
		'*.zip',
		'*.log',
		'tmp-*.php',
		'local-*.php',
	);

	/**
	 * Whether a path should be excluded from the release archive.
	 *
	 * @param string $art_theme_relative_path Path relative to theme root.
	 */
	$art_theme_should_exclude = static function ( $art_theme_relative_path ) use ( $art_theme_exclude_dirs, $art_theme_exclude_file_patterns ) {
		$art_theme_relative_path = str_replace( '\\', '/', $art_theme_relative_path );
		$art_theme_parts         = explode( '/', $art_theme_relative_path );

		foreach ( $art_theme_parts as $art_theme_part ) {
			if ( in_array( $art_theme_part, $art_theme_exclude_dirs, true ) ) {
				return true;
			}
		}

		$art_theme_basename = basename( $art_theme_relative_path );
		foreach ( $art_theme_exclude_file_patterns as $art_theme_pattern ) {
			if ( fnmatch( $art_theme_pattern, $art_theme_basename ) ) {
				return true;
			}
		}

		return false;
	};

	$art_theme_zip    = new ZipArchive();
	$art_theme_opened = $art_theme_zip->open( $art_theme_output, ZipArchive::OVERWRITE | ZipArchive::CREATE );

	if ( true !== $art_theme_opened ) {
		art_theme_build_release_stderr( 'Cannot create zip: ' . $art_theme_output . "\n" );
		return 1;
	}

	$art_theme_iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $art_theme_dir, RecursiveDirectoryIterator::SKIP_DOTS )
	);

	foreach ( $art_theme_iterator as $art_theme_file_info ) {
		/**
		 * SplFileInfo instance for the current archive entry.
		 *
		 * @var SplFileInfo $art_theme_file_info
		 */
		$art_theme_absolute_path = $art_theme_file_info->getPathname();
		$art_theme_relative_path = substr( $art_theme_absolute_path, strlen( $art_theme_dir ) + 1 );

		if ( $art_theme_should_exclude( $art_theme_relative_path ) ) {
			continue;
		}

		$art_theme_zip_path = $art_theme_slug . '/' . str_replace( '\\', '/', $art_theme_relative_path );

		if ( $art_theme_file_info->isDir() ) {
			$art_theme_zip->addEmptyDir( rtrim( $art_theme_zip_path, '/' ) );
			continue;
		}

		$art_theme_zip->addFile( $art_theme_absolute_path, $art_theme_zip_path );
	}

	$art_theme_zip->close();

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI outputs a local filesystem path.
	echo $art_theme_output, PHP_EOL;

	return 0;
}

if ( 'cli' !== PHP_SAPI ) {
	exit;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI exit code, not rendered output.
exit( art_theme_build_release( $argv ) );
