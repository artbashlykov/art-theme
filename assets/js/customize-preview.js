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

	function normalizeColor( value, fallback ) {
		var color = String( value || '' ).trim();

		if ( /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( color ) ) {
			return color;
		}

		return fallback || '';
	}

	function paintAppearanceColors() {
		var root = document.documentElement;
		var i;
		var item;
		var value;
		var accent = '';
		var text = '';

		if ( ! root || ! root.style || typeof root.style.setProperty !== 'function' ) {
			return;
		}

		for ( i = 0; i < colorItems.length; i++ ) {
			item = colorItems[ i ];

			if ( ! item || ! item.var ) {
				continue;
			}

			value = normalizeColor( colorValues[ item.id ], item.default );

			if ( ! value ) {
				continue;
			}

			root.style.setProperty( item.var, value );

			if ( '--art-theme-accent' === item.var ) {
				accent = value;
			}

			if ( '--art-theme-fg' === item.var ) {
				text = value;
			}
		}

		if ( text ) {
			root.style.setProperty(
				'--art-theme-muted',
				'color-mix(in srgb, ' + text + ' 55%, #ffffff)'
			);
		}

		if ( accent ) {
			root.style.setProperty( '--art-theme-category-fg', accent );
			root.style.setProperty(
				'--art-theme-category-bg',
				'color-mix(in srgb, ' + accent + ' 12%, #ffffff)'
			);
			root.style.setProperty(
				'--art-theme-category-border',
				'color-mix(in srgb, ' + accent + ' 35%, #ffffff)'
			);
		}
	}

	function bindColorSetting( colorItem ) {
		api( colorItem.id, function ( setting ) {
			colorValues[ colorItem.id ] = setting.get();
			paintAppearanceColors();

			setting.bind( function ( value ) {
				colorValues[ colorItem.id ] = value;
				paintAppearanceColors();
			} );
		} );
	}

	function bindAppearanceColors() {
		var i;

		if ( appearanceBound || ! colorItems.length ) {
			return;
		}

		appearanceBound = true;

		for ( i = 0; i < colorItems.length; i++ ) {
			bindColorSetting( colorItems[ i ] );
		}

		api.bind( 'change', function ( setting ) {
			var j;

			if ( ! setting || ! setting.id ) {
				return;
			}

			for ( j = 0; j < colorItems.length; j++ ) {
				if ( colorItems[ j ].id === setting.id ) {
					colorValues[ setting.id ] = setting.get();
					paintAppearanceColors();
					return;
				}
			}
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

	api.bind( 'preview-ready', function () {
		bindAppearanceColors();
		bindCopyright();
	} );

	// Fallback if preview-ready already fired before this script ran.
	if ( api.settings && api.settings.values ) {
		bindAppearanceColors();
		bindCopyright();
	}
}( wp.customize, window.artThemeCustomizePreview || null ) );
