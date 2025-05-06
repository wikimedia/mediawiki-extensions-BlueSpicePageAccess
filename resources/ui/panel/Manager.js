bs.util.registerNamespace( 'ext.bluespice.pageaccess.ui.panel' );

ext.bluespice.pageaccess.ui.panel.Manager = function ( cfg ) {
	ext.bluespice.pageaccess.ui.panel.Manager.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-pageaccess-store',
		pageSize: 25
	} );

	this.setup();
};

OO.inheritClass( ext.bluespice.pageaccess.ui.panel.Manager, OO.ui.PanelLayout );

ext.bluespice.pageaccess.ui.panel.Manager.prototype.setup = function () {
	this.gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( this.gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.pageaccess.ui.panel.Manager.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			prefixedText: {
				headerText: mw.message( 'bs-pageaccess-column-title' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => new OO.ui.HtmlSnippet( mw.html.element(
					'a',
					{
						href: mw.util.getUrl( value )
					},
					value
				) )
			},
			groups: {
				headerText: mw.message( 'bs-pageaccess-column-groups' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value ) => value.map( ( group ) => group.displayname ).join( ', ' )
			}
		},
		store: this.store,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();
					const $table = $( '<table>' );

					const $thead = $( '<thead>' )
						.append( $( '<tr>' )
							.append( $( '<th>' ).text( mw.message( 'bs-pageaccess-column-title' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-pageaccess-column-groups' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins
							const record = response[ id ];
							const groups = record.groups.map( ( group ) => group.displayname ).join( ' - ' ); // CSV comma delimiter

							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.prefixedText ) )
								.append( $( '<td>' ).text( groups ) )
							);
						}
					}

					$table.append( $thead, $tbody );

					deferred.resolve( `<table>${ $table.html() }</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};
