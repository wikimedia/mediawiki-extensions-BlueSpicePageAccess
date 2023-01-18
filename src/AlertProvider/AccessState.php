<?php

namespace BlueSpice\PageAccess\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\IAlertProvider;
use MediaWiki\MediaWikiServices;

class AccessState extends AlertProviderBase {

	/**
	 * Output Message if Tag connected
	 * @return string
	 */
	public function getHTML() {
		$title = $this->skin->getTitle();
		if ( !$title ) {
			return '';
		}

		$groups = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getPagePropHelper( $title )
			->getPageProp( 'bs-page-access' );

		if ( !$groups ) {
			return '';
		}

		return $this->skin->msg( 'bs-pageaccess-access-restricted',
					count( explode( ',', $groups ) ),
					$groups );
	}

	/**
	 * Set type of message
	 * @return string
	 */
	public function getType() {
		return IAlertProvider::TYPE_INFO;
	}

}
