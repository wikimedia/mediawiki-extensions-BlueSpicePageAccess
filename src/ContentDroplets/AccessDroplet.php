<?php

namespace BlueSpice\PageAccess\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use Message;
use RawMessage;

class AccessDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return new RawMessage( 'PageAccess' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return new RawMessage( "PageAccess description" );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'key';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return 'ext.bluespice.pageaccess.visualEditorTagDefinition';
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'content' ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return 'bs:pageaccess';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [
			'groups' => 'sysop'
		];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'pageAccessCommand';
	}

}
