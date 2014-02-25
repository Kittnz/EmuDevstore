<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarningList.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows a list of active user warnings.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class UserWarningListPage extends SortablePage {
	// system
	public $templateName = 'userWarningList';
	public $defaultSortField = 'title';
	public $deletedUserWarningID = 0;
	
	/**
	 * user warning list object
	 * 
	 * @var	UserWarningList
	 */
	public $userWarningList = null;
	
	// parameters
	public $username = '';
	public $judge = '';
	public $warningID = 0;
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
		if (isset($_REQUEST['judge'])) $this->judge = StringUtil::trim($_REQUEST['judge']);
		if (isset($_REQUEST['warningID'])) $this->warningID = intval($_REQUEST['warningID']);
		if (isset($_REQUEST['status'])) $this->status = $_REQUEST['status'];
		if (isset($_REQUEST['fromDay'])) $this->fromDay = intval($_REQUEST['fromDay']);
		if (isset($_REQUEST['fromMonth'])) $this->fromMonth = intval($_REQUEST['fromMonth']);
		if (isset($_REQUEST['fromYear'])) $this->fromYear = intval($_REQUEST['fromYear']);
		if (isset($_REQUEST['untilDay'])) $this->untilDay = intval($_REQUEST['untilDay']);
		if (isset($_REQUEST['untilMonth'])) $this->untilMonth = intval($_REQUEST['untilMonth']);
		if (isset($_REQUEST['untilYear'])) $this->untilYear = intval($_REQUEST['untilYear']);
		if (isset($_REQUEST['deletedUserWarningID'])) $this->deletedUserWarningID = intval($_REQUEST['deletedUserWarningID']);
		
		// init list
		$this->userWarningList = new UserWarningList();
		// set conditions
		$this->userWarningList->sqlConditions .= 'user_warning.packageID = '.PACKAGE_ID;
		if (!empty($this->username)) $this->userWarningList->sqlConditions .= " AND user_warning.userID = (SELECT userID FROM wcf".WCF_N."_user WHERE username = '".escapeString($this->username)."')";
		if (!empty($this->judge)) $this->userWarningList->sqlConditions .= " AND user_warning.judgeID = (SELECT userID FROM wcf".WCF_N."_user WHERE username = '".escapeString($this->judge)."')";
		if ($this->warningID) $this->userWarningList->sqlConditions .= " AND user_warning.warningID = ".$this->warningID;
		if ($this->status == 'active') $this->userWarningList->sqlConditions .= " AND (user_warning.expires = 0 OR user_warning.expires > ".TIME_NOW.")";
		else if ($this->status == 'expired') $this->userWarningList->sqlConditions .= " AND user_warning.expires > 0 AND user_warning.expires < ".TIME_NOW;
		if ($this->fromDay && $this->fromMonth && $this->fromYear) $this->userWarningList->sqlConditions .= " AND user_warning.time > ".gmmktime(0, 0, 0, $this->fromMonth, $this->fromDay, $this->fromYear);
		if ($this->untilDay && $this->untilMonth && $this->untilYear) $this->userWarningList->sqlConditions .= " AND user_warning.time < ".gmmktime(0, 0, 0, $this->untilMonth, $this->untilDay, $this->untilYear);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->userWarningList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->userWarningList->sqlLimit = $this->itemsPerPage;
		$this->userWarningList->sqlOrderBy = 'user_warning.'.$this->sortField." ".$this->sortOrder;
		$this->userWarningList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'userWarningID':
			case 'packageID':
			case 'objectID':
			case 'objectType':
			case 'userID':
			case 'judgeID':
			case 'warningID':
			case 'time':
			case 'title':
			case 'points':
			case 'expires': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->userWarningList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'userWarnings' => $this->userWarningList->getObjects(),
			'deletedUserWarningID' => $this->deletedUserWarningID,
			'username' => $this->username,
			'judge' => $this->judge,
			'warningID' => $this->warningID,
			'status' => $this->status,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear,
			'availableWarnings' => Warning::getWarnings()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.infraction.userWarning.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.user.infraction.canEditWarning', 'admin.user.infraction.canDeleteWarning'));
		
		parent::show();
	}
}
?>