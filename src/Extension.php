<?php

/**
 * BlueSpice MediaWiki
 * Extension: PageAccess
 * Description: Controls access on page level.
 * Authors: Marc Reymann, Leonid Verhovskij, Peter Boehm
 *
 * Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit https://bluespice.com
 *
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @author     Peter Boehm <boehm@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage PageAccess
 * @copyright  Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
/**
 * PageAccess adds a tag, used in WikiMarkup as follows:
 * Grant exclusive access to group "sysop": <bs:pageaccess groups="sysop" />
 * Separate multiple groups by commas.
 */

namespace BlueSpice\PageAccess;

class Extension extends \BlueSpice\Extension {
}
