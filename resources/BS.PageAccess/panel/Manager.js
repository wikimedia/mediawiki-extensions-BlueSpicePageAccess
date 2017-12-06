Ext.define( 'BS.PageAccess.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],

	initComponent: function() {

		this._gridCols = [
			{
				text: mw.message( 'bs-pageaccess-column-title' ).plain(),
				dataIndex: 'prefixedText',
				sortable: true,
				filterable:true,
				renderer: this.renderPage,
				flex: 1
			},
			{
				text: mw.message( 'bs-pageaccess-column-groups' ).plain(),
				dataIndex: 'groups',
				sortable: true,
				filterable:true,
				renderer: this.renderGroups,
				flex: 1
			}
		];

		this._storeFields = [
			'page_id',
			'page_title',
			'page_namespace',
			'prefixedText',
			'groups'
		];

		this.callParent( arguments );
	},

	makeGridColumns: function(){
		this.colMainConf.columns = this._gridCols;
		return this.colMainConf.columns;
		return this.callParent( arguments );
	},

	makeRowActions: function() {
		return [];
	},

	makeMainStore: function() {
		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-pageaccess-store',
			fields: this._storeFields
		});
		return this.callParent( arguments );
	},

	makeTbarItems: function() {
		return [];
	},

	renderGroups: function( val ) {
		var res = '';
		for( var i = 0; i < val.length; i++ ) {
			if( i > 0 ) {
				res += ", ";
			}
			res += val[i].displayname;
		}
		return res;
	},

	renderPage: function( val ) {
		return '<a href="' + mw.util.getUrl( val ) + '" title="' + val + '">'
				+ val
				+ "</a>";
	}
});