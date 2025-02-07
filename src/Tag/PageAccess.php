<?php

namespace BlueSpice\PageAccess\Tag;

use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\Tag;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class PageAccess extends Tag {
	/**
	 * @param mixed $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 *
	 * @return IHandler
	 */
	public function getHandler(
		$processedInput,
		array $processedArgs,
		Parser $parser,
		PPFrame $frame
		) {
		return new PageAccessHandler( $processedInput, $processedArgs, $parser, $frame );
	}

	/**
	 *
	 * @return \ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::STRING,
				'groups',
				null,
				null,
				false
			)
		];
	}

	/**
	 * @return string[]
	 */
	public function getTagNames() {
		return [ 'bs:pageaccess', 'pageaccess' ];
	}

	/**
	 * @return bool
	 */
	public function needsDisabledParserCache() {
		return true;
	}
}
