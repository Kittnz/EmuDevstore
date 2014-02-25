<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/log/SessionLog.class.php');
require_once(WCF_DIR.'lib/data/log/SessionAccessLogList.class.php');

/**
 * Shows the details of a logged sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class SessionLogPage extends SortablePage {
	// system
	public $templateName = 'sessionLog';
	public $itemsPerPage = 20;
	public $defaultSortField = 'time';
	
	/**
	 * session log id
	 *
	 * @var integer
	 */
	public $sessionLogID = 0;
	
	/**
	 * session log object
	 *
	 * @var SessionLog
	 */
	public $sessionLog = null;
	
	/**
	 * session access log list object
	 * 
	 * @var	SessionAccessLogList
	 */
	public $sessionAccessLogList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get session log
		if (isset($_REQUEST['sessionLogID'])) $this->sessionLogID = intval($_REQUEST['sessionLogID']);
		$this->sessionLog = new SessionLog($this->sessionLogID);
		if (!$this->sessionLog->sessionLogID) {
			throw new IllegalLinkException();
		}
		$this->sessionAccessLogList = new SessionAccessLogList();
		$this->sessionAccessLogList->sqlConditions = 'sessionLogID = '.$this->sessionLogID;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->sessionAccessLogList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->sessionAccessLogList->sqlLimit = $this->itemsPerPage;
		$this->sessionAccessLogList->sqlOrderBy = ($this->sortField != 'packageName' ? 'session_access_log.' : '').$this->sortField." ".$this->sortOrder;
		$this->sessionAccessLogList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'sessionAccessLogID':
			case 'ipAddress':
			case 'time':
			case 'requestURI':
			case 'requestMethod':
			case 'className':
			case 'packageName': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->sessionAccessLogList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'sessionAccessLogs' => $this->sessionAccessLogList->getObjects(),
			'sessionLogID' => $this->sessionLogID,
			'sessionLog' => $this->sessionLog
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.log');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.canViewLog');
		
		parent::show();
	}
}
?>