<?php

namespace BlueSpice\PageAccess;

use User;
use Hooks;

class CheckAccess {

	/**
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @param \Config $config
	 */
	public function __construct( \Config $config ) {
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
	 * @param Service $service
	 * @return bool
	 */
	public function isUserAllowed( $title, $user, $service ) {
		$allTitles = $title->getTemplateLinksFrom();
		$allTitles[] = $title;

		foreach ( $allTitles as $titleToCheck ) {
			$prop = $service->getPagePropHelper( $titleToCheck )->getPageProp( 'bs-page-access' );
			if ( !$prop ) {
				continue;
			}
			$accessGroups = $this->groupsStringToArray( $prop );
			if ( $this->processGroups( $user, $accessGroups ) ) {
				return false;
			}
		}
		return true;
	}

}
