<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');

/**
 * Shows the list of package update search results.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageUpdateSearchResultPage extends SortablePage {
	public $templateName = 'packageUpdateSearchResult';
	public $defaultSortField = 'packageName';
	
	public $searchID = 0;
	public $search;
	public $packages = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['searchID'])) $this->searchID = intval($_REQUEST['searchID']);
		
		// get search data
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ".$this->searchID."
				AND userID = ".WCF::getUser()->userID."
				AND searchType = 'packages'";
		$this->search = WCF::getDB()->getFirstRow($sql);
		if (empty($this->search['searchID'])) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read packages
		$this->readPackages();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_update
			WHERE	packageUpdateID IN (".$this->search['searchData'].")";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'package':
			case 'packageName':
			case 'author': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * Gets a list of packages.
	 */
	protected function readPackages() {
		if ($this->items) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_package_update
				WHERE		packageUpdateID IN (".$this->search['searchData'].")
				ORDER BY	".$this->sortField." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			while ($row = WCF::getDB()->fetchArray($result)) {
				// default values
				$row['isUnique'] = 0;
				$row['updatableInstances'] = array();
				$row['packageVersions'] = array();
				$row['packageVersion'] = '1.0.0';
				$row['instances'] = 0;
				
				// get package versions
				$sql = "SELECT	packageVersion
					FROM	wcf".WCF_N."_package_update_version
					WHERE	packageUpdateID IN (
							SELECT	packageUpdateID
							FROM	wcf".WCF_N."_package_update
							WHERE	package = '".escapeString($row['package'])."'
						)";
				$result2 = WCF::getDB()->sendQuery($sql);
				while ($row2 = WCF::getDB()->fetchArray($result2)) {
					$row['packageVersions'][] = $row2['packageVersion'];
				}
				
				if (count($row['packageVersions'])) {
					// remove duplicates
					$row['packageVersions'] = array_unique($row['packageVersions']);
					// sort versions
					usort($row['packageVersions'], array('Package', 'compareVersion'));
					// take lastest version
					$row['packageVersion'] = end($row['packageVersions']);
				}
					
				// get installed instances
				$sql = "SELECT	package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
					FROM	wcf".WCF_N."_package package
					WHERE 	package.package = '".escapeString($row['package'])."'";
				$result2 = WCF::getDB()->sendQuery($sql);
				while ($row2 = WCF::getDB()->fetchArray($result2)) {
					$row['instances']++;

					// is already installed unique?
					if ($row2['isUnique'] == 1) $row['isUnique'] = 1;
					
					// check update support
					if (Package::compareVersion($row2['packageVersion'], $row['packageVersion'], '<')) {
						$row['updatableInstances'][] = $row2;
					}
				}
				
				$this->packages[] = $row;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'searchID' => $this->searchID,
			'packages' => $this->packages,
			'selectedPackages' => array()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package.database');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage'));
		
		parent::show();
	}
}
?>