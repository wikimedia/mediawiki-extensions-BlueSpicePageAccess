bs.util.registerNamespace( 'bs.pgaccss.util.tag' );

bs.pgaccss.util.tag.PageAccessDefinition = function BsVecUtilTagPageAccessDefinition() {
	bs.pgaccss.util.tag.PageAccessDefinition.super.call( this );
};

OO.inheritClass( bs.pgaccss.util.tag.PageAccessDefinition, bs.vec.util.tag.Definition );

bs.pgaccss.util.tag.PageAccessDefinition.prototype.getCfg = function() {
	var cfg = bs.pgaccss.util.tag.PageAccessDefinition.super.prototype.getCfg.call( this );
	return $.extend( cfg, {
		classname : 'PageAccess',
		name: 'pageAccess',
		tagname: 'bs:pageaccess',
		menuItemMsg: 'bs-pageaccess-tag-pageaccess-title',
		descriptionMsg: 'bs-pageaccess-tag-pageaccess-desc',
		attributes: [{
			name: 'groups',
			labelMsg: 'bs-pageaccess-ve-pageaccessinspector-groups',
			helpMsg: 'bs-pageaccess-tag-pageaccess-desc-param-groups',
			type: 'custom',
			widgetClass: bs.vec.ui.GroupListInputWidget,
			default: 'sysop'
		}]
	});
};

bs.vec.registerTagDefinition(
	new bs.pgaccss.util.tag.PageAccessDefinition()
);
