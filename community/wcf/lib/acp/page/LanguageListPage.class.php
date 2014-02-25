<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows a list of all installed languages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.language
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class LanguageListPage extends SortablePage {
	public $templateName = 'languageList';
	public $defaultSortField = 'languageCode';
	public $itemsPerPage = 30;
	public $deletedLanguageID = 0;
	public $deletedVariable = '';
	public $languages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedLanguageID'])) $this->deletedLanguageID = intval($_REQUEST['deletedLanguageID']);
		if (isset($_REQUEST['deletedVariable'])) $this->deletedVariable = $_REQUEST['deletedVariable'];
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'languageID':
			case 'languageCode':
			case 'languageEncoding':
			case 'users':
			case 'variables':
			case 'customVariables': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_language";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readLanguages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languages' => $this->languages,
			'deletedLanguageID' => $this->deletedLanguageID,
			'deletedVariable' => $this->deletedVariable
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.language.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.language.canEditLanguage', 'admin.language.canDeleteLanguage'));
		
		parent::show();
	}
	
	/**
	 * Gets a list of languages.
	 */
	protected function readLanguages() {
		if ($this->items) {
			$sql = "SELECT		language.*,
						(SELECT COUNT(*) FROM wcf".WCF_N."_user user WHERE languageID = language.languageID) AS users,
						(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID) AS variables,
						(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID AND languageHasCustomValue = 1) AS customVariables
				FROM		wcf".WCF_N."_language language
				ORDER BY	".$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->languages[] = $row;
			}
		}
	}
}
?>