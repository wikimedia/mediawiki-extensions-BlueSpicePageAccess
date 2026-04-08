<?php

namespace BlueSpice\PageAccess\Hook\SpecialPageBeforeExecute;

use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\Hook\SpecialPageBeforeExecuteHook;
use MediaWiki\Title\Title;
use PermissionsError;
use TitleFactory;

class CheckBrowsePagePermission implements SpecialPageBeforeExecuteHook {

	/** @var TitleFactory */
	protected $titleFactory;

	/** @var PermissionManager */
	protected $permissionManager;

	/**
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( TitleFactory $titleFactory, PermissionManager $permissionManager ) {
		$this->titleFactory = $titleFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function onSpecialPageBeforeExecute( $special, $subPage ) {
		if ( $special->getName() !== 'Browse' ) {
			return;
		}

		$page = $subPage;
		if ( !$page ) {
			$page = $special->getRequest()->getVal( 'article' );
		}

		if ( !$page ) {
			return;
		}

		$title = $this->parseTitle( $page );
		if ( !$title ) {
			return;
		}

		$userCanReadPage = $this->permissionManager->userCan(
			'read',
			$special->getUser(),
			$title
		);

		if ( !$userCanReadPage ) {
			throw new PermissionsError( 'read' );
		}
	}

	/**
	 * @param string $page
	 * @return Title|null
	 */
	private function parseTitle( string $page ): ?Title {
		$page = ltrim( $page, ':' );

		$page = str_replace(
			[ '-20', '-2F', '-3A', '-5F' ],
			[ ' ', '/', ':', '_' ],
			$page
		);

		// strip SMW subobject identifier
		$page = strstr( $page, '-23', true ) ?: $page;

		return $this->titleFactory->newFromText( $page );
	}

}
