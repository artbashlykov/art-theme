/**
 * Footer repeaters in ART Theme Customizer.
 */
( function ( $, api, config ) {
	'use strict';

	if ( ! api || ! config ) {
		return;
	}

	var syncTimers = {};
	var SYNC_DELAY_MS = 800;

	function parseValue( raw ) {
		if ( Array.isArray( raw ) ) {
			return raw;
		}

		if ( 'string' === typeof raw && raw ) {
			try {
				var parsed = JSON.parse( raw );
				return Array.isArray( parsed ) ? parsed : [];
			} catch ( error ) {
				return [];
			}
		}

		return [];
	}

	function getNetworksOptions( selected ) {
		var html = '';
		var networks = config.socialNetworks || {};

		Object.keys( networks ).forEach( function ( slug ) {
			html += '<option value="' + slug + '"' + ( selected === slug ? ' selected' : '' ) + '>' + networks[ slug ] + '</option>';
		} );

		return html;
	}

	function renderSocialRow( item ) {
		item = item || {};

		return (
			'<div class="art-theme-footer-repeater__item art-theme-footer-repeater__item--social">' +
				'<div class="art-theme-footer-repeater__fields">' +
					'<select class="art-theme-footer-repeater__network">' + getNetworksOptions( item.network || '' ) + '</select>' +
					'<input type="url" class="art-theme-footer-repeater__url" value="' + ( item.url || '' ).replace( /"/g, '&quot;' ) + '" placeholder="https://..." />' +
				'</div>' +
				'<button type="button" class="button-link-delete art-theme-footer-repeater__remove" aria-label="' + ( config.removeLabel || 'Удалить' ) + '">&times;</button>' +
			'</div>'
		);
	}

	function renderLinkRow( item ) {
		item = item || {};
		var checked = item.open_new_tab ? ' checked' : '';

		return (
			'<div class="art-theme-footer-repeater__item art-theme-footer-repeater__item--link">' +
				'<div class="art-theme-footer-repeater__fields">' +
					'<input type="text" class="art-theme-footer-repeater__label" value="' + ( item.label || '' ).replace( /"/g, '&quot;' ) + '" placeholder="' + ( config.linkLabelPlaceholder || 'Текст ссылки' ) + '" />' +
					'<input type="url" class="art-theme-footer-repeater__url" value="' + ( item.url || '' ).replace( /"/g, '&quot;' ) + '" placeholder="https://..." />' +
					'<label class="art-theme-footer-repeater__new-tab">' +
						'<input type="checkbox" class="art-theme-footer-repeater__new-tab-input"' + checked + ' />' +
						( config.openNewTabLabel || 'Открывать в новой вкладке' ) +
					'</label>' +
				'</div>' +
				'<button type="button" class="button-link-delete art-theme-footer-repeater__remove" aria-label="' + ( config.removeLabel || 'Удалить' ) + '">&times;</button>' +
			'</div>'
		);
	}

	function readSocialRow( $row ) {
		return {
			network: $row.find( '.art-theme-footer-repeater__network' ).val() || '',
			url: $row.find( '.art-theme-footer-repeater__url' ).val() || '',
		};
	}

	function readLinkRow( $row ) {
		return {
			label: $row.find( '.art-theme-footer-repeater__label' ).val() || '',
			url: $row.find( '.art-theme-footer-repeater__url' ).val() || '',
			open_new_tab: $row.find( '.art-theme-footer-repeater__new-tab-input' ).is( ':checked' ) ? 1 : 0,
		};
	}

	function collectItems( $field, type ) {
		var items = [];

		$field.find( '.art-theme-footer-repeater__item' ).each( function () {
			var $row = $( this );
			var item = 'links' === type ? readLinkRow( $row ) : readSocialRow( $row );

			if ( 'links' === type ) {
				if ( item.label || item.url ) {
					items.push( item );
				}
				return;
			}

			if ( item.network || item.url ) {
				items.push( item );
			}
		} );

		return items;
	}

	function collectPreviewItems( $field, type ) {
		var items = collectItems( $field, type );

		if ( 'links' === type ) {
			return items.filter( function ( item ) {
				return item.label && item.url;
			} );
		}

		return items.filter( function ( item ) {
			return item.network && item.url;
		} );
	}

	function renderRows( $field, items, type ) {
		var $list = $field.find( '.art-theme-footer-repeater__list' );
		$list.empty();

		items.forEach( function ( item ) {
			$list.append( 'links' === type ? renderLinkRow( item ) : renderSocialRow( item ) );
		} );
	}

	function pushSetting( setting, items, immediate ) {
		var settingId = setting.id;

		if ( immediate ) {
			if ( syncTimers[ settingId ] ) {
				clearTimeout( syncTimers[ settingId ] );
				delete syncTimers[ settingId ];
			}

			setting.set( items );
			return;
		}

		if ( syncTimers[ settingId ] ) {
			clearTimeout( syncTimers[ settingId ] );
		}

		syncTimers[ settingId ] = window.setTimeout( function () {
			setting.set( items );
			delete syncTimers[ settingId ];
		}, SYNC_DELAY_MS );
	}

	function syncField( $field, setting, immediate ) {
		var type = $field.data( 'repeater-type' );
		var items = immediate ? collectPreviewItems( $field, type ) : collectItems( $field, type );

		$field.find( '.art-theme-footer-repeater__value' ).val( JSON.stringify( items ) );
		pushSetting( setting, items, immediate );
	}

	function enhanceRepeaterControl( control ) {
		control.deferred.embedded.done( function () {
			if ( 'art_theme_footer_repeater' !== control.params.type ) {
				return;
			}

			var $field = control.container.find( '.art-theme-footer-repeater' ).first();

			if ( ! $field.length || $field.data( 'enhanced' ) ) {
				return;
			}

			$field.data( 'enhanced', true );

			var type = $field.data( 'repeater-type' );
			var maxItems = parseInt( $field.data( 'max-items' ), 10 ) || 10;
			var items = parseValue( control.setting.get() );

			renderRows( $field, items, type );

			$field.on( 'click', '.art-theme-footer-repeater__add', function ( event ) {
				event.preventDefault();

				if ( $field.find( '.art-theme-footer-repeater__item' ).length >= maxItems ) {
					return;
				}

				$field.find( '.art-theme-footer-repeater__list' ).append(
					'links' === type ? renderLinkRow( {} ) : renderSocialRow( {} )
				);
			} );

			$field.on( 'click', '.art-theme-footer-repeater__remove', function ( event ) {
				event.preventDefault();
				$( this ).closest( '.art-theme-footer-repeater__item' ).remove();
				syncField( $field, control.setting, true );
			} );

			$field.on( 'input', 'input', function () {
				syncField( $field, control.setting, false );
			} );

			$field.on( 'change', 'select, input[type="checkbox"]', function () {
				syncField( $field, control.setting, true );
			} );
		} );
	}

	window.artThemeFooterRepeater = {
		renderRows: renderRows,
		parseValue: parseValue,
	};

	api.bind( 'ready', function () {
		api.control.each( enhanceRepeaterControl );
		api.control.bind( 'add', enhanceRepeaterControl );
	} );
}( jQuery, wp.customize, window.artThemeFooterCustomize || null ) );
