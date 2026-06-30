<?php

namespace BlueSpice\PageAccess\Hook\ChangesListSpecialPageQuery;

use BlueSpice\PageAccess\CheckAccess;
use MediaWiki\Context\RequestContext;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use Wikimedia\Rdbms\IConnectionProvider;

class FilterRestrictedPages implements ChangesListSpecialPageQueryHook {

	/** @var CheckAccess */
	private $checkAccess;

	/** @var IConnectionProvider */
	private $connectionProvider;

	/**
	 * @param CheckAccess $checkAccess
	 * @param IConnectionProvider $connectionProvider
	 */
	public function __construct( CheckAccess $checkAccess, IConnectionProvider $connectionProvider ) {
		$this->checkAccess = $checkAccess;
		$this->connectionProvider = $connectionProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function onChangesListSpecialPageQuery( $name, &$tables, &$fields,
		&$conds, &$query_options, &$join_conds, $opts
	) {
		$user = RequestContext::getMain()->getUser();
		$forbiddenPageIds = $this->checkAccess->getForbiddenPageIdsForUser( $user );
		if ( empty( $forbiddenPageIds ) ) {
			return;
		}
		$dbr = $this->connectionProvider->getReplicaDatabase();
		$conds[] = 'rc_cur_id NOT IN (' . $dbr->makeList( $forbiddenPageIds ) . ')';
	}
}
