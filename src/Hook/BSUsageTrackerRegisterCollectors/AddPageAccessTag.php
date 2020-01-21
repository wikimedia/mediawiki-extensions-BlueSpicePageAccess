<?php

namespace BlueSpice\PageAccess\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddPageAccessTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:pageaccess'] = [
			'class' => 'Property',
			'config' => [ 'identifier' => 'bs-tag-pageaccess' ]
		];
	}

}
