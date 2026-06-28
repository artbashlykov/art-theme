/**
 * Header menu select: sync with Primary Menu location and "Create menu" shortcut.
 */
( function ( $, api, config ) {
	'use strict';

	if ( ! api || ! config || ! config.controlId || ! config.createValue ) {
		return;
	}

	var controlId = config.controlId;
	var createValue = config.createValue;
	var menuLocation = config.menuLocation || 'primary';
	var locationSettingId = 'nav_menu_locations[' + menuLocation + ']';

	function getLocationSetting() {
		return api( locationSettingId );
	}

	function focusCreateMenuPanel() {
		var panel = api.panel( 'nav_menus' );
		var section = api.section( 'add_menu' );

		if ( panel ) {
			panel.focus();
		}

		if ( section ) {
			section.expand();
		}
	}

	function getMenuNameFromSetting( setting ) {
		var data = setting.get();

		if ( ! data || false === data ) {
			return '';
		}

		return data.name || '';
	}

	function getMenuIdFromSetting( setting ) {
		var match = setting.id.match( /^nav_menu\[(-?\d+)\]$/ );

		return match ? match[ 1 ] : '';
	}

	function appendMenuOption( $select, menuId, menuName ) {
		if ( ! menuId || $select.find( 'option[value="' + menuId + '"]' ).length ) {
			return;
		}

		var $createOption = $select.find( 'option[value="' + createValue + '"]' );
		var $option = $( '<option/>', {
			value: menuId,
			text: menuName || ( 'Menu ' + menuId ),
		} );

		if ( $createOption.length ) {
			$option.insertBefore( $createOption );
		} else {
			$select.append( $option );
		}
	}

	function syncPrimaryLocationFromControl( menuId ) {
		var locationSetting = getLocationSetting();

		if ( ! locationSetting ) {
			return;
		}

		var nextId = parseInt( menuId, 10 ) || 0;
		var currentId = parseInt( locationSetting.get(), 10 ) || 0;

		if ( currentId === nextId ) {
			return;
		}

		locationSetting.set( nextId );
	}

	function syncControlFromPrimaryLocation( control, menuId ) {
		var next = String( menuId || '' );
		var current = String( control.setting.get() || '' );

		if ( ! next || next === current || createValue === next ) {
			return;
		}

		control.setting.set( next );
	}

	function bindMenuSetting( control, setting ) {
		var menuId = getMenuIdFromSetting( setting );

		if ( ! menuId ) {
			return;
		}

		control.deferred.embedded.done( function () {
			var $select = control.container.find( 'select' );

			appendMenuOption( $select, menuId, getMenuNameFromSetting( setting ) );

			setting.bind( function ( data ) {
				var name = data && data.name ? data.name : '';
				$select.find( 'option[value="' + menuId + '"]' ).text( name || ( 'Menu ' + menuId ) );
			} );
		} );
	}

	function initControl( control ) {
		control.deferred.embedded.done( function () {
			var $select = control.container.find( 'select' );
			var lastValid = String( control.setting.get() || '' );
			var locationSetting = getLocationSetting();

			if ( createValue === lastValid ) {
				lastValid = '';
				control.setting.set( '' );
			}

			if ( locationSetting ) {
				var locationMenuId = String( locationSetting.get() || '' );

				if ( locationMenuId && ( ! lastValid || '0' === lastValid ) ) {
					lastValid = locationMenuId;
					control.setting.set( locationMenuId );
				}
			}

			if ( lastValid && $select.find( 'option[value="' + lastValid + '"]' ).length ) {
				$select.val( lastValid );
			}

			if ( locationSetting ) {
				locationSetting.bind( function ( value ) {
					syncControlFromPrimaryLocation( control, value );
				} );
			}

			control.setting.bind( function ( value ) {
				var next = String( value || '' );

				if ( createValue === next ) {
					focusCreateMenuPanel();
					control.setting.set( lastValid );
					$select.val( lastValid );
					return;
				}

				lastValid = next;
				$select.val( next );
				syncPrimaryLocationFromControl( next );
			} );
		} );
	}

	api.bind( 'ready', function () {
		var control = api.control( controlId );

		if ( ! control ) {
			return;
		}

		initControl( control );

		api.each( function ( setting ) {
			if ( /^nav_menu\[/.test( setting.id ) ) {
				bindMenuSetting( control, setting );
			}
		} );

		api.bind( 'add', function ( setting ) {
			if ( ! /^nav_menu\[/.test( setting.id ) ) {
				return;
			}

			bindMenuSetting( control, setting );
		} );
	} );
}( jQuery, wp.customize, window.artThemeHeaderMenuSelect || null ) );
