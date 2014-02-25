<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category 	Community Framework
 */
// Constant to get relative path to the wcf-root-dir.
// This constant is already set in each package which got an own config.inc.php 
define('RELATIVE_WCF_DIR', '../');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');

// starting wcf acp
require_once(WCF_DIR.'lib/system/WCFACP.class.php');
new WCFACP();
?>