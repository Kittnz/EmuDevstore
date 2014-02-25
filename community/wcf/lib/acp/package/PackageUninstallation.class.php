<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/Package.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * PackageUninstallation executes package uninstallations.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageUninstallation extends PackageInstallationQueue {
	protected $stepAfterPIP = 'packageInstallationPlugins';
	protected $pipSortOrder = 'ASC';
	
	/**
	 * Creates a new PackageUninstallation object.
	 * 
	 * @param	integer		$queueID
	 */
	public function __construct($queueID) {
		parent::__construct($queueID);
		$this->assignPackageInfo();
		$this->uninstall();
	}
	
	/**
	 * @see PackageInstallationQueue::getInstallationInfo()
	 */
	protected function getInstallationInfo() {
		$info = parent::getInstallationInfo();
		$this->openPackage($info);
	}
	
	/**
	 * Creates a Package object with the given package information.
	 * 
	 * @param	array		$info
	 */
	protected function openPackage($info) {
		$this->package = new Package(null, $info);
	}
	
	/**
	 * @see PackageInstallationQueue::assignPackageInfo();
	 */
	protected function assignPackageInfo() {
		WCF::getTPL()->assign(array(
			'packageName' => $this->package->getName(),
			'packageDescription' => $this->package->getDescription(),
			'packageVersion' => $this->package->getVersion(),
			'packageDate' => $this->package->getDate(),
			'packageAuthor' => $this->package->getAuthor(),
			'packageAuthorURL' => $this->package->getAuthorURL()
		));
	}
	
	/**
	 * Steps through the uninstallation process of a package.
	 */
	protected function uninstall() {
		try {
			switch ($this->step) {
				// prepare package uninstallation
				case '':
					$this->nextStep = $this->startUninstallation();
					HeaderUtil::redirect('index.php?page=Package&action='.$this->action.'&queueID='.$this->queueID.'&step='.$this->nextStep.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					exit;
					break;
					
				// show the installation frame
				case 'installationFrame':
					$this->calcProgress(0);
					$this->showInstallationFrame();
					break;
					
				// check requirements
				case 'requirements':
					$this->calcProgress(1);
					$this->nextStep = $this->checkPackageRequirements();
					break;	
			
				// execute package installation plugins	
				case 'execPackageInstallationPlugins':
					$this->calcProgress(2);
					$this->loadPackageInstallationPlugins();
					$this->nextStep = $this->getNextPackageInstallationPlugin('');	
					break;
			
				// uninstall package installation plugins	
				case 'packageInstallationPlugins':
					$this->calcProgress(3);
					$this->nextStep = 'package';
					$this->uninstallPackageInstallationPlugins();
					break;
			
				// delete package	
				case 'package':
					$this->calcProgress(4);
					$this->uninstallPackage();
					WCF::getTPL()->assign('step', 'finish');
					$this->nextStep = $this->finishUninstallation();
					break;
					
				// cancel the installation
				case 'cancel':
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		queueID = ".$this->queueID;
					WCF::getDB()->sendQuery($sql);
					
					HeaderUtil::redirect('index.php?page=PackageList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					break;	
			
				// finish uninstallation
				case 'finish':
					$this->calcProgress(4);
					$this->nextStep = $this->finishUninstallation();
					break;
			
				// execute a package installation plugin	
				default:
					$this->executePackageInstallationPlugin($this->step);
					$this->nextStep = $this->getNextPackageInstallationPlugin($this->step);
			}
		
			WCF::getTPL()->assign('nextStep', $this->nextStep);
			WCF::getTPL()->display('packageInstallationNext');
		}
		catch (SystemException $e) {
			require_once(WCF_DIR.'lib/acp/package/PackageInstallationRollback.class.php');
			if (!($this instanceof PackageInstallationRollback) || $this->step == 'package') {
				$this->showPackageInstallationException($e);
			}
			else {
				$this->step = 'package';
				$this->uninstall();
			}
		}
	}
	
	/**
	 * Uninstalls all package installation plugins of this package.
	 */
	protected function uninstallPackageInstallationPlugins() {
		$sql = "SELECT pluginName FROM	wcf".WCF_N."_package_installation_plugin
			WHERE		packageID = ".$this->packageID;
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			$fileName = $row['pluginName'].'.class.php';
			$directory = WCF_DIR.'lib/acp/package/plugin';
			@unlink($directory.'/'.$fileName);
		}
		
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_plugin
			WHERE		packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Removes package information from database.
	 */
	protected function uninstallPackage() {
		$this->makeClear();

		// delete package
		$sql = "DELETE FROM	wcf".WCF_N."_package
			WHERE		packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		
		// update queue entry
		$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
			SET	packageID = 0
			WHERE	queueID = ".$this->queueID;			
		WCF::getDB()->sendQuery($sql);
		
		// rebuild config.inc.php files if necessary
		// check if this package requires a standalone package (except com.woltlab.wcf)
		$configPackageIDs = array();
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package
			WHERE	packageID IN (
					SELECT	requirement
					FROM	wcf".WCF_N."_package_requirement_map
					WHERE	packageID = ".$this->packageID."
				)
				AND standalone = 1
				AND package <> 'com.woltlab.wcf'";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count'] > 0) {
			// get all dependent standalone packages (except com.woltlab.wcf)
			$sql = "SELECT	packageID
				FROM	wcf".WCF_N."_package
				WHERE	packageID IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package_dependency
						WHERE	dependency = ".$this->packageID."
					)
					AND standalone = 1
					AND packageDir <> ''";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$configPackageIDs[] = $row['packageID'];
			}
		}
		
		// delete requirements
		$sql = "DELETE FROM	wcf".WCF_N."_package_requirement
			WHERE		packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_package_requirement_map
			WHERE		packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		// delete excluded packages
		$sql = "DELETE FROM	wcf".WCF_N."_package_exclusion
			WHERE		packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		
		// reset package cache
		WCF::getCache()->clearResource('packages');
		
		// rebuild package dependencies
		Package::rebuildParentPackageDependencies($this->packageID);
	
		// delete dependencies
		$sql = "DELETE FROM	wcf".WCF_N."_package_dependency
			WHERE		packageID = ".$this->packageID."
					OR dependency = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		
		// rebuild config.inc.php files if necessary
		foreach ($configPackageIDs as $configPackageID) {
			Package::writeConfigFile($configPackageID);
		}
	}
	
	/**
	 * Does nothing.
	 */
	protected function checkPackageRequirements() {
		return 'execPackageInstallationPlugins';
	}
	
	/**
	 * Checks whether this package is required by other packages.
	 * If so than a template will be displayed to warn the user that 
	 * a further uninstallation will uninstall also the dependent packages 
	 */
	public static function checkDependencies() {
		$packageID = 0;
		if (isset($_REQUEST['activePackageID'])) {
			$packageID = intval($_REQUEST['activePackageID']);
		}
		
		// get packages info
		try {
			// create object of uninstalling package
			$package = new Package($packageID);
		}
		catch (SystemException $e) {
			throw new IllegalLinkException(); 
		}
		
		// can not uninstall wcf package.
		if ($package->getPackage() == 'com.woltlab.wcf') {
			throw new IllegalLinkException(); 
		}
		
		$dependentPackages = array();
		if ($package->isRequired()) {
			// get packages that requires this package
			$dependentPackages = self::getPackageDependencies($package->getPackageID());
			$uninstallAvailable = true;
			foreach ($dependentPackages as $dependentPackage) {
				if ($dependentPackage['packageID'] == PACKAGE_ID) {
					$uninstallAvailable = false;
					break;
				}
			}
			
			// show uninstall dependencies template
			if (!isset($_POST['send']) && count($dependentPackages)) {
				WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package');
				
				// show delete requirements sure template
				WCF::getTPL()->assign(array(
					'packageObj' => $package,
					'dependentPackages' => $dependentPackages,
					'activePackageID' => $packageID,
					'uninstallAvailable' => $uninstallAvailable
				));
				WCF::getTPL()->display('packageUninstallationDependencies');
				exit();	
			}
			// uninstall dependencies submitted. add them to queue
			else {
				if (!$uninstallAvailable) {
					throw new IllegalLinkException(); 
				}
				self::addQueueEntries($package, $dependentPackages);
			}
		}
		// no dependencies. add this package to queue
		self::addQueueEntries($package);
	}
	
	/**
	 * Get all packages which require this package.
	 * 
	 * @param	integer		$packageID
	 * @return	array
	 */
	protected static function getPackageDependencies($packageID) {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package 
			WHERE		packageID IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package_requirement_map
						WHERE	requirement = ".$packageID."
					)";
		$result = WCF::getDB()->sendQuery($sql);
		$packages = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Finalises uninstallation of this package.
	 * 
	 * @return 	string 		nextStep
	 */
	protected function finishUninstallation() {
		// reset all cache resources
		WCF::getCache()->clear(WCF_DIR.'cache', '*.php', true);
		
		// unregister package installation plugins
		WCF::getSession()->unregister('queueID'.$this->queueID.'PIPs');
		
		// mark this package uninstallation as done
		$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
			SET	done = 1
			WHERE	queueID = ".$this->queueID;
		WCF::getDB()->sendQuery($sql);
		
		// search for other open queue entries in current level
		$sql = "SELECT		queueID, action
			FROM		wcf".WCF_N."_package_installation_queue
			WHERE		parentQueueID = ".$this->parentQueueID."
					AND processNo = ".$this->processNo."
					AND done = 0
			ORDER BY	queueID";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['queueID'])) {
			// other entries found
			WCF::getTPL()->assign(array(
				'action' => $row['action'],
				'queueID' => $row['queueID'],
				'processNo' => $this->processNo
			));
			
			// reload installation frame
			// and uninstall next package
			WCF::getTPL()->display('packageInstallationReloadFrame');
			exit;
		}
		else {
			// nothing to do
			// finish uninstallation
			
			// delete all package installation queue entries with the active process number
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
				WHERE		processNo = ".$this->processNo;
			WCF::getDB()->sendQuery($sql);
			
			// reset sessions
			Session::resetSessions();
			
			// var to redirect to package list
			WCF::getTPL()->assign('installationType', 'other');
			
			// show finish page
			WCF::getTPL()->display('packageInstallationFinish');
			exit;
		}
	}
	
	/**
	 * Starts the uninstallation of this package.
	 */
	protected function startUninstallation() {
		if ($this->parentQueueID == 0) {
			return 'installationFrame';
		}
		else {
			return 'requirements';
		}
	}
	
	/**
	 * Shows the uninstallation output frame.
	 */
	protected function showInstallationFrame() {
		//$this->nextStep = 'requirements';
		$this->nextStep = 'execPackageInstallationPlugins';
		WCF::getTPL()->assign(array(
			'nextStep' => $this->nextStep
		));
		WCF::getTPL()->display('packageInstallationFrame');
		exit;
	}
	
	/**
	 * @see PackageInstallationQueue::getTotalStep()
	 */
	protected function getTotalStep() {
		return 4;
	}
	
	/**
	 * Adds an uninstall entry to the package installation queue.
	 * 
	 * @param	Package		$package
	 * @param	array		$packages
	 */
	public static function addQueueEntries(Package $package, $packages = array()) {
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// add dependent packages to queue
		$inserts = ''; 
		$userID = WCF::getUser()->userID;
		foreach ($packages as $dependentPackage) {
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$processNo.", ".$userID.", '".escapeString($dependentPackage['packageName'])."', ".$dependentPackage['packageID'].", 'uninstall')";
		}
		
		// add uninstalling package to queue
		if (!empty($inserts)) $inserts .= ','; 
		$inserts .= "(".$processNo.", ".$userID.", '".escapeString($package->getName())."', ".$package->getPackageID().", 'uninstall')";
		
		// insert queue entry (entries)
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
					(processNo, userID, package, packageID, action)
			VALUES		".$inserts;
		WCF::getDB()->sendQuery($sql);
		
		// open queue
		HeaderUtil::redirect('index.php?page=Package&action=openQueue&processNo='.$processNo.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * Deletes the given list of files from the target dir.
	 *
	 * @param 	string 		$targetDir
	 * @param 	string 		$files
	 * @param	boolean		$deleteEmptyDirectories
	 * @param	booelan		$deleteEmptyTargetDir
	 */
	public function deleteFiles($targetDir, $files, $deleteEmptyTargetDir = false, $deleteEmptyDirectories = true) {
		if ($ftp = $this->checkSafeMode()) { 
			require_once(WCF_DIR.'lib/system/setup/FTPUninstaller.class.php');
			new FTPUninstaller($targetDir, $files, $ftp, $deleteEmptyTargetDir, $deleteEmptyDirectories);
		}
		else {
			require_once(WCF_DIR.'lib/system/setup/FileUninstaller.class.php');
			new FileUninstaller($targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories);
		}
	}
	
	/**
	 * @see PackageInstallationQueue::calcProgress()
	 */
	protected function calcProgress($currentStep) {
		if ($this->parentQueueID == 0) {
			parent::calcProgress($currentStep);
		}
	}
}
?>