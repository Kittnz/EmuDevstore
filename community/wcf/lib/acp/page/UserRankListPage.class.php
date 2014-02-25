<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');

/**
 * Shows a list of user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.rank
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class UserRankListPage extends SortablePage {
	public $templateName = 'userRankList';
	public $deletedRankID = 0;
	public $defaultSortField = 'rankTitle';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedRankID'])) $this->deletedRankID = intval($_REQUEST['deletedRankID']);
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'rankID':
			case 'groupID':
			case 'neededPoints':
			case 'rankTitle':
			case 'rankImage':
			case 'gender': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_rank";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'ranks' => $this->getRanks(),
			'deletedRankID' => $this->deletedRankID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.rank.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.rank.canEditRank', 'admin.user.rank.canDeleteRank'));
		
		parent::show();
	}
	
	/**
	 * Returns a list of ranks.
	 */
	protected function getRanks() {
		if (!$this->items) return array();
		
		$ranks = array();
		$sql = "SELECT		rank.*, usergroup.groupName
			FROM		wcf".WCF_N."_user_rank rank
			LEFT JOIN	wcf".WCF_N."_group usergroup
			ON		(usergroup.groupID = rank.groupID)
			ORDER BY	rank.".$this->sortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['object'] = new UserRank(null, $row);
			$ranks[] = $row;
		}
		
		return $ranks;
	}
}
?>