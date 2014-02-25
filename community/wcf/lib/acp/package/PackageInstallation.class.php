<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/Package.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageArchive.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
require_once(WCF_DIR.'lib/system/setup/FileInstaller.class.php');
require_once(WCF_DIR.'lib/system/setup/FTPInstaller.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * PackageInstallation executes package installations and updates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageInstallation extends PackageInstallationQueue {
	/**
	 * the archive object
	 *
	 * @var PackageArchive
	 */
	protected $packageArchive;
	
	/**
	 * package object of an existing package
	 *
	 * @var Package
	 */
	protected $progressPackage = null;
	
	
	protected $stepAfterPIP = 'optionals';
	protected $selectedRequirements = array();
	const CONFIG_FILE = 'config.inc.php';
	
	/**
	 * Creates a new PackageInstallation object.
	 * 
	 * @param	integer		$queueID
	 */
	public function __construct($queueID) {
		parent::__construct($queueID);
		
		switch ($this->action) {
			case 'install':
			case 'update':
				$this->install();
				break;
		}
	}
	
	/**
	 * @see PackageInstallationQueue::getInstallationInfo()
	 */
	protected function getInstallationInfo() {
		$info = parent::getInstallationInfo();
		$this->package = $info['packageID'] ? new Package(null, $info) : null;
		$this->packageArchive = new PackageArchive($info['archive'], $this->package);
		
		if ($this->parentQueueID == 0) $this->progressPackage = $this;
		else {
			require_once(WCF_DIR.'lib/acp/package/PackageInstallationInfo.class.php');
			$this->progressPackage = new PackageInstallationInfo($this->parentQueueID);
			if ($this->progressPackage->parentQueueID != 0) $this->progressPackage = null;
		}
		
		// during the package installation we use languages variables of the installed package
		//if ($info['packageID']) WCF::getLanguage()->packageID = $info['packageID'];
	}
	
	/**
	 * @see PackageInstallationQueue::assignPackageInfo()
	 */
	protected function assignPackageInfo() {
		try {
			$this->packageArchive->openArchive();
		}
		catch (SystemException $e) { // ignore package errors in rollback
			if ($this->step != 'cancel' && $this->step != 'rollback') {
				throw $e;
			}
		}
				
		WCF::getTPL()->assign(array(
			'packageName' => $this->packageArchive->getPackageInfo('packageName'),
			'packageDescription' => $this->packageArchive->getPackageInfo('packageDescription'),
			'packageVersion' => $this->packageArchive->getPackageInfo('version'),
			'packageDate' => $this->packageArchive->getPackageInfo('date'),
			'packageAuthor' => $this->packageArchive->getAuthorInfo('author'),
			'packageAuthorURL' => $this->packageArchive->getAuthorInfo('authorURL')
		));
	}
	
	/**
	 * Steps through the installation process of a package.
	 */
	protected function install() {
		try {
			// open package file
			if (!FileUtil::isURL($this->packageArchive->getArchive())) {
				$this->assignPackageInfo();
			}
			
			switch ($this->step) {
				// prepare package installation
				case '':
				
					// download package file if necessary
					if (FileUtil::isURL($this->packageArchive->getArchive())) {
						// get return value and update entry in 
						// package_installation_queue with this value
						$archive = $this->packageArchive->downloadArchive();
						$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
							SET	archive = '".escapeString($archive)."' 
							WHERE	queueID = ".$this->queueID;
						WCF::getDB()->sendQuery($sql);
						$this->assignPackageInfo();
					}
					
					// check package file
					$this->checkArchive();
					
					// show confirm package installation site if necessary
					if ($this->confirmInstallation == 1 && $this->parentQueueID == 0) {
						$this->nextStep = 'confirm';
					}
					// show the installation frame if necessary	
					else if ($this->parentQueueID == 0) {
						$this->nextStep = 'installationFrame';
					}
					else {
						$this->nextStep = 'requirements';
					}
					
					HeaderUtil::redirect('index.php?page=Package&action='.$this->action.'&queueID='.$this->queueID.'&step='.$this->nextStep.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					exit;
					break;
				
				// show confirm package installation site	
				case 'confirm':
					// get requirements
					$requirements = $this->packageArchive->getRequirements();
					$openRequirements = $this->packageArchive->getOpenRequirements();
					$updatableInstances = array();
					$missingPackages = 0;
					foreach ($requirements as $key => $requirement) {
						if (isset($openRequirements[$requirement['name']])) {
							$requirements[$key]['open'] = 1;
							$requirements[$key]['action'] = $openRequirements[$requirement['name']]['action'];
							if (!isset($requirements[$key]['file'])) $missingPackages++;
						}
						else {
							$requirements[$key]['open'] = 0;
						}
					}
					
					// get other instances
					if ($this->action == 'install') {
						$updatableInstances = $this->packageArchive->getUpdatableInstances();
					}
					
					WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package');
					WCF::getTPL()->assign(array(
						'archive' => $this->packageArchive,
						'package' => $this->package,
						'requiredPackages' => $requirements,
						'missingPackages' => $missingPackages,
						'updatableInstances' => $updatableInstances,
						'excludingPackages' => $this->packageArchive->getConflictedExcludingPackages(),
						'excludedPackages' => $this->packageArchive->getConflictedExcludedPackages()
					));
					WCF::getTPL()->display('packageInstallationConfirm');
					exit;
					break;
					
				case 'changeToUpdate': 
					// get package id
					$updatePackageID = 0;
					if (isset($_REQUEST['updatePackageID'])) $updatePackageID = intval($_REQUEST['updatePackageID']);
					
					// check package id
					$updatableInstances = $this->packageArchive->getUpdatableInstances();
					if (!isset($updatableInstances[$updatePackageID])) {
						throw new IllegalLinkException();
					}
					
					// update queue entry
					$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
						SET	action = 'update',
							packageID = ".$updatePackageID."
						WHERE	queueID = ".$this->queueID;
					WCF::getDB()->sendQuery($sql);
					
					HeaderUtil::redirect('index.php?page=Package&action=update&queueID='.$this->queueID.'&step=installationFrame&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					exit;
					break;
					
				// cancel the installation
				case 'cancel':
					$this->packageArchive->deleteArchive();
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		queueID = ".$this->queueID;
					WCF::getDB()->sendQuery($sql);
					
					HeaderUtil::redirect('index.php?page=PackageList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					break;
				
				// show the installation frame	
				case 'installationFrame':
					$this->calcProgress(0);
					$this->nextStep = 'requirements';
					WCF::getTPL()->assign(array(
						'nextStep' => $this->nextStep
					));
					WCF::getTPL()->display('packageInstallationFrame');
					exit;
					break;
				
				// check if this package depends on other packages	
				case 'requirements':
					if ($this->installPackageRequirements()) {
						$this->calcProgress(1);
						$this->nextStep = 'exclusions';
					}
					else {
						WCF::getTPL()->display('packageInstallationRequirements');
						exit;
					}
					break;
					
				// check for conflicted exclusions	
				case 'exclusions':
					$this->checkExclusions();
					$this->calcProgress(1);
					$this->nextStep = 'package';
					break;
				
				// install package itself
				case 'package':
					// install selectable package requirements
					$this->installSelectablePackageRequirements();
					// install package
					$this->installPackage();
					$this->calcProgress(2);
					$this->nextStep = 'parent';
					break;
					
				// manage parent packages (these are needed by plugins)
				case 'parent':
					$this->installPackageParent();
					$this->rebuildConfigFiles();
					$this->calcProgress(3);
					$this->nextStep = 'packageInstallationPlugins';	
					break;
					
				// install packageInstallationPlugins
				case 'packageInstallationPlugins':
					if ($this->hasPackageInstallationPlugins()) {
						$this->installPackageInstallationPlugins();
					}
					$this->calcProgress(4);
					$this->nextStep = 'execPackageInstallationPlugins';
					break;
					
				// At this point execution of packageInstallationPlugins starts. The getNextStep method will return 
				// the class name of the first available packageInstallationPlugin. Thus in the next installation step
				// this switch will jump into the default: directive. The default: directive will then be called for 
				// every available packageInstallationPlugin. The actual execution of the respective packageInstallationPlugin 
				// is being done there.
				case 'execPackageInstallationPlugins':
					$this->loadPackageInstallationPlugins();
					$this->calcProgress(5);
					$this->nextStep = $this->getNextPackageInstallationPlugin('');
					break;
				
				// check if this package has optional packages
				case 'optionals':
					// update package version before installing optionals
					$this->updatePackageVersion();
					
					// install optional packages
					if ($this->installPackageOptionals()) {
						$this->calcProgress(6);
						$this->nextStep = 'finish';
					}
					else {
						WCF::getTPL()->display('packageInstallationOptionals');
						exit;
					}
					break;
					
				// finish installation
				case 'finish':
					$this->calcProgress(7);
					$this->nextStep = $this->finishInstallation();
					break;
					
				// start rollback of all installations in the active process
				case 'rollback':
					// delete packages that are not rollbackable
					$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
						WHERE		processNo = ".$this->processNo."
								AND (
									action <> 'install'
									OR cancelable = 0
								)";
					WCF::getDB()->sendQuery($sql);
					
					// enable rollback for all other packages
					$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
						SET	action = 'rollback',
							done = 0
						WHERE	processNo = ".$this->processNo."
							AND action = 'install'
							AND cancelable = 1";
					WCF::getDB()->sendQuery($sql);
					HeaderUtil::redirect('index.php?page=Package&action=openQueue&processNo='.$this->processNo.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
					exit;
					break;
					
				// execute PackageInstallationPlugins
				default:
					$this->executePackageInstallationPlugin($this->step);
					$this->nextStep = $this->getNextPackageInstallationPlugin($this->step);
			}
			
			WCF::getTPL()->assign(array(
				'nextStep' => $this->nextStep
			));
			WCF::getTPL()->display('packageInstallationNext');
		}
		catch (SystemException $e) {
			$this->showPackageInstallationException($e);
		}
	}
	
	/**
	 * Updates the version of a package.
	 */
	protected function updatePackageVersion() {
		if ($this->getAction() == 'update') {
			$version = $this->packageArchive->getPackageInfo('version');
			$this->package->setVersion($version);
			
			$packageDir = $this->package->getDir();
			if (!empty($packageDir) && $this->package->isStandalone() == 1) {
				// update constant PACKAGE_VERSION in config.inc.php
				Package::writeConfigFile($this->package->getPackageID());
			}
		}
	}
	
	/**
	 * finalises installation of this package.
	 * 
	 * @return 	string 		nextStep
	 */
	protected function finishInstallation() {
		$this->makeClear();

		// check language to package relations
		// a standalone package needs at least one language relation
		if ($this->packageArchive->getPackageInfo('standalone')) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_language_to_packages
				WHERE	packageID = ".$this->packageID;
			$row = WCF::getDB()->getFirstRow($sql);
			if ($row['count'] == 0) {
				$sql = "INSERT INTO	wcf".WCF_N."_language_to_packages
							(languageID, packageID)
					SELECT		languageID, ".$this->packageID."
					FROM		wcf".WCF_N."_language
					ORDER BY	isDefault DESC";
				WCF::getDB()->sendQuery($sql, 1);
				
				// reset language cache
				WCF::getCache()->clearResource('languages');
				LanguageEditor::updateAll();
			}
		}
		
		// unregister package installation plugins
		WCF::getSession()->unregister('queueID'.$this->queueID.'PIPs');
		
		// mark this package installation as done
		$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
			SET	done = 1
			WHERE	queueID = ".$this->queueID;
		WCF::getDB()->sendQuery($sql);
		
		// delete package archive
		$this->packageArchive->getTar()->close();
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE	archive = '".escapeString($this->packageArchive->getArchive())."'
				AND done = 0";
		$row = WCF::getDB()->getFirstRow($sql);
		if ($row['count'] == 0) {
			@unlink($this->packageArchive->getArchive());
		}
		
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
			
			if ($this->parentQueueID == 0) {
				// reload installation frame
				// and install next package
				WCF::getTPL()->display('packageInstallationReloadFrame');
				exit;
			}
			else {
				// install next package in current window
				return '';
			}
		}
		else {
			if ($this->parentQueueID == 0) {
				// nothing to do
				// finish installation
				
				// get installationType ('setup', 'install' or 'other')
				$sql = "SELECT	installationType
					FROM	wcf".WCF_N."_package_installation_queue
					WHERE	queueID = ".$this->queueID;
				$type = WCF::getDB()->getFirstRow($sql);					
					
				
				// delete all package installation queue entries with the active process number
				$sql = "DELETE FROM	wcf".WCF_N."_package_installation_queue
					WHERE		processNo = ".$this->processNo;
				WCF::getDB()->sendQuery($sql);
				
				// reset sessions
				Session::resetSessions();
				
				// var to redirect to installation form or acp index
				WCF::getTPL()->assign('installationType', $type['installationType']);
				
				// show finish page
				WCF::getTPL()->display('packageInstallationFinish');
				exit;
			}
			else {
				// jump to parent package installation
				// get information about parent queue id
				$sql = "SELECT	*
					FROM	wcf".WCF_N."_package_installation_queue
					WHERE	queueID = ".$this->parentQueueID;
				$parentQueueEntry = WCF::getDB()->getFirstRow($sql);
				
				WCF::getTPL()->assign(array(
					'action' => $parentQueueEntry['action'],
					'queueID' => $this->parentQueueID
				));
				
				if ($this->packageType == 'requirement') {
					return 'requirements';
				}
				if ($this->packageType == 'optional') {
					return 'finish';
				}
			}
		}
	}
	
	/**
	 * Finds and extracts userexits archive in package archive and start extraction
	 */
	protected function installPackageInstallationPlugins() {
		$targetDir = WCF_DIR.'lib/acp/package/plugin/';

		// unpack file archive
		$instructions = $this->getInstructions();
		$sourceArchive = $instructions['packageinstallationplugins']['cdata'];

		// find this file in .tar package
		$sourceArchive = $this->packageArchive->extractTar($sourceArchive, 'packageinstallationplugins_');
		
		// start extraction of files
		require_once(WCF_DIR.'lib/acp/package/PackageInstallationPluginsFileHandler.class.php');
		$pipFileHandler = new PackageInstallationPluginsFileHandler($this);
		$this->extractFiles($targetDir, $sourceArchive, $pipFileHandler);
		
		// delete temporary sourceArchive
		@unlink($sourceArchive);
	}
	
	/**
	 * Extracts files from .tar (or .tar.gz) archive and installs them
	 *
	 * @param 	string 			$targetDir
	 * @param 	string 			$sourceArchive
	 * @param	FileHandler		$fileHandler
	 * @return	Installer	
	 */
	public function extractFiles($targetDir, $sourceArchive, $fileHandler = null) {
		// check for PHP's safe_mode
		$ftp = $this->checkSafeMode();
		if ($ftp === null) {
			return new FileInstaller($targetDir, $sourceArchive, $fileHandler);
		} else {
			return new FTPInstaller($targetDir, $sourceArchive, $ftp, $fileHandler);			
		}
	}
	
	/**
	 * checks if a tag named $tagName exists in package.xml file
	 * and has contents (either cdata or attributes)
	 *
	 * @param 	string 		$tagName
	 * @return 	boolean 	$exists
	 */
	public function XMLTagExists($tagName) {
		$instructions = $this->getInstructions();
		return isset($instructions[$tagName]);
	}
	
	/**
	 * Returns the content of the tag with the given tag name in package.xml file.
	 * 
	 * @param 	string 		$tagName
	 * @return 	string		content
	 */
	public function getXMLTag($tagName) {
		$instructions = $this->getInstructions();
		if (!isset($instructions[$tagName])) {
			return null;
		}
		
		return $instructions[$tagName];
	}
	
	/**
	 * checks if this package has packageInstallationPlugins to add.
	 *
	 * @return 	boolean 	$hasPackageInstallationPlugins
	 */
	protected function hasPackageInstallationPlugins() {
		return $this->XMLTagExists('packageinstallationplugins');
	}
	
	/**
	 * Registers package information into database.
	 */
	protected function installPackage() {
		$requirements = $this->packageArchive->getAllExistingRequirements();

		// finish installation
		if ($this->action == 'install') {
			$requirementsChanged = true;
			
			// calculate the number of instances of this package
			$instanceNo = 1;
			$sql = "SELECT	COUNT(*) AS count, MAX(instanceNo) AS instanceNo
				FROM	wcf".WCF_N."_package
				WHERE	package = '".escapeString($this->packageArchive->getPackageInfo('name'))."'";
			$row = WCF::getDB()->getFirstRow($sql);
			if ($row['count'] > 0) $instanceNo = $row['instanceNo'] + 1;

			// register this package
			$sql = "INSERT INTO 	wcf".WCF_N."_package
						(package, packageName, instanceNo, packageDescription, packageVersion, packageDate, packageURL, 
						isUnique, standalone, author, authorURL, installDate, updateDate)
				VALUES		('".escapeString($this->packageArchive->getPackageInfo('name'))."',
						'".escapeString($this->packageArchive->getPackageInfo('packageName'))."',
						".$instanceNo.",
						'".escapeString($this->packageArchive->getPackageInfo('packageDescription'))."',
						'".escapeString($this->packageArchive->getPackageInfo('version'))."',
						".intval($this->packageArchive->getPackageInfo('date')).",
						'".escapeString($this->packageArchive->getPackageInfo('packageURL'))."',
						'".intval($this->packageArchive->getPackageInfo('isUnique'))."',
						'".intval($this->packageArchive->getPackageInfo('standalone'))."',
						'".escapeString($this->packageArchive->getAuthorInfo('author'))."',
						'".escapeString($this->packageArchive->getAuthorInfo('authorURL'))."',
						".TIME_NOW.",
						".TIME_NOW.")";
			WCF::getDB()->sendQuery($sql);
			$this->packageID = WCF::getDB()->getInsertID();

			// package package id in queue
			$sql = "UPDATE	wcf".WCF_N."_package_installation_queue
				SET 	packageID 	= ".$this->packageID." 
				WHERE 	queueID 	= ".$this->queueID;
			WCF::getDB()->sendQuery($sql);
		}
		else {
			$requirementsChanged = false;
			
			// update this package
			try {
				$sql = "UPDATE 	wcf".WCF_N."_package 
					SET 	packageName 		= '".escapeString($this->packageArchive->getPackageInfo('packageName'))."', 
						packageDescription 	= '".escapeString($this->packageArchive->getPackageInfo('packageDescription'))."', 
						packageDate 		= ".intval($this->packageArchive->getPackageInfo('date')).", 
						packageURL 		= '".escapeString($this->packageArchive->getPackageInfo('packageURL'))."', 
						author 			= '".escapeString($this->packageArchive->getAuthorInfo('author'))."', 
						authorURL 		= '".escapeString($this->packageArchive->getAuthorInfo('authorURL'))."',
						updateDate		= ".TIME_NOW."
					WHERE 	packageID 		= ".$this->packageID;
				WCF::getDB()->sendQuery($sql);
			}
			catch (DatabaseException $e) {
				// horizon update workaround
				$sql = "UPDATE 	wcf".WCF_N."_package 
					SET 	packageName 		= '".escapeString($this->packageArchive->getPackageInfo('packageName'))."', 
						packageDescription 	= '".escapeString($this->packageArchive->getPackageInfo('packageDescription'))."', 
						packageDate 		= ".intval($this->packageArchive->getPackageInfo('date')).", 
						packageURL 		= '".escapeString($this->packageArchive->getPackageInfo('packageURL'))."', 
						author 			= '".escapeString($this->packageArchive->getAuthorInfo('author'))."', 
						authorURL 		= '".escapeString($this->packageArchive->getAuthorInfo('authorURL'))."'
					WHERE 	packageID 		= ".$this->packageID;
				WCF::getDB()->sendQuery($sql);
			}
			
			// check whether the requirements were changed
			$newRequirements = $this->packageArchive->getExistingRequirements();
				
			// get old requirements
			$sql = "SELECT	requirement
				FROM	wcf".WCF_N."_package_requirement
				WHERE	packageID = ".$this->packageID;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$oldRequirement = false;
				
				foreach ($newRequirements as $key => $requirement) {
					if ($row['requirement'] == $requirement['packageID']) {
						$oldRequirement = true;
						unset($newRequirements[$key]);
						break;
					}
				}
				
				if (!$oldRequirement) {
					$requirementsChanged = true;
					break;
				}
			}
			
			if (count($newRequirements) != 0) {
				$requirementsChanged = true;
			}
			
			// delete old excluded packages
			$sql = "DELETE FROM	wcf".WCF_N."_package_exclusion 
				WHERE 		packageID = ".$this->packageID;
			WCF::getDB()->sendQuery($sql);

			// delete old requirements
			$sql = "DELETE FROM	wcf".WCF_N."_package_requirement 
				WHERE 		packageID = ".$this->packageID;
			WCF::getDB()->sendQuery($sql);
		}
		
		// save excluded packages
		if (count($this->packageArchive->getExcludedPackages()) > 0) {
			$insertExcludedPackages = '';
			foreach ($this->packageArchive->getExcludedPackages() as $excludedPackage) {
				if (!empty($insertExcludedPackages)) $insertExcludedPackages .= ',';
				$insertExcludedPackages .= "(".$this->packageID.", '".escapeString($excludedPackage['name'])."', '".(!empty($excludedPackage['version']) ? escapeString($excludedPackage['version']) : '')."')";
			}
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_exclusion 
						(packageID, excludedPackage, excludedPackageVersion)
				VALUES 		".$insertExcludedPackages;
			WCF::getDB()->sendQuery($sql);
		}
		
		// register package requirements
		if (count($requirements) > 0) {
			$insertRequirements = '';
			$requirementIDs = '';
			foreach ($requirements as $identifier => $possibleRequirements) {
				if (count($possibleRequirements) == 1) $requirement = array_shift($possibleRequirements);
				else {
					$requirement = $possibleRequirements[$this->selectedRequirements[$identifier]];
				}
				
				if (!empty($insertRequirements)) $insertRequirements .= ',';
				$insertRequirements .= "(".$this->packageID.", ".$requirement['packageID'].")";
				
				if (!empty($requirementIDs)) $requirementIDs .= ',';
				$requirementIDs .= $requirement['packageID'];
			}
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_requirement 
						(packageID, requirement)
				VALUES 		".$insertRequirements;
			WCF::getDB()->sendQuery($sql);
		}
		
		// rebuild package dependencies
		if ($requirementsChanged) {
			// build requirement map
			Package::rebuildPackageRequirementMap($this->packageID);
			
			// rebuild dependencies
			Package::rebuildPackageDependencies($this->packageID);
			if ($this->action == 'update') {
				Package::rebuildParentPackageDependencies($this->packageID);
			}
		}
		
		// reset package cache
		WCF::getCache()->clearResource('packages');
		// reset all cache resources
		//WCF::getCache()->clear(WCF_DIR.'cache', '*.php');
				
		// this is a bad fix to avoid
		// logout and some language errors 
		// during the installation of package com.woltlab.wcf
		if ($this->action == 'install' && $this->packageArchive->getPackageInfo('name') == 'com.woltlab.wcf') {
			// avoid logout
			// update package id in acp sessions
			$sql = "UPDATE	wcf".WCF_N."_acp_session
				SET	packageID = ".$this->packageID."
				WHERE	packageID = 0";
			WCF::getDB()->sendQuery($sql);
			
			// avoid language errors
			// update package id in language to packages relations
			$sql = "UPDATE	wcf".WCF_N."_language_to_packages
				SET	packageID = ".$this->packageID."
				WHERE	packageID = 0";
			WCF::getDB()->sendQuery($sql);
			
			// update package id in language items
			$sql = "UPDATE	wcf".WCF_N."_language_item
				SET	packageID = ".$this->packageID."
				WHERE	packageID = 0";
			WCF::getDB()->sendQuery($sql);
			
			// reset language cache
			WCF::getCache()->clearResource('languages');
			
			// rebuild language files
			LanguageEditor::updateAll();
			
			// delete language files of package 0
			LanguageEditor::deleteLanguageFiles('*', '*', 0);
			
			// update default group options
			// to avoid permission denied errors after next login
			$sql = "UPDATE	wcf".WCF_N."_group_option
				SET	packageID = ".$this->packageID."
				WHERE	packageID = 0";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Installs optional packages for this package.
	 */
	protected function installPackageOptionals() {
		if (count($this->packageArchive->getOptionals()) == 0) {
			// packages does not have any optional packages
			return true;
		}
		
		$installedOptionals = array();
		$optionalNames = '';
		foreach ($this->packageArchive->getOptionals() as $optional) {
			if (!empty($optionalNames)) $optionalNames .= ',';
			$optionalNames .= "'".escapeString($optional['name'])."'";
		}
		
		if ($this->action == 'update') {
			// try to find updatable optionals
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				WHERE	package IN (".$optionalNames.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$installedOptionals[$row['package']][] = $row;
			}
			
			if (count($installedOptionals) > 0) {
				$queueInserts = '';
				
				foreach ($this->packageArchive->getOptionals() as $optional) {
					if (isset($installedOptionals[$optional['name']])) {
						// unzip and open package
						// check the given filename
						if (!FileUtil::isURL($optional['file'])) {
							// filename is no url
							// optional package is delivered with this package
							$optional['file'] = $this->packageArchive->extractTar($optional['file'], 'optionalPackage_');
							$optionalPackage = new PackageArchive($optional['file']);
						}
						else {
							$optionalPackage = new PackageArchive($optional['file']);
							$optionalPackage->downloadArchive();
						}
						
						// open package archive
						$optionalPackage->openArchive();
						
						// get version number
						$optionalVersion = $optionalPackage->getPackageInfo('version');
						
						foreach ($installedOptionals[$optional['name']] as $installedOptional) {
							if (Package::compareVersion($optionalVersion, $installedOptional['packageVersion']) == 1) {
								// unzip tar
								$optional['file'] = PackageArchive::unzipPackageArchive($optional['file']);

								// add queue entry
								if (!empty($queueInserts)) $queueInserts .= ',';
								$queueInserts .= "(".$this->queueID.", ".$this->processNo.", ".WCF::getUser()->userID.", '".escapeString($installedOptional['package'])."', ".$installedOptional['packageID'].", '".escapeString($optional['file'])."', 'optional', 0, 'update')";
							}
						}
					}
				}
				
				if (!empty($queueInserts)) {
					$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
								(parentQueueID, processNo, userID, package, packageID, archive, packageType, cancelable, action)
						VALUES		".$queueInserts;
					WCF::getDB()->sendQuery($sql);
					return false;
				}
			}
			
			return true;
		}
		else {
			// check whether optional packages are already installed
			$sql = "SELECT	DISTINCT package, packageID, isUnique
				FROM	wcf".WCF_N."_package
				WHERE	package IN (".$optionalNames.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$installedOptionals[$row['package']] = $row;
			}
			
			// build list of available optional packages
			$availableOptionals = array();
			foreach ($this->packageArchive->getOptionals() as $optional) {
				$optional['available'] = 1;
				$optional['installed'] = 0;
				
				if (isset($installedOptionals[$optional['name']]) && $installedOptionals[$optional['name']]['isUnique'] == 1) {
					$optional['installed'] = 1;
					$optional['available'] = 0;
				}
				
				// unzip and open package
				// check the given filename
				if (!FileUtil::isURL($optional['file'])) {
					// filename is no url
					// optional package is delivered with this package
					$optional['file'] = $this->packageArchive->extractTar($optional['file'], 'optionalPackage_');
					$optionalPackage = new PackageArchive($optional['file']);
				}
				else {
					$optionalPackage = new PackageArchive($optional['file']);
					$optionalPackage->downloadArchive();
				}
				
				// open package archive
				$optionalPackage->openArchive();
				
				// check requirements
				foreach ($optionalPackage->getOpenRequirements() as $requirement) {
					// no filename for required package given
					if (!isset($requirement['file'])) {
						$optional['available'] = 0;
						break;
					}
				}
				
				// get name and description
				$optional['packageName'] = $optionalPackage->getPackageInfo('packageName');
				$optional['packageDescription'] = $optionalPackage->getPackageInfo('packageDescription');
				
				$availableOptionals[$optional['name']] = $optional;
			}
			
			// form send
			$queueInserts = '';
			if (isset($_POST['send'])) {
				$optionalPackages = array();
				if (isset($_POST['optionalPackages']) && is_array($_POST['optionalPackages'])) {
					$optionalPackages = $_POST['optionalPackages'];
				}
				
				// build inserts
				foreach ($optionalPackages as $optionalPackage) {
					if (!isset($availableOptionals[$optionalPackage]) || $availableOptionals[$optionalPackage]['available'] == 0 || $availableOptionals[$optionalPackage]['installed'] == 1) {
						continue;
					}
					
					// unzip tar
					$availableOptionals[$optionalPackage]['file'] = PackageArchive::unzipPackageArchive($availableOptionals[$optionalPackage]['file']);
					
					if (!empty($queueInserts)) $queueInserts .= ',';
					$cancelable = $this->getAction() == 'install' ? 1 : 0;
					$queueInserts .= "(".$this->queueID.", ".$this->processNo.", ".WCF::getUser()->userID.", '".escapeString($optionalPackage)."', '".escapeString($availableOptionals[$optionalPackage]['file'])."', 'optional', ".$cancelable.")";
					$availableOptionals[$optionalPackage]['selected'] = true;
				}
			}
			
			// delete tmp files
			foreach ($availableOptionals as $availableOptional) {
				if (!isset($availableOptional['selected'])) {
					@unlink($availableOptional['file']);
				}
			}
			
			// save inserts
			if (isset($_POST['send'])) {
				if (!empty($queueInserts)) {
					$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
								(parentQueueID, processNo, userID, package, archive, packageType, cancelable)
						VALUES		".$queueInserts;
					WCF::getDB()->sendQuery($sql);
					return false;
				}
				else {
					return true;
				}
			}
			
			// sort optionals
			uasort($availableOptionals, create_function('$packageA,$packageB', 'return strcasecmp($packageA[\'packageName\'], $packageB[\'packageName\']);'));
			
			// show form
			WCF::getTPL()->assign(array(
				'availableOptionals' => $availableOptionals
			));
			WCF::getTPL()->display('packageInstallationSelectOptionals');
			exit;
		}
	}
	
	/**
	 * Shows a select form if multiple instances of a reqiured package are installed.
	 */
	protected function installSelectablePackageRequirements() {
		// check if this package has selectable requirements
		$existingPackages = $this->getArchive()->getAllExistingRequirements();
		if (!count($existingPackages)) return true;
		
		// remove singles
		$selectableRequirements = array();
		foreach ($existingPackages as $identifier => $packages) {
			if (count($packages) > 1) {
				$selectableRequirements[$identifier] = $packages;
			}
		}
		unset($existingPackages);
		if (!count($selectableRequirements)) return true;
		
		if (!isset($_POST['send'])) {
			// try to find requirements in current installation process
			$processPackages = array();
			$sql = "SELECT	package, packageID
				FROM	wcf".WCF_N."_package_installation_queue
				WHERE	processNo = ".$this->processNo."
					AND package IN ('".implode("','", array_map('escapeString', array_keys($selectableRequirements)))."')
					AND packageID <> 0";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($processPackages[$row['package']])) $processPackages[$row['package']] = array();
				$processPackages[$row['package']] = $row['packageID'];
			}
			
			$selectedRequirements = array();
			if (count($processPackages) == count($selectableRequirements)) {
				foreach ($selectableRequirements as $identifier => $packages) {
					if (isset($processPackages[$identifier]) && count($processPackages[$identifier])) {
						$selectedRequirements[$identifier] = $processPackages[$identifier];
					}
				}
			}
			
			if (count($selectedRequirements) == count($selectableRequirements)) {
				$this->selectedRequirements = $selectedRequirements;
				return true;
			}
		}
		
		// show form
		$errorField = $errorType = '';
		if (isset($_POST['send'])) {
			// read form parameters
			if (isset($_POST['selectedRequirements']) && is_array($_POST['selectedRequirements'])) {
				$this->selectedRequirements = ArrayUtil::toIntegerArray($_POST['selectedRequirements']);
			}
			
			// validate
			try {
				foreach ($selectableRequirements as $identifier => $packages) {
					if (!isset($this->selectedRequirements[$identifier]) || !isset($packages[$this->selectedRequirements[$identifier]])) {
						throw new UserInputException($identifier);
					}
				}
				
				return true;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		
		WCF::getTPL()->assign(array(
			'selectableRequirements' => $selectableRequirements,
			'selectedRequirements' => $this->selectedRequirements,
			'errorField' => $errorField,
			'errorType' => $errorType
		));
		WCF::getTPL()->display('packageInstallationSelectRequirements');
		exit;
	}
	
	/**
	 * determines if the package that's about to install does have a "parent" package,
	 * and if so, associates the package with that parent.
	 */
	protected function installPackageParent() {
		if ($this->packageArchive->getPackageInfo('standalone') != 0 || $this->action != 'install' || !$this->packageArchive->getPackageInfo('plugin')) {
			// package does not need a parent package
			return;
		}
		
		// get parent package from requirements
		$sql = "SELECT	requirement
			FROM	wcf".WCF_N."_package_requirement
			WHERE	packageID = ".$this->packageID."
				AND requirement IN (
					SELECT	packageID
					FROM	wcf".WCF_N."_package
					WHERE	package = '".escapeString($this->packageArchive->getPackageInfo('plugin'))."'
				)";
		$row = WCF::getDB()->getFirstRow($sql);
		if (empty($row['requirement'])) {
			throw new SystemException("can not find any available installations of required parent package '".$this->packageArchive->getPackageInfo('plugin')."'", 13012);
		}
		
		// save parent package
		$this->saveParentPackage($row['requirement']);
	}
	
	/**
	 * Saves the given parent package id as parent package for this package.
	 * 
	 * @param	integer		$parentPackageID
	 */
	protected function saveParentPackage($parentPackageID) {
		$sql = "UPDATE 	wcf".WCF_N."_package 
			SET 	parentPackageID = ".$parentPackageID." 
			WHERE 	packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		
		// rebuild parent package dependencies								
		Package::rebuildParentPackageDependencies($this->packageID);
		
		// rebuild parent's parent package dependencies
		Package::rebuildParentPackageDependencies($parentPackageID);
	}
	
	/**
	 * Installs the requirements of the current package.
	 * 
	 * Inserts needed package installations or updates into the package installation queue.
	 * Returns true, if no package installations are needed. Otherwise false.
	 * 
	 * @return	boolean
	 */
	protected function installPackageRequirements() {
		// build queue inserts
		$queueInserts = '';
		foreach ($this->packageArchive->getOpenRequirements() as $requirement) {
			// the required package was not found
			// so the installation will be canceled
			if (!isset($requirement['file'])) {
				if (isset($requirement['minversion']) && !empty($requirement['packageID'])) {
					throw new SystemException("required package '".$requirement['name']."' in needed version '".$requirement['minversion']."' not found.", 13006);
				}
				else {
					throw new SystemException("required package '".$requirement['name']."' not found.", 13006);
				}
			}
			
			// check the given filename
			if (!FileUtil::isURL($requirement['file'])) {
				// filename is no url
				// required package is delivered with this package
				$requirement['file'] = $this->packageArchive->extractTar($requirement['file'], 'requiredPackage_');
				
				// unzip tar
				$requirement['file'] = PackageArchive::unzipPackageArchive($requirement['file']);
			}
			
			if (!empty($queueInserts)) $queueInserts .= ',';
			$action = $requirement['action'];
			$cancelable = ($action == 'install' && $this->getAction() == 'install' ? 1 : 0);
			$queueInserts .= "(".$this->queueID.", ".$this->processNo.", ".WCF::getUser()->userID.", '".escapeString($requirement['name'])."', ".$requirement['packageID'].", '".escapeString($requirement['file'])."', '".$action."', 'requirement', ".$cancelable.")";
		}
		
		// insert needed installations or updates
		if (!empty($queueInserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
						(parentQueueID, processNo, userID, package, packageID, archive, action, packageType, cancelable)
				VALUES		".$queueInserts;
			WCF::getDB()->sendQuery($sql);
			return false;
		}
		
		return true;
	}
	
	/**
	 * Checks the correctness of the package archive.
	 */
	protected function checkArchive() {
		if ($this->action == 'install') {
			// check install support
			if (!$this->packageArchive->isValidInstall()) {
				$this->packageArchive->deleteArchive();
				throw new SystemException("'".$this->packageArchive->getArchive()."' is not a valid WCF Package.", 13004);
			}
			
			// TODO: in case this is an installation of a package that brought dependencies
			// and the dependent packages' package.xml contains a misspelled name in the 
			// <package> tag, checkArchive will return false here --> "wrong error"!
			
			// check whether this package is already installed
			if ($this->packageArchive->isAlreadyInstalled()) {
				$this->packageArchive->deleteArchive();
				throw new SystemException("package '".$this->packageArchive->getPackageInfo('name')."' is already installed.", 13014);
			}
		}
		else if ($this->action == 'update') {
			// check update support
			if (!$this->packageArchive->isValidUpdate()) {
				if ($this->packageArchive->getPackageInfo('name') == $this->package->getPackage() && $this->packageArchive->getPackageInfo('version') == $this->package->getVersion()) {
					if ($this->parentQueueID == 0) {
						$this->calcProgress(0);
						WCF::getTPL()->assign(array(
							'nextStep' => 'finish'
						));
						WCF::getTPL()->display('packageInstallationFrame');
						exit;
					}
					else {
						$this->calcProgress(7);
						$this->nextStep = $this->finishInstallation();
						WCF::getTPL()->assign(array(
							'nextStep' => $this->nextStep
						));
						WCF::getTPL()->display('packageInstallationNext');
						exit;
					}
				}
				
				$this->packageArchive->deleteArchive();
				throw new SystemException("package '".$this->packageArchive->getPackageInfo('name')." v".$this->packageArchive->getPackageInfo('version')."' is not a compatible update for package '".$this->package->getPackage()." v".$this->package->getVersion()."'.", 13005);
			}
		}
	}
	
	/**
	 * Returns the package installation instructions.
	 * 
	 * @return	array
	 */
	public function getInstructions() {
		return $this->packageArchive->getInstructions($this->action);
	}
	
	/**
	 * Returns the package archive.
	 * 
	 * @return	PackageArchive
	 */
	public function getArchive() {
		return $this->packageArchive;
	}
	
	/**
	 * @see PackageInstallationQueue::getTotalStep()
	 */
	protected function getTotalStep() {
		return 7 + count($this->progressPackage->packageArchive->getRequirements());
	}
	
	/**
	 * @see PackageInstallationQueue::getCurrentStep()
	 */
	protected function getCurrentStep($currentStep) {
		if ($this->parentQueueID == 0) {
			if ($currentStep > 0) {
				$currentStep += count($this->progressPackage->packageArchive->getRequirements());
			}
		}
		else {
			$currentStep = count($this->progressPackage->packageArchive->getRequirements()) - count($this->progressPackage->packageArchive->getOpenRequirements());
		}
		return $currentStep;
	}
	
	/**
	 * @see PackageInstalltionQueue::calcProgress()
	 */
	protected function calcProgress($currentStep) {
		if (!$this->progressPackage || $this->packageType == 'optional') {
			return;
		}
		
		parent::calcProgress($currentStep);
	}
	
	/**
	 * Rebuilds config.inc.php files after package updates or plugin installations.
	 */
	protected function rebuildConfigFiles() {
		// rebuild config.inc.php files if necessary
		// check if this package requires a standalone package (except com.woltlab.wcf)
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
				Package::writeConfigFile($row['packageID']);
			}
		}
	}
	
	/**
	 * Checks for conflicted exclusions.
	 */
	protected function checkExclusions() {
		$excludedPackages = $this->packageArchive->getConflictedExcludedPackages();
		if (count($excludedPackages) > 0) {
			// this package exludes existing packages -> stop installation
			WCF::getTPL()->assign(array(
				'excludedPackages' => $excludedPackages
			));
			WCF::getTPL()->display('packageInstallationExcludedPackages');
			exit;
		}
		
		$excludingPackages = $this->packageArchive->getConflictedExcludingPackages();
		if (count($excludingPackages) > 0) {
			$stop = 1;
			// this package is excluded by existing packages
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package_installation_queue
				WHERE	processNo = ".$this->processNo."
					AND packageID IN (".implode(',', array_keys($excludingPackages)).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$archive = new PackageArchive($row['archive']);
				$archive->openArchive();
				$newExclusions = $archive->getExcludedPackages();
				if (!count($newExclusions) || !isset($newExclusions[$this->packageArchive->getPackageInfo('name')]) || (isset($newExclusions[$this->packageArchive->getPackageInfo('name')]['version']) && Package::compareVersion($this->packageArchive->getPackageInfo('version'), $newExclusions[$this->packageArchive->getPackageInfo('name')]['version'], '<'))) {
					unset($excludingPackages[$row['packageID']]);
					$stop = 0;
				}
			}
			
			if (count($excludingPackages) > 0) {
				WCF::getTPL()->assign(array(
					'excludingPackages' => $excludingPackages,
					'stop' => $stop,
					'nextStep' => 'package'
				));
				WCF::getTPL()->display('packageInstallationExcludingPackages');
				exit;
			}
		}
	}
}
?>