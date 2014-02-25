<?php
// com.woltlab.wbb vars
// wbb
if (!defined('WBB_DIR')) define('WBB_DIR', dirname(__FILE__).'/');
if (!defined('RELATIVE_WBB_DIR')) define('RELATIVE_WBB_DIR', '');
if (!defined('WBB_N')) define('WBB_N', '1_1');
$packageDirs[] = WBB_DIR;

// general info
if (!defined('RELATIVE_WCF_DIR'))	define('RELATIVE_WCF_DIR', RELATIVE_WBB_DIR.'wcf/');
if (!defined('PACKAGE_ID')) define('PACKAGE_ID', 48);
if (!defined('PACKAGE_NAME')) define('PACKAGE_NAME', 'WoltLab Burning Board');
if (!defined('PACKAGE_VERSION')) define('PACKAGE_VERSION', '3.1.8');
?>