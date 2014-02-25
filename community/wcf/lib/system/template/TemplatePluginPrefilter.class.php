<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/template/TemplateScriptingCompiler.class.php');
}

/**
 * Prefilters are used to process the source of the template immediately before compilation.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
interface TemplatePluginPrefilter {
	/**
	 * Executes this prefilter.
	 * 
	 * @param	string				$sourceContent	
	 * @param	TemplateScriptingCompiler 	$compiler	
	 * @return 	string				$sourceContent
	 */
	public function execute($sourceContent, TemplateScriptingCompiler $compiler);
}
?>