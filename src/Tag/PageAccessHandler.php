<?php

namespace BlueSpice\PageAccess\Tag;

use BlueSpice\Tag\Handler;

class PageAccessHandler extends Handler {

	public function handle() {
		$oldAccessGroups = $this->parser->getOutput()->getPageProperty( 'bs-page-access' );
		if ( $oldAccessGroups ) {
			$this->processedArgs['groups'] = $oldAccessGroups . "," . $this->processedArgs['groups'];
		}
		$this->parser->getOutput()->setPageProperty( 'bs-page-access', $this->processedArgs['groups'] );

		return '';
	}
}
