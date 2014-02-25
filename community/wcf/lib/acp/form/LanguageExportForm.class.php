<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');

/**
 * Shows the language edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class LanguageExportForm extends ACPForm {
	public $templateName = 'languageExport';
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	public $neededPermissions = 'admin.language.canEditLanguage';
	
	public $packageNameLength = 0;
	public $selectedPackages = array();
	public $packages = array();
	public $exportCustomValues = false;
	
	public $languageID = 0;
	public $language;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get language
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		$this->language = new LanguageEditor($this->languageID);
		if (!$this->language->getLanguageID()) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['selectedPackages']) && is_array($_POST['selectedPackages'])) {
			$selectedPackages = ArrayUtil::toIntegerArray($_POST['selectedPackages']);
			$this->selectedPackages = array_combine($selectedPackages, $selectedPackages);
			if (isset($this->selectedPackages[0])) unset($this->selectedPackages[0]);
		}
		
		if (isset($_POST['exportCustomValues'])) {
			$this->exportCustomValues = true;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readPackages();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// send headers
		header('Content-Type: text/xml; charset='.CHARSET);
		header('Content-Disposition: attachment; filename="'.$this->language->getLanguageCode().'.xml"');
 		$this->language->export($this->selectedPackages, $this->exportCustomValues);
 		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageID' => $this->languageID,
			'languages' => Language::getLanguages(),
			'selectedPackages' => $this->selectedPackages,
			'packages' => $this->packages,
			'selectAllPackages' => true,
			'packageNameLength' => $this->packageNameLength
		));
	}
	
	/**
	 * Read packages
	 */
	protected function readPackages() {
		$sql = "SELECT		package.*,
					CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
			FROM		wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_language_to_packages language_to_packages
			ON		(language_to_packages.languageID = ".$this->languageID." AND language_to_packages.packageID = package_dependency.dependency)
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.dependency)
			WHERE		package_dependency.packageID = ".PACKAGE_ID."
					AND language_to_packages.languageID IS NOT NULL
			ORDER BY	packageName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['packageNameLength'] = StringUtil::length($row['packageName']);
			$this->packages[] = new Package(null, $row);
			if ($row['packageNameLength'] > $this->packageNameLength) {
				$this->packageNameLength = $row['packageNameLength'];	
			}
		}
	}
}
?>