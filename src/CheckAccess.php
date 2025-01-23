<?php

namespace BlueSpice\PageAccess;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class CheckAccess {

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
	 * @param string|null $groups a comma separated list of user groups
	 * @return array
	 */
	public function groupsStringToArray( ?string $groups ): array {
		if ( $groups === null ) {
			return [];
		}
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
		$pageProps = MediaWikiServices::getInstance()->getPageProps();
		foreach ( $allTitles as $titleToCheck ) {
			$titleProp = $pageProps->getProperties( $titleToCheck, 'bs-page-access' );
			$prop = $titleProp[$titleToCheck->getArticleID()] ?? '';
			if ( $prop === '' ) {
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
