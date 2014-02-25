<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspensionList.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows a list of user suspensions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class UserSuspensionListPage extends SortablePage {
	// system
	public $templateName = 'userSuspensionList';
	public $defaultSortField = 'expires';
	public $defaultSortOrder = 'DESC';
	public $deletedUserSuspensionID = 0;

	/**
	 * user suspension list object
	 * 
	 * @var	UserSuspensionList
	 */
	public $userSuspensionList = null;
	
	// parameters
	public $username = '';
	public $suspensionID = 0;
	public $status = '';
	public $fromDay = 0;
	public $fromMonth = 0;
	public $fromYear = 0;
	public $untilDay = 0;
	public $untilMonth = 0;
	public $untilYear = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// parameters
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['suspensionID'])) $this->suspensionID = intval($_REQUEST['suspensionID']);
		if (isset($_REQUEST['status'])) $this->status = $_REQUEST['status'];
		if (isset($_REQUEST['fromDay'])) $this->fromDay = intval($_REQUEST['fromDay']);
		if (isset($_REQUEST['fromMonth'])) $this->fromMonth = intval($_REQUEST['fromMonth']);
		if (isset($_REQUEST['fromYear'])) $this->fromYear = intval($_REQUEST['fromYear']);
		if (isset($_REQUEST['untilDay'])) $this->untilDay = intval($_REQUEST['untilDay']);
		if (isset($_REQUEST['untilMonth'])) $this->untilMonth = intval($_REQUEST['untilMonth']);
		if (isset($_REQUEST['untilYear'])) $this->untilYear = intval($_REQUEST['untilYear']);
		if (isset($_REQUEST['deletedUserSuspensionID'])) $this->deletedUserSuspensionID = intval($_REQUEST['deletedUserSuspensionID']);
		
		// init list
		$this->userSuspensionList = new UserSuspensionList();
		// set conditions
		$this->userSuspensionList->sqlConditions .= 'user_suspension.packageID = '.PACKAGE_ID;
		if (!empty($this->username)) $this->userSuspensionList->sqlConditions .= " AND user_suspension.userID = (SELECT userID FROM wcf".WCF_N."_user WHERE username = '".escapeString($this->username)."')";
		if ($this->suspensionID) $this->userSuspensionList->sqlConditions .= " AND user_suspension.suspensionID = ".$this->suspensionID;
		if ($this->status == 'active') $this->userSuspensionList->sqlConditions .= " AND (user_suspension.expires = 0 OR user_suspension.expires > ".TIME_NOW.")";
		else if ($this->status == 'expired') $this->userSuspensionList->sqlConditions .= " AND user_suspension.expires > 0 AND user_suspension.expires < ".TIME_NOW;
		if ($this->fromDay && $this->fromMonth && $this->fromYear) $this->userSuspensionList->sqlConditions .= " AND user_suspension.time > ".gmmktime(0, 0, 0, $this->fromMonth, $this->fromDay, $this->fromYear);
		if ($this->untilDay && $this->untilMonth && $this->untilYear) $this->userSuspensionList->sqlConditions .= " AND user_suspension.time < ".gmmktime(0, 0, 0, $this->untilMonth, $this->untilDay, $this->untilYear); 
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->userSuspensionList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->userSuspensionList->sqlLimit = $this->itemsPerPage;
		$this->userSuspensionList->sqlOrderBy = (($this->sortField != 'title' && $this->sortField != 'username') ? 'user_suspension.' : '').$this->sortField." ".$this->sortOrder;
		$this->userSuspensionList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'userSuspensionID':
			case 'packageID':
			case 'userID':
			case 'suspensionID':
			case 'time':
			case 'expires':
			case 'title':
			case 'username': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->userSuspensionList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'userSuspensions' => $this->userSuspensionList->getObjects(),
			'deletedUserSuspensionID' => $this->deletedUserSuspensionID,
			'username' => $this->username,
			'suspensionID' => $this->suspensionID,
			'status' => $this->status,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear,
			'availableSuspensions' => Suspension::getSuspensions()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.infraction.userSuspension.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.infraction.canEditSuspension', 'admin.user.infraction.canDeleteSuspension'));
		
		parent::show();
	}
}
?>