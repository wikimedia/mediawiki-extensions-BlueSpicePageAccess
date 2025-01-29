<?php

namespace BlueSpice\PageAccess\Hook\PageContentSave;

use BlueSpice\Hook\PageContentSave;
use ManualLogEntry;
use MediaWiki\Content\TextContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

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
			if ( $tmpTitle !== null ) {
				$templateTitles[] = $tmpTitle;
			}
		}
		return $templateTitles;
	}

	protected function doProcess() {
		# Prevent user from locking himself out of his own page
		$updater = $this->wikipage->newPageUpdater( $this->user );
		$updater->setContent( SlotRecord::MAIN, $this->content );
		$preparedUpdate = $updater->prepareUpdate();
		$output = $preparedUpdate->getCanonicalParserOutput();

		$services = MediaWikiServices::getInstance();
		$prop = $output->getPageProperty( 'bs-page-access' );
		$checkAccessService = $services->getService( 'BSPageAccessCheckAccess' );

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
		$contentText = ( $this->content instanceof TextContent ) ? $this->content->getText() : '';
		$templateTitles = $this->getTemplateTitles( $contentText );
		foreach ( $templateTitles as $templateTitle ) {
			if ( !$checkAccessService->isUserAllowed( $templateTitle, $this->user ) ) {
				$this->status->fatal(
					'bs-pageaccess-error-included-forbidden-template',
					$templateTitle->getText() );
				return false;
			}
		}
		$title = $this->wikipage->getTitle();
		$pageProps = $services->getPageProps()->getProperties( $title, 'bs-page-access' );
		$accessGroupsOld = $pageProps[$title->getArticleID()] ?? [];

		if ( $accessGroups != $accessGroupsOld ) {
			// Create a log entry for the change on the page-access settings
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
