<?php

namespace BlueSpice\PageAccess\Hook\PageContentSave;

use BlueSpice\Hook\PageContentSave;
use Title;
use ManualLogEntry;

class EditPageAccess extends PageContentSave {

	/**
	 * Returns an array of title objects that are used as templates in the given Wikitext.
	 * @param string $wikitext Wiki markup
	 * @return array Title objects
	 */
	public function getTemplateTitles( $wikitext ) {
		# not very sophisticated but only used for lockout prevention
		$regex = '|{{:(.*?)}}|';
		$matches = [];
		preg_match_all( $regex, $wikitext, $matches );
		$templateTitles = [];
		foreach ( $matches[1] as $templateTitleText ) {
			$tmpTitle = Title::newFromText( $templateTitleText );
			if ( !is_null( $tmpTitle ) ) {
				$templateTitles[] = $tmpTitle;
			}
		}
		return $templateTitles;
	}

	protected function doProcess() {
		# Prevent user from locking himself out of his own page
		$editInfo = $this->wikipage->prepareContentForEdit(
			$this->content, null, $this->user
		);

		$prop = $editInfo->output->getProperty( 'bs-page-access' );
		$checkAccessService = $this->getServices()->getService( 'BSPageAccessCheckAccess' );

		if ( $prop ) {
			$accessGroups = $checkAccessService->groupsStringToArray( $prop );
		} else {
			$accessGroups = [];
		}
		if ( $checkAccessService->processGroups( $this->user, $accessGroups ) ) {
			$this->status->fatal( 'bs-pageaccess-error-not-member-of-given-groups' );
			return false;
		}

		# Also check if user includes forbidden templates
		$templateTitles = $this->getTemplateTitles( $this->content->getNativeData() );
		foreach ( $templateTitles as $templateTitle ) {
			if ( !$checkAccessService->isUserAllowed( $templateTitle, $this->user ) ) {
				$this->status->fatal(
					'bs-pageaccess-error-included-forbidden-template',
					$templateTitle->getText() );
				return false;
			}
		}
		$service = $this->getServices()->getBSUtilityFactory();
		$accessGroupsOld = $checkAccessService->groupsStringToArray(
			$service->getPagePropHelper( $this->wikipage->getTitle() )->getPageProp( 'bs-page-access' )
		);

		if ( $accessGroups != $accessGroupsOld ) {
			// Create a log entry for the change on the page-access settings
			$title = $this->wikipage->getTitle();
			$logger = new ManualLogEntry( 'bs-pageaccess', 'change' );
			$logger->setPerformer( $this->user );
			$logger->setTarget( $title );
			$logger->setParameters( [
				'4::accessGroups' => $accessGroups
			] );
			$logger->insert();
		}

		# All seems good. Let user save.
		return true;
	}

}
