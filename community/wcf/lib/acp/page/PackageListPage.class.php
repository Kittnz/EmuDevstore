<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');

/**
 * Shows a list of all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageListPage extends SortablePage {
	public $packages = array();
	public $templateName = 'packageList';
	public $itemsPerPage = 50;
	public $defaultSortField = 'packageType';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		$this->readPackages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('packages', $this->packages);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package.view');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage'));
		
		parent::show();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'packageID':
			case 'package':
			case 'packageDir':
			case 'packageName':
			case 'instanceNo':
			case 'packageDescription':
			case 'packageVersion':
			case 'packageDate':
			case 'packageURL':
			case 'parentPackageID':
			case 'isUnique':
			case 'standalone':
			case 'author':
			case 'authorURL':
			case 'installDate':
			case 'updateDate': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets all packages sorted by type and name.
	 */
	protected function readPackages() {
		if ($this->items) {
			try {
				$sql = "SELECT		package.*, CASE WHEN parentPackageID > 0 THEN 1 ELSE 0 END AS plugin,
							CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
					FROM		wcf".WCF_N."_package package
					ORDER BY	".($this->sortField == 'packageType' ? 'standalone '.$this->sortOrder.', plugin '.$this->sortOrder : $this->sortField.' '.$this->sortOrder)
							.($this->sortField != 'packageName' ? ', packageName ASC' : '');
				$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->packages[] = $row;
				}
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "SELECT		package.*, CASE WHEN parentPackageID > 0 THEN 1 ELSE 0 END AS plugin
					FROM		wcf".WCF_N."_package package
					ORDER BY	".($this->sortField == 'packageType' ? 'standalone '.$this->sortOrder.', plugin '.$this->sortOrder : $this->sortField.' '.$this->sortOrder)
							.($this->sortField != 'packageName' ? ', packageName ASC' : '');
				$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->packages[] = $row;
				}
			}
		}
	}
}
?>