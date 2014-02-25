<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/acp/package/update/PackageUpdate.class.php');
require_once(WCF_DIR.'lib/acp/package/update/UpdateServer.class.php');

/**
 * Shows the package update search form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class PackageUpdateSearchForm extends ACPForm {
	public $templateName = 'packageUpdateSearch';
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canInstallPackage');
	public $activeMenuItem = 'wcf.acp.menu.link.package.database';
	
	public $packageUpdateServerIDs = array();
	public $packageName = '';
	public $author = '';
	public $searchDescription = 0;
	public $plugin = 1;
	public $standalone = 1;
	public $other = 0;
	public $ignoreUniques = 1;
	
	public $updateServers = array();
	public $packageUpdateIDs = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->ignoreUniques = $this->plugin = $this->standalone = 0;
		if (isset($_POST['packageUpdateServerIDs']) && is_array($_POST['packageUpdateServerIDs'])) $this->packageUpdateServerIDs = ArrayUtil::toIntegerArray($_POST['packageUpdateServerIDs']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['author'])) $this->author = StringUtil::trim($_POST['author']);
		if (isset($_POST['searchDescription'])) $this->searchDescription = intval($_POST['searchDescription']);
		if (isset($_POST['plugin'])) $this->plugin = intval($_POST['plugin']);
		if (isset($_POST['standalone'])) $this->standalone = intval($_POST['standalone']);
		if (isset($_POST['other'])) $this->other = intval($_POST['other']);
		if (isset($_POST['ignoreUniques'])) $this->ignoreUniques = intval($_POST['ignoreUniques']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// refresh package database
		PackageUpdate::refreshPackageDatabase($this->packageUpdateServerIDs);
		
		// build conditions
		require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
		$conditions = new ConditionBuilder();
		// update servers
		if (count($this->packageUpdateServerIDs)) $conditions->add('packageUpdateServerID IN ('.implode(',', $this->packageUpdateServerIDs).')');
		// name
		if (!empty($this->packageName)) {
			$condition = "packageName LIKE '%".escapeString($this->packageName)."%'";
			if ($this->searchDescription == 1) $condition .= " OR packageDescription LIKE '%".escapeString($this->packageName)."%'";
			$conditions->add('('.$condition.')');
		}
		// author
		if (!empty($this->author)) $conditions->add("author LIKE '".escapeString($this->author)."%'");
		// ignore already installed uniques
		if ($this->ignoreUniques == 1) $conditions->add("package NOT IN (SELECT package FROM wcf".WCF_N."_package WHERE isUnique = 1)");
		// package type
		if (($this->plugin == 0 || $this->standalone == 0 || $this->other == 0) && ($this->plugin == 1 || $this->standalone == 1 || $this->other == 1)) {
			if ($this->standalone == 1) {
				$condition = 'standalone = 1';
				if ($this->plugin == 1) {
					$condition .= " OR plugin IN (SELECT package FROM wcf".WCF_N."_package)";
				}
				else if ($this->other == 1) { 
					$condition .= " OR plugin = ''";
				}
				
				$conditions->add('('.$condition.')');
			}
			else if ($this->plugin == 1) {
				$condition = "plugin IN (SELECT package FROM wcf".WCF_N."_package)";
				if ($this->other == 1) { 
					$condition .= " OR standalone = 0";
				}
				
				$conditions->add('('.$condition.')');
			}
			else if ($this->other) {
				$conditions->add("(standalone = 0 AND plugin = '')");
			}
		}
		
		// search package database
		$packages = array();
		$packageUpdateIDs = '';
		$sql = "SELECT	package, packageUpdateID
			FROM	wcf".WCF_N."_package_update
			".$conditions->get();
		$result = WCF::getDB()->sendQuery($sql, 1000);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($packageUpdateIDs)) $packageUpdateIDs .= ',';
			$packageUpdateIDs .= $row['packageUpdateID'];
			
			if (!isset($packages[$row['package']])) $packages[$row['package']] = array();
			$packages[$row['package']][$row['packageUpdateID']] = array();
		}
		
		if (empty($packageUpdateIDs)) {
			throw new UserInputException('packageName');
		}
		
		// remove duplicates
		$sql = "SELECT		puv.packageVersion, pu.package, pu.packageUpdateID
			FROM		wcf".WCF_N."_package_update_version puv
			LEFT JOIN	wcf".WCF_N."_package_update pu
			ON		(pu.packageUpdateID = puv.packageUpdateID)
			WHERE		puv.packageUpdateID IN (".$packageUpdateIDs.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[$row['package']][$row['packageUpdateID']][] = $row['packageVersion'];
		}
		
		foreach ($packages as $identifier => $packageUpdates) {
			if (count($packageUpdates) > 1) {
				foreach ($packageUpdates as $packageUpdateID => $versions) {
					usort($versions, array('Package', 'compareVersion'));
					$packageUpdates[$packageUpdateID] = array_pop($versions);
				}
				
				uasort($packageUpdates, array('Package', 'compareVersion'));
			}
			
			$keys = array_keys($packageUpdates);
			if (!empty($this->packageUpdateIDs)) $this->packageUpdateIDs .= ',';
			$this->packageUpdateIDs .= array_pop($keys);
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save search
		$sql = "INSERT INTO	wcf".WCF_N."_search
					(userID, searchData, searchDate, searchType)
			VALUES		(".WCF::getUser()->userID.", '".$this->packageUpdateIDs."', ".TIME_NOW.", 'packages')";
		WCF::getDB()->sendQuery($sql);
		$searchID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// forward
		HeaderUtil::redirect('index.php?page=PackageUpdateSearchResult&searchID='.$searchID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->updateServers = UpdateServer::getActiveUpdateServers();
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updateServers' => $this->updateServers,
			'packageName' => $this->packageName,
			'searchDescription' => $this->searchDescription,
			'author' => $this->author,
			'standalone' => $this->standalone,
			'plugin' => $this->plugin,
			'other' => $this->other,
			'packageUpdateServerIDs' => $this->packageUpdateServerIDs,
			'ignoreUniques' => $this->ignoreUniques
		));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>