<?php

namespace BlueSpice\PageAccess\Permission\Lockdown\Module;

use BlueSpice\PageAccess\CheckAccess;
use Config;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use Title;
use User;

class BlockActionsOnTagPageAccess extends \BlueSpice\Permission\Lockdown\Module {

	/**
	 *
	 * @var string[]
	 */
	protected $blockableActions = null;

	/**
	 *
	 * @var CheckAccess
	 */
	protected $accessFactory = null;

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param array $blockableActions
	 * @param CheckAccess $accessFactory
	 */
	protected function __construct( Config $config, IContextSource $context,
		MediaWikiServices $services, array $blockableActions, CheckAccess $accessFactory ) {
		parent::__construct( $config, $context, $services );

		$this->blockableActions = $blockableActions;
		$this->accessFactory = $accessFactory;
	}

	/**
	 *
	 * @param Config $config
	 * @param IContextSource $context
	 * @param MediaWikiServices $services
	 * @param array|null $blockableActions
	 * @param CheckAccess|null $accessFactory
	 * @return \static
	 */
	public static function getInstance( Config $config, IContextSource $context,
		MediaWikiServices $services, array $blockableActions = null,
		CheckAccess $accessFactory = null ) {
		if ( !$blockableActions ) {
			$blockableActions = [];
			if ( $config->has( 'PageAccessBlockableActions' ) ) {
				$blockableActions = $config->get(
					'PageAccessBlockableActions'
				);
			}
		}
		if ( !$accessFactory ) {
			$accessFactory = $services->getService( 'BSPageAccessCheckAccess' );
		}

		return new static(
			$config,
			$context,
			$services,
			$blockableActions,
			$accessFactory
		);
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	public function applies( Title $title, User $user ) {
		return $title->exists() && $title->getNamespace() >= 0;
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return bool
	 */
	public function mustLockdown( Title $title, User $user, $action ) {
		if ( !in_array( $action, $this->blockableActions ) ) {
			return false;
		}

		return !$this->accessFactory->isUserAllowed( $title, $user );
	}

	/**
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @return Message
	 */
	public function getLockdownReason( Title $title, User $user, $action ) {
		$actionMsg = $this->msg( "right-$action" );
		$accessGroups = $this->accessFactory->getAccessGroups( $title );
		return $this->msg(
			'bs-pageaccess-blockactionsontag-lockdown-reason',
			$actionMsg->exists() ? $actionMsg : $action,
			count( $accessGroups ),
			implode( ', ', $accessGroups )
		);
	}

}
