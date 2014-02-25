<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/acp/option/Options.class.php');

/**
 * Shows the option import / export form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class OptionImportAndExportForm extends ACPForm {
	public $templateName = 'optionImportAndExport';
	public $activeMenuItem = 'wcf.acp.menu.link.option.importAndExport';
	public $neededPermissions = 'admin.system.canEditOption';
	
	// parameters
	public $optionImport = null;
	
	// data
	public $options = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_FILES['optionImport'])) $this->optionImport = $_FILES['optionImport'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// upload
		if ($this->optionImport && $this->optionImport['error'] != 4) {
			if ($this->optionImport['error'] != 0) {
				throw new UserInputException('optionImport', 'uploadFailed');
			}
			
			try {
				$xml = new XML($this->optionImport['tmp_name']);
				$optionsXML = $xml->getElementTree('options');
				foreach ($optionsXML['children'] as $option) {
					$name = $value = '';
					foreach ($option['children'] as $optionData) {
						switch ($optionData['name']) {
							case 'name':
								$name = $optionData['cdata'];
								break;
							case 'value':
								$value = $optionData['cdata'];
								break;
						}
					}
					
					if (!empty($name)) {
						$this->options[$name] = $value;
					}
				}
			}
			catch (SystemException $e) {
				throw new UserInputException('optionImport', 'importFailed');
			}
		}
		else {
			throw new UserInputException('optionImport');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// get option ids
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_option acp_option,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		acp_option.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDArray = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDArray[$row['optionName']] = $row['optionID'];
		}
		
		// save
		foreach ($this->options as $name => $value) {
			if (isset($optionIDArray[$name])) {
				$sql = "UPDATE	wcf".WCF_N."_option
					SET	optionValue = '".escapeString($value)."'
					WHERE	optionID = ".$optionIDArray[$name];
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// reset cache
		Options::resetFile();
		Options::resetCache();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>