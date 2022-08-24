<?php

namespace BlueSpice\PageAccess;

use BlueSpice\IServiceProvider;
use Config;
use MediaWiki\MediaWikiServices;
use Title;
use User;

class CheckAccess implements IServiceProvider {

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var string[]
	 */
	protected static $accessGroupsByPageId = [];

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
		$this->getServices()->getHookContainer()->run( 'BSPageAccessAddAdditionalAccessGroups', [
			&$accessGroupsList
		] );
		$userGroupManager = $this->getServices()->getUserGroupManager();
		$userGroups = $userGroupManager->getUserEffectiveGroups( $user );
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
		if ( isset( static::$accessGroupsByPageId[ $title->getArticleID() ] ) ) {
			return static::$accessGroupsByPageId[ $title->getArticleID() ];
		}
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

		static::$accessGroupsByPageId[ $title->getArticleID() ] = array_unique( $accessGroups );
		return static::$accessGroupsByPageId[ $title->getArticleID() ];
	}

	/**
	 *
	 * @param Title $title
	 * @return \BlueSpice\Utility\PagePropHelper
	 */
	protected function getPagePropHelper( Title $title ) {
		return $this->getServices()->getService( 'BSUtilityFactory' )->getPagePropHelper( $title );
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	public function getServices() {
		return MediaWikiServices::getInstance();
	}

}
