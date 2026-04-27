<?php

namespace BlueSpice\PageAccess;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\IConnectionProvider;

class CheckAccess {

	public const CACHE_KEY = 'BlueSpicePageAccess-AllAccessGroups';
	private const CACHE_TTL = WANObjectCache::TTL_HOUR;

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var WANObjectCache
	 */
	private $cache;

	/**
	 * @var IConnectionProvider
	 */
	private $connectionProvider;

	/**
	 * @var string[]
	 */
	protected static $accessGroupsByPageId = [];

	/**
	 * In-process map of page_id => groups string from page_props, loaded once per request.
	 * @var array|null
	 */
	private $allAccessGroupProps = null;

	/**
	 * @param Config $config
	 * @param WANObjectCache $cache
	 * @param IConnectionProvider $connectionProvider
	 */
	public function __construct(
		Config $config,
		WANObjectCache $cache,
		IConnectionProvider $connectionProvider
	) {
		$this->config = $config;
		$this->cache = $cache;
		$this->connectionProvider = $connectionProvider;
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

		$allAccessGroupProps = $this->getAllAccessGroupProps();

		$allTitles = $title->getTemplateLinksFrom();
		$allTitles[] = $title;
		$accessGroups = [];
		foreach ( $allTitles as $titleToCheck ) {
			$prop = $allAccessGroupProps[$titleToCheck->getArticleID()] ?? '';
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
	 * Bulk-loads all bs-page-access properties from the page_props table,
	 * using the WAN cache to avoid repeated DB lookups.
	 *
	 * @return array Map of page_id => groups string
	 */
	private function getAllAccessGroupProps(): array {
		if ( $this->allAccessGroupProps !== null ) {
			return $this->allAccessGroupProps;
		}

		$cacheKey = $this->cache->makeKey( self::CACHE_KEY );
		$this->allAccessGroupProps = $this->cache->getWithSetCallback(
			$cacheKey,
			self::CACHE_TTL,
			function () {
				return $this->fetchAllAccessGroupPropsFromDB();
			}
		);

		return $this->allAccessGroupProps;
	}

	/**
	 * @return array Map of page_id => groups string
	 */
	private function fetchAllAccessGroupPropsFromDB(): array {
		$dbr = $this->connectionProvider->getReplicaDatabase();
		$result = $dbr->newSelectQueryBuilder()
			->select( [ 'pp_page', 'pp_value' ] )
			->from( 'page_props' )
			->where( [ 'pp_propname' => 'bs-page-access' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$map = [];
		foreach ( $result as $row ) {
			$map[(int)$row->pp_page] = $row->pp_value;
		}
		return $map;
	}

	/**
	 * Invalidate the WAN cache for all access group properties.
	 * Should be called when page access settings change.
	 */
	public function invalidateCache(): void {
		$cacheKey = $this->cache->makeKey( self::CACHE_KEY );
		$this->cache->delete( $cacheKey );
		$this->allAccessGroupProps = null;
		static::$accessGroupsByPageId = [];
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
