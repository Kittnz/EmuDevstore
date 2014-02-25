<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a language variable.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class LanguageVariableDeleteAction extends AbstractAction {
	public $languageItem = '';
	public $languageID = 0;
	public $languageCategoryID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['languageItem'])) $this->languageItem = $_REQUEST['languageItem'];
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		if (isset($_REQUEST['languageCategoryID'])) $this->languageCategoryID = intval($_REQUEST['languageCategoryID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.language.canDeleteLanguage');
		
		// delete language variable
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		LanguageEditor::deleteVariable($this->languageItem);
		
		// delete empty categories
		$deletedCategories = LanguageEditor::deleteEmptyCategories();
		$this->executed();
		
		// forward to list page
		if ($this->languageID && $this->languageCategoryID && !isset($deletedCategories[$this->languageCategoryID])) {
			HeaderUtil::redirect('index.php?form=LanguageEdit&languageID='.$this->languageID.'&languageCategoryID='.$this->languageCategoryID.'&deletedVariable='.$this->languageItem.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		}
		else {
			HeaderUtil::redirect('index.php?page=LanguageList&deletedVariable='.$this->languageItem.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		}
		exit;
	}
}
?>