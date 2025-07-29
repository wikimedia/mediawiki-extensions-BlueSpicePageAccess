<?php

namespace BlueSpice\PageAccess\Tests;

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group api
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @covers BSApiPageAccessStore
 */
class BSApiPageAccessStoreTest extends BSApiExtJSStoreTestBase {

	/** @var int */
	protected $iFixtureTotal = 1;

	protected function getStoreSchema() {
		return [
			'page_id' => [
				'type' => 'numeric'
			],
			'page_namespace' => [
				'type' => 'numeric'
			],
			'page_title' => [
				'type' => 'string'
			],
			'prefixedText' => [
				'type' => 'string'
			],
			'groups' => [
				'type' => 'array'
			]
		];
	}

	protected function createStoreFixtureData() {
		$dbw = $this->getDb();
		$this->setUp();

		$pageID = $this->insertPage( 'Test' )['id'];
		$dbw->insert(
			'page_props',
			[
				'pp_page' => $pageID,
				'pp_propname' => 'bs-page-access',
				'pp_value' => 'sysop'
			],
			__METHOD__
		);

		return 1;
	}

	protected function getModuleName() {
		return 'bs-pageaccess-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by page_title' => [ 'string', 'ct', 'page_title', 'Test', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by page_namespace and page_title' => [
				[
					[
						'type' => 'numeric',
						'comparison' => 'eq',
						'field' => 'page_namespace',
						'value' => 0
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_title',
						'value' => 'Test'
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return [
			'Test page_title' => [ 'page_title', 'Test' ],
			'Test page_namespace' => [ 'page_namespace', 0 ]
		];
	}
}
