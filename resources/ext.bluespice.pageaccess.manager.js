( function( mw, $, bs, d, undefined ){
	function _renderGrid() {
		Ext.onReady(function(){
			Ext.Loader.setPath(
				'BS.PageAccess',
				mw.config.get( "wgScriptPath" ) + '/extensions/BlueSpicePageAccess/resources/BS.PageAccess'
			);
			Ext.create( 'BS.PageAccess.panel.Manager', {
				renderTo: 'bs-pageaccess-manager'
			});
		});
	}

	var deps = mw.config.get( 'bsPageAccessManagerDeps', false );
	if( deps ) {
		mw.loader.using( deps, _renderGrid );
	}
	else {
		_renderGrid();
	}

})( mediaWiki, jQuery, blueSpice, document );