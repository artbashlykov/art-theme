/**
 * Site header navigation: mobile panel, submenu toggles, desktop dropdowns.
 */
( function ( config ) {
	'use strict';

	config = config || {};

	function isDesktopNav() {
		return window.matchMedia( '(min-width: 783px)' ).matches;
	}

	function getDirectChildLink( item ) {
		if ( ! item || ! item.children ) {
			return null;
		}

		for ( var i = 0; i < item.children.length; i++ ) {
			if ( 'A' === item.children[ i ].tagName ) {
				return item.children[ i ];
			}
		}

		return null;
	}

	function getDirectSubmenuToggle( item ) {
		if ( ! item || ! item.children ) {
			return null;
		}

		for ( var i = 0; i < item.children.length; i++ ) {
			if ( item.children[ i ].classList.contains( 'art-theme-site-header__submenu-toggle' ) ) {
				return item.children[ i ];
			}
		}

		return null;
	}

	function getDirectSubmenu( item ) {
		if ( ! item || ! item.children ) {
			return null;
		}

		for ( var i = 0; i < item.children.length; i++ ) {
			if ( item.children[ i ].classList.contains( 'sub-menu' ) ) {
				return item.children[ i ];
			}
		}

		return null;
	}

	function closeSiblingSubmenus( item ) {
		var menu = item.parentElement;

		if ( ! menu ) {
			return;
		}

		Array.prototype.forEach.call( menu.children, function ( sibling ) {
			if ( sibling === item || ! sibling.classList.contains( 'menu-item-has-children' ) ) {
				return;
			}

			toggleSubmenu( sibling, false );
		} );
	}

	function toggleSubmenu( item, open ) {
		var button = getDirectSubmenuToggle( item );

		if ( open ) {
			item.classList.add( 'is-submenu-open' );
		} else {
			item.classList.remove( 'is-submenu-open' );
		}

		if ( button ) {
			button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}
	}

	function getToggleLabel( link ) {
		var base = config.toggleLabel || 'Open submenu';
		var title = link && link.textContent ? link.textContent.replace( /\s+/g, ' ' ).trim() : '';

		return title ? base + ': ' + title : base;
	}

	function injectSubmenuToggles( root ) {
		if ( ! root ) {
			return;
		}

		root.querySelectorAll( '.menu-item-has-children' ).forEach( function ( item ) {
			if ( getDirectSubmenuToggle( item ) ) {
				return;
			}

			var link = getDirectChildLink( item );
			var submenu = getDirectSubmenu( item );

			if ( ! link || ! submenu ) {
				return;
			}

			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'art-theme-site-header__submenu-toggle';
			button.setAttribute( 'aria-expanded', item.classList.contains( 'is-submenu-open' ) ? 'true' : 'false' );
			button.setAttribute( 'aria-label', getToggleLabel( link ) );

			var icon = document.createElement( 'span' );
			icon.className = 'art-theme-site-header__submenu-toggle-icon';
			icon.setAttribute( 'aria-hidden', 'true' );
			button.appendChild( icon );

			item.insertBefore( button, submenu );

			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();

				closeSiblingSubmenus( item );
				toggleSubmenu( item, ! item.classList.contains( 'is-submenu-open' ) );
			} );
		} );
	}

	function initMenuRoot( root ) {
		if ( ! root ) {
			return;
		}

		injectSubmenuToggles( root );
	}

	function initDesktopDropdowns( header ) {
		var menu = header.querySelector( '.art-theme-site-header__desktop .art-theme-site-header__menu' );

		initMenuRoot( menu );

		document.addEventListener( 'click', function ( event ) {
			if ( ! isDesktopNav() || ! menu || header.contains( event.target ) ) {
				return;
			}

			menu.querySelectorAll( '.menu-item-has-children.is-submenu-open' ).forEach( function ( item ) {
				toggleSubmenu( item, false );
			} );
		} );
	}

	function initMobilePanel( header ) {
		var toggle = header.querySelector( '.art-theme-site-header__toggle' );
		var panel = header.querySelector( '.art-theme-site-header__panel' );

		if ( ! toggle || ! panel ) {
			return;
		}

		initMenuRoot( panel );

		var setOpen = function ( open ) {
			header.classList.toggle( 'is-open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			if ( open ) {
				panel.removeAttribute( 'hidden' );
			} else {
				panel.setAttribute( 'hidden', 'hidden' );
				panel.querySelectorAll( '.menu-item-has-children.is-submenu-open' ).forEach( function ( item ) {
					toggleSubmenu( item, false );
				} );
			}
		};

		toggle.addEventListener( 'click', function () {
			setOpen( ! header.classList.contains( 'is-open' ) );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && header.classList.contains( 'is-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );
	}

	function initDesktopCollapse( header ) {
		if ( ! header.classList.contains( 'art-theme-site-header--menu-collapsible' ) ) {
			return;
		}

		var inner = header.querySelector( '.art-theme-site-header__desktop .art-theme-site-header__inner' );

		if ( ! inner ) {
			return;
		}

		var menu = inner.querySelector( '.art-theme-site-header__menu' );
		var brand = inner.querySelector( '.art-theme-site-header__brand' );
		var actions = inner.querySelector( '.art-theme-site-header__actions' );

		// The grid columns use min-width: 0, so an oversized menu overflows its
		// track (overlapping the brand/button) instead of widening the container.
		// Measuring the painted geometry detects this regardless of grid sizing.
		var desktopOverflows = function () {
			if ( ! menu ) {
				return false;
			}

			// Zones widen the grid past its container (button pushed out of view).
			if ( inner.scrollWidth > inner.clientWidth + 1 ) {
				return true;
			}

			// The menu list is clipped by its own box.
			if ( menu.scrollWidth > menu.clientWidth + 1 ) {
				return true;
			}

			var menuRect = menu.getBoundingClientRect();

			if ( actions ) {
				var actionsRect = actions.getBoundingClientRect();

				if ( menuRect.right > actionsRect.left + 1 && menuRect.left < actionsRect.right ) {
					return true;
				}
			}

			if ( brand ) {
				var brandRect = brand.getBoundingClientRect();

				if ( menuRect.left < brandRect.right - 1 && menuRect.right > brandRect.left ) {
					return true;
				}
			}

			return false;
		};

		var evaluate = function () {
			if ( ! isDesktopNav() ) {
				header.classList.remove( 'is-desktop-collapsed' );

				return;
			}

			// Measure in the expanded state; toggling within one frame avoids flicker.
			header.classList.remove( 'is-desktop-collapsed' );

			if ( desktopOverflows() ) {
				header.classList.add( 'is-desktop-collapsed' );
			} else {
				header.classList.remove( 'is-open' );
			}
		};

		evaluate();

		var scheduled = null;
		var schedule = function () {
			if ( scheduled ) {
				window.cancelAnimationFrame( scheduled );
			}

			scheduled = window.requestAnimationFrame( evaluate );
		};

		window.addEventListener( 'resize', schedule );
		window.addEventListener( 'load', evaluate );

		if ( document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function' ) {
			document.fonts.ready.then( evaluate );
		}
	}

	function initHeader( header ) {
		if ( ! header || header.dataset.artThemeHeaderInit ) {
			return;
		}

		header.dataset.artThemeHeaderInit = '1';

		initDesktopDropdowns( header );
		initMobilePanel( header );
		initDesktopCollapse( header );
	}

	document.querySelectorAll( '[data-art-theme-header]' ).forEach( initHeader );
}( window.artThemeHeaderNav || {} ) );
