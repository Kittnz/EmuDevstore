<?php
// wcf imports
require_once(WCF_DIR.'lib/system/template/TemplatePluginPrefilter.class.php');
require_once(WCF_DIR.'lib/system/template/TemplateScriptingCompiler.class.php');

/**
 * The 'icon' prefilter compiles static icon paths.
 * 
 * Usage:
 * {icon}iconS.png{/icon}
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginPrefilterIcon implements TemplatePluginPrefilter {
	/**
	 * @see TemplatePluginPrefilter::execute()
	 */
	public function execute($sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		$sourceContent = preg_replace("~{$ldq}icon{$rdq}([\w\.]+){$ldq}/icon{$rdq}~", '{literal}<?php echo StyleManager::getStyle()->getIconPath(\'$1\'); ?>{/literal}', $sourceContent);

		return $sourceContent;
	}
}
?>