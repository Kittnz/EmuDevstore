<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows information about configured cron jobs.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CronjobsListPage extends SortablePage {
	// system
	public $templateName = 'cronjobsList';
	public $defaultSortField = 'description';
	public $deleteJob = 0;
	public $successfulExecuted = false;
	
	/**
	 * list of cronjobs
	 * 
	 * @var	array
	 */
	public $cronjobs = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// detect job deletion.
		if (isset($_REQUEST['deleteJob'])) $this->deleteJob = intval($_REQUEST['deleteJob']);
		// detect execution
		if (isset($_REQUEST['successfulExecuted'])) $this->successfulExecuted = true;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readCronjobs();
	}
	
	/**
	 * Gets the list of cronjobs.
	 */
	protected function readCronjobs() {
		$sql = "SELECT		cronjobs.*
			FROM		wcf".WCF_N."_cronjobs cronjobs,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		cronjobs.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
			ORDER BY	cronjobs.".$this->sortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['editable'] = WCF::getUser()->getPermission('admin.system.cronjobs.canEditCronjob') && $row['canBeEdited'];
			$row['deletable'] = WCF::getUser()->getPermission('admin.system.cronjobs.canDeleteCronjob') && $row['canBeEdited'];
			$row['enableDisable'] = WCF::getUser()->getPermission('admin.system.cronjobs.canEnableDisableCronjob') && $row['canBeDisabled'];
			
			$this->cronjobs[] = $row;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'cronjobs' => $this->cronjobs,
			'deleteJob' => $this->deleteJob,
			'successfulExecuted' => $this->successfulExecuted
		));
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		// count cronjobs
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_cronjobs cronjobs,
				wcf".WCF_N."_package_dependency package_dependency
			WHERE 	cronjobs.packageID = package_dependency.dependency
				AND package_dependency.packageID = ".PACKAGE_ID;
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'description':
			case 'cronjobID':
			case 'nextExec':
			case 'startMinute':
			case 'startHour':
			case 'startDom':
			case 'startMonth':
			case 'startDow': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item.
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.cronjobs.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.system.cronjobs.canEditCronjob', 'admin.system.cronjobs.canDeleteCronjob', 'admin.system.cronjobs.canEnableDisableCronjob'));
		
		parent::show();
	}
}
?>