<?php

use MediaWiki\Title\Title;

class BSApiPageAccessStore extends BSApiExtJSStoreBase {

	/**
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		global $wgAdditionalGroups;
		$data = [];

		$dBr = $this->getDB( DB_REPLICA );
		$res = $dBr->select(
			[ 'page_props' ], [ 'pp_page', 'pp_value' ], [ 'pp_propname' => 'bs-page-access' ], __METHOD__
		);

		foreach ( $res as $row ) {
			$groups = [];
			foreach ( explode( ',', $row->pp_value ) as $group ) {
				$groups[] = [
					'group_name' => $group,
					'additional_group' => isset( $wgAdditionalGroups[$group] ),
					'displayname' => wfMessage( "group-$group" )->exists() ?
					wfMessage( "group-$group" ) :
					$group,
				];
			}
			$title = Title::newFromID( $row->pp_page );
			if ( !$title ) {
				continue;
			}
			$data[] = (object)[
					'page_id' => (int)$title->getArticleID(),
					'page_namespace' => (int)$title->getNamespace(),
					'page_title' => $title->getText(),
					'prefixedText' => $title->getPrefixedText(),
					'groups' => $groups,
			];
		}

		return $data;
	}

	/**
	 * Performs string filtering the array of groups on the group_name and the
	 * displayname
	 * @param stdClass $filter
	 * @param stdClass $dataSet
	 * @return bool true if filter applies, false if not
	 */
	public function filterString( $filter, $dataSet ) {
		if ( $filter->field !== 'groups' ) {
			return parent::filterString( $filter, $dataSet );
		}

		foreach ( $dataSet->groups as $key => $group ) {
			if ( BsStringHelper::filter(
					$filter->comparison, $group['group_name'], $filter->value )
			) {
				return true;
			}

			if ( BsStringHelper::filter(
					$filter->comparison, $group['displayname'], $filter->value )
			) {
				return true;
			}
		}

		return false;
	}

}
