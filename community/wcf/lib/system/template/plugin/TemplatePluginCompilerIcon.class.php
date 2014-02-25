<?php
// wcf imports
require_once(WCF_DIR.'lib/system/template/TemplatePluginCompiler.class.php');
require_once(WCF_DIR.'lib/system/template/TemplateScriptingCompiler.class.php');

/**
 * The 'icon' compiler function compiles dynamic icon paths.
 *
 * Usage:
 * {icon}{$blah}{/icon}
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerIcon implements TemplatePluginCompiler {
	/**
	 * @see TemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('icon');
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see TemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('icon');
		$hash = StringUtil::getRandomID();
		return "<?php \$_icon".$hash." = ob_get_contents(); ob_end_clean(); echo StyleManager::getStyle()->getIconPath(\$_icon".$hash."); ?>";
	}
}
?>