<?php

/**
 * Special page for PageAccess for MediaWiki
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @package    BlueSpice_PageAccess
 * @subpackage PageAccess
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
class SpecialPageAccess extends \BlueSpice\SpecialPage {

	/**
	 * Constructor of SpecialPageAccess class
	 */
	public function __construct() {
		parent::__construct( 'PageAccess', 'pageaccess-viewspecialpage' );
	}

	/**
	 * Renders special page output.
	 * @param string $param URL parameters to special page.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function execute( $param ) {
		parent::execute( $param );
		$oOutputPage = $this->getOutput();

		$oOutputPage->addModules( 'ext.PageAccess.manager' );
		$oOutputPage->addHTML( Html::element( 'div', [
				'id' => 'bs-pageaccess-manager'
		] ) );
	}

}
