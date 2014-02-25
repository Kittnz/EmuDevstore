<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplatePluginModifier.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
}

/**
 * The 'datediff' modifier calculates the difference between two unix timestamps.
 * 
 * Usage:
 * {$timestamp|datediff}
 * {"123456789"|datediff:$timestamp}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginModifierDatediff implements TemplatePluginModifier {
	/**
	 * @see TemplatePluginModifier::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		// get timestamps
		if (!isset($tagArgs[1])) $tagArgs[1] = TIME_NOW;
		$start = min($tagArgs[0], $tagArgs[1]);
		$end = max($tagArgs[0], $tagArgs[1]);
		
		return DateUtil::diff($start, $end, 'string');
	}
}
?>