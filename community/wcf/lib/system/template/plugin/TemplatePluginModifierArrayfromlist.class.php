<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * The 'arrayfromlist' modifier generates an associative array out of a key-value list.
 * The list has key-value pairs separated by : with each pair on an own line:
 * 
 * Example list:
 * key1:value1
 * key2:value2
 * ...
 * 
 * Usage:
 * {$list|arrayfromlist}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierArrayfromlist implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		
		return OptionUtil::parseSelectOptions($tagArgs[0]);
	}
}
?>