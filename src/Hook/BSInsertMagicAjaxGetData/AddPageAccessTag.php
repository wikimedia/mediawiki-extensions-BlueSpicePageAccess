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
		$descriptor->name = 'pageaccess';
		$descriptor->desc = wfMessage( 'pageaccess' )->plain();
		$descriptor->code = '<bs:pageaccess groups="GROUP" />';
		$descriptor->previewable = false;
		$descriptor->examples = array(
			array(
				'code' => '<bs:pageaccess groups="sysop" />'
			)
		);
		$descriptor->helplink = 'https://help.bluespice.com/index.php/PageAccess';
		$this->response->result[] = $descriptor;

		return true;
	}

}