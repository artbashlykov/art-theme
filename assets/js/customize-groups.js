/**
 * Customizer group heading sections (non-expandable labels).
 */
( function ( api, $ ) {
	'use strict';

	if ( ! api || ! api.Section || ! $ ) {
		return;
	}

	/**
	 * Strip focus/click affordances so the section reads as a plain label.
	 *
	 * @param {wp.customize.Section} section Section instance.
	 */
	function makeLabelOnly( section ) {
		var $head = section.headContainer;
		var $title;
		var $trigger;

		if ( ! $head || ! $head.length ) {
			return;
		}

		$head
			.attr( {
				'aria-hidden': 'true',
				role: 'presentation'
			} )
			.removeAttr( 'tabindex' )
			.addClass( 'cannot-expand' );

		$title = $head.children( '.accordion-section-title' );

		if ( ! $title.length ) {
			$title = $head.find( '.accordion-section-title' ).first();
		}

		$title
			.removeAttr( 'tabindex' )
			.removeAttr( 'aria-expanded' )
			.removeAttr( 'aria-controls' )
			.removeAttr( 'aria-describedby' )
			.attr( {
				role: 'presentation',
				tabindex: '-1'
			} );

		$trigger = $title.find( '.accordion-trigger' );

		if ( $trigger.length ) {
			$trigger
				.removeAttr( 'aria-expanded' )
				.removeAttr( 'aria-controls' )
				.removeAttr( 'aria-describedby' )
				.attr( {
					tabindex: '-1',
					role: 'presentation',
					type: 'button',
					disabled: 'disabled'
				} )
				.off( 'click keydown keypress keyup' );
		}

		$title.find( '.screen-reader-text' ).remove();

		$head.on( 'click.artThemeGroup keydown.artThemeGroup', function ( event ) {
			event.preventDefault();
			event.stopImmediatePropagation();
		} );
	}

	api.sectionConstructor.art_theme_group = api.Section.extend( {
		/**
		 * Group titles are labels only — no expand/collapse.
		 */
		attachEvents: function () {},

		/**
		 * Always show the heading in the root list.
		 *
		 * @return {boolean}
		 */
		isContextuallyActive: function () {
			return true;
		},

		/**
		 * Keep the label non-interactive.
		 */
		ready: function () {
			makeLabelOnly( this );
		},

		/**
		 * Never expand into a nested pane.
		 *
		 * @param {boolean} expanded Expanded state.
		 * @param {Object}  args     Expand args.
		 */
		onChangeExpanded: function ( expanded, args ) {
			var completeCallback = args && args.completeCallback;

			if ( completeCallback ) {
				completeCallback();
			}
		}
	} );
}( wp.customize, jQuery ) );
