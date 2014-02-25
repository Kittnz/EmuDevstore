<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
}

/**
 * Template loads and displays template.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class Template {
	protected $languageID = 0;
	protected $templatePaths;
	protected $pluginDir;
	protected $compileDir;
	protected $forceCompile = false;
	protected $prefilters = array();
	protected $compilerObj = '';
	protected $v = array();
	protected $templateStructure;
	protected $cachePrefix = '';
	
	/**
	 * Creates a new Template object.
	 *
	 * @param 	integer 	$languageID
	 * @param 	array 		$templatePaths
	 * @param 	string 		$pluginDir
	 * @param 	string 		$compileDir
	 */
	public function __construct($languageID = 0, $templatePaths = array(), $pluginDir = '', $compileDir = '') {
		$this->setLanguageID($languageID);

		if (!$templatePaths) $templatePaths = array(WCF_DIR.'templates/');
		if (!$pluginDir) $pluginDir = WCF_DIR.'lib/system/template/plugin/';
		if (!$compileDir) $compileDir = WCF_DIR.'templates/compiled/';
		
		$this->setTemplatePaths($templatePaths);
		$this->setCompileDir($compileDir);
		$this->setPluginDir($pluginDir);
		$this->loadTemplateStructure();
		$this->assignSystemVariables();
	}
	
	/**
	 * Loads the cached template structure.
	 */
	protected function loadTemplateStructure() {
		WCF::getCache()->addResource($this->cachePrefix.'templates-'.PACKAGE_ID, WCF_DIR.'cache/cache.'.$this->cachePrefix.'templates-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderTemplates.class.php');
		$this->templateStructure = WCF::getCache()->get($this->cachePrefix.'templates-'.PACKAGE_ID);
	}
	
	/**
	 * Assigns some system variables.
	 */
	protected function assignSystemVariables() {
		$this->v['tpl'] = array();
		
		// assign super globals
		$this->v['tpl']['get'] =& $_GET;
		$this->v['tpl']['post'] =& $_POST;
		$this->v['tpl']['cookie'] =& $_COOKIE;
		$this->v['tpl']['server'] =& $_SERVER;
		$this->v['tpl']['env'] =& $_ENV;
		
		// system info
		$this->v['tpl']['now'] = TIME_NOW;
		$this->v['tpl']['template'] = '';
		$this->v['tpl']['includedTemplates'] = array();
		
		// section / foreach / capture arrays
		$this->v['tpl']['section'] = $this->v['tpl']['foreach'] = $this->v['tpl']['capture'] = array();
	}
	
	/**
	 * Sets the id of the current language.
	 *
	 * @param 	integer 	$languageID
	 */
	public function setLanguageID($languageID) {
		$this->languageID = $languageID;
	}
	
	/**
	 * Sets the dir for the compiled templates.
	 *
	 * @param 	string 		$compileDir
	 */
	public function setCompileDir($compileDir) {
		if (!is_dir($compileDir)) {
			throw new SystemException("'".$compileDir."' is not a valid dir", 11014);
		}
		
		$this->compileDir = $compileDir;
	}
	
	/**
	 * Sets the path to the template sources.
	 *
	 * @param 	mixed 		$templatePath
	 */
	public function setTemplatePaths($templatePaths) {
		if (!is_array($templatePaths)) $templatePaths = array($templatePaths);
		
		foreach ($templatePaths as $templatePath) {
			if (!is_dir($templatePath)) {
				throw new SystemException("'".$templatePath."' is not a valid dir", 11014);
			}
		}
		$this->templatePaths = $templatePaths;
	}
	
	/**
	 * Sets the path to the template plugins.
	 *
	 * @param 	string 		$pluginDir
	 */
	public function setPluginDir($pluginDir) {
		if (!is_dir($pluginDir)) {
			throw new SystemException("'".$pluginDir."' is not a valid dir", 11014);
		}
		$this->pluginDir = $pluginDir;
	}
	
	/**
	 * Returns the current path to the template sources.
	 *
	 * @return 	array 		$templatePath
	 */
	public function getTemplatePaths() {
		return $this->templatePaths;
	}

	/**
	 * Returns the current path to the template plugins
	 *
	 * @return 	string 		$pluginDir
	 */
	public function getPluginDir() {
		return $this->pluginDir;
	}
	
	/**
	 * Assigns a template variable.
	 *
	 * @param 	mixed 		$variable
	 * @param 	mixed 		$value
	 */
	public function assign($variable, $value = '') {
		if (is_array($variable)) {
			foreach ($variable as $key => $val) {
				if ($key != '') {
					$this->assign($key, $val);
				}
			}
		}
		else {
			if (!empty($variable)) {
				$this->v[$variable] = $value;
			}
		}
	}
	
	/**
	 * Appends content to an existing template variable.
	 *
	 * @param 	mixed 		$variable
	 * @param 	mixed 		$value
	 */
	public function append($variable, $value = '') {
		if (is_array($variable)) {
			foreach ($variable as $key => $val) {
				if ($key != '') {
					$this->append($key, $val);
				}
			}
		}
		else {
			if (!empty($variable)) {
				if (isset($this->v[$variable])) {
					if (is_array($this->v[$variable]) && is_array($value)) {
						$keys = array_keys($value);
						foreach ($keys as $key) {
							if (isset($this->v[$variable][$key])) {
								$this->v[$variable][$key] .= $value[$key];
							}
							else { 
								$this->v[$variable][$key] = $value[$key];
							}
						}
					}
					else {
						$this->v[$variable] .= $value;
					}
				}
				else {
					$this->v[$variable] = $value;
				}
			}
		}
	}
	
	/**
	 * Assigns a template variable by reference.
	 *
	 * @param 	string 		$variable
	 * @param	mixed 		$value
	 */
	public function assignByRef($variable, &$value) {
		if (!empty($variable)) {
			$this->v[$variable] = &$value;
		}
	}
	
	/**
	 * Clears an assignment of a template variable.
	 *
	 * @param 	mixed 		$variable
	 */
	public function clearAssign($variable) {
		if (is_array($variable)) {
			foreach ($variable as $val) {
				unset($this->v[$val]);
			}
		}
		else {
			unset($this->v[$variable]);
		}
	}
	
	/**
	 * Clears assignment of all template variables.
	 */
	public function clearAllAssign() {
		$this->v = array();
	}
	
	/**
	 * Outputs a template.
	 *
	 * @param	string		$templateName
	 * @param	boolean		$sendHeaders		if true, content type header is echoed
	 */
	public function display($templateName, $sendHeaders = true) {
		if ($sendHeaders) {
			HeaderUtil::sendHeaders();

			// call shouldDisplay event
			if (!defined('NO_IMPORTS')) EventHandler::fireAction($this, 'shouldDisplay');
		}
		
		$compiledFilename = $this->getCompiledFilename($templateName);
		$sourceFilename = $this->getSourceFilename($templateName);

		// check if compilation is necessary
		if (!$this->isCompiled($sourceFilename, $compiledFilename)) {
			// compile
			$this->compileTemplate($templateName, $sourceFilename, $compiledFilename);
		}

		include($compiledFilename);
		
		if ($sendHeaders) {
			// call didDisplay event
			if (!defined('NO_IMPORTS')) EventHandler::fireAction($this, 'didDisplay');
		}
	}
	
	/**
	 * Returns the absolute filename of a compiled template.
	 *
	 * @param 	string 		$templateName
	 * @param 	integer 	$packageID
	 * @return 	string 		$path
	 */
	public function getCompiledFilename($templateName, $packageID = 0) {
		if ($packageID == 0) $packageID = $this->getPackageID($templateName);
		
		return $this->compileDir.$packageID.'_'.$this->languageID.'_'.$templateName.'.php';
	}
	
	/**
	 * Returns the package id of a template.
	 * 
	 * @param	string		$templateName
	 * @return	integer		package id
	 */
	protected function getPackageID($templateName) {
		if (!isset($this->templateStructure[$templateName])) {
			// unknown template
			// try enable package id
			$packageID = PACKAGE_ID;
		}
		else {
			$packageID = $this->templateStructure[$templateName];
		}
		
		return $packageID;
	}
	
	/**
	 * Returns the absolute filename of a template source.
	 *
	 * @param	string		$templateName
	 * @param	integer		$packageID
	 * @return	string		$path
	 */
	public function getSourceFilename($templateName, $packageID = 0) {
		if ($packageID == 0) $packageID = $this->getPackageID($templateName);
		
		foreach ($this->templatePaths as $templatePath) {
			if (file_exists($templatePath.$templateName.'.tpl')) return $templatePath.$templateName.'.tpl';
		}
		
		throw new SystemException("Unable to find template '$templateName'", 12005);
	}
	
	/**
	 * Checks wheater a template is already compiled or not.
	 *
	 * @param 	string 		$sourceFilename
	 * @param 	string 		$compiledFilename
	 * @return 	boolean 	$isCompiled
	 */
	protected function isCompiled($sourceFilename, $compiledFilename) {
		if ($this->forceCompile || !file_exists($compiledFilename)) {
			return false;
		}
		else {
			$sourceMTime = @filemtime($sourceFilename);
			$compileMTime = @filemtime($compiledFilename);

			return !($sourceMTime >= $compileMTime);
		}
	}
	
	/**
	 * Compiles a template.
	 *
	 * @param 	string 		$templateName
	 * @param 	string 		$sourceFilename
	 * @param 	string 		$compiledFilename
	 */
	protected function compileTemplate($templateName, $sourceFilename, $compiledFilename) {
		// get compiler
		if (!is_object($this->compilerObj)) {
			$this->compilerObj = $this->getCompiler();
		}
		
		// get source
		$sourceContent = $this->getSourceContent($sourceFilename);
		
		// compile template
		$this->compilerObj->compile($templateName, $sourceContent, $compiledFilename);
	}
	
	/**
	 * Returns a new template compiler object.
	 * 
	 * @return	TemplateCompiler
	 */
	protected function getCompiler() {
		require_once(WCF_DIR.'lib/system/template/TemplateCompiler.class.php');
		return new TemplateCompiler($this);
	}
	
	/**
	 * Reads the content of a template file.
	 *
	 * @param	string		$sourceFilename
	 * @return	string		$sourceContent
	 */
	public function getSourceContent($sourceFilename) {
		$sourceContent = '';
		if (!file_exists($sourceFilename) || (($sourceContent = @file_get_contents($sourceFilename)) === false)) {
			throw new SystemException("Could not open template '$sourceFilename' for reading", 12005);
		}
		else {
			return $sourceContent;
		}
	}
	
	/**
	 * Returns the output of a template.
	 *
	 * @param 	string 		$templateName
	 * @return 	string 		output
	 */
	public function fetch($templateName) {
		ob_start();
		$this->display($templateName, false);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	/**
	 * Executes a compiled template scripting source and returns the result.
	 *
	 * @param 	string 		$compiledSource
	 * @return 	string 		result
	 */
	public function fetchString($compiledSource) {
		ob_start();
		eval('?>'.$compiledSource);
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	/**
	 * Includes a template.
	 *
	 * @param 	string 		$templateName
	 * @param 	array 		$variables
	 * @param 	boolean		$sandbox	enables execution in sandbox
	 */
	protected function includeTemplate($templateName, $variables = array(), $sandbox = true) {
		// add new template variables
		if ($sandbox) {
			$templateVars = $this->v;
		}

		if (count($variables)) {
			$this->v = array_merge($this->v, $variables);
		}
		
		$this->display($templateName, false);
		
		if ($sandbox) {
			$this->v = $templateVars;
		}
	}
	
	/**
	 * Returns an array with all prefilters.
	 *
	 * @return 	array
	 */
	public function getPrefilters() {
		return $this->prefilters;
	}
	
	/**
	 * Returns the filename of a plugin.
	 *
	 * @param 	string 		$type
	 * @param 	string 		$tag
	 * @return 	string 				filename
	 */
	public function getPluginFilename($type, $tag) {
		return $this->pluginDir.'TemplatePlugin'.StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($type)).StringUtil::firstCharToUpperCase(StringUtil::toLowerCase($tag)).'.class.php';
	}
	
	/**
	 * Registers a prefilter.
	 * This method accepts a single prefilter name
	 * or an array of prefilters.
	 *
	 * @param 	mixed 		$name
	 */
	public function registerPrefilter($name) {
		if (is_array($name)) {
			foreach ($name as $val) {
				$this->registerPrefilter($val);
			}
		}
		else {
			$this->prefilters[$name] = $name;
		}
	}
	
	/**
	 * Returns the value of a template variable.
	 * 
	 * @param 	string		$varname
	 * @return	mixed
	 */
	public function get($varname) {
		if (isset($this->v[$varname])) {
			return $this->v[$varname];
		}
		
		return null;
	}
	
	/**
	 * Deletes all compiled templates.
	 * 
	 * @param 	string		$compileDir
	 */
	public static function deleteCompiledTemplates($compileDir = '') {
		if (empty($compileDir)) $compileDir = WCF_DIR.'templates/compiled/';
		
		// delete compiled templates
		$matches = glob($compileDir . '*_*_*.php');
		if (is_array($matches)) {
			foreach ($matches as $match) @unlink($match);
		}
	}
}
?>