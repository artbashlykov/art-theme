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

			setting.set( defaultOrder );

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

			setting.set( footerItems );

			if ( window.artThemeFooterRepeater ) {
				window.artThemeFooterRepeater.renderRows( $footerField, footerItems, footerType );
			}

			return;
		}

		if ( 'checkbox' === controlType ) {
			var checked = isCheckedDefault( defaultValue );

			setting.set( checked );
			control.container.find( 'input[type="checkbox"]' ).prop( 'checked', checked ).trigger( 'change' );
			return;
		}

		if ( 'color' === controlType ) {
			setting.set( defaultValue );

			var $picker = control.container.find( 'input.wp-color-picker' ).first();

			if ( $picker.length && typeof $picker.wpColorPicker === 'function' ) {
				$picker.wpColorPicker( 'color', defaultValue );
			} else {
				$picker.val( defaultValue ).trigger( 'change' );
			}

			return;
		}

		setting.set( defaultValue );

		control.container
			.find( 'input[type="text"], input[type="number"], input[type="url"], textarea, select' )
			.first()
			.val( defaultValue )
			.trigger( 'change' );
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
