<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Shows a list of all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class GroupListPage extends SortablePage {
	public $templateName = 'groupList';
	public $deletedGroups = 0;
	public $groups = array();
	public $defaultSortField = 'groupName';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// detect group deletion
		if (isset($_REQUEST['deletedGroups'])) {
			$this->deletedGroups = intval($_REQUEST['deletedGroups']);
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'groupID':
			case 'groupName':
			case 'groupType':
			case 'members': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_group";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		$this->readGroups();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groups' 	=> $this->groups,
			'deletedGroups' => $this->deletedGroups
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.group.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.canEditGroup', 'admin.user.canDeleteGroup'));
		
		parent::show();
	}
	
	/**
	 * Gets all user groups and the number of their members.
	 */
	protected function readGroups() {
		if ($this->items) {
			$sql = "SELECT		user_group.*, (SELECT COUNT(*) FROM wcf".WCF_N."_user_to_groups WHERE groupID = user_group.groupID) AS members
				FROM		wcf".WCF_N."_group user_group
				ORDER BY	".($this->sortField != 'members' ? 'user_group.' : '').$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['deletable'] = (!WCF::getUser()->getPermission('admin.user.canDeleteGroup') || Group::isMember($row['groupID']) || !Group::isAccessibleGroup($row['groupID']) || $row['groupType'] == Group::EVERYONE || $row['groupType'] == Group::GUESTS || $row['groupType'] == Group::USERS) ? 0 : 1;
				$row['editable'] = (WCF::getUser()->getPermission('admin.user.canEditGroup') && Group::isAccessibleGroup($row['groupID'])) ? 1 : 0;
				
				$this->groups[] = $row;
			}
		}
	}
}
?>