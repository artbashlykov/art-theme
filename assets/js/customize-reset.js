/**
 * Per-field reset buttons in ART Theme Customizer controls.
 */
( function ( $, api, config ) {
	'use strict';

	if ( ! api || ! config ) {
		return;
	}

	var sections = {
		art_theme_header_template: true,
		art_theme_header_layout: true,
		art_theme_footer_template: true,
		art_theme_footer_content: true,
		art_theme_not_found: true,
		art_theme_page_template: true,
		art_theme_blog_template: true,
		art_theme_blog_header: true,
		art_theme_blog_card: true,
		art_theme_single_template: true,
		art_theme_single_meta: true,
		art_theme_appearance: true,
	};

	function getDefaultValue( setting ) {
		if ( ! setting ) {
			return '';
		}

		if ( config.defaults && Object.prototype.hasOwnProperty.call( config.defaults, setting.id ) ) {
			return config.defaults[ setting.id ];
		}

		if ( setting.params && Object.prototype.hasOwnProperty.call( setting.params, 'default' ) ) {
			return setting.params.default;
		}

		return '';
	}

	function isCheckedDefault( value ) {
		return value === true || value === 1 || value === '1';
	}

	/**
	 * Keep the setting in the Customizer changeset after reset.
	 *
	 * Without `_dirty`, preview refresh (e.g. after creating a menu) POSTs only
	 * dirty settings and the reset is lost — the saved DB value returns.
	 *
	 * @param {wp.customize.Setting|wp.customize.Value} setting Setting instance.
	 */
	function markSettingDirty( setting ) {
		if ( setting ) {
			setting._dirty = true;
		}

		if ( api.state( 'saved' ) ) {
			api.state( 'saved' ).set( false );
		}
	}

	/**
	 * Apply a value to a setting and keep it dirty for preview refresh / publish.
	 *
	 * @param {wp.customize.Setting|wp.customize.Value} setting Setting instance.
	 * @param {*}                                       value   New value.
	 */
	function setSettingValue( setting, value ) {
		if ( ! setting ) {
			return;
		}

		setting.set( value );
		markSettingDirty( setting );
	}

	/**
	 * One-row color control: title | square | reset.
	 * Keeps Iris DOM intact; reset stays a direct child of the control.
	 *
	 * @param {wp.customize.Control} control Control instance.
	 */
	function enhanceColorControl( control ) {
		var $container = control.container;

		if ( $container.hasClass( 'art-theme-field-enhanced' ) || $container.data( 'artThemeColorEnhancing' ) ) {
			return;
		}

		$container.data( 'artThemeColorEnhancing', 1 );

		var tries = 0;

		function placeResetButton() {
			var $btn = $container.children( '.art-theme-customize-reset' ).first();

			if ( ! $btn.length ) {
				$btn = $container.find( '> .art-theme-customize-reset, .art-theme-customize-reset' ).first();
			}

			if ( ! $btn.length ) {
				$btn = $( '<button/>', {
					type: 'button',
					class: 'button art-theme-customize-reset art-theme-customize-reset--inline',
					text: config.label,
				} );

				$btn.on( 'click', function ( event ) {
					event.preventDefault();

					if ( ! window.confirm( config.confirm ) ) {
						return;
					}

					applyDefaultToControl( control, getDefaultValue( control.setting ) );
				} );
			}

			// Always keep reset as a direct child of the control (never inside Iris).
			$container.append( $btn );
		}

		function attempt() {
			if ( $container.hasClass( 'art-theme-field-enhanced' ) ) {
				return;
			}

			var $picker = $container.find( '.wp-picker-container' ).first();

			if ( ! $picker.length ) {
				tries += 1;

				if ( tries < 40 ) {
					window.setTimeout( attempt, 50 );
				}

				return;
			}

			placeResetButton();
			$container.addClass( 'art-theme-field-enhanced art-theme-field-enhanced--color' );
			$container.removeData( 'artThemeColorEnhancing' );

			// Iris can re-wrap nodes after init — re-assert button placement once more.
			window.setTimeout( placeResetButton, 100 );

			var syncOpenClass = function () {
				var isOpen = $picker.hasClass( 'wp-picker-active' );

				$( '.art-theme-field-enhanced--color' ).removeClass( 'art-theme-color-picker-open' );

				if ( isOpen ) {
					$container.addClass( 'art-theme-color-picker-open' );
				}
			};

			$picker.on( 'click.artThemeColorOpen', '.wp-color-result', function () {
				window.setTimeout( syncOpenClass, 0 );
			} );

			$( document ).on( 'click.artThemeColorOpen' + control.id, function () {
				window.setTimeout( syncOpenClass, 0 );
			} );
		}

		attempt();
	}

	/**
	 * Reset a color control without letting Iris restore the previous color.
	 *
	 * @param {wp.customize.Control} control      Control instance.
	 * @param {string}               defaultValue Default hex color.
	 */
	function applyColorDefault( control, defaultValue ) {
		var setting = control.setting;
		var value = String( defaultValue || '' );
		var $input = control.container.find( 'input.wp-color-picker' ).first();
		var $result = control.container.find( '.wp-color-result' ).first();
		var unlockTimer = null;

		if ( ! setting ) {
			return;
		}

		// While Iris settles, force the reset value if a stale change event fires.
		var guard = function ( to ) {
			if ( to !== value ) {
				setting.set( value );
				markSettingDirty( setting );
			}
		};

		setting.bind( guard );
		setSettingValue( setting, value );

		if ( $input.length ) {
			$input.val( value );

			if ( $result.length ) {
				$result.css( { 'background-color': value } );
			}

			// Prefer native change over wpColorPicker('color') — Iris can race and
			// write the previous color back into the setting.
			$input.trigger( 'change' );
			setSettingValue( setting, value );
		}

		unlockTimer = window.setTimeout( function () {
			setting.unbind( guard );
			setSettingValue( setting, value );
			unlockTimer = null;
		}, 100 );
	}

	function applyDefaultToControl( control, defaultValue ) {
		var controlType = control.params.type;
		var setting = control.setting;

		if ( 'art_theme_layout_order' === controlType ) {
			var defaultOrder = defaultValue;

			if ( typeof defaultOrder === 'string' ) {
				defaultOrder = defaultOrder.split( ',' ).map( function ( item ) {
					return item.trim();
				} );
			}

			if ( ! Array.isArray( defaultOrder ) ) {
				defaultOrder = [];
			}

			setSettingValue( setting, defaultOrder );

			if ( window.artThemeLayoutOrder ) {
				var $field = control.container.find( '.art-theme-layout-order-field' ).first();
				var $list = $field.find( '.art-theme-layout-order' ).first();

				window.artThemeLayoutOrder.reorderList( $list, defaultOrder );
				window.artThemeLayoutOrder.syncHiddenInputs( $field, defaultOrder );
			}

			return;
		}

		if ( 'art_theme_footer_repeater' === controlType ) {
			var footerItems = Array.isArray( defaultValue ) ? defaultValue : [];
			var $footerField = control.container.find( '.art-theme-footer-repeater' ).first();
			var footerType = $footerField.data( 'repeater-type' ) || 'socials';

			setSettingValue( setting, footerItems );

			if ( window.artThemeFooterRepeater ) {
				window.artThemeFooterRepeater.renderRows( $footerField, footerItems, footerType );
			}

			return;
		}

		if ( 'checkbox' === controlType ) {
			var checked = isCheckedDefault( defaultValue );

			setSettingValue( setting, checked );
			control.container.find( 'input[type="checkbox"]' ).prop( 'checked', checked ).trigger( 'change' );
			markSettingDirty( setting );
			return;
		}

		if ( 'color' === controlType ) {
			applyColorDefault( control, defaultValue );
			return;
		}

		setSettingValue( setting, defaultValue );

		control.container
			.find( 'input[type="text"], input[type="number"], input[type="url"], textarea, select' )
			.first()
			.val( defaultValue )
			.trigger( 'change' );

		markSettingDirty( setting );
	}

	function enhanceControl( control ) {
		control.deferred.embedded.done( function () {
			var sectionId = control.section.get();

			if ( ! sections[ sectionId ] ) {
				return;
			}

			if ( control.container.hasClass( 'art-theme-field-enhanced' ) ) {
				return;
			}

			if ( ! control.setting ) {
				return;
			}

			var controlType = control.params.type;

			if ( 'art_theme_layout_order' === controlType ) {
				var $title = control.container.children( '.customize-control-title' ).first();

				if ( ! $title.length || control.container.hasClass( 'art-theme-field-enhanced' ) ) {
					return;
				}

				var $row = $( '<div class="art-theme-customize-field-row art-theme-customize-field-row--layout-order" />' );
				var $button = $( '<button/>', {
					type: 'button',
					class: 'button art-theme-customize-reset art-theme-customize-reset--inline',
					text: config.label,
				} );

				$button.on( 'click', function ( event ) {
					event.preventDefault();

					if ( ! window.confirm( config.confirm ) ) {
						return;
					}

					applyDefaultToControl( control, getDefaultValue( control.setting ) );
				} );

				$title.wrap( $row );
				$row = $title.parent();
				$row.append( $button );
				control.container.addClass( 'art-theme-field-enhanced' );
				return;
			}

			// Color picker: title + square + reset on one row.
			if ( 'color' === controlType ) {
				enhanceColorControl( control );
				return;
			}

			var $container = control.container;
			controlType = control.params.type;
			var $button = $( '<button/>', {
				type: 'button',
				class: 'button art-theme-customize-reset art-theme-customize-reset--inline',
				text: config.label,
			} );

			$button.on( 'click', function ( event ) {
				event.preventDefault();

				if ( ! window.confirm( config.confirm ) ) {
					return;
				}

				applyDefaultToControl( control, getDefaultValue( control.setting ) );
			} );

			if ( 'checkbox' === controlType ) {
				var $label = $container.children( 'label' ).first();

				if ( ! $label.length ) {
					return;
				}

				var $row = $( '<div class="art-theme-customize-field-row art-theme-customize-field-row--checkbox" />' );
				$label.before( $row );
				$row.append( $label.detach(), $button );
			} else {
				var $input = $container.find( 'input[type="text"], input[type="number"], input[type="url"], textarea, select' ).first();

				if ( ! $input.length ) {
					return;
				}

				var $row = $( '<div class="art-theme-customize-field-row" />' );
				var isTextarea = $input.is( 'textarea' );

				if ( isTextarea ) {
					$row.addClass( 'art-theme-customize-field-row--textarea' );
				}

				$input.before( $row );
				$row.append( $input.detach(), $button );
			}

			$container.addClass( 'art-theme-field-enhanced' );
		} );
	}

	api.bind( 'ready', function () {
		api.control.each( enhanceControl );
		api.control.bind( 'add', enhanceControl );
	} );
}( jQuery, wp.customize, window.artThemeCustomizeReset || null ) );
