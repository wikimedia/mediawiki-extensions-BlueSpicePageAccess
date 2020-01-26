<?php

use BlueSpice\PageAccess\CheckAccess;
use MediaWiki\MediaWikiServices;

return [
	'BSPageAccessCheckAccess' => function ( MediaWikiServices $services ) {
		return new CheckAccess( $services->getConfigFactory()->makeConfig( 'bsg' ) );
	},
];
