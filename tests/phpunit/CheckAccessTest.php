<?php

namespace BlueSpice\PageAccess\Tests;

use BlueSpice\PageAccess\CheckAccess;
use HashBagOStuff;
use MediaWiki\Config\HashConfig;
use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\FakeResultWrapper;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * @covers \BlueSpice\PageAccess\CheckAccess
 * @group BlueSpice
 * @group BlueSpicePageAccess
 */
class CheckAccessTest extends TestCase {

	/**
	 * @var WANObjectCache
	 */
	private $cache;

	/**
	 * @var IConnectionProvider
	 */
	private $connectionProvider;

	/**
	 * @var IDatabase
	 */
	private $db;

	protected function setUp(): void {
		parent::setUp();
		$this->cache = new WANObjectCache( [ 'cache' => new HashBagOStuff() ] );

		$this->db = $this->createMock( IDatabase::class );
		$this->connectionProvider = $this->createMock( IConnectionProvider::class );
		$this->connectionProvider->method( 'getReplicaDatabase' )
			->willReturn( $this->db );

		// Reset the static in-process cache between tests to prevent cross-test pollution
		$prop = ( new \ReflectionClass( CheckAccess::class ) )->getProperty( 'accessGroupsByPageId' );
		$prop->setValue( null, [] );
	}

	private function createCheckAccess(): CheckAccess {
		$config = new HashConfig( [] );
		return new CheckAccess( $config, $this->cache, $this->connectionProvider );
	}

	private function mockDbResult( array $rows ): void {
		$resultRows = [];
		foreach ( $rows as $pageId => $value ) {
			$resultRows[] = (object)[ 'pp_page' => $pageId, 'pp_value' => $value ];
		}

		$queryBuilder = $this->createMock( SelectQueryBuilder::class );
		$queryBuilder->method( 'select' )->willReturnSelf();
		$queryBuilder->method( 'from' )->willReturnSelf();
		$queryBuilder->method( 'where' )->willReturnSelf();
		$queryBuilder->method( 'caller' )->willReturnSelf();
		$queryBuilder->method( 'fetchResultSet' )
			->willReturn( new FakeResultWrapper( $resultRows ) );

		$this->db->method( 'newSelectQueryBuilder' )->willReturn( $queryBuilder );
	}

	public function testGetAccessGroupsBulkLoadsFromCache() {
		$this->mockDbResult( [
			1 => 'sysop,editor',
			2 => 'bureaucrat',
		] );

		$checkAccess = $this->createCheckAccess();

		$title = $this->createMock( Title::class );
		$title->method( 'getArticleID' )->willReturn( 1 );
		$title->method( 'getTemplateLinksFrom' )->willReturn( [] );

		$groups = $checkAccess->getAccessGroups( $title );
		$this->assertEquals( [ 'sysop', 'editor' ], $groups );

		// Second call for a different page should use cache (no additional DB call)
		$title2 = $this->createMock( Title::class );
		$title2->method( 'getArticleID' )->willReturn( 2 );
		$title2->method( 'getTemplateLinksFrom' )->willReturn( [] );

		$groups2 = $checkAccess->getAccessGroups( $title2 );
		$this->assertEquals( [ 'bureaucrat' ], $groups2 );
	}

	public function testGetAccessGroupsReturnsEmptyForUnprotectedPage() {
		$this->mockDbResult( [
			1 => 'sysop',
		] );

		$checkAccess = $this->createCheckAccess();

		$title = $this->createMock( Title::class );
		$title->method( 'getArticleID' )->willReturn( 99 );
		$title->method( 'getTemplateLinksFrom' )->willReturn( [] );

		$groups = $checkAccess->getAccessGroups( $title );
		$this->assertSame( [], $groups );
	}

	public function testGetAccessGroupsIncludesTemplateLinks() {
		$this->mockDbResult( [
			10 => 'editor',
			20 => 'sysop',
		] );

		$checkAccess = $this->createCheckAccess();

		$templateTitle = $this->createMock( Title::class );
		$templateTitle->method( 'getArticleID' )->willReturn( 20 );

		$title = $this->createMock( Title::class );
		$title->method( 'getArticleID' )->willReturn( 10 );
		$title->method( 'getTemplateLinksFrom' )->willReturn( [ $templateTitle ] );

		$groups = $checkAccess->getAccessGroups( $title );
		$this->assertContains( 'editor', $groups );
		$this->assertContains( 'sysop', $groups );
	}

	public function testInvalidateCacheClearsWanCache() {
		$this->mockDbResult( [
			1 => 'sysop',
		] );

		$checkAccess = $this->createCheckAccess();

		$title = $this->createMock( Title::class );
		$title->method( 'getArticleID' )->willReturn( 1 );
		$title->method( 'getTemplateLinksFrom' )->willReturn( [] );

		// First call populates cache
		$groups = $checkAccess->getAccessGroups( $title );
		$this->assertEquals( [ 'sysop' ], $groups );

		// Invalidate cache
		$checkAccess->invalidateCache();

		// Verify the WAN cache key was deleted
		$cacheKey = $this->cache->makeKey( CheckAccess::CACHE_KEY );
		$value = $this->cache->get( $cacheKey );
		$this->assertFalse( $value );
	}

	public function testGroupsStringToArray() {
		$checkAccess = $this->createCheckAccess();

		$this->assertEquals( [ 'sysop', 'editor' ], $checkAccess->groupsStringToArray( 'sysop, editor' ) );
		$this->assertEquals( [], $checkAccess->groupsStringToArray( null ) );
		$this->assertEquals( [ 'sysop' ], $checkAccess->groupsStringToArray( 'sysop' ) );
	}

	public function testCacheIsUsedOnSubsequentInstances() {
		$this->mockDbResult( [
			1 => 'sysop',
		] );

		$config = new HashConfig( [] );

		// First instance populates the WAN cache
		$checkAccess1 = new CheckAccess( $config, $this->cache, $this->connectionProvider );
		$title = $this->createMock( Title::class );
		$title->method( 'getArticleID' )->willReturn( 1 );
		$title->method( 'getTemplateLinksFrom' )->willReturn( [] );
		$checkAccess1->getAccessGroups( $title );

		// Second instance should use WAN cache (DB won't be called again)
		$db2 = $this->createMock( IDatabase::class );
		$db2->expects( $this->never() )->method( 'newSelectQueryBuilder' );

		$connectionProvider2 = $this->createMock( IConnectionProvider::class );
		$connectionProvider2->method( 'getReplicaDatabase' )->willReturn( $db2 );

		// Clear the static in-process cache to simulate a new service instance
		$reflection = new \ReflectionClass( CheckAccess::class );
		$prop = $reflection->getProperty( 'accessGroupsByPageId' );
		$prop->setValue( null, [] );

		$checkAccess2 = new CheckAccess( $config, $this->cache, $connectionProvider2 );
		$title2 = $this->createMock( Title::class );
		$title2->method( 'getArticleID' )->willReturn( 1 );
		$title2->method( 'getTemplateLinksFrom' )->willReturn( [] );

		$groups = $checkAccess2->getAccessGroups( $title2 );
		$this->assertEquals( [ 'sysop' ], $groups );
	}
}
