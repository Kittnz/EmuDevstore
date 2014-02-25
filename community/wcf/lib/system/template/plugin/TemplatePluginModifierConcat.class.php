<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * The 'concat' modifier returns the string that results from concatenating the arguments.
 * May have two or more arguments.
 * 
 * Usage:
 * {"left"|concat:$right}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierConcat implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		if (count($tagArgs) < 2) {
			throw new SystemException("concat modifier needs two or more arguments", 12001);
		}
		
		$result = '';
		foreach ($tagArgs as $arg) {
			$result .= $arg;
		}
	
		return $result;	
	}
}
?>