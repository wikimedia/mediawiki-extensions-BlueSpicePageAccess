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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

class SpecialPageAccess extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'PageAccess', 'pageaccess-viewspecialpage' );
	}

	public function execute( $par ) {
		parent::execute( $par );
		$oOutputPage = $this->getOutput();

		$oOutputPage->addModules( 'ext.PageAccess.manager' );
		$oOutputPage->addHTML( Html::element( 'div', [
			'id' => 'bs-pageaccess-manager'
		]));
	}

}
