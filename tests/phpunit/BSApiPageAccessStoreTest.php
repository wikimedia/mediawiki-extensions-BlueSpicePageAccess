<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group api
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 */
class BSApiPageAccessStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 2;

	protected function getStoreSchema() {
		return [
			'page_id' => [
				'type' => 'integer'
			],
			'page_namespace' => [
				'type' => 'integer'
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
		$aFixtures = array(
			array( 'Template:Test', "<bs:pageaccess groups='sysop' />"),
			array( 'Test page', "<bs:pageaccess groups='user' />"),
			array( 'Test page 2', "Dummy text")

		);
		foreach( $aFixtures as $aFixture ) {
			$this->insertPage( $aFixture[0], $aFixture[1] );
		}

		return 2;
	}

	protected function getModuleName() {
		return 'bs-pageaccess-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by page_title' => [ 'string', 'ct', 'page_title', 'Test', 2 ]
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
						'value' => 10
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
		return[
			'Test Template:Test for page_title' => [ "page_title", "Test" ],
			'Test Template:Test for page_namespace' => [ "page_namespace", 10 ],
		];
	}
}