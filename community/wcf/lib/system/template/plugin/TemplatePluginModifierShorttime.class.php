<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
	require_once(WCF_DIR.'lib/system/language/Language.class.php');
}

/**
 * The 'shorttime' modifier formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 * 
 * Usage:
 * {$timestamp|shorttime}
 * {"132845333"|shorttime:"Y-m-d h:ia"}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierShorttime implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		if (isset($tagArgs[2])) {
			$useStrftime = $tagArgs[2] ? true : false;
		} else {
			$useStrftime = isset($tagArgs[1]) ? true : false;
		}
		return DateUtil::formatShortTime(isset($tagArgs[1]) ? $tagArgs[1] : null, $tagArgs[0], isset($tagArgs[1]) ? false : true, $useStrftime);
	}
}
?>