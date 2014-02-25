<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows information about available update package servers.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UpdateServerListPage extends SortablePage {
	public $templateName = 'updateServerList';
	public $defaultSortField = 'server';
	public $updateServers = array();
	public $deletedPackageUpdateServerID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedPackageUpdateServerID'])) $this->deletedPackageUpdateServerID = intval($_REQUEST['deletedPackageUpdateServerID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get servers
		$this->readUpdateServers();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updateServers' => $this->updateServers,
			'deletedPackageUpdateServerID' => $this->deletedPackageUpdateServerID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package.server.view');
		
		// check permission.
		WCF::getUser()->checkPermission('admin.system.package.canEditServer');
		
		parent::show();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'packageUpdateServerID':
			case 'server':
			case 'status':
			case 'errorText':
			case 'timestamp':
			case 'packages': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_update_server";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets all update servers.
	 */
	protected function readUpdateServers() {
		if ($this->items) {
			$sql = "SELECT		server.*, COUNT(package.packageUpdateID) AS packages
				FROM		wcf".WCF_N."_package_update_server server
				LEFT JOIN	wcf".WCF_N."_package_update package
				ON		(package.packageUpdateServerID = server.packageUpdateServerID)
				GROUP BY	server.packageUpdateServerID
				ORDER BY	".($this->sortField != 'packages' ? 'server.' : '') . $this->sortField.' '.$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->updateServers[] = $row;
			}
		}
	}
}
?>