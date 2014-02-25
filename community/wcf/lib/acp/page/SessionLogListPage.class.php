<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/log/SessionLogList.class.php');

/**
 * Shows a list of log sessions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class SessionLogListPage extends SortablePage {
	// system
	public $templateName = 'sessionLogList';
	public $itemsPerPage = 20;
	public $defaultSortField = 'lastActivityTime';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * session log list object
	 * 
	 * @var	SessionLogList
	 */
	public $sessionLogList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->sessionLogList = new SessionLogList();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read objects
		$this->sessionLogList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->sessionLogList->sqlLimit = $this->itemsPerPage;
		$this->sessionLogList->sqlOrderBy = (($this->sortField != 'accesses' && $this->sortField != 'username') ? 'session_log.' : '').$this->sortField." ".$this->sortOrder;
		$this->sessionLogList->readObjects();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'sessionLogID':
			case 'username':
			case 'ipAddress':
			case 'userAgent':
			case 'time':
			case 'lastActivityTime':
			case 'accesses': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->sessionLogList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'sessionLogs' => $this->sessionLogList->getObjects()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.log.session');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.canViewLog');
		
		parent::show();
	}
}
?>