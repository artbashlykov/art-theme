/**
 * Customizer preview for the 404 page section.
 */
( function ( $, api, config ) {
	'use strict';

	if ( ! api || ! config || ! config.previewUrl ) {
		return;
	}

	function set404Preview() {
		var current = api.previewer.previewUrl.get();

		if ( current !== config.previewUrl ) {
			api.previewer.previewUrl.set( config.previewUrl );
		}
	}

	api.bind( 'ready', function () {
		api.section( 'art_theme_not_found', function ( section ) {
			section.expanded.bind( function ( isExpanded ) {
				if ( isExpanded ) {
					set404Preview();
				}
			} );

			if ( section.expanded.get() ) {
				set404Preview();
			}
		} );
	} );
}( jQuery, wp.customize, window.artThemeNotFoundCustomize || null ) );
