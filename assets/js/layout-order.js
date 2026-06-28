/**
 * Drag-and-drop layout order (Customizer).
 */
( function ( $ ) {
	'use strict';

	function getItemsFromList( $list ) {
		return $list
			.find( '.art-theme-layout-order__item' )
			.map( function () {
				return $( this ).data( 'item' );
			} )
			.get();
	}

	function syncHiddenInputs( $field, order ) {
		$field.find( '.art-theme-layout-order-value' ).val( order.join( ',' ) );
	}

	function reorderList( $list, order ) {
		order.forEach( function ( slug ) {
			var $item = $list.find( '.art-theme-layout-order__item[data-item="' + slug + '"]' ).first();

			if ( $item.length ) {
				$list.append( $item );
			}
		} );
	}

	function initField( $field ) {
		var $list = $field.find( '.art-theme-layout-order' ).first();

		if ( ! $list.length || $list.data( 'layoutOrderInit' ) ) {
			return;
		}

		$list.data( 'layoutOrderInit', true );

		$list.sortable( {
			handle: '.art-theme-layout-order__handle',
			axis: 'y',
			containment: 'parent',
			tolerance: 'pointer',
			update: function () {
				var order = getItemsFromList( $list );

				syncHiddenInputs( $field, order );
				$field.find( '.art-theme-layout-order-value' ).trigger( 'change' );
				$field.trigger( 'art-theme-layout-order-updated', [ order ] );
			},
		} );
	}

	function bindCustomizerFields( api ) {
		$( '.art-theme-layout-order-field--customize' ).each( function () {
			var $field = $( this );
			var settingId = $field.data( 'customizeSetting' );

			if ( ! settingId || $field.data( 'customizeBound' ) ) {
				return;
			}

			$field.data( 'customizeBound', true );
			initField( $field );

			api( settingId, function ( setting ) {
				$field.on( 'art-theme-layout-order-updated', function ( event, order ) {
					setting.set( order );
				} );

				setting.bind( function ( value ) {
					var order = value;

					if ( typeof order === 'string' ) {
						order = order.split( ',' ).map( function ( item ) {
							return item.trim();
						} );
					}

					if ( ! Array.isArray( order ) || ! order.length ) {
						return;
					}

					reorderList( $field.find( '.art-theme-layout-order' ).first(), order );
					syncHiddenInputs( $field, order );
				} );
			} );
		} );
	}

	if ( window.wp && window.wp.customize ) {
		window.wp.customize.bind( 'ready', function () {
			bindCustomizerFields( window.wp.customize );
		} );

		window.wp.customize.control.bind( 'add', function () {
			bindCustomizerFields( window.wp.customize );
		} );
	}

	window.artThemeLayoutOrder = {
		reorderList: reorderList,
		getItemsFromList: getItemsFromList,
		syncHiddenInputs: syncHiddenInputs,
	};
}( jQuery ) );
