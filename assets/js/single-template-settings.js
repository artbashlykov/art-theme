/**
 * Toggle boxed-only template settings (single post + page) in admin and Customizer.
 */
( function ( $, api, config ) {
	'use strict';

	function isBoxedVariant( value ) {
		return 'boxed' === value;
	}

	function toggleAdminBoxedSettings( selector, boxedClass, value ) {
		if ( selector ) {
			$( selector ).closest( 'form' ).find( '.' + boxedClass ).toggle( isBoxedVariant( value ) );
		}
	}

	function toggleCustomizerBoxedSettings( boxedClass, value ) {
		if ( ! api ) {
			return;
		}

		$( '.customize-control.' + boxedClass ).each( function () {
			$( this ).toggle( isBoxedVariant( value ) );
		} );
	}

	function bindTemplateVariantSetting( settingId, boxedClass ) {
		if ( ! api || ! settingId ) {
			return;
		}

		api( settingId, function ( setting ) {
			var sync = function ( value ) {
				toggleCustomizerBoxedSettings( boxedClass, value );
			};

			setting.bind( sync );
			sync( setting.get() );
		} );
	}

	function isFixedHeaderWidth( value ) {
		return ( config && config.headerWidthModeFixed ? config.headerWidthModeFixed : 'fixed' ) === value;
	}

	function toggleCustomizerFixedHeaderSettings( value ) {
		if ( ! api ) {
			return;
		}

		$( '.customize-control.art-theme-header-fixed-only' ).each( function () {
			$( this ).toggle( isFixedHeaderWidth( value ) );
		} );
	}

	function isFixedFooterWidth( value ) {
		return ( config && config.footerWidthModeFixed ? config.footerWidthModeFixed : 'fixed' ) === value;
	}

	function toggleCustomizerFixedFooterSettings( value ) {
		if ( ! api ) {
			return;
		}

		$( '.customize-control.art-theme-footer-fixed-only' ).each( function () {
			$( this ).toggle( isFixedFooterWidth( value ) );
		} );
	}

	function bindHeaderWidthModeSetting( settingId ) {
		if ( ! api || ! settingId ) {
			return;
		}

		api( settingId, function ( setting ) {
			var sync = function ( value ) {
				toggleCustomizerFixedHeaderSettings( value );
			};

			setting.bind( sync );
			sync( setting.get() );
		} );
	}

	function bindFooterWidthModeSetting( settingId ) {
		if ( ! api || ! settingId ) {
			return;
		}

		api( settingId, function ( setting ) {
			var sync = function ( value ) {
				toggleCustomizerFixedFooterSettings( value );
			};

			setting.bind( sync );
			sync( setting.get() );
		} );
	}

	function initAdmin() {
		var $singleSelect = $( '#art_theme_single_template_variant' );

		if ( $singleSelect.length ) {
			$singleSelect.on( 'change', function () {
				toggleAdminBoxedSettings( '#art_theme_single_template_variant', 'art-theme-single-boxed-only', $singleSelect.val() );
			} );
			toggleAdminBoxedSettings( '#art_theme_single_template_variant', 'art-theme-single-boxed-only', $singleSelect.val() );
		}

		var $pageSelect = $( '#art_theme_page_template_variant' );

		if ( $pageSelect.length ) {
			$pageSelect.on( 'change', function () {
				toggleAdminBoxedSettings( '#art_theme_page_template_variant', 'art-theme-page-boxed-only', $pageSelect.val() );
			} );
			toggleAdminBoxedSettings( '#art_theme_page_template_variant', 'art-theme-page-boxed-only', $pageSelect.val() );
		}
	}

	function initCustomizer() {
		if ( ! api ) {
			return;
		}

		bindTemplateVariantSetting(
			config && config.templateVariantSettingId ? config.templateVariantSettingId : '',
			'art-theme-single-boxed-only'
		);
		bindTemplateVariantSetting(
			config && config.pageTemplateVariantSettingId ? config.pageTemplateVariantSettingId : '',
			'art-theme-page-boxed-only'
		);
		bindHeaderWidthModeSetting(
			config && config.headerWidthModeSettingId ? config.headerWidthModeSettingId : ''
		);
		bindFooterWidthModeSetting(
			config && config.footerWidthModeSettingId ? config.footerWidthModeSettingId : ''
		);
	}

	$( initAdmin );

	if ( api ) {
		api.bind( 'ready', initCustomizer );
	}
}( jQuery, window.wp && window.wp.customize ? window.wp.customize : null, window.artThemeSingleTemplateSettings || null ) );
