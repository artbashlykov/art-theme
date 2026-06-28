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
