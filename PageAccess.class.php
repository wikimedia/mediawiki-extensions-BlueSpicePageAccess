<?php

/**
 * PageAccess extension for BlueSpice
 *
 * Controls access on page level.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Marx Reymann <reymann@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage PageAccess
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * PageAccess adds a tag, used in WikiMarkup as follows:
 * Grant exclusive access to group "sysop": <bs:pageaccess groups="sysop" />
 * Separate multiple groups by commas.
 */
class PageAccess extends BsExtensionMW {

	private static $aAllowedPairs = array(); // <page_id>-<user_id>

	protected function initExt() {
		$this->setHook( 'PageContentSave' );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'userCan' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );
	}

	public function onPageContentSave( &$wikiPage, &$user, &$content, &$summary, $minor, $watchthis, $sectionanchor, &$flags, &$status ) {
		# Prevent user from locking himself out of his own page
		$oEditInfo = $wikiPage->prepareContentForEdit( $content, null, $user );
		$sAccessGroups = $oEditInfo->output->getProperty( 'bs-page-access' );
		if ( !$this->checkAccessGroups( $user, $sAccessGroups ) ) {
			$err[0] = 'bs-pageaccess-error-not-member-of-given-groups';
			throw new PermissionsError( 'edit', array( $err ) ); # since MW 1.18
			return false;
		}

		# Also check if user includes forbidden templates
		$aTemplateTitles = $this->getTemplateTitles( $content->getNativeData() );
		foreach ( $aTemplateTitles as $oTemplateTitle ) {
			if ( !$this->isUserAllowed( $oTemplateTitle, $user ) ) {
				$err[0] = 'bs-pageaccess-error-included-forbidden-template';
				$err[1] = $oTemplateTitle->getText();
				throw new PermissionsError( 'edit', array( $err ) ); # since MW 1.18
				return false;
			}
		}

		$dbr = wfGetDB( DB_REPLICA );
		$sAccessGroupsOld = $dbr->selectField(
			'page_props', 'pp_value', array (
			'pp_page' => $wikiPage->getTitle()->getArticleID(),
			'pp_propname' => 'bs-page-access'
			), __METHOD__ );

		if ( $sAccessGroups != $sAccessGroupsOld ) {
			// Create a log entry for the change on the page-access settings
			$oTitle = $wikiPage->getTitle();
			$oUser = RequestContext::getMain()->getUser();
			$oLogger = new ManualLogEntry( 'bs-pageaccess', 'change' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array (
				'4::accessGroups' => $sAccessGroups
			) );
			$oLogger->insert();
		}

		# All seems good. Let user save.
		return true;
	}

	/**
	 * Returns an array of title objects that are used as templates in the given Wikitext.
	 * @param string $sWikitext Wiki markup
	 * @return array Title objects
	 */
	public function getTemplateTitles( $sWikitext ) {
		$sRegex = '|{{:(.*?)}}|'; # not very sophisticated but only used for lockout prevention
		preg_match_all( $sRegex, $sWikitext, $aMatches );
		$aTemplateTitles = array();
		foreach ( $aMatches[1] as $sTemplateTitleText ) {
			$oTmpTitle = Title::newFromText( $sTemplateTitleText );
			if ( !is_null( $oTmpTitle ) ) $aTemplateTitles[] = $oTmpTitle;
		}
		return $aTemplateTitles;
	}

	/**
	 * Checks if user is in one of the given user groups
	 * @param User $oUser the current user
	 * @param string $sAccessGroups a comma separated list of user groups
	 * @return bool
	 */
	public function checkAccessGroups( $oUser, $sAccessGroups) {
		if ( !$sAccessGroups ) return true;
		$aAccessGroups = array_map("trim", explode( ',', $sAccessGroups ) );
		Hooks::run( 'BSPageAccessAddAdditionalAccessGroups', array( &$aAccessGroups ) );
		$aUserGroups = array_merge( $oUser->getGroups(), $oUser->getImplicitGroups() );
		return (bool) array_intersect( $aAccessGroups, $aUserGroups );
	}

	/**
	 * Checks if user is allowed to view page
	 * @param Title $oPage title or article object
	 * @param User $oUser the current user
	 * @return bool
	 */
	public function isUserAllowed( $oPage, $oUser ) {
		$oPage = ( $oPage instanceof Article ) ? $oPage->getTitle() : $oPage;
		// if this is not a valid article or there is no user,
		// this is none of our business.
		if ( $oPage->getArticleId() == 0 ) {
			return true;
		}

		$sPair = $oPage->getArticleId().'-'.$oUser->getId();

		if( isset( self::$aAllowedPairs[$sPair] ) ) {
			return self::$aAllowedPairs[$sPair];
		}

		$dbr = wfGetDB( DB_REPLICA );
		$bHasAccess = true;
		$aAllTitles = $oPage->getTemplateLinksFrom();
		$aAllTitles[] = $oPage;
		foreach ( $aAllTitles as $oTitleToCheck ) {
			$sAccessGroups = $dbr->selectField(
				'page_props',
				'pp_value',
				array(
					'pp_page' => $oTitleToCheck->getArticleID(),
					'pp_propname' => 'bs-page-access'
				),
				__METHOD__
			);
			if ( !$this->checkAccessGroups( $oUser, $sAccessGroups ) ) {
				$bHasAccess = false;
			}
		}
		self::$aAllowedPairs[$sPair] = $bHasAccess;
		return $bHasAccess;
	}

	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:pageaccess', array( $this, 'onTagBsPageAccess' ) );
		return true;
	}

	public function onUserCan( $title, $user, $action, &$result ) {
		// TODO MRG: Is this list really exhaustive enough?
		if( !in_array($action, array('read', 'edit', 'delete', 'move')) ) {
			return true;
		}
		if ( $this->isUserAllowed( $title, $user ) ) {
			return true;
		}
		$result = false;
		return false;
	}

	/**
	 *
	 * @param type $input
	 * @param string $args
	 * @param Parser $parser
	 * @return string
	 */
	public function onTagBsPageAccess( $input, $args, $parser ) {
		//ignore access tag on mainpage or it will break all ajax calls without title param
		if( $parser->getTitle()->equals( Title::newMainPage() ) === true ) return '';

		$parser->disableCache();
		$parser->getOutput()->setProperty( 'bs-tag-pageaccess', 1 );

		if ( !isset( $args['groups'] ) ) {
			$oErrorView = new ViewTagError( wfMessage( 'bs-pageaccess-error-no-groups-given' )->escaped() );
			return $oErrorView->execute();
		}

		$sOldAccessGroups = $parser->getOutput()->getProperty( 'bs-page-access' );
		if ( $sOldAccessGroups ) $args['groups'] = $sOldAccessGroups . "," . $args['groups'];
		$parser->getOutput()->setProperty( 'bs-page-access', $args['groups'] );
		return '<div class="alert alert-info">'
					. wfMessage( 'bs-pageaccess-access-restricted', count( explode($args[groups], ',') ), $args[groups] )
					. '</div>';
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:pageaccess'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-pageaccess'
			)
		);
		return true;
	}
}
