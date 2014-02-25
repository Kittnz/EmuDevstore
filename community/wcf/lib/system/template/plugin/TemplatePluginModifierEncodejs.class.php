<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * The 'encodejs' modifier formats a string for usage in a single quoted javascript string. 
 * Escapes single quotes and new lines.
 * 
 * Usage:
 * {$string|encodejs}
 * {"bl''ah"|encodejs}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierEncodejs implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		// escape backslash
		$tagArgs[0] = StringUtil::replace("\\", "\\\\", $tagArgs[0]);
		
		// escape singe quote
		$tagArgs[0] = StringUtil::replace("'", "\'", $tagArgs[0]);
		
		// escape new lines
		$tagArgs[0] = StringUtil::replace("\n", '\n', $tagArgs[0]);
		
		// escape slashes
		$tagArgs[0] = StringUtil::replace("/", '\/', $tagArgs[0]);
		
		return $tagArgs[0];
	}
}
?>