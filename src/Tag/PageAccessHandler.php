<?php

namespace BlueSpice\PageAccess\Tag;

use BlueSpice\Tag\Handler;

class PageAccessHandler extends Handler {

	public function handle() {
		$oldAccessGroups = $this->parser->getOutput()->getProperty( 'bs-page-access' );
		if ( $oldAccessGroups ) {
			$this->processedArgs['groups'] = $oldAccessGroups . "," . $this->processedArgs['groups'];
		}
		$this->parser->getOutput()->setProperty( 'bs-page-access', $this->processedArgs['groups'] );

		$html = \Html::rawElement(
				'div', [ 'class' => 'alert alert-info' ],
				wfMessage(
					'bs-pageaccess-access-restricted',
					count( explode( ',', $this->processedArgs['groups'] ) ),
					$this->processedArgs['groups']
				)->parse()
		);

		return $html;
	}

}
