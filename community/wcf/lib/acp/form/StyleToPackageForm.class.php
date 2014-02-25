<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');

/**
 * Shows the style set defaults form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleToPackageForm extends ACPForm {
	// system
	public $templateName = 'styleToPackage';
	public $activeMenuItem = 'wcf.acp.menu.link.style.toPackage';
	public $neededPermissions = 'admin.style.canEditStyle';
	
	/**
	 * list of styles
	 *
	 * @var	array<Style>
	 */
	public $styles = array();
	
	/**
	 * list of standalone packages.
	 * 
	 * @var	array
	 */
	public $packages = array();
	
	/**
	 * list of default style ids
	 *
	 * @var	array<integer>
	 */
	public $defaultStyleIDArray = array();
	
	/**
	 * list of disabled style ids
	 *
	 * @var	array<array>
	 */
	public $disabledStyleIDArray = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['defaultStyleIDArray']) && is_array($_POST['defaultStyleIDArray'])) $this->defaultStyleIDArray = ArrayUtil::toIntegerArray($_POST['defaultStyleIDArray']);
		if (isset($_POST['disabledStyleIDArray']) && is_array($_POST['disabledStyleIDArray'])) $this->disabledStyleIDArray = ArrayUtil::toIntegerArray($_POST['disabledStyleIDArray']);
		foreach ($this->disabledStyleIDArray as $packageID => $styleIDArray) {
			if (is_array($styleIDArray)) {
				$this->disabledStyleIDArray[intval($packageID)] = $styleIDArray;
			}
			else {
				unset($this->disabledStyleIDArray[$packageID]);
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// remove old data
		$sql = "DELETE FROM wcf".WCF_N."_style_to_package";
		WCF::getDB()->sendQuery($sql);
		
		// save data
		$styleToPackage = array();
		foreach ($this->defaultStyleIDArray as $packageID => $styleID) {
			if (!isset($styleToPackage[$packageID])) $styleToPackage[$packageID] = array();
			if (!isset($styleToPackage[$packageID][$styleID])) $styleToPackage[$packageID][$styleID] = array('isDefault' => 0, 'disabled' => 0);
			$styleToPackage[$packageID][$styleID]['isDefault'] = 1;
		}
		foreach ($this->disabledStyleIDArray as $packageID => $styleIDArray) {
			if (!isset($styleToPackage[$packageID])) $styleToPackage[$packageID] = array();
			foreach ($styleIDArray as $styleID) {
				if (!isset($styleToPackage[$packageID][$styleID])) $styleToPackage[$packageID][$styleID] = array('isDefault' => 0, 'disabled' => 0);
				$styleToPackage[$packageID][$styleID]['disabled'] = 1;
			}
		}
		
		foreach ($styleToPackage as $packageID => $styles) {
			foreach ($styles as $styleID => $style) {
				$sql = "INSERT INTO	wcf".WCF_N."_style_to_package
							(styleID, packageID, isDefault, disabled)
					VALUES		(".$styleID.", ".$packageID.", ".$style['isDefault'].", ".$style['disabled'].")";
				WCF::getDB()->sendQuery($sql);
			}
		}
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.style.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get default values
		if (!count($_POST)) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style_to_package";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['isDefault']) {
					$this->defaultStyleIDArray[$row['packageID']] = $row['styleID'];
				}
				if ($row['disabled']) {
					if (!isset($this->disabledStyleIDArray[$row['packageID']])) {
						$this->disabledStyleIDArray[$row['packageID']] = array();
					}
					$this->disabledStyleIDArray[$row['packageID']][] = $row['styleID'];
				}
			}
		}
		
		// get standalone packages
		$sql = "SELECT	package.*,
				CASE WHEN package.instanceName <> '' THEN package.instanceName ELSE package.packageName END AS packageName
			FROM	wcf".WCF_N."_package package
			WHERE	package.packageID IN (
					SELECT	packageID
					FROM	wcf".WCF_N."_package_dependency
					WHERE	dependency IN (
							SELECT	packageID
							FROM	wcf".WCF_N."_package
							WHERE	package = 'com.woltlab.wcf.system.style'
						)
				)
				AND standalone = 1";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->packages[] = $row;
		}
		
		// get available styles
		$this->styles = Style::getStyles();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'packages' => $this->packages,
			'styles' => $this->styles,
			'defaultStyleIDArray' => $this->defaultStyleIDArray,
			'disabledStyleIDArray' => $this->disabledStyleIDArray
		));
	}
}
?>