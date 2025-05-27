<?php

namespace BlueSpice\PageAccess\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\UserGroupManager;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\InputProcessor\Processor\UserGroupListValue;

class PageAccess extends GenericTag {

	public function __construct(
		private readonly UserGroupManager $userGroupManager
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:pageaccess', 'pageaccess' ];
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new PageAccessHandler();
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		$groups = ( new UserGroupListValue( $this->userGroupManager ) )
			->setRequired( true )
			->setListSeparator( ',' );

		return [
			'groups' => $groups
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'group_multiselect',
				'name' => 'groups',
				'label' => Message::newFromKey( 'bs-pageaccess-ve-pageaccessinspector-groups' )->text(),
				'help' => Message::newFromKey( 'bs-pageaccess-tag-pageaccess-desc-param-groups' )->text(),
				'widget_$overlay' => true,
			],
		] );

		return new ClientTagSpecification(
			'PageAccess',
			Message::newFromKey( 'bs-pageaccess-tag-pageaccess-desc' ),
			$formSpec,
			Message::newFromKey( 'bs-pageaccess-tag-pageaccess-title' )
		);
	}
}
