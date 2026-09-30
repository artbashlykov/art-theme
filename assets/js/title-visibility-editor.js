/**
 * Gutenberg: Astra-like title visibility switch next to the post/page title.
 */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! config || ! wp.data ) {
		return;
	}

	var META = config.metaKey || 'art_theme_hide_title';
	var LEGACY_PAGE_META = config.legacyPageMetaKey || 'art_theme_page_hide_title';
	var supportedTypes = Array.isArray( config.supportedTypes ) ? config.supportedTypes : [ 'post', 'page' ];
	var SHELL_CLASS = 'art-theme-editor-title-shell';
	var HIDDEN_CLASS = 'is-title-hidden';
	var SWITCH_CLASS = 'art-theme-title-switch';
	var started = false;
	var pollTimer = null;

	function getPostType() {
		try {
			var type = wp.data.select( 'core/editor' ).getCurrentPostType();
			return type || '';
		} catch ( e ) {
			return '';
		}
	}

	function isSupported() {
		var type = getPostType();
		return !! type && -1 !== supportedTypes.indexOf( type );
	}

	function getMeta() {
		try {
			return wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		} catch ( e ) {
			return {};
		}
	}

	function isTitleHidden() {
		var meta = getMeta();

		if ( Object.prototype.hasOwnProperty.call( meta, META ) && null !== meta[ META ] && '' !== meta[ META ] ) {
			return !! meta[ META ];
		}

		if ( 'page' === getPostType() && Object.prototype.hasOwnProperty.call( meta, LEGACY_PAGE_META ) ) {
			return !! meta[ LEGACY_PAGE_META ];
		}

		return false;
	}

	function setTitleHidden( hide ) {
		var meta = Object.assign( {}, getMeta() );
		meta[ META ] = !! hide;

		if ( 'page' === getPostType() ) {
			meta[ LEGACY_PAGE_META ] = !! hide;
		}

		try {
			wp.data.dispatch( 'core/editor' ).editPost( { meta: meta } );
		} catch ( e ) {
			// Ignore if editor store is unavailable.
		}
	}

	function getEditorDocuments() {
		var docs = [ document ];
		var iframe = document.querySelector(
			'iframe[name="editor-canvas"], iframe.editor-canvas__iframe, .editor-canvas__iframe'
		);

		if ( iframe ) {
			try {
				if ( iframe.contentDocument && iframe.contentDocument.body ) {
					docs.push( iframe.contentDocument );
					ensureIframeStyles( iframe.contentDocument );
				}
			} catch ( e ) {
				// Cross-origin canvas — ignore.
			}
		}

		return docs;
	}

	function ensureIframeStyles( doc ) {
		if ( ! doc || ! doc.head || doc.getElementById( 'art-theme-title-visibility-editor-css' ) ) {
			return;
		}

		var parentLink = document.getElementById( 'art-theme-title-visibility-editor-css' )
			|| document.querySelector( 'link[href*="title-visibility-editor.css"]' );

		if ( ! parentLink || ! parentLink.href ) {
			return;
		}

		var link = doc.createElement( 'link' );
		link.id = 'art-theme-title-visibility-editor-css';
		link.rel = 'stylesheet';
		link.href = parentLink.href;
		doc.head.appendChild( link );
	}

	function findTitleWrap( doc ) {
		return (
			doc.querySelector( '.edit-post-visual-editor__post-title-wrapper' ) ||
			doc.querySelector( '.editor-visual-editor__post-title-wrapper' ) ||
			doc.querySelector( '.editor-post-title__block' ) ||
			doc.querySelector( '.editor-post-title' ) ||
			doc.querySelector( '[data-type="core/post-title"]' ) ||
			doc.querySelector( 'h1.wp-block-post-title' ) ||
			doc.querySelector( '.wp-block-post-title' )
		);
	}

	function findTitleText( shell ) {
		if ( ! shell ) {
			return null;
		}

		return (
			shell.querySelector( 'h1.editor-post-title__input' ) ||
			shell.querySelector( '.editor-post-title__input' ) ||
			shell.querySelector( 'h1.editor-post-title' ) ||
			shell.querySelector( '.editor-post-title' ) ||
			shell.querySelector( 'h1.wp-block-post-title' ) ||
			shell.querySelector( '.wp-block-post-title' ) ||
			shell.querySelector( 'h1' ) ||
			( shell.matches( 'h1, .editor-post-title, .wp-block-post-title' ) ? shell : null )
		);
	}

	function createSwitch( doc ) {
		var button = doc.createElement( 'button' );
		button.type = 'button';
		button.className = SWITCH_CLASS + ' is-on';
		button.setAttribute( 'aria-pressed', 'true' );

		var track = doc.createElement( 'span' );
		track.className = SWITCH_CLASS + '__track';
		track.setAttribute( 'aria-hidden', 'true' );

		var thumb = doc.createElement( 'span' );
		thumb.className = SWITCH_CLASS + '__thumb';
		thumb.setAttribute( 'aria-hidden', 'true' );

		track.appendChild( thumb );
		button.appendChild( track );

		button.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			setTitleHidden( ! isTitleHidden() );
			syncAll();
		} );

		return button;
	}

	function positionSwitch( shell, switchEl ) {
		var titleEl = findTitleText( shell );

		if ( ! titleEl || ! switchEl ) {
			return;
		}

		var view = titleEl.ownerDocument.defaultView || window;
		var shellRect = shell.getBoundingClientRect();
		var titleRect = titleEl.getBoundingClientRect();
		var styles = view.getComputedStyle( titleEl );
		var paddingLeft = parseFloat( styles.paddingLeft ) || 0;
		var paddingTop = parseFloat( styles.paddingTop ) || 0;
		var paddingBottom = parseFloat( styles.paddingBottom ) || 0;
		var gap = 12;
		var hitSize = 44;
		var textLeft = titleRect.left - shellRect.left + paddingLeft;
		var textTop = titleRect.top - shellRect.top + paddingTop;
		var textHeight = Math.max( 22, titleRect.height - paddingTop - paddingBottom );
		// Approximate first-line center for multi-line titles.
		var lineHeight = parseFloat( styles.lineHeight );
		if ( ! lineHeight || isNaN( lineHeight ) ) {
			lineHeight = Math.min( textHeight, parseFloat( styles.fontSize ) * 1.2 || 32 );
		}
		var firstLineCenter = textTop + ( Math.min( textHeight, lineHeight ) / 2 );
		var spaceLeft = textLeft;
		var stacked = spaceLeft < ( hitSize + gap + 4 );

		// Phones: always stack. Tablets keep inline when there is side room.
		if ( view.innerWidth && view.innerWidth <= 600 ) {
			stacked = true;
		}

		shell.classList.toggle( 'is-switch-stacked', stacked );

		if ( stacked ) {
			switchEl.style.left = Math.round( Math.max( 0, textLeft ) ) + 'px';
			switchEl.style.top = Math.round( Math.max( hitSize, textTop - 8 ) ) + 'px';
			return;
		}

		switchEl.style.left = Math.round( textLeft - gap ) + 'px';
		switchEl.style.top = Math.round( firstLineCenter ) + 'px';
	}

	function ensureShell( doc ) {
		var wrap = findTitleWrap( doc );

		if ( ! wrap ) {
			return null;
		}

		var shell = wrap.classList.contains( SHELL_CLASS ) ? wrap : wrap.closest( '.' + SHELL_CLASS );

		if ( ! shell ) {
			wrap.classList.add( SHELL_CLASS );
			shell = wrap;
		}

		if ( ! shell.querySelector( '.' + SWITCH_CLASS ) ) {
			shell.insertBefore( createSwitch( doc ), shell.firstChild );
		}

		return shell;
	}

	function syncShell( shell ) {
		if ( ! shell ) {
			return;
		}

		var hidden = isTitleHidden();
		var switchEl = shell.querySelector( '.' + SWITCH_CLASS );

		shell.classList.toggle( HIDDEN_CLASS, hidden );

		if ( ! switchEl ) {
			return;
		}

		switchEl.classList.toggle( 'is-on', ! hidden );
		switchEl.classList.toggle( 'is-off', hidden );
		switchEl.setAttribute( 'aria-pressed', hidden ? 'false' : 'true' );
		switchEl.setAttribute(
			'aria-label',
			hidden ? ( config.enableLabel || '' ) : ( config.disableLabel || '' )
		);
		switchEl.title = hidden ? ( config.enableLabel || '' ) : ( config.disableLabel || '' );

		positionSwitch( shell, switchEl );
	}

	function syncAll() {
		if ( ! isSupported() ) {
			return;
		}

		getEditorDocuments().forEach( function ( doc ) {
			syncShell( ensureShell( doc ) );
		} );
	}

	function observeDocument( doc ) {
		if ( ! doc || ! doc.body || doc.documentElement.getAttribute( 'data-art-theme-title-obs' ) ) {
			return;
		}

		doc.documentElement.setAttribute( 'data-art-theme-title-obs', '1' );

		var observer = new MutationObserver( function () {
			window.requestAnimationFrame( syncAll );
		} );

		observer.observe( doc.body, {
			childList: true,
			subtree: true,
		} );
	}

	function start() {
		if ( started || ! isSupported() ) {
			return;
		}

		started = true;

		if ( pollTimer ) {
			window.clearInterval( pollTimer );
			pollTimer = null;
		}

		syncAll();
		getEditorDocuments().forEach( observeDocument );

		wp.data.subscribe( function () {
			syncAll();
		} );

		window.addEventListener( 'resize', function () {
			syncAll();
		} );

		window.setInterval( function () {
			getEditorDocuments().forEach( observeDocument );
			syncAll();
		}, 1000 );
	}

	function boot() {
		start();

		if ( started ) {
			return;
		}

		// Editor store / post type may not be ready on first paint.
		pollTimer = window.setInterval( function () {
			start();
		}, 250 );

		wp.data.subscribe( function () {
			start();
		} );
	}

	if ( wp.domReady ) {
		wp.domReady( boot );
	} else if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}( window.wp, window.artThemeTitleVisibility || null ) );
