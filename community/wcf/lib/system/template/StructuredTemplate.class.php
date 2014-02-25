<?php
// wcf imports
require_once(WCF_DIR.'lib/system/template/Template.class.php');

/**
 * StructuredTemplate extends Template by template pack support.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class StructuredTemplate extends Template {
	protected $templatePackID = 0;
	protected $templatePackCache = array();
	
	/**
	 * Creates a new StructuredTemplate object.
	 *
	 * @param	integer		$templatePackID
	 * @param 	integer 	$languageID
	 * @param 	array 		$templatePaths
	 * @param 	string 		$pluginDir
	 * @param 	string 		$compileDir
	 */
	public function __construct($templatePackID = 0, $languageID = 0, $templatePaths = array(), $pluginDir = '', $compileDir = '') {
		parent::__construct($languageID, $templatePaths, $pluginDir, $compileDir);
		$this->loadTemplatePackCache();
		$this->setTemplatePackID($templatePackID);
	}
	
	/**
	 * Returns the active template pack id.
	 * 
	 * @return	integer
	 */
	public function getTemplatePackID() {
		return $this->templatePackID;
	}
	
	/**
	 * Sets the active template pack id.
	 * 
	 * @param	integer		$templatePackID
	 */
	public function setTemplatePackID($templatePackID) {
		if ($templatePackID && !isset($this->templatePackCache[$templatePackID])) {
			$templatePackID = 0;
			// throw new SystemException("Unknown template pack id '".$templatePackID."'", 12006);
		}
		
		$this->templatePackID = $templatePackID;
	}
	
	/**
	 * Loads cached template pack information.
	 */
	protected function loadTemplatePackCache() {
		WCF::getCache()->addResource('templatePacks', WCF_DIR.'cache/cache.templatePacks.php', WCF_DIR.'lib/system/cache/CacheBuilderTemplatePack.class.php');
		$this->templatePackCache = WCF::getCache()->get('templatePacks');
	}
	
	/**
	 * @see Template::getSourceFilename();
	 */
	public function getSourceFilename($templateName, $packageID = 0) {
		if ($packageID == 0) $packageID = $this->getPackageID($templateName);
		
		foreach ($this->templatePaths as $templatePath) {
			$templatePackID = $this->templatePackID;
			while ($templatePackID != 0) {
				$templatePack = $this->templatePackCache[$templatePackID];
				
				// try to find template in template group
				if (file_exists($templatePath.$templatePack['templatePackFolderName'].$templateName.'.tpl')) {
					return $templatePath.$templatePack['templatePackFolderName'].$templateName.'.tpl';
				}
				
				$templatePackID = $templatePack['parentTemplatePackID'];
			}
			
			// use default template
			if (file_exists($templatePath.$templateName.'.tpl')) {
				return $templatePath.$templateName.'.tpl';
			}
		}
		
		throw new SystemException("Unable to find template '$templateName'", 12005);
	}
	
	/**
	 * @see Template::getCompiledFilename()
	 */
	public function getCompiledFilename($templateName, $packageID = 0) {
		if ($packageID == 0) $packageID = $this->getPackageID($templateName);
		
		return $this->compileDir.$packageID.'_'.$this->templatePackID.'_'.$this->languageID.'_'.$templateName.'.php';
	}
}
?>