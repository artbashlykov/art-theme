/**
 * Per-page template settings in the block editor document sidebar.
 */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! config || ! wp.plugins || ! wp.plugins.registerPlugin ) {
		return;
	}

	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel )
		|| ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var SelectControl = wp.components && wp.components.SelectControl;
	var ToggleControl = wp.components && wp.components.ToggleControl;
	var useSelect = wp.data && wp.data.useSelect;
	var useEntityProp = wp.coreData && wp.coreData.useEntityProp;
	var createElement = wp.element && wp.element.createElement;
	var META_HIDE_TITLE = 'art_theme_page_hide_title';
	var supportedPostTypes = Array.isArray( config.supportedPostTypes ) ? config.supportedPostTypes : [ 'page' ];
	var hideTitlePostTypes = Array.isArray( config.hideTitlePostTypes ) ? config.hideTitlePostTypes : [ 'page' ];

	function usePageLayoutMeta() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var metaState = useEntityProp( 'postType', postType, 'meta' );

		if ( -1 === supportedPostTypes.indexOf( postType ) ) {
			return null;
		}

		return {
			postType: postType,
			meta: metaState[ 0 ] || {},
			setMeta: metaState[ 1 ],
			hideTitle: !!( metaState[ 0 ] && metaState[ 0 ][ META_HIDE_TITLE ] ),
		};
	}

	function setHideTitle( meta, setMeta, hideTitle ) {
		var next = Object.assign( {}, meta );
		next[ META_HIDE_TITLE ] = hideTitle;
		setMeta( next );
	}

	function PageTemplatePanel() {
		var pageMeta = usePageLayoutMeta();

		if ( ! pageMeta ) {
			return null;
		}

		var templateVariant = pageMeta.meta.art_theme_page_template_variant || 'default';
		var helpText = 'default' === templateVariant ? config.defaultHelp : '';
		var showHideTitle = -1 !== hideTitlePostTypes.indexOf( pageMeta.postType );

		return createElement(
			PluginDocumentSettingPanel,
			{
				name: 'art-theme-page-template',
				title: config.panelTitle,
				className: 'art-theme-page-template-panel',
			},
			createElement( SelectControl, {
				label: config.controlLabel,
				value: templateVariant,
				options: config.choices,
				help: helpText,
				onChange: function ( value ) {
					var next = Object.assign( {}, pageMeta.meta, {
						art_theme_page_template_variant: value,
					} );
					pageMeta.setMeta( next );
				},
			} ),
			showHideTitle
				? createElement(
					'div',
					{ className: 'art-theme-page-template-panel__hide-title' },
					createElement( ToggleControl, {
						label: config.hideTitleLabel,
						help: config.hideTitleHelp,
						checked: pageMeta.hideTitle,
						onChange: function ( value ) {
							setHideTitle( pageMeta.meta, pageMeta.setMeta, value );
						},
					} )
				)
				: null
		);
	}

	wp.plugins.registerPlugin( 'art-theme-page-template', {
		render: PageTemplatePanel,
		icon: null,
	} );
}( window.wp, window.artThemePageTemplate || null ) );
