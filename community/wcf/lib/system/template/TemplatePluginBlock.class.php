<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * Block functions encloses a template block and operate on the contents of this block.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface TemplatePluginBlock {
	/**
	 * Executes this template block.
	 * 
	 * @param	array			$tagArgs
	 * @param	string			$blockContent
	 * @param	Template 		$tplObj
	 * @return	string					output
	 */
	public function execute($tagArgs, $blockContent, Template $tplObj);
	
	/**
	 * Initialises this template block.
	 * 
	 * @param	array			$tagArgs
	 * @param	Template		$tplObj
	 */
	public function init($tagArgs, Template $tplObj);
	
	/**
	 * This function is called before every execution of this block function.
	 * 
	 * @param	Template		$tplObj
	 * @return	boolean
	 */
	public function next(Template $tplObj);
}
?>