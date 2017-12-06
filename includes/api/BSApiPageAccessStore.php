<?php

class BSApiPageAccessStore extends BSApiExtJSStoreBase {

	/**
	 * @global array $wgAdditionalGroups
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		global $wgAdditionalGroups;
		$aData = array();

		$oDBr = $this->getDB( DB_REPLICA );
		$oRes = $oDBr->select(
				[ 'page_props' ],
				[ 'pp_page', 'pp_value' ],
				[ 'pp_propname' => 'bs-page-access' ],
				__METHOD__
		);

		foreach( $oRes as $oRow ) {
			$aGroups = [];
			foreach( explode( ',',$oRow->pp_value ) as $sGroup ) {
				$aGroups[] = [
					'group_name' => $sGroup,
					'additional_group' => isset( $wgAdditionalGroups[$sGroup] ),
					'displayname' => wfMessage( "group-$sGroup" )->exists()
						? wfMessage( "group-$sGroup" )
						: $sGroup
					,
				];
			}

			if( !$oTitle = Title::newFromID( $oRow->pp_page ) ) {
				continue;
			}
			$aData[] = (object) [
				'page_id' => (int) $oTitle->getArticleID(),
				'page_namespace' => (int) $oTitle->getNamespace(),
				'page_title' => $oTitle->getText(),
				'prefixedText' => $oTitle->getPrefixedText(),
				'groups' => $aGroups,
			];
		}

		return $aData;
	}

	/**
	 * Performs string filtering the array of groups on the group_name and the
	 * displayname
	 * @param stdClass $oFilter
	 * @param stdClass $aDataSet
	 * @return boolean true if filter applies, false if not
	 */
	public function filterString( $oFilter, $aDataSet ) {
		if( $oFilter->field !== 'groups') {
			return parent::filterString( $oFilter, $aDataSet );
		}

		foreach( $aDataSet->groups as $iKey => $aGroup ) {
			$bRes = BsStringHelper::filter(
				$oFilter->comparison,
				$aGroup['group_name'],
				$oFilter->value
			);
			if( $bRes ) {
				return true;
			}

			$bRes = BsStringHelper::filter(
				$oFilter->comparison,
				$aGroup['displayname'],
				$oFilter->value
			);
			if( $bRes ) {
				return true;
			}
		}

		return false;
	}

}