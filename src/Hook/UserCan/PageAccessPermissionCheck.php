<?php

namespace BlueSpice\PageAccess\Hook\UserCan;

use BlueSpice\Hook\UserCan;

class PageAccessPermissionCheck extends UserCan {

	protected function doProcess() {
		// TODO MRG: Is this list really exhaustive enough?
		if ( !in_array( $this->action, [ 'read', 'edit', 'delete', 'move' ] ) ) {
			return true;
		}

		$checkAccessService = $this->getServices()->getService( 'BSPageAccessCheckAccess' );
		$service = $this->getServices()->getBSUtilityFactory();
		if ( $checkAccessService->isUserAllowed( $this->title, $this->user, $service ) ) {
			return true;
		}

		$this->result = false;
		return false;
	}

}
