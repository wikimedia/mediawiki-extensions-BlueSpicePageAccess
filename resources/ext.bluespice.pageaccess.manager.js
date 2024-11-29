( ( $ ) => {

	$( () => {
		const $container = $( '#bs-pageaccess-manager' ); // eslint-disable-line no-jquery/no-global-selector
		if ( $container.length === 0 ) {
			return;
		}

		const panel = new ext.bluespice.pageaccess.ui.panel.Manager();

		$container.append( panel.$element );
	} );

} )( jQuery );
