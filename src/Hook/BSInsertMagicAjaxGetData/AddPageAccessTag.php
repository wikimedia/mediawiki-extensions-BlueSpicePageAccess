<?php

namespace BlueSpice\PageAccess\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddPageAccessTag extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:pageaccess';
		$descriptor->type = 'tag';
		$descriptor->name = wfMessage( 'bs-pageaccess-tag-pageaccess-title' )->plain();
		$descriptor->desc = wfMessage( 'pageaccess' )->plain();
		$descriptor->code = '<bs:pageaccess groups="GROUP" />';
		$descriptor->mwvecommand = 'pageAccessCommand';
		$descriptor->previewable = false;
		$descriptor->examples = array(
			array(
				'code' => '<bs:pageaccess groups="sysop" />'
			)
		);
		$extension =  $this->getServices()->getBSExtensionFactory()->getExtension( 'BlueSpicePageAccess' );
		$descriptor->helplink = $extension->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}