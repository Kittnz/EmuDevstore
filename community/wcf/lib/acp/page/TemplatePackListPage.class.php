<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows a list of installed template packs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.template
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class TemplatePackListPage extends SortablePage {
	public $templateName = 'templatePackList';
	public $deletedTemplatePackID = 0;
	public $defaultSortField = 'templatePackName';
	
	public $templatePacks = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedTemplatePackID'])) $this->deletedTemplatePackID = intval($_REQUEST['deletedTemplatePackID']);
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'templatePackID':
			case 'templatePackName':
			case 'templatePackFolderName':
			case 'templates': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template_pack";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readTemplatePacks();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templatePacks' => $this->templatePacks,
			'deletedTemplatePackID' => $this->deletedTemplatePackID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.templatepack.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.template.canEditTemplatePack', 'admin.template.canDeleteTemplatePack'));
		
		parent::show();
	}
	
	/**
	 * Gets a list of template packs.
	 */
	protected function readTemplatePacks() {
		if ($this->items) {
			$sql = "SELECT		pack.*, COUNT(template.templateID) AS templates
				FROM		wcf".WCF_N."_template_pack pack
				LEFT JOIN	wcf".WCF_N."_template template
				ON		(template.templatePackID = pack.templatePackID)
				GROUP BY	pack.templatePackID
				ORDER BY	".($this->sortField != 'templates' ? 'pack.' : '').$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->templatePacks[] = $row;
			}
		}
	}
}
?>