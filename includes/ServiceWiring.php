<?php

use MediaWiki\MediaWikiServices;
use \BlueSpice\PageAccess\CheckAccess;

return [
	'BSPageAccessCheckAccess' => function ( MediaWikiServices $services ) {
		return new CheckAccess( $services->getConfigFactory()->makeConfig( 'bsg' ) );
	},
];
