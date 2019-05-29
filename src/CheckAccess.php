<?php

namespace BlueSpice\PageAccess;

use Title;
use User;
use Hooks;
use Config;
use BlueSpice\Services;
use BlueSpice\IServiceProvider;

class CheckAccess implements IServiceProvider {

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @param string $groups a comma separated list of user groups
	 * @return array
	 */
	public function groupsStringToArray( $groups ) {
		return array_map( "trim", explode( ',', $groups ) );
	}

	/**
	 * @param User $user
	 * @param array $accessGroupsList
	 * @return bool
	 */
	public function processGroups( User $user, array $accessGroupsList ) {
		if ( empty( $accessGroupsList ) ) {
			return false;
		}
		Hooks::run( 'BSPageAccessAddAdditionalAccessGroups', [ &$accessGroupsList ] );
		$userGroups = array_merge( $user->getGroups(), $user->getImplicitGroups() );
		return empty( array_intersect( $accessGroupsList, $userGroups ) );
	}

	/**
	 * Checks if user is allowed to view page
	 * @param Title $title title object
	 * @param User $user the current user
	 * @return bool
	 */
	public function isUserAllowed( Title $title, User $user ) {
		return !$this->processGroups( $user, $this->getAccessGroups( $title ) );
	}

	/**
	 *
	 * @param Title $title
	 * @return string[]
	 */
	public function getAccessGroups( Title $title ) {
		$allTitles = $title->getTemplateLinksFrom();
		$allTitles[] = $title;
		$accessGroups = [];
		foreach ( $allTitles as $titleToCheck ) {
			$prop = $this->getPagePropHelper( $titleToCheck )->getPageProp(
				'bs-page-access'
			);
			if ( !$prop ) {
				continue;
			}
			$accessGroups = array_merge(
				$accessGroups,
				$this->groupsStringToArray( $prop )
			);
		}
		return array_unique( $accessGroups );
	}

	/**
	 *
	 * @param Title $title
	 * @return \BlueSpice\Utility\PagePropHelper
	 */
	protected function getPagePropHelper( Title $title ) {
		return $this->getServices()->getBSUtilityFactory()->getPagePropHelper( $title );
	}

	/**
	 *
	 * @return Services
	 */
	public function getServices() {
		return Services::getInstance();
	}

}
