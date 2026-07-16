/**
 * Live Customizer preview for footer copyright.
 */
( function ( api, config ) {
	'use strict';

	if ( ! api || ! config ) {
		return;
	}

	var YEAR_SHORTCODE = config.yearShortcode || '[current_year]';
	var currentYear = String( config.currentYear || new Date().getFullYear() );
	var siteName = config.siteName || '';

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

	api.bind( 'ready', function () {
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
	} );
}( wp.customize, window.artThemeCustomizePreview || null ) );
