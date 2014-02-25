<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
require_once(WCF_DIR.'lib/system/style/StyleManager.class.php');

/**
 * Shows the style import form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleImportForm extends ACPForm {
	public $templateName = 'styleImport';
	public $activeMenuItem = 'wcf.acp.menu.link.style.import';
	public $neededPermissions = 'admin.style.canImportStyle';
	
	public $styleUpload;
	public $styleURL = 'http://';
	public $style;
	public $styleData = array();
	public $filename = '';
	public $newFilename = '';
	public $destinationStyleID = 0;
	public $destinationStyle = null;
	public $availableStyles = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['filename'])) $this->filename = StringUtil::trim($_POST['filename']);
		if (isset($_POST['styleURL'])) $this->styleURL = StringUtil::trim($_POST['styleURL']);
		if (isset($_FILES['styleUpload'])) $this->styleUpload = $_FILES['styleUpload'];
		if (!empty($_POST['destinationStyleID'])) {
			$this->destinationStyleID = intval($_POST['destinationStyleID']);
			$this->destinationStyle = new StyleEditor($this->destinationStyleID);
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (!empty($this->filename)) {
			// import style
			$this->style = StyleEditor::import($this->filename, PACKAGE_ID, ($this->destinationStyle !== null && $this->destinationStyle->styleID ? $this->destinationStyle : null));
		}
		// show style information
		else {
			// import destination
			if ($this->destinationStyle !== null && !$this->destinationStyle->styleID) {
				throw new UserInputException('destinationStyleID');
			}

			// upload style
			if ($this->styleUpload && $this->styleUpload['error'] != 4) {
				if ($this->styleUpload['error'] != 0) {
					throw new UserInputException('styleUpload', 'uploadFailed');
				}
			
				$this->newFilename = $this->styleUpload['tmp_name'];
			
				try {
					$this->styleData = StyleEditor::getStyleData($this->styleUpload['tmp_name']);
				}
				catch (SystemException $e) {
					throw new UserInputException('styleUpload', 'invalid');
				}
				
				// copy file
				$newFilename = FileUtil::getTemporaryFilename('style_');
				if (@move_uploaded_file($this->styleUpload['tmp_name'], $newFilename)) {
					$this->newFilename = $newFilename;
				}
			}
			// download style
			else if ($this->styleURL != 'http://') {
				if (StringUtil::indexOf($this->styleURL, 'http://') !== 0) {
					throw new UserInputException('styleURL', 'downloadFailed');
				}
				
				try {
					$this->newFilename = FileUtil::downloadFileFromHttp($this->styleURL, 'style');
				}
				catch (SystemException $e) {
					throw new UserInputException('styleURL', 'downloadFailed');
				}
				
				try {
					$this->styleData = StyleEditor::getStyleData($this->newFilename);
				}
				catch (SystemException $e) {
					throw new UserInputException('styleURL', 'invalid');
				}
			}
			else {
				throw new UserInputException('styleUpload');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		if (!empty($this->filename)) {
			// reset values
			$this->styleURL = '';
			$this->destinationStyleID = 0;
			
			// reset cache
			WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.style.php');
			WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templatePacks.php');
			WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.templates-*.php');
			$this->saved();
			
			// show success message
			WCF::getTPL()->assign(array(
				'success' => true,
				'style' => $this->style
			));
		}
		else {
			// show style preview
			WCF::getTPL()->assign(array(
				'style' => $this->styleData,
				'filename' => $this->newFilename,
				'destinationStyleID' => $this->destinationStyleID
			));
			$this->saved();
			WCF::getTPL()->display('styleImportPreview');
			exit;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->availableStyles = StyleManager::getAvailableStyles();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'styleURL' => $this->styleURL,
			'destinationStyleID' => $this->destinationStyleID,
			'availableStyles' => $this->availableStyles
		));
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