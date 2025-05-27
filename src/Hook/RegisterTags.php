<?php

namespace BlueSpice\PageAccess\Hook;

use BlueSpice\PageAccess\Tag\PageAccess;
use MediaWiki\User\UserGroupManager;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct(
		private readonly UserGroupManager $userGroupManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new PageAccess( $this->userGroupManager );
	}
}
