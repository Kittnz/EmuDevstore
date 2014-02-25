<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
// include config
$packageDirs = array();
require_once(dirname(__FILE__).'/config.inc.php');

// include WCF
require_once(RELATIVE_WCF_DIR.'global.php');
if (!count($packageDirs)) $packageDirs[] = WBB_DIR;
$packageDirs[] = WCF_DIR;

// starting wbb core
require_once(WBB_DIR.'lib/system/WBBCore.class.php');
new WBBCore();
?>