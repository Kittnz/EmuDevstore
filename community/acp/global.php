<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
// define paths
define('RELATIVE_WBB_DIR', '../');

// include config
$packageDirs = array();
require_once(dirname(dirname(__FILE__)).'/config.inc.php');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');
if (!count($packageDirs)) $packageDirs[] = WBB_DIR;
$packageDirs[] = WCF_DIR;

// starting wbb acp
require_once(WBB_DIR.'lib/system/WBBACP.class.php');
new WBBACP();
?>