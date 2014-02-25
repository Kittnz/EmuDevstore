<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * Modifiers are functions that are applied to a variable in the template 
 * before it is displayed or used in some other context.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface TemplatePluginModifier {
	/**
	 * Executes this modifier.
	 * 
	 * @param	array			$tagArgs		
	 * @param	Template		$tplObj
	 * @return	string			output		
	 */
	public function execute($tagArgs, Template $tplObj);
}
?>