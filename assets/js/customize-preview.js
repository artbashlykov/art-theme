/**
 * Live Customizer preview: footer copyright + appearance colors.
 */
( function ( api, config ) {
	'use strict';

	if ( ! api || ! config ) {
		return;
	}

	var YEAR_SHORTCODE = config.yearShortcode || '[current_year]';
	var currentYear = String( config.currentYear || new Date().getFullYear() );
	var siteName = config.siteName || '';
	var appearance = config.appearance || {};
	var colorItems = appearance.colors || [];
	var colorValues = {};
	var appearanceBound = false;

	function expandYear( text ) {
		return String( text || '' ).split( YEAR_SHORTCODE ).join( currentYear );
	}

	function buildCopyrightLine( rawText ) {
		var text = String( rawText || '' ).trim();

		if ( ! text ) {
			text = siteName;
		}

		if ( text.indexOf( YEAR_SHORTCODE ) !== -1 ) {
			return expandYear( text );
		}

		return '© ' + currentYear + ( text ? ' ' + text : '' );
	}

	function getCopyrightEl() {
		return document.querySelector( '.art-theme-site-footer__copyright' );
	}

	function ensureCopyrightEl() {
		var el = getCopyrightEl();

		if ( el ) {
			return el;
		}

		var footerInner = document.querySelector( '.art-theme-site-footer__inner' );
		var stack = document.querySelector( '.art-theme-site-footer__stack' );
		var parent = stack || footerInner;

		if ( ! parent ) {
			return null;
		}

		el = document.createElement( 'div' );
		el.className = 'art-theme-site-footer__copyright art-theme-site-footer__copyright--' +
			( stack ? 'stack' : 'columns' );
		parent.appendChild( el );

		return el;
	}

	function updateCopyright( text, visible ) {
		var el = getCopyrightEl();

		if ( ! visible ) {
			if ( el && el.parentNode ) {
				el.parentNode.removeChild( el );
			}

			return;
		}

		el = ensureCopyrightEl();

		if ( ! el ) {
			return;
		}

		el.textContent = buildCopyrightLine( text );
	}

	function ensureAppearanceStyleEl() {
		var el = document.getElementById( 'art-theme-appearance-live' );

		if ( el ) {
			return el;
		}

		el = document.createElement( 'style' );
		el.id = 'art-theme-appearance-live';
		document.head.appendChild( el );

		return el;
	}

	function paintAppearanceColors() {
		var css = ':root{';
		var i;
		var item;
		var value;

		for ( i = 0; i < colorItems.length; i++ ) {
			item = colorItems[ i ];
			value = colorValues[ item.id ];

			if ( ! item || ! item.var || ! value ) {
				continue;
			}

			css += item.var + ':' + value + ';';

			if ( '--art-theme-accent' === item.var ) {
				css += '--art-theme-category-fg:' + value + ';';
				css += '--art-theme-category-bg:color-mix(in srgb,' + value + ' 12%,#ffffff);';
				css += '--art-theme-category-border:color-mix(in srgb,' + value + ' 35%,#ffffff);';
			}
		}

		css += '}';
		ensureAppearanceStyleEl().textContent = css;
	}

	function bindAppearanceColors() {
		var i;
		var item;

		if ( appearanceBound || ! colorItems.length ) {
			return;
		}

		appearanceBound = true;

		for ( i = 0; i < colorItems.length; i++ ) {
			item = colorItems[ i ];

			( function ( colorItem ) {
				api( colorItem.id, function ( setting ) {
					colorValues[ colorItem.id ] = setting.get();
					paintAppearanceColors();

					setting.bind( function ( value ) {
						colorValues[ colorItem.id ] = value;
						paintAppearanceColors();
					} );
				} );
			}( item ) );
		}

		// Fallback: catch updates if deferred api(id) callbacks lag.
		api.bind( 'change', function ( setting ) {
			var j;
			var match = null;

			if ( ! setting || ! setting.id ) {
				return;
			}

			for ( j = 0; j < colorItems.length; j++ ) {
				if ( colorItems[ j ].id === setting.id ) {
					match = colorItems[ j ];
					break;
				}
			}

			if ( ! match ) {
				return;
			}

			colorValues[ match.id ] = setting.get();
			paintAppearanceColors();
		} );
	}

	function bindCopyright() {
		var textId = config.copyrightTextSettingId;
		var showId = config.showCopyrightSettingId;

		if ( ! textId || ! showId ) {
			return;
		}

		api( textId, function ( textSetting ) {
			api( showId, function ( showSetting ) {
				var sync = function () {
					updateCopyright( textSetting.get(), !! showSetting.get() );
				};

				sync();
				textSetting.bind( sync );
				showSetting.bind( sync );
			} );
		} );
	}

	api.bind( 'ready', function () {
		bindAppearanceColors();
		bindCopyright();
	} );

	api.bind( 'preview-ready', function () {
		bindAppearanceColors();
	} );
}( wp.customize, window.artThemeCustomizePreview || null ) );
