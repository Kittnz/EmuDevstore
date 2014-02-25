<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * ACPTemplate loads and displays template in the admin control panel of the wcf.
 * ACPTemplate does not support template packs.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class ACPTemplate extends Template {
	protected $cachePrefix = 'acp-';
	
	/**
	 * @see Template::__construct()
	 */
	public function __construct($languageID = 0, $templatePaths = array(), $pluginDir = '', $compileDir = '') {
		if (!$templatePaths) $templatePaths = WCF_DIR.'acp/templates/';
		if (!$compileDir) $compileDir = WCF_DIR.'acp/templates/compiled/';
		parent::__construct($languageID, $templatePaths, $pluginDir, $compileDir);
	}
	
	/**
	 * Deletes all compiled acp templates.
	 * 
	 * @param 	string		$compileDir
	 */
	public static function deleteCompiledACPTemplates($compileDir = '') {
		if (empty($compileDir)) $compileDir = WCF_DIR.'acp/templates/compiled/';
		
		self::deleteCompiledTemplates($compileDir);
	}
}
?>