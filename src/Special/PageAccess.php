<?php

namespace BlueSpice\PageAccess\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class PageAccess extends SpecialPage {

	public function __construct() {
		parent::__construct( 'PageAccess', 'pageaccess-viewspecialpage' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$out->addModules( [ 'ext.pageaccess.manager' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-pageaccess-manager' ] ) );
	}
}
