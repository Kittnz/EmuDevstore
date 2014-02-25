<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');

/**
 * Shows a list of installed styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class StyleListPage extends MultipleLinkPage {
	public $templateName = 'styleList';
	
	public $styles = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readData() {
		parent::readData();
		
		$this->readStyles();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'styles' => $this->styles
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.style.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.style.canEditStyle', 'admin.style.canDeleteStyle', 'admin.style.canExportStyle'));
		
		parent::show();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_style";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets a list of styles.
	 */
	protected function readStyles() {
		$sql = "SELECT		style.*, (SELECT COUNT(*) FROM wcf".WCF_N."_user WHERE styleID = style.styleID) AS users
			FROM		wcf".WCF_N."_style style
			ORDER BY	isDefault DESC, styleName";
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['image'] && !@file_exists(WCF_DIR.$row['image'])) $row['image'] = '';
			$this->styles[] = $row;
		}
		ksort($this->styles);
	}
}
?>