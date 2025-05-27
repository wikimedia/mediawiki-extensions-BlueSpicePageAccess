<?php

namespace BlueSpice\PageAccess\Tag;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class PageAccessHandler implements ITagHandler {

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$groups = implode( ',', $params['groups'] ?? [] );
		$oldAccessGroups = $parser->getOutput()->getPageProperty( 'bs-page-access' );
		if ( $oldAccessGroups ) {
			$groups = $oldAccessGroups . "," . $groups;
		}
		$parser->getOutput()->setPageProperty( 'bs-page-access', $groups );

		return '';
	}
}
