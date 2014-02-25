<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows cron jobs log information.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CronjobsShowLogPage extends SortablePage {
	// sytem
	public $templateName = 'cronjobsShowLog';
	public $itemsPerPage = 100;
	public $defaultSortField = 'execTime';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * list of log entries
	 * 
	 * @var	array
	 */
	public $logEntries = array();
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		// count cronjobs
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_cronjobs_log
			WHERE	cronjobID IN (
					SELECT	cronjobID
					FROM	wcf".WCF_N."_cronjobs cronjobs,
						wcf".WCF_N."_package_dependency package_dependency
					WHERE 	cronjobs.packageID = package_dependency.dependency
						AND package_dependency.packageID = ".PACKAGE_ID."
				)";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get the log entries.
		$this->readLogEntries();
	}
	
	/**
	 * Gets the log entries.
	 */
	protected function readLogEntries() {
		$sql = "SELECT		cronjobs.*, cronjobs_log.*
			FROM		wcf".WCF_N."_cronjobs_log cronjobs_log
			LEFT JOIN	wcf".WCF_N."_cronjobs cronjobs
			ON		(cronjobs.cronjobID = cronjobs_log.cronjobID)
			WHERE		cronjobs_log.cronjobID IN (
						SELECT	cronjobID
						FROM	wcf".WCF_N."_cronjobs cronjobs,
							wcf".WCF_N."_package_dependency package_dependency
						WHERE 	cronjobs.packageID = package_dependency.dependency
							AND package_dependency.packageID = ".PACKAGE_ID."
					)
			ORDER BY	".(($this->sortField == 'classPath' || $this->sortField == 'description') ? 'cronjobs.' : 'cronjobs_log.').$this->sortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['error']) {
				$row['error'] = nl2br(StringUtil::encodeHTML($row['error']));
			}
			$this->logEntries[] = $row;
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'cronjobID':
			case 'classPath':
			case 'description':
			case 'execTime':
			case 'success': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign cronjobs and call the template.
		WCF::getTPL()->assign(array(
			'logEntries' => $this->logEntries
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item.
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.cronjobs.showLog');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.cronjobs.canEditCronjob');
		
		parent::show();
	}
}
?>