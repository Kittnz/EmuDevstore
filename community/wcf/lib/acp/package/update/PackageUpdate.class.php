<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/update/PackageUpdateAuthorizationRequiredException.class.php');
require_once(WCF_DIR.'lib/acp/package/update/UpdateServerEditor.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageArchive.class.php');
require_once(WCF_DIR.'lib/system/io/File.class.php');

/**
 * Contains business logic related to retrieval of update package info and update packages themselves.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.update
 * @category 	Community Framework
 */
class PackageUpdate {
	/**
	 * list of packages to update or install
	 * 
	 * @var	array
	 */
	protected $selectedPackages = array();
	
	/**
	 * list of package update server ids
	 * 
	 * @var	array
	 */
	protected $packageUpdateServerIDs;
	
	/**
	 * enables downloading of updates
	 * 
	 * @var	boolean
	 */
	protected $download;
	
	/**
	 * virtual package versions
	 * 
	 * @var	array
	 */
	protected $virtualPackageVersions = array();
	
	/**
	 * stack of package installations / updates
	 * 
	 * @var	array
	 */
	protected $packageInstallationStack = array();
	
	/**
	 * Creates a new PackageUpdate object.
	 * 
	 * @param	array		$selectedPackages	(packageID/package => version)
	 * @param	array		$packageUpdateServerIDs
	 * @param	boolean		$download		enable downloading of updates
	 */
	public function __construct($selectedPackages, $packageUpdateServerIDs = array(), $download = true) {
		$this->selectedPackages = $selectedPackages;
		$this->packageUpdateServerIDs = $packageUpdateServerIDs;
		$this->download = $download;
	}

	/**
	 * Builds the stack of package installations / updates.
	 */
	public function buildPackageInstallationStack() {
		foreach ($this->selectedPackages as $package => $version) {
			if (is_numeric($package)) {
				$this->updatePackage($package, $version);
			}
			else {
				$this->tryToInstallPackage($package, $version, true);
			}
		}
	}
	
	/**
	 * Updates an existing package.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$version	new package version
	 */
	protected function updatePackage($packageID, $version) {
		// get package info
		$package = new Package($packageID);
		
		// get current package version
		$packageVersion = $package->getVersion();
		if (isset($this->virtualPackageVersions[$packageID])) {
			$packageVersion = $this->virtualPackageVersions[$packageID];
			// check virtual package version
			if (Package::compareVersion($packageVersion, $version, '>=')) {
				// virtual package version is greater than requested version
				// skip package update
				return; 
			}
		}
		
		// get highest version of the required major release
		if (preg_match('/(\d+\.\d+\.)/', $version, $match)) {
			$packageVersions = array();
			$sql = "SELECT	DISTINCT packageVersion
				FROM	wcf".WCF_N."_package_update_version
				WHERE	packageUpdateID IN (
						SELECT	packageUpdateID
						FROM	wcf".WCF_N."_package_update
						WHERE	package = '".escapeString($package->getPackage())."'
					)
					AND packageVersion LIKE '".escapeString($match[1])."%'";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$packageVersions[] = $row['packageVersion'];
			}
			
			if (count($packageVersions) > 1) {
				// sort by version number
				usort($packageVersions, array('Package', 'compareVersion'));
				
				// get highest version
				$version = array_pop($packageVersions);
			}
		}
		
		// get all fromversion
		$fromversions = array();
		$sql = "SELECT		puv.packageVersion, puf.fromversion
			FROM		wcf".WCF_N."_package_update_fromversion puf
			LEFT JOIN	wcf".WCF_N."_package_update_version puv
			ON		(puv.packageUpdateVersionID = puf.packageUpdateVersionID)
			WHERE		puf.packageUpdateVersionID IN (
						SELECT	packageUpdateVersionID
						FROM	wcf".WCF_N."_package_update_version
						WHERE 	packageUpdateID IN (
							SELECT	packageUpdateID
							FROM	wcf".WCF_N."_package_update
							WHERE	package = '".escapeString($package->getPackage())."'
						)
					)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($fromversions[$row['packageVersion']])) $fromversions[$row['packageVersion']] = array();
			$fromversions[$row['packageVersion']][$row['fromversion']] = $row['fromversion'];
		}
		
		// sort by version number
		uksort($fromversions, array('Package', 'compareVersion'));
		
		// find shortest update thread
		$updateThread = self::findShortestUpdateThread($package->getPackage(), $fromversions, $packageVersion, $version);
		
		// process update thread
		foreach ($updateThread as $fromversion => $toVersion) {
			$packageUpdateVersions = self::getPackageUpdateVersions($package->getPackage(), $toVersion);
			
			// resolve requirements
			$this->resolveRequirements($packageUpdateVersions[0]['packageUpdateVersionID']);

			// download package
			$download = '';
			if ($this->download) {
				$download = $this->downloadPackage($package->getPackage(), $packageUpdateVersions);
			}
			
			// add to stack
			$this->packageInstallationStack[] = array(
				'packageName' => $package->getName(),
				'instanceNo' => $package->getInstanceNo(),
				'fromversion' => $fromversion,
				'toVersion' => $toVersion,
				'package' => $package->getPackage(),
				'packageID' => $packageID,
				'archive' => $download,
				'action' => 'update'
			);
			
			// update virtual versions
			$this->virtualPackageVersions[$packageID] = $toVersion;
		}
	}
	
	/**
	 * Determines intermediate update steps using a backtracking algorithm in case there is no direct upgrade possible.
	 * 
	 * @param	string		$package		package identifier
	 * @param	array		$fromversions		list of all fromversions
	 * @param	string		$currentVersion		current package version
	 * @param	string		$newVersion		new package version
	 * @return	array		list of update steps (old version => new version, old version => new version, ...)
	 */
	protected static function findShortestUpdateThread($package, $fromversions, $currentVersion, $newVersion) {
		if (!isset($fromversions[$newVersion])) {
			throw new SystemException("An update of package ".$package." from version ".$currentVersion." to ".$newVersion." is not supported.", 18100);
		}

		// find direct update
		foreach ($fromversions[$newVersion] as $fromversion) {
			if (Package::checkFromversion($currentVersion, $fromversion)) {
				return array($currentVersion => $newVersion);
			}
		}

		// find intermediate update
		$packageVersions = array_keys($fromversions);
		$updateThreadList = array();
		foreach ($fromversions[$newVersion] as $fromversion) {
			$innerUpdateThreadList = array();
			// find matching package versions
			foreach ($packageVersions as $packageVersion) {
				if (Package::checkFromversion($packageVersion, $fromversion) && Package::compareVersion($packageVersion, $currentVersion, '>') && Package::compareVersion($packageVersion, $newVersion, '<')) {
					$innerUpdateThreadList[] = self::findShortestUpdateThread($package, $fromversions, $currentVersion, $packageVersion) + array($packageVersion => $newVersion);
				}
			}
			
			if (count($innerUpdateThreadList)) {
				// sort by length
				usort($innerUpdateThreadList, array('PackageUpdate', 'compareUpdateThreadLists'));
				
				// add to thread list
				$updateThreadList[] = array_shift($innerUpdateThreadList);
			}
		}
		
		if (!count($updateThreadList)) {
			/*// try next newest version
			foreach ($packageVersions as $packageVersion) {
				if (Package::compareVersion($packageVersion, $newVersion, '>')) {
					return self::findShortestUpdateThread($package, $fromversions, $currentVersion, $packageVersion);
				}
			}*/
			
			throw new SystemException("An update of package ".$package." from version ".$currentVersion." to ".$newVersion." is not supported.", 18100);
		}
		
		// sort by length
		usort($updateThreadList, array('PackageUpdate', 'compareUpdateThreadLists'));
		
		// take shortest
		return array_shift($updateThreadList);
	}
	
	/**
	 * Resolves the package requirements of an package uppdate.
	 * Starts the installation or update to higher version of required packages.
	 * 
	 * @param	integer		$packageUpdateVersionID
	 */
	protected function resolveRequirements($packageUpdateVersionID) {
		// resolve requirements
		$requiredPackages = '';
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_update_requirement
			WHERE	packageUpdateVersionID = ".$packageUpdateVersionID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($requiredPackages)) $requiredPackages .= ',';
			$requiredPackages .= "'".escapeString($row['package'])."'";
		}
		
		if (!empty($requiredPackages)) {
			// find installed packages
			$installedPackages = array();
			$sql = "SELECT	packageID, package, packageVersion
				FROM	wcf".WCF_N."_package
				WHERE	package IN (".$requiredPackages.")";
			$result2 = WCF::getDB()->sendQuery($sql);
			while ($row2 = WCF::getDB()->fetchArray($result2)) {
				if (!isset($installedPackages[$row2['package']])) $installedPackages[$row2['package']] = array();
				$installedPackages[$row2['package']][$row2['packageID']] = (isset($this->virtualPackageVersions[$row2['packageID']]) ? $this->virtualPackageVersions[$row2['packageID']] : $row2['packageVersion']);
			}
			
			// check installed / missing packages
			WCF::getDB()->seekResult($result, 0);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (isset($installedPackages[$row['package']])) {
					// package already installed -> check version
					// sort multiple instances by version number
					uasort($installedPackages[$row['package']], array('Package', 'compareVersion'));
					
					foreach ($installedPackages[$row['package']] as $packageID => $packageVersion) {
						if (empty($row['minversion']) || Package::compareVersion($row['minversion'], $packageVersion, '<=')) {
							continue 2;
						}
					}
					
					// package version too low -> update necessary
					$this->updatePackage($packageID, $row['minversion']);
				}
				else {
					$this->tryToInstallPackage($row['package'], $row['minversion']);
				}
			}
		}
	}
	
	/**
	 * Trys to install a new package. Checks the virtual package version list.
	 * 
	 * @param 	string		$package		package identifier
	 * @param	string		$minversion		preferred package version
	 * @param	boolean		$installOldVersion	true, if you want to install the package in the given minversion and not in the newest version
	 */
	protected function tryToInstallPackage($package, $minversion = '', $installOldVersion = false) {
		// check virtual package version
		if (isset($this->virtualPackageVersions[$package])) {
			if (!empty($minversion) && Package::compareVersion($this->virtualPackageVersions[$package], $minversion, '<')) {
				$stackPosition = -1;
				// remove installation of older version
				foreach ($this->packageInstallationStack as $key => $value) {
					if ($value['package'] == $package) {
						$stackPosition = $key;
						break;
					}
				}
				
				// install newer version
				$this->installPackage($package, ($installOldVersion ? $minversion : ''), $stackPosition);
			}
		}
		else {
			// package is missing -> install
			$this->installPackage($package, ($installOldVersion ? $minversion : ''));
		}
	}
	
	/**
	 * Installs a new package.
	 * 
	 * @param 	string		$package	package identifier
	 * @param	string		$version	package version
	 * @param	integer		$stackPosition
	 */
	protected function installPackage($package, $version = '', $stackPosition = -1) {
		// get package update versions
		$packageUpdateVersions = self::getPackageUpdateVersions($package, $version);

		// resolve requirements
		$this->resolveRequirements($packageUpdateVersions[0]['packageUpdateVersionID']);
		
		// download package
		$download = '';
		if ($this->download) {
			$download = $this->downloadPackage($package, $packageUpdateVersions);
		}
			
		// add to stack
		$data = array(
			'packageName' => $packageUpdateVersions[0]['packageName'],
			'packageVersion' => $packageUpdateVersions[0]['packageVersion'],
			'package' => $package,
			'packageID' => 0,
			'archive' => $download,
			'action' => 'install'
		);
		if ($stackPosition == -1) $this->packageInstallationStack[] = $data;
		else $this->packageInstallationStack[$stackPosition] = $data;
		
		// update virtual versions
		$this->virtualPackageVersions[$package] = $packageUpdateVersions[0]['packageVersion'];
	}
	
	/**
	 * Tries to download a package from available update servers.
	 * 
	 * @param	string		$package		package identifier
	 * @param	array		$packageUpdateVersions	package update versions
	 * @return	string		tmp filename of a downloaded package
	 */
	protected function downloadPackage($package, $packageUpdateVersions) {
		// get download from cache
		if ($filename = $this->getCachedDownload($package, $packageUpdateVersions[0]['package'])) {
			return $filename;
		}

		// download file
		$authorizationRequiredException = array();
		$systemExceptions = array();
		foreach ($packageUpdateVersions as $packageUpdateVersion) {
			try {
				// get auth data
				$authData = self::getAuthData($packageUpdateVersion);		
			
				// send request
				if (!empty($packageUpdateVersion['file'])) {
					$response = self::sendRequest($packageUpdateVersion['file'], array(), $authData);
				}
				else {
					$response = self::sendRequest($packageUpdateVersion['server'], array('packageName' => $packageUpdateVersion['package'], 'packageVersion' => $packageUpdateVersion['packageVersion']), $authData);
				}
			
				// check response
				// check http code
				if ($response['httpStatusCode'] == 401) {
					throw new PackageUpdateAuthorizationRequiredException($packageUpdateVersion['packageUpdateServerID'], (!empty($packageUpdateVersion['file']) ? $packageUpdateVersion['file'] : $packageUpdateVersion['server']), $response);
				}
				
				if ($response['httpStatusCode'] != 200) {
					throw new SystemException(WCF::getLanguage()->get('wcf.acp.packageUpdate.error.downloadFailed', array('$package' => $package)) . ' ('.$response['httpStatusLine'].')', 18009);
				}
				
				// write content to tmp file
				$filename = FileUtil::getTemporaryFilename('package_');
				$file = new File($filename);
				$file->write($response['content']);
				$file->close();
				
				// test compression
				if (substr($response['content'], 0, 2) == "\37\213") {
					$tmpFilename = FileUtil::getTemporaryFilename('package_', '.tar');
					if (FileUtil::uncompressFile($filename, $tmpFilename)) {
						@unlink($filename);
						$filename = $tmpFilename;
					}
				}
				unset($response['content']);
				
				// test package
				$archive = new PackageArchive($filename);
				$archive->openArchive();
				$archive->getTar()->close();
				
				// cache download in session
				$this->cacheDownload($package, $packageUpdateVersion['packageVersion'], $filename);
				
				return $filename;
			}
			catch (PackageUpdateAuthorizationRequiredException $e) {
				$authorizationRequiredException[] = $e;
			}
			catch (SystemException $e) {
				$systemExceptions[] = $e;
			}
		}
		
		if (count($authorizationRequiredException)) {
			throw array_shift($authorizationRequiredException);
		}
		
		if (count($systemExceptions)) {
			throw array_shift($systemExceptions);
		}
		
		return false;
	}
	
	/**
	 * Stores the filename of a download in session.
	 * 
	 * @param 	string		$package	package identifier
	 * @param 	string		$version	package version
	 * @param 	string		$filename
	 */
	protected function cacheDownload($package, $version, $filename) {
		$cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
		if (!is_array($cachedDownloads)) {
			$cachedDownloads = array();
		}
		
		// store in session
		$cachedDownloads[$package.'@'.$version] = $filename;
		WCF::getSession()->register('cachedPackageUpdateDownloads', $cachedDownloads);
	}
	
	/**
	 * Gets the filename of in session stored donwloads.
	 * 
	 * @param 	string		$package	package identifier
	 * @param 	string		$version	package version
	 * @return  	string		$filename
	 */
	protected function getCachedDownload($package, $version) {
		$cachedDownloads = WCF::getSession()->getVar('cachedPackageUpdateDownloads');
		if (isset($cachedDownloads[$package.'@'.$version]) && @file_exists($cachedDownloads[$package.'@'.$version])) {
			return $cachedDownloads[$package.'@'.$version];
		}
		
		return false;
	}
	
	/**
	 * Returns the newest available version of a package.
	 * 
	 * @param	string		$package	package identifier
	 * @return	string		newest package version
	 */
	public static function getNewestPackageVersion($package) {
		// get all versions
		$versions = array();
		$sql = "SELECT	packageVersion
			FROM	wcf".WCF_N."_package_update_version
			WHERE	packageUpdateID IN (
					SELECT	packageUpdateID
					FROM	wcf".WCF_N."_package_update
					WHERE	package = '".escapeString($package)."'
				)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$versions[$row['packageVersion']] = $row['packageVersion'];
		}
		
		// sort by version number
		usort($versions, array('Package', 'compareVersion'));
		
		// take newest (last)
		return array_pop($versions);
	}
	
	/**
	 * Gets package update versions of a package.
	 * 
	 * @param	string		$package	package identifier
	 * @param	string		$version	package version
	 * @return	array		package update versions
	 */
	public static function getPackageUpdateVersions($package, $version = '') {
		// get newest package version
		if (empty($version)) {
			$version = self::getNewestPackageVersion($package);
		}
		
		// get versions
		$versions = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_update_version puv,
					wcf".WCF_N."_package_update pu
			LEFT JOIN	wcf".WCF_N."_package_update_server pus
			ON		(pus.packageUpdateServerID = pu.packageUpdateServerID)
			WHERE		puv.packageUpdateID = pu.packageUpdateID
					AND pu.package = '".escapeString($package)."'
					AND puv.packageVersion = '".escapeString($version)."'";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$versions[] = $row;
		}
		
		if (!count($versions)) {
			throw new SystemException("Can not find package '".$package."' in version '".$version."'", 18101);
		}
		
		return $versions;
	}
	
	/**
	 * Saves the stack of package installations in the package installation queue table.
	 */
	public function savePackageInstallationStack() {
		// get new process no
		require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		// build inserts
		$inserts = '';
		foreach ($this->packageInstallationStack as $package) {
			// unzip tar
			$package['archive'] = PackageArchive::unzipPackageArchive($package['archive']);
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= "(".$processNo.", ".WCF::getUser()->userID.", '".escapeString($package['package'])."', ".$package['packageID'].", '".escapeString($package['archive'])."', '".escapeString($package['action'])."')";
		}
		
		// save inserts
		if (!empty($inserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
						(processNo, userID, package, packageID, archive, action)
				VALUES		".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		return $processNo;
	}
	
	/**
	 * Returns the stack of package installations.
	 * 
	 * @return	array
	 */
	public function getPackageInstallationStack() {
		return $this->packageInstallationStack;
	}

	/**
	 * Refreshes the package database.
	 *
	 * @param	array		$packageUpdateServerIDs
	 */
	public static function refreshPackageDatabase($packageUpdateServerIDs = array()) {
		// get update server data
		$updateServers = UpdateServer::getActiveUpdateServers($packageUpdateServerIDs);
		
		// loop servers
		foreach ($updateServers as $updateServer) {
			if ($updateServer['timestamp'] < TIME_NOW - 600) {
				try {
					self::getPackageUpdateXML($updateServer);
				}
				catch (SystemException $e) {
					// save error status
					$updateServerEditor = new UpdateServerEditor(null, $updateServer);
					$updateServerEditor->updateStatus($updateServer['timestamp'], 'offline', $e->getMessage());
				}
			}
		}
	}
	
	/**
	 * Refreshes the package database automatically - without prompting for authentication.
	 */
	public static function refreshPackageDatabaseAutomatically() {
		// get update server data
		$updateServers = UpdateServer::getActiveUpdateServers();
		
		// loop servers
		foreach ($updateServers as $updateServer) {
			try {
				self::getPackageUpdateXML($updateServer);
			}
			catch (PackageUpdateAuthorizationRequiredException $e) {
				// ignore
			}
			catch (SystemException $e) {
				// save error status
				$updateServerEditor = new UpdateServerEditor(null, $updateServer);
				$updateServerEditor->updateStatus($updateServer['timestamp'], 'offline', $e->getMessage());
			}
		}
	}
	
	/**
	 * Gets the package_update.xml from an update server.
	 * 
	 * @param	array		$updateServer
	 */
	protected static function getPackageUpdateXML($updateServer) {
		// get auth data
		$authData = self::getAuthData($updateServer);		
		
		// send request
		$response = self::sendRequest($updateServer['server'], array('timestamp' => $updateServer['timestamp']), $authData);
		
		// check response
		// check http code
		if ($response['httpStatusCode'] == 401) {
			throw new PackageUpdateAuthorizationRequiredException($updateServer['packageUpdateServerID'], $updateServer['server'], $response);
		}
		
		if ($response['httpStatusCode'] != 200) {
			throw new SystemException(WCF::getLanguage()->get('wcf.acp.packageUpdate.error.listNotFound') . ' ('.$response['httpStatusLine'].')', 18009);
		}
		
		// parse given package update xml
		$allNewPackages = self::parsePackageUpdateXML($response['content']);
		unset($response);
		
		// save packages
		if (count($allNewPackages)) {
			self::savePackageUpdates($allNewPackages, $updateServer['packageUpdateServerID']);
		}
		unset($allNewPackages);
		
		// update server status
		$updateServerEditor = new UpdateServerEditor(null, $updateServer);
		$updateServerEditor->updateStatus(TIME_NOW);
	}
	
	/**
	 * Updates information parsed from a packages_update.xml into the database.
	 * 
	 * @param 	array		$allNewPackages
	 * @param	integer		$packageUpdateServerID
	 */
	protected static function savePackageUpdates(&$allNewPackages, $packageUpdateServerID) {
		// find existing packages and delete them
		$packageNames = implode("','", array_map('escapeString', array_keys($allNewPackages)));
		
		// get existing packages
		$existingPackages = array();
		$sql = "SELECT	package, packageUpdateID
			FROM	wcf".WCF_N."_package_update
			WHERE	packageUpdateServerID = ".$packageUpdateServerID."
				AND package IN ('".$packageNames."')";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$existingPackages[$row['package']] = $row['packageUpdateID'];
		}
		
		// get existing versions
		$existingPackageVersions = array();
		if (count($existingPackages) > 0) {
			$sql = "SELECT	packageUpdateID, packageUpdateVersionID, packageVersion
				FROM	wcf".WCF_N."_package_update_version
				WHERE	packageUpdateID IN (".implode(',', $existingPackages).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($existingPackageVersions[$row['packageUpdateID']])) $existingPackageVersions[$row['packageUpdateID']] = array();
				$existingPackageVersions[$row['packageUpdateID']][$row['packageVersion']] = $row['packageUpdateVersionID'];
			}
		}
		
		// insert updates
		$requirementInserts = $fromversionInserts = $excludedPackagesInserts = '';
		foreach ($allNewPackages as $identifier => $packageData) {
			if (isset($existingPackages[$identifier])) {
				$packageUpdateID = $existingPackages[$identifier];
				
				// update database entry
				$sql = "UPDATE	wcf".WCF_N."_package_update
					SET	packageName = '".escapeString($packageData['packageName'])."',
						packageDescription = '".escapeString($packageData['packageDescription'])."',
						author = '".escapeString($packageData['author'])."',
						authorURL = '".escapeString($packageData['authorURL'])."',
						standalone = ".$packageData['standalone'].",
						plugin = '".escapeString($packageData['plugin'])."'
					WHERE	packageUpdateID = ".$packageUpdateID;
				WCF::getDB()->sendQuery($sql);
			}
			else {			
				// create new database entry
				$sql = "INSERT INTO			wcf".WCF_N."_package_update 
									(packageUpdateServerID, package, packageName, 
									packageDescription, author, authorURL, standalone, plugin)  
					VALUES				(".$packageUpdateServerID.", 
									'".escapeString($identifier)."',
									'".escapeString($packageData['packageName'])."',
									'".escapeString($packageData['packageDescription'])."',
									'".escapeString($packageData['author'])."',
									'".escapeString($packageData['authorURL'])."',
									".$packageData['standalone'].",
									'".escapeString($packageData['plugin'])."')";
				WCF::getDB()->sendQuery($sql);
				$packageUpdateID = WCF::getDB()->getInsertID();
			}
			
			// register version(s) of this update package.
			if (isset($packageData['versions'])) {
				foreach ($packageData['versions'] as $packageVersion => $versionData) {
					if (isset($versionData['file'])) $packageFile = $versionData['file'];
					else $packageFile = '';
					
					if (isset($existingPackageVersions[$packageUpdateID]) && isset($existingPackageVersions[$packageUpdateID][$packageVersion])) {
						$packageUpdateVersionID = $existingPackageVersions[$packageUpdateID][$packageVersion];
						
						// update database entry
						$sql = "UPDATE	wcf".WCF_N."_package_update_version
							SET	updateType = '".escapeString($versionData['updateType'])."',
								timestamp = '".escapeString($versionData['timestamp'])."',
								file = '".escapeString($packageFile)."'
							WHERE	packageUpdateVersionID = ".$packageUpdateVersionID;
						WCF::getDB()->sendQuery($sql);
					}
					else {
						// create new database entry
						$sql = "INSERT INTO			wcf".WCF_N."_package_update_version 
											(packageUpdateID, packageVersion, updateType, 
											timestamp, file) 
							VALUES				(".$packageUpdateID.",
											'".escapeString($packageVersion)."',
											'".escapeString($versionData['updateType'])."',
											'".escapeString($versionData['timestamp'])."',
											'".escapeString($packageFile)."')";
						WCF::getDB()->sendQuery($sql);
						$packageUpdateVersionID = WCF::getDB()->getInsertID();
					}
					
					// register requirement(s) of this update package version.
					if (isset($versionData['requiredPackages'])) {
						foreach ($versionData['requiredPackages'] as $requiredIdentifier => $required) {
							if (!empty($requirementInserts)) $requirementInserts .= ',';
							$requirementInserts .= "(".$packageUpdateVersionID.", '".escapeString($requiredIdentifier)."',
										'".(!empty($required['minversion']) ? escapeString($required['minversion']) : '')."')";
						}
					}
					
					// register excluded packages of this update package version.
					if (isset($versionData['excludedPackages'])) {
						foreach ($versionData['excludedPackages'] as $excludedIdentifier => $exclusion) {
							if (!empty($excludedPackagesInserts)) $excludedPackagesInserts .= ',';
							$excludedPackagesInserts .= "(".$packageUpdateVersionID.", '".escapeString($excludedIdentifier)."',
										'".(!empty($exclusion['version']) ? escapeString($exclusion['version']) : '')."')";
						}
					}
					
					// register fromversions of this update package version.
					if (isset($versionData['fromversions'])) {
						foreach ($versionData['fromversions'] as $fromversion) {
							if (!empty($fromversionInserts)) $fromversionInserts .= ',';
							$fromversionInserts .= "(".$packageUpdateVersionID.", '".escapeString($fromversion)."')";
						}
					}
				}
			}
		}
		
		// save requirements, excluded packages and fromversions
		// use multiple inserts to save some queries
		if (!empty($requirementInserts)) {
			$sql = "INSERT INTO			wcf".WCF_N."_package_update_requirement 
								(packageUpdateVersionID, package, 
								minversion) 
				VALUES				".$requirementInserts."
				ON DUPLICATE KEY UPDATE		minversion = VALUES(minversion)";
			WCF::getDB()->sendQuery($sql);
		}
		if (!empty($excludedPackagesInserts)) {
			$sql = "INSERT INTO			wcf".WCF_N."_package_update_exclusion 
								(packageUpdateVersionID, excludedPackage, excludedPackageVersion) 
				VALUES				".$excludedPackagesInserts."
				ON DUPLICATE KEY UPDATE		excludedPackageVersion = VALUES(excludedPackageVersion)";
			WCF::getDB()->sendQuery($sql);
		}
		if (!empty($fromversionInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_update_fromversion 
							(packageUpdateVersionID, fromversion) 
				VALUES			".$fromversionInserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Parses a stream containing info from a packages_update.xml.
	 *
	 * @param	string		$content
	 * @return	array		$allNewPackages
	 */
	protected static function parsePackageUpdateXML($content) {
		// load xml document
		$xmlObj = new XML();
		$xmlObj->loadString($content);
		
		// load the <section> tag (which must be the root element).
		$xml = $xmlObj->getElementTree('section');
		$encoding = $xmlObj->getEncoding();
		unset($xmlObj);
		
		// loop through <package> tags inside the <section> tag.
		$allNewPackages = array();
		foreach ($xml['children'] as $child) {
			// name attribute is missing, thus this package is not valid
			if (!isset($child['attrs']['name']) || !$child['attrs']['name']) {
				throw new SystemException("required 'name' attribute for 'package' tag is missing", 13001);
			}

			// the "name" attribute of this <package> tag must be a valid package identifier.
			if (!Package::isValidPackageName($child['attrs']['name'])) {
				throw new SystemException("'".$child['attrs']['name']."' is not a valid package name.", 18004);
			}
			
			$package = $child['attrs']['name'];
			// parse packages_update.xml and fill $packageInfo.
			$packageInfo = self::parsePackageUpdateXMLBlock($child, $package);
			// convert enconding
			if ($encoding != CHARSET) {
				$packageInfo['packageName'] = StringUtil::convertEncoding($encoding, CHARSET, $packageInfo['packageName']);
				$packageInfo['packageDescription'] = StringUtil::convertEncoding($encoding, CHARSET, $packageInfo['packageDescription']);
				$packageInfo['author'] = StringUtil::convertEncoding($encoding, CHARSET, $packageInfo['author']);
				$packageInfo['authorURL'] = StringUtil::convertEncoding($encoding, CHARSET, $packageInfo['authorURL']);
			}
			
			$allNewPackages[$child['attrs']['name']] = $packageInfo;
		}
		unset($xml);
		
		return $allNewPackages;
	}
	
	/**
	 * Parses the xml stucture from a packages_update.xml.
	 *
	 * @param	array		$child
	 * @param	string		$package
	 * @return	array		$packageInfo
	 */
	protected static function parsePackageUpdateXMLBlock($child = array(), $package = '') {
		// define default values
		$packageInfo = array(
			'packageDescription' => '',
			'standalone' => 0,
			'plugin' => '',
			'author' => '',
			'authorURL' => '',
			'versions' => array()
		);
		
		// loop through tags inside the <package> tag.
		foreach ($child['children'] as $packageDefinition) {
			switch (StringUtil::toLowerCase($packageDefinition['name'])) {
				case 'packageinformation':
					// loop through tags inside the <packageInformation> tag.
					foreach ($packageDefinition['children'] as $packageInformation) {
						switch (StringUtil::toLowerCase($packageInformation['name'])) {
							case 'packagename':
								$packageInfo['packageName'] = $packageInformation['cdata'];
								break;
							case 'packagedescription':
								$packageInfo['packageDescription'] = $packageInformation['cdata'];
								break;
							case 'standalone':
								$packageInfo['standalone'] = intval($packageInformation['cdata']);
								break;
							case 'plugin':
								$packageInfo['plugin'] = $packageInformation['cdata'];
								break;
						}
					}
					
					break;
				case 'authorinformation':
					// loop through tags inside the <authorInformation> tag.
					foreach ($packageDefinition['children'] as $authorInformation) {
						switch (StringUtil::toLowerCase($authorInformation['name'])) {
							case 'author':
								$packageInfo['author'] = $authorInformation['cdata'];
							break;
							case 'authorurl':
								$packageInfo['authorURL'] = $authorInformation['cdata'];
							break;
						}
					}
					break;
				case 'versions':
					// loop through <version> tags inside the <versions> tag.
					foreach ($packageDefinition['children'] as $versions) {
						$versionNo = $versions['attrs']['name'];
						// loop through tags inside this <version> tag.
						foreach ($versions['children'] as $version) {
							switch (StringUtil::toLowerCase($version['name'])) {
								case 'fromversions':
									// loop through <fromversion> tags inside the <fromversions> block.
									foreach ($version['children'] as $fromversion) {
										$packageInfo['versions'][$versionNo]['fromversions'][] = $fromversion['cdata'];
									}
									break;
								case 'updatetype':
									$packageInfo['versions'][$versionNo]['updateType'] = $version['cdata'];
									break;
								case 'timestamp':
									$packageInfo['versions'][$versionNo]['timestamp'] = $version['cdata'];
									break;
								case 'file':
									$packageInfo['versions'][$versionNo]['file'] = $version['cdata'];
									break;
								case 'requiredpackages':
									// loop through <requiredPackage> tags inside the <requiredPackages> block.
									foreach ($version['children'] as $requiredPackages) {
										$required = $requiredPackages['cdata'];
										$packageInfo['versions'][$versionNo]['requiredPackages'][$required] = array();
										if (isset($requiredPackages['attrs']['minversion'])) {
											$packageInfo['versions'][$versionNo]['requiredPackages'][$required]['minversion'] = $requiredPackages['attrs']['minversion'];
										}
									}
									break;
								case 'excludedpackages':
									// loop through <excludedpackage> tags inside the <excludedpackages> block.
									foreach ($version['children'] as $excludedpackage) {
										$exclusion = $excludedpackage['cdata'];
										$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion] = array();
										if (isset($excludedpackage['attrs']['version'])) {
											$packageInfo['versions'][$versionNo]['excludedPackages'][$exclusion]['version'] = $excludedpackage['attrs']['version'];
										}
									}
									break;
							}
						}
					}
					break;
			}
		}
		
		// check required tags
		if (!isset($packageInfo['packageName'])) {
			throw new SystemException("required tag 'packageName' is missing for package '".$package."'", 13001);
		}
		if (!count($packageInfo['versions'])) {
			throw new SystemException("required tag 'versions' is missing for package '".$package."'", 13001);
		}
		
		return $packageInfo;
	}
	
	/**
	 * Sends a request to a remote (update) server.
	 * 
	 * @param	string		$url
	 * @param	array		$values
	 * @param	array		$authData
	 * @return	array		$response
	 */
	protected static function sendRequest($url, $values = array(), $authData = array()) {
		// default values
		$host = '';
		$path = '/';
		$port = 80;
		$postString = '';
		
		// parse url
		$parsedURL = parse_url($url);
		if (!empty($parsedURL['host'])) $host = $parsedURL['host'];
		if (!empty($parsedURL['path'])) $path = $parsedURL['path'];
		if (!empty($parsedURL['query'])) $postString = $parsedURL['query'];
		if (!empty($parsedURL['port'])) $port = $parsedURL['port'];
		
		// connect to server
		require_once(WCF_DIR.'lib/system/io/RemoteFile.class.php');
		if (PROXY_SERVER_HTTP) {
			$parsedProxyURL = parse_url(PROXY_SERVER_HTTP);
			$remoteFile = new RemoteFile($parsedProxyURL['host'], $parsedProxyURL['port'], 30);
			$path = $url;
			$host = $parsedProxyURL['host'];
		}
		else {
			$remoteFile = new RemoteFile($host, $port, 30);
		}
		
		// Build and send the http request
		$request = "POST ".$path." HTTP/1.0\r\n";
		if (isset($authData['authType'])) {
			$request .= "Authorization: Basic ".base64_encode($authData['htUsername'].":".$authData['htPassword'])."\r\n";
		}
		
		$request .= "User-Agent: HTTP.PHP (PackageUpdate.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->getLanguageCode().")\r\n";
		$request .= "Accept: */*\r\n";
		$request .= "Accept-Language: ".WCF::getLanguage()->getLanguageCode()."\r\n";
		$request .= "Host: ".$host."\r\n";
		
		// build post string
		foreach ($values as $name => $value) {
			if (!empty($postString)) $postString .= '&';
			$postString .= $name.'='.$value;
		}
		
		// send content type and length
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
	   	$request .= "Content-Length: ".strlen($postString)."\r\n";
	   	// if it is a POST request, there MUST be a blank line before the POST data, but there MUST NOT be 
	   	// another blank line before, and of course there must be another blank line at the end of the request!
	   	$request .= "\r\n";
	   	if (!empty($postString)) $request .= $postString."\r\n";
		// send close
	   	$request .= "Connection: Close\r\n\r\n";
		
	   	// send request
	   	$remoteFile->puts($request);
	   	unset($request, $postString);
	   	
	   	// define response vars
	   	$header = $content = '';
		
		// fetch the response.
		while (!$remoteFile->eof()) {
			$line = $remoteFile->gets();
			if (rtrim($line) != '') {
				$header .= $line;
			} else {
				break;
			}
		}
		while (!$remoteFile->eof()) {
			$content .= $remoteFile->gets();
		}
		
		// clean up and return the server's response.
		$remoteFile->close();
		
		// get http status code / line
		$httpStatusCode = 0;
		$httpStatusLine = '';
		if (preg_match('%http/\d\.\d (\d{3})[^\n]*%i', $header, $match)) {
			$httpStatusLine = trim($match[0]);
			$httpStatusCode = $match[1];
		}
		
		// catch http 301 Moved Permanently
		// catch http 302 Found
		// catch http 303 See Other
		if ($httpStatusCode == 301 || $httpStatusCode == 302 || $httpStatusCode == 303) {
			// find location
			if (preg_match('/location:([^\n]*)/i', $header, $match)) {
				$location = trim($match[1]);
				if ($location != $url) {
					return self::sendRequest($location, $values, $authData);
				}
			}
		}
		// catch other http codes here
		
		return array(
			'httpStatusLine' => $httpStatusLine,
			'httpStatusCode' => $httpStatusCode,
			'header' => $header,
			'content' => $content
		);
	}
	
	/**
	 * Gets stored auth data of given update server.
	 *
	 * @param	array		$updateServer
	 * @return	array		$authData
	 */
	protected static function getAuthData($updateServer) {
		$updateServerObj = new UpdateServer(null, $updateServer);
		return $updateServerObj->getAuthData();
	}
	
	/**
	 * Returns a list of available updates for installed packages.
	 * 
	 * @param	boolean		$removeRequirements
	 * @return 	array
	 */
	public static function getAvailableUpdates($removeRequirements = true) {
		$updates = array();
		
		// get update server data
		$updateServers = UpdateServer::getActiveUpdateServers();
		$packageUpdateServerIDs = implode(',', array_keys($updateServers));
		if (empty($packageUpdateServerIDs)) return $updates;
		
		// get existing packages and their versions
		$existingPackages = array();
		$sql = "SELECT	packageID, package, instanceNo, packageDescription,
				packageVersion, packageDate, author, authorURL, standalone,
				CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
			FROM	wcf".WCF_N."_package";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$existingPackages[$row['package']][] = $row;
		}
		$existingPackageIdentifiers = implode("','", array_keys($existingPackages));
		if (empty($existingPackageIdentifiers)) return $updates;
		
		// get all update versions
		$sql = "SELECT		pu.packageUpdateID, pu.packageUpdateServerID, pu.package,
					puv.packageUpdateVersionID, puv.updateType, puv.timestamp, puv.file, puv.packageVersion
			FROM		wcf".WCF_N."_package_update pu
			LEFT JOIN	wcf".WCF_N."_package_update_version puv
			ON		(puv.packageUpdateID = pu.packageUpdateID)
			WHERE		pu.packageUpdateServerID IN (".$packageUpdateServerIDs.")
					AND package IN (
						SELECT	DISTINCT package
						FROM	wcf".WCF_N."_package
					)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// test version
			foreach ($existingPackages[$row['package']] as $existingVersion) {
				if (Package::compareVersion($existingVersion['packageVersion'], $row['packageVersion'], '<')) {
					// package data
					if (!isset($updates[$existingVersion['packageID']])) {
						$existingVersion['versions'] = array();
						$updates[$existingVersion['packageID']] = $existingVersion;
					}
					
					// version data
					if (!isset($updates[$existingVersion['packageID']]['versions'][$row['packageVersion']])) {
						$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']] = array(
							'updateType' => $row['updateType'],
							'timestamp' => $row['timestamp'],
							'packageVersion' => $row['packageVersion'],
							'servers' => array()
						);
					}
					
					// server data
					$updates[$existingVersion['packageID']]['versions'][$row['packageVersion']]['servers'][] = array(
						'packageUpdateID' => $row['packageUpdateID'],
						'packageUpdateServerID' => $row['packageUpdateServerID'],
						'packageUpdateVersionID' => $row['packageUpdateVersionID'],
						'file' => $row['file']
					);
				}
			}
		}
		
		// sort package versions
		// and remove old versions
		foreach ($updates as $packageID => $data) {
			uksort($updates[$packageID]['versions'], array('Package', 'compareVersion'));
			$updates[$packageID]['version'] = end($updates[$packageID]['versions']);
		}
		
		// remove requirements of standalone packages
		if ($removeRequirements) {
			foreach ($existingPackages as $identifier => $instances) {
				foreach ($instances as $instance) {
					if ($instance['standalone'] && isset($updates[$instance['packageID']])) {
						$updates = self::removeUpdateRequirements($updates, $updates[$instance['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
					}
				}
			}
		}
		
		return $updates;
	}
	
	/**
	 * Removes unnecessary updates of requirements from the list of available updates.
	 * 
	 * @param	array		$updates
	 * @param 	integer		$packageUpdateVersionID
	 * @return	array		$updates
	 */
	protected static function removeUpdateRequirements($updates, $packageUpdateVersionID) {
		$sql = "SELECT		pur.package, pur.minversion, p.packageID
			FROM		wcf".WCF_N."_package_update_requirement pur
			LEFT JOIN	wcf".WCF_N."_package p
			ON		(p.package = pur.package)
			WHERE		pur.packageUpdateVersionID = ".$packageUpdateVersionID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (isset($updates[$row['packageID']])) {
				$updates = self::removeUpdateRequirements($updates, $updates[$row['packageID']]['version']['servers'][0]['packageUpdateVersionID']);
				if (Package::compareVersion($row['minversion'], $updates[$row['packageID']]['version']['packageVersion'], '>=')) {
					unset($updates[$row['packageID']]);
				}
			}
		}
		
		return $updates;
	}
	
	/**
	 * Compares the length of two updates threads.
	 * 
	 * @param	array		$updateThreadListA
	 * @param	array		$updateThreadListB
	 * @return	integer
	 */
	private static function compareUpdateThreadLists($updateThreadListA, $updateThreadListB) {
		$countA = count($updateThreadListA);
		$countB = count($updateThreadListB);
		
		if ($countA < $countB) return -1;
		if ($countA > $countB) return 1;
		return 0;
	}
	
	/**
	 * Returns a list of excluded packages.
	 * 
	 * @return	array
	 */
	public function getExcludedPackages() {
		$excludedPackages = array();
		
		if (count($this->packageInstallationStack)) {
			$packageInstallations = array();
			$packageIdentifier = array();
			foreach ($this->packageInstallationStack as $packageInstallation) {
				$packageInstallation['newVersion'] = ($packageInstallation['action'] == 'update' ? $packageInstallation['toVersion'] : $packageInstallation['packageVersion']);
				$packageInstallations[] = $packageInstallation;
				$packageIdentifier[] = $packageInstallation['package'];
			}

			// check exclusions of the new packages
			// get package update ids
			$sql = "SELECT	packageUpdateID, package
				FROM	wcf".WCF_N."_package_update
				WHERE	package IN ('".implode("','", $packageIdentifier)."')";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['package']) {
						$packageInstallations[$key]['packageUpdateID'] = $row['packageUpdateID'];
					}
				}
			}
			
			// get exclusions of the new packages
			// build conditions
			$conditions = '';
			foreach ($packageInstallations as $packageInstallation) {
				if (!empty($conditions)) $conditions .= ' OR ';
				$conditions .= "(packageUpdateID = ".$packageInstallation['packageUpdateID']." AND packageVersion = '".escapeString($packageInstallation['newVersion'])."')";
			}
			
			$sql = "SELECT		package.*, package_update_exclusion.*,
						package_update.packageUpdateID,
						package_update.package
				FROM		wcf".WCF_N."_package_update_exclusion package_update_exclusion
				LEFT JOIN	wcf".WCF_N."_package_update_version package_update_version
				ON		(package_update_version.packageUpdateVersionID = package_update_exclusion.packageUpdateVersionID)
				LEFT JOIN	wcf".WCF_N."_package_update package_update
				ON		(package_update.packageUpdateID = package_update_version.packageUpdateID)
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.package = package_update_exclusion.excludedPackage)
				WHERE		package_update_exclusion.packageUpdateVersionID IN (
							SELECT	packageUpdateVersionID
							FROM	wcf".WCF_N."_package_update_version
							WHERE	".$conditions."
						)
						AND package.package IS NOT NULL";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['package']) {
						if (!isset($packageInstallations[$key]['excludedPackages'])) {
							$packageInstallations[$key]['excludedPackages'] = array();
						}
						$packageInstallations[$key]['excludedPackages'][$row['excludedPackage']] = array('package' => $row['excludedPackage'], 'version' => $row['excludedPackageVersion']);
						
						// check version
						if (!empty($row['excludedPackageVersion'])) {
							if (Package::compareVersion($row['packageVersion'], $row['excludedPackageVersion'], '<')) {
								continue;
							}
						}
						
						$excludedPackages[] = array(
							'package' => $row['package'],
							'packageName' => $packageInstallations[$key]['packageName'],
							'packageVersion' => $packageInstallations[$key]['newVersion'],
							'action' => $packageInstallations[$key]['action'],
							'conflict' => 'newPackageExcludesExistingPackage',
							'existingPackage' => $row['excludedPackage'],
							'existingPackageName' => $row['packageName'],
							'existingPackageVersion' => $row['packageVersion']
						);
					}
				}
			}
			
			// check excluded packages of the existing packages
			$sql = "SELECT		package.*, package_exclusion.*
				FROM		wcf".WCF_N."_package_exclusion package_exclusion
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = package_exclusion.packageID)
				WHERE		excludedPackage IN ('".implode("','", $packageIdentifier)."')";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				foreach ($packageInstallations as $key => $packageInstallation) {
					if ($packageInstallation['package'] == $row['excludedPackage']) {
						if (!empty($row['excludedPackageVersion'])) {
							// check version
							if (Package::compareVersion($packageInstallation['newVersion'], $row['excludedPackageVersion'], '<')) {
								continue;
							}
							
							// search exclusing package in stack
							foreach ($packageInstallations as $packageUpdate) {
								if ($packageUpdate['packageID'] == $row['packageID']) {
									// check new exclusions
									if (!isset($packageUpdate['excludedPackages']) || !isset($packageUpdate['excludedPackages'][$row['excludedPackage']]) || (!empty($packageUpdate['excludedPackages'][$row['excludedPackage']]['version']) && Package::compareVersion($packageInstallation['newVersion'], $packageUpdate['excludedPackages'][$row['excludedPackage']]['version'], '<'))) {
										continue 2;
									}
								}
							}
						}

						$excludedPackages[] = array(
							'package' => $row['excludedPackage'],
							'packageName' => $packageInstallation['packageName'],
							'packageVersion' => $packageInstallation['newVersion'],
							'action' => $packageInstallation['action'],
							'conflict' => 'existingPackageExcludesNewPackage',
							'existingPackage' => $row['package'],
							'existingPackageName' => $row['packageName'],
							'existingPackageVersion' => $row['packageVersion']
						);
					}
				}
			}
		}

		return $excludedPackages;
	}
}
?>