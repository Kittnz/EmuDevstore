<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/acp/package/Package.class.php');
	require_once(WCF_DIR.'lib/system/io/Tar.class.php');
	require_once(WCF_DIR.'lib/system/io/RemoteFile.class.php');
	require_once(WCF_DIR.'lib/util/StringUtil.class.php');
}

/**
 * This class holds all information of a package archive. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageArchive {
	/**
	 * path to archive
	 *
	 * @var string
	 */
	protected $archive;
	
	/**
	 * package object of an existing package
	 *
	 * @var Package
	 */
	protected $package;
	
	/**
	 * tar archive object
	 *
	 * @var string
	 */
	protected $tar;
	
	/**
	 * general package information
	 *
	 * @var array
	 */
	protected $packageInfo = array();
	
	/**
	 * author information
	 *
	 * @var array
	 */
	protected $authorInfo = array();
	
	/**
	 * list of requirements
	 *
	 * @var array
	 */
	protected $requirements = array();
	
	/**
	 * list of optional packages
	 *
	 * @var array
	 */
	protected $optionals = array();
	
	/**
	 * list of excluded packages
	 * 
	 * @var	array
	 */
	protected $excludedPackages = array();
	
	/**
	 * installation instructions
	 *
	 * @var array
	 */
	protected $install = null;
	
	/**
	 * update instructions
	 *
	 * @var array
	 */
	protected $update = null;
	
	/**
	 * default name of the package.xml file
	 *
	 * @var string
	 */
	const INFO_FILE = 'package.xml';
	
	/**
	 * Creates a new PackageArchive object.
	 * 
	 * @param	string		$archive
	 * @param	Package		$package
	 */
	public function __construct($archive, $package = null) {
		$this->archive = $archive; 	// be careful: this is a string within this class, 
						// but an object in the packageStartInstallForm.class!
		$this->package = $package;
	}
	
	/**
	 * Returns the name of the package archive.
	 * 
	 * @return	string
	 */
	public function getArchive() {
		return $this->archive;
	}
	
	/**
	 * Returns the object of the package archive.
	 * 
	 * @return	Tar
	 */
	public function getTar() {
		return $this->tar;
	}
	
	/**
	 * Opens the package archive and reads package information.
	 */
	public function openArchive() {
		// check whether archive exists and is a TAR archive
		if (!file_exists($this->archive)) {
			throw new SystemException("unable to find package file '".$this->archive."'", 11002);
		}

		// open archive and read package information
		$this->tar = new Tar($this->archive);
		$this->readPackageInfo();
	}
	
	/**
	 * Extracts information about this package (parses package.xml).
	 */
	protected function readPackageInfo() {
		// search package.xml in package archive
		// throw error message if not found
		if ($this->tar->getIndexByFilename(self::INFO_FILE) === false) {
			throw new SystemException("package information file '".(self::INFO_FILE)."' not found in '".$this->archive."'", 13000);
		}
		
		// extract package.xml, parse with SimpleXML
		// and compile an array with XML::getElementTree()
		$xml = new XML();
		try {
			$xml->loadString($this->tar->extractToString(self::INFO_FILE));
		}
		catch (Exception $e) { // bugfix to avoid file caching problems
			$xml->loadString($this->tar->extractToString(self::INFO_FILE));
		}
		$xmlContent = $xml->getElementTree('package');
		
		// name attribute is missing, thus this package is not valid
		if (!isset($xmlContent['attrs']['name']) || !$xmlContent['attrs']['name']) {
			throw new SystemException("required 'name' attribute for 'package' tag is missing in ".self::INFO_FILE, 13001);
		}

		// package name is not a valid package identifier
		if (!Package::isValidPackageName($xmlContent['attrs']['name'])) {
			throw new SystemException("'".$xmlContent['attrs']['name']."' is not a valid package name.", 13002);
		}

		// assign name attribute and loop through child tags
		$this->packageInfo['name'] = $xmlContent['attrs']['name'];
		foreach ($xmlContent['children'] as $child) {
			switch (StringUtil::toLowerCase($child['name'])) {
				// read in package information
				case 'packageinformation':
					foreach ($child['children'] as $packageInformation) {
						switch (StringUtil::toLowerCase($packageInformation['name'])) {
							case 'packagename':
								if (!isset($this->packageInfo['packageName'])) $this->packageInfo['packageName'] = array();
								
								if (isset($packageInformation['attrs']['language'])) {
									$languageCode = $packageInformation['attrs']['language'];
								}
								else if (isset($packageInformation['attrs']['languagecode'])) {
									$languageCode = $packageInformation['attrs']['languagecode'];
								}
								else {
									$languageCode = 'default';
								}
								
								$this->packageInfo['packageName'][$languageCode] = $packageInformation['cdata'];
								break;
							case 'packagedescription':
								if (!isset($this->packageInfo['packageDescription'])) $this->packageInfo['packageDescription'] = array();
								
								if (isset($packageInformation['attrs']['language'])) {
									$languageCode = $packageInformation['attrs']['language'];
								}
								else if (isset($packageInformation['attrs']['languagecode'])) {
									$languageCode = $packageInformation['attrs']['languagecode'];
								}
								else {
									$languageCode = 'default';
								}
								
								$this->packageInfo['packageDescription'][$languageCode] = $packageInformation['cdata'];
								break;
							case 'isunique':
								$this->packageInfo['isUnique'] = intval($packageInformation['cdata']);
								break;
							case 'standalone':
								$this->packageInfo['standalone'] = intval($packageInformation['cdata']);
								break;
							case 'promptparent':
							case 'plugin':
								if ($packageInformation['cdata'] != 0 && !Package::isValidPackageName($packageInformation['cdata'])) {
									throw new SystemException("'".$packageInformation['cdata']."' is not a valid package name.", 13002);
								}

								$this->packageInfo['plugin'] = $packageInformation['cdata'];
								break;
							case 'version':
								$this->packageInfo['version'] = $packageInformation['cdata'];
								break;
							case 'date':
								$this->packageInfo['date'] = @strtotime($packageInformation['cdata']);
								if ($this->packageInfo['date'] === -1 || $this->packageInfo['date'] === false) {
									throw new SystemException("invalid dateformat '".$packageInformation['cdata']."' in package.xml", 13003);
								}
								$this->packageInfo['date'] += 43201;
								break;
							case 'packageurl':
								$this->packageInfo['packageURL'] = $packageInformation['cdata'];
								break;
						}
					}
					break;

				// read in author information
				case 'authorinformation':
					foreach ($child['children'] as $authorInformation) {
						switch (StringUtil::toLowerCase($authorInformation['name'])) {
							case 'author':
								$this->authorInfo['author'] = $authorInformation['cdata'];
								break;
							case 'authorurl':
								$this->authorInfo['authorURL'] = $authorInformation['cdata'];
								break;
						}
					}
					break;

				// read in requirements
				case 'requiredpackages':
					foreach ($child['children'] as $requiredPackage) {
						// reference to required package is not a valid package identifier
						if (!Package::isValidPackageName($requiredPackage['cdata'])) {
							throw new SystemException("'".$requiredPackage['cdata']."' is not a valid package name.", 13002);
						}
						$this->requirements[$requiredPackage['cdata']] = (array('name' => $requiredPackage['cdata']) + $requiredPackage['attrs']);
					}
					break;

				// read in optionals
				case 'optionalpackages':
					foreach ($child['children'] as $optionalPackage) {
						// reference to optional package is not a valid package identifier
						if (!Package::isValidPackageName($optionalPackage['cdata'])) {
							throw new SystemException("'".$optionalPackage['cdata']."' is not a valid package name.", 13002);
						}
						if (!isset($optionalPackage['attrs']['file']) || !$optionalPackage['attrs']['file']) {
							throw new SystemException("required 'file' attribute for 'optionalPackage' tag is missing in ".self::INFO_FILE, 13001);
						}
						$this->optionals[] = (array('name' => $optionalPackage['cdata']) + $optionalPackage['attrs']);
					}
					break;

				// read in excluded packages
				case 'excludedpackages':
					foreach ($child['children'] as $excludedPackage) {
						// reference to excluded package is not a valid package identifier
						if (!Package::isValidPackageName($excludedPackage['cdata'])) {
							throw new SystemException("'".$excludedPackage['cdata']."' is not a valid package name.", 13002);
						}
						$this->excludedPackages[$excludedPackage['cdata']] = (array('name' => $excludedPackage['cdata']) + $excludedPackage['attrs']);
					}
					break;
					
				// get installation and update instructions
				case 'instructions':
					if ($child['attrs']['type'] == 'update') {
						if (!isset($child['attrs']['fromversion']) || !$child['attrs']['fromversion']) {
							throw new SystemException("required 'fromversion' attribute for 'instructions type=update' tag is missing in ".self::INFO_FILE, 13001);
						}
						
						$this->update[$child['attrs']['fromversion']] = array();
						$processData =& $this->update[$child['attrs']['fromversion']];
					}
					else {
						$this->install = array();
						$processData =& $this->install;
					}
					
					foreach ($child['children'] as $instruction) {
						switch ($instruction['name']) {
							// get links to sql file
							case 'sql':
								$processData['sql'] = $instruction['cdata'];
								break;

							// get links to language files
							case 'languages':
								if (!isset($processData['languages'])) {
									$processData['languages'] = array();
								}
								$processData['languages'][] = array('cdata' => $instruction['cdata']) + $instruction['attrs'];
								break;

							// get links to other (any but not sql) files
							default:
								if (!isset($processData[$instruction['name']])) {
									$processData[$instruction['name']] = array();
								}
								$processData[$instruction['name']][] = array('cdata' => $instruction['cdata']) + $instruction['attrs'];
						}
					}
					
					foreach ($processData as $key => $val) {
						if ($key != 'languages' && is_array($val) && count($val) == 1) {
							$processData[$key] = array_shift($val);
						}
					}
				
				break;
			}
		}
		
		// add com.woltlab.wcf to package requirements
		if (!isset($this->requirements['com.woltlab.wcf']) && $this->packageInfo['name'] != 'com.woltlab.wcf') {
			$this->requirements['com.woltlab.wcf'] = array('name' => 'com.woltlab.wcf');
		}
		
		// examine the right update instruction block
		if ($this->package !== null && $this->update !== null) {
			$validUpdate = null;
			foreach ($this->update as $fromVersion => $update) {
				if (Package::checkFromversion($this->package->getVersion(), $fromVersion)) {
					$validUpdate = $update;
					break;
				}
			}
			$this->update = $validUpdate;
		}
		
		// check required tags
		if (!isset($this->packageInfo['packageName'])) {
			throw new SystemException("required tag 'packageName' is missing in ".self::INFO_FILE, 13001);
		}
		if (!isset($this->packageInfo['version'])) {
			throw new SystemException("required tag 'version' is missing in ".self::INFO_FILE, 13001);
		}
		
		// set default values
		if (!isset($this->packageInfo['isUnique'])) $this->packageInfo['isUnique'] = 0;
		if (!isset($this->packageInfo['standalone'])) $this->packageInfo['standalone'] = 0;
		if (!isset($this->packageInfo['plugin'])) $this->packageInfo['plugin'] = '';
		if (!isset($this->packageInfo['packageURL'])) $this->packageInfo['packageURL'] = '';
		
		// get package name in selected language
		$this->getLocalizedInformation('packageName');
		
		// get package description in selected language
		if (isset($this->packageInfo['packageDescription'])) {
			$this->getLocalizedInformation('packageDescription');
		}
		
		if (CHARSET != 'UTF-8') {
			if (isset($this->authorInfo['author'])) {
				$this->authorInfo['author'] = StringUtil::convertEncoding('UTF-8', CHARSET, $this->authorInfo['author']);
			}
			if (isset($this->authorInfo['authorURL'])) {
				$this->authorInfo['authorURL'] = StringUtil::convertEncoding('UTF-8', CHARSET, $this->authorInfo['authorURL']);
			}
		}
		
		// add plugin to requirements
		if ($this->packageInfo['plugin'] && !isset($this->requirements[$this->packageInfo['plugin']])) {
			$this->requirements[$this->packageInfo['plugin']] = array('name' => $this->packageInfo['plugin']);
		}
	}
	
	/**
	 * Gets localized package information strings.
	 * 
	 * @param 	string		$key
	 */
	protected function getLocalizedInformation($key) {
		if (isset($this->packageInfo[$key][LANGUAGE_CODE])) {
			$this->packageInfo[$key] = $this->packageInfo[$key][LANGUAGE_CODE];
		}
		else if (isset($this->packageInfo[$key]['default'])) {
			$this->packageInfo[$key] = $this->packageInfo[$key]['default'];
		}
		else {
			$this->packageInfo[$key] = array_shift($this->packageInfo[$key]);
		}
		
		// convert utf-8 to charset
		if (CHARSET != 'UTF-8') {
			$this->packageInfo[$key] = StringUtil::convertEncoding('UTF-8', CHARSET, $this->packageInfo[$key]);
		}
	}
	
	/**
	 * Downloads the package archive.
	 * 
	 * @return	string		path to the dowloaded file
	 */
	public function downloadArchive() {
		$parsedUrl = parse_url($this->archive);
		$prefix = 'package';
		
		// file transfer via hypertext transfer protocol.
		if ($parsedUrl['scheme'] == 'http') {
			$this->archive = FileUtil::downloadFileFromHttp($this->archive, $prefix);
		}
		// file transfer via file transfer protocol.
		elseif ($parsedUrl['scheme'] == 'ftp') {
			$this->archive = FTPUtil::downloadFileFromFtp($this->archive, $prefix);
		}
		
		// unzip tar
		$this->archive = self::unzipPackageArchive($this->archive);
		
		return $this->archive;
	}
	
	/**
	 * Closes and deletes the tar archive of this package. 
	 */
	public function deleteArchive() {
		if ($this->tar instanceof Tar) {
			$this->tar->close();
		}
		
		@unlink($this->archive);
	}
	
	/**
	 * Return true, if the package archive supports a new installation.
	 * 
	 * @return	boolean
	 */
	public function isValidInstall() {
		return $this->install !== null;
	}
	
	/**
	 * Checks if the new package is compatible with
	 * the package that is about to be updated.
	 *
	 * @return 	boolean 	isValidUpdate
	 */
	public function isValidUpdate() {
		// Check name of the installed package against the name of the update. Both must be identical.
		if ($this->packageInfo['name'] != $this->package->getPackage()) {
			return false;
		}
		
		// Check if the version number of the installed package is lower than the version number to which
		// it's about to be updated.
		if (Package::compareVersion($this->packageInfo['version'], $this->package->getVersion()) != 1) {
			return false;
		}
		// Check if the package provides an instructions block for the update from the installed package version
		if ($this->update === null) {
			return false;
		}
		return true;
	}
	
	/**
	 * checks if the current package is already installed
	 * 
	 * if the package is not a unique package, this method
	 * returns false, because a non-unique package may be
	 * installed as many times as one wants while a unique 
	 * package can only be installed once.
	 *
	 * @return 	boolean 	isAlreadyInstalled
	 */
	public function isAlreadyInstalled() {
		// is not a unique package and can be
		// installed as many times as you want
		if ($this->packageInfo['isUnique'] == 0) {
			return false;
		}
		// this package may only be installed
		// once (e. g. library package)
		else {
			return (count($this->getDuplicates()) != 0);
		}
	}
	
	/**
	 * Returns a list of all installed instances of this package.
	 * 
	 * @return	array		packages
	 */
	public function getDuplicates() {
		$packages = array();
		$sql = "SELECT	*
			FROM 	wcf".WCF_N."_package 
			WHERE 	package = '".escapeString($this->packageInfo['name'])."'";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[$row['packageID']] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Returns a list of all updatable instances of this package.
	 * 
	 * @return	array		packages
	 */
	public function getUpdatableInstances() {
		$packages = $this->getDuplicates();
		$updatable = array();
		$newVersion = $this->packageInfo['version'];
		
		foreach ($packages as $package) {
			if (Package::compareVersion($newVersion, $package['packageVersion']) == 1) {
				$updatable[$package['packageID']] = $package;
			}
		}
		
		return $updatable;
	}
	
	/**
	 * Returns information about the author of this package archive.
	 * 
	 * @param	string 		$name		name of the requested information
	 * @return	string
	 */
	public function getAuthorInfo($name) {
		if (isset($this->authorInfo[$name])) return $this->authorInfo[$name];
		return null;
	}
	
	/**
	 * Returns information about this package.
	 * 
	 * @param	string 		$name		name of the requested information
	 * @return	mixed
	 */
	public function getPackageInfo($name) {
		if (isset($this->packageInfo[$name])) return $this->packageInfo[$name];
		return null;
	}
	
	/**
	 * Returns a list of all requirements of this package.
	 * 
	 * @return	array
	 */
	public function getRequirements() {
		return $this->requirements;
	}
	
	/**
	 * Returns a list of all delivered optional packages of this package.
	 * 
	 * @return	array
	 */
	public function getOptionals() {
		return $this->optionals;
	}
	
	/**
	 * Returns a list of excluded packages.
	 * 
	 * @return	array
	 */
	public function getExcludedPackages() {
		return $this->excludedPackages;
	}
	
	/**
	 * Returns the package installation instructions.
	 * 
	 * @param	string		$action		installation type (install or update)
	 * @return	array
	 */
	public function getInstructions($action) {
		return $this->{$action};
	}
	
	/**
	 * Checks which package requirements do already exist in right version.
	 * Returns a list with all existing requirements.
	 * 
	 * @return	array
	 */
	public function getAllExistingRequirements() {
		$existingRequirements = array();
		$existingPackages = array();
		if ($this->package !== null) {
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_requirement requirement
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = requirement.requirement)
				WHERE		requirement.packageID = ".$this->package->getPackageID();
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$existingRequirements[$row['package']] = $row;
			}
		}

		// build sql
		$packageNames = '';
		$requirements = $this->getRequirements();
		foreach ($requirements as $requirement) {
			if (isset($existingRequirements[$requirement['name']])) {
				$existingPackages[$requirement['name']] = array();
				$existingPackages[$requirement['name']][$existingRequirements[$requirement['name']]['packageID']] = $existingRequirements[$requirement['name']];
			}
			else {
				if (!empty($packageNames)) $packageNames .= ',';
				$packageNames .= "'".escapeString($requirement['name'])."'";
			}
		}
	
		// check whether the required packages do already exist
		if (!empty($packageNames)) {
			$sql = "SELECT 	package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM	wcf".WCF_N."_package package
				WHERE	package.package IN (".$packageNames.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				// check required package version
				if (isset($requirements[$row['package']]['minversion']) && Package::compareVersion($row['packageVersion'], $requirements[$row['package']]['minversion']) == -1) {
					continue;
				}
				
				if (!isset($existingPackages[$row['package']])) {
					$existingPackages[$row['package']] = array();
				}
				
				$existingPackages[$row['package']][$row['packageID']] = $row;
			}
		}
		
		return $existingPackages;
	}
	
	/**
	 * Checks which package requirements do already exist in database.
	 * Returns a list with the existing requirements.
	 * 
	 * @return	array
	 */
	public function getExistingRequirements() {
		// build sql
		$packageNames = '';
		foreach ($this->requirements as $requirement) {
			if (!empty($packageNames)) $packageNames .= ',';
			$packageNames .= "'".escapeString($requirement['name'])."'";
		}
	
		// check whether the required packages do already exist
		$existingPackages = array();
		if (!empty($packageNames)) {
			$sql = "SELECT 	*
				FROM	wcf".WCF_N."_package
				WHERE	package IN (".$packageNames.")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($existingPackages[$row['package']])) {
					$existingPackages[$row['package']] = array();
				}
				
				$existingPackages[$row['package']][$row['packageVersion']] = $row;
			}
			
			// sort multiple packages by version number
			foreach ($existingPackages as $packageName => $instances) {
				uksort($instances, array('Package', 'compareVersion'));
				
				// get package with highest version number (get last package)
				$existingPackages[$packageName] = array_pop($instances);	
			}
		}
		
		return $existingPackages;
	}
	
	/**
	 * Returns a list of all open requirements of this package.
	 * 
	 * @return	array
	 */
	public function getOpenRequirements() {
		// get all existing requirements
		$existingPackages = $this->getExistingRequirements();
		
		// check for open requirements
		$openRequirements = array();
		foreach ($this->requirements as $requirement) {
			if (isset($existingPackages[$requirement['name']])) {
				// package does already exist
				// maybe an update is necessary
				if (!isset($requirement['minversion']) || Package::compareVersion($existingPackages[$requirement['name']]['packageVersion'], $requirement['minversion']) >= 0) {
					// package does already exist in needed version
					// skip installation of requirement 
					continue;
				}
				
				$requirement['packageID'] = $existingPackages[$requirement['name']]['packageID'];
				$requirement['action'] = 'update';
			}
			else {
				// package does not exist
				// new installation is necessary
				$requirement['packageID'] = 0;
				$requirement['action'] = 'install';
			}
			
			$openRequirements[$requirement['name']] = $requirement;
		}
		
		return $openRequirements;
	}
	
	/**
	 * Extracts the requested file in the package archive to the temp folder.
	 * 
	 * @param	string		$filename
	 * @param	string		$tempPrefix
	 * @return	string		path to the extracted file
	 */
	public function extractTar($filename, $tempPrefix = 'package_') {
		// search the requested tar archive in our package archive.
		// throw error message if not found.
		if (($fileIndex = $this->tar->getIndexByFilename($filename)) === false) {
			throw new SystemException("tar archive '".$filename."' not found in '".$this->archive."'.", 13007);
		}
		
		// requested tar archive was found
		$fileInfo = $this->tar->getFileInfo($fileIndex);
		$filename = FileUtil::getTemporaryFilename($tempPrefix, preg_replace('!^.*?(\.(?:tar\.gz|tgz|tar))$!i', '\\1', $fileInfo['filename']));
		$this->tar->extract($fileIndex, $filename);
		
		return $filename;
	}
	
	/**
	 * Unzips compressed package archives.
	 * 
	 * @param 	string		$archive	filename
	 * @return 	string		new filename
	 */
	public static function unzipPackageArchive($archive) {
		if (!FileUtil::isURL($archive)) {
			$tar = new Tar($archive);
			$tar->close();
			if ($tar->isZipped()) {
				$tmpName = FileUtil::getTemporaryFilename('package_');
				if (FileUtil::uncompressFile($archive, $tmpName)) {
					return $tmpName;
				}
			}
		}
		
		return $archive;
	}
	
	/**
	 * Returns a list of packages, which excluding this package.
	 * 
	 * @return	array
	 */
	public function getConflictedExcludingPackages() {
		$conflictedPackages = array();
		$sql = "SELECT		package.*, package_exclusion.*
			FROM		wcf".WCF_N."_package_exclusion package_exclusion
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_exclusion.packageID)	
			WHERE		excludedPackage = '".$this->packageInfo['name']."'";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($row['excludedPackageVersion'])) {
				if (Package::compareVersion($this->packageInfo['version'], $row['excludedPackageVersion'], '<')) {
					continue;
				}
			}
			
			$conflictedPackages[$row['packageID']] = $row;
		}
		
		return $conflictedPackages;
	}
	
	/**
	 * Returns a list of packages, which are excluded by this package.
	 * 
	 * @return	array
	 */
	public function getConflictedExcludedPackages() {
		$conflictedPackages = array();
		if (count($this->excludedPackages) > 0) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				WHERE	package IN ('".implode("','", array_keys($this->excludedPackages))."')";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($this->excludedPackages[$row['package']]['version'])) {
					if (Package::compareVersion($row['packageVersion'], $this->excludedPackages[$row['package']]['version'], '<')) {
						continue;
					}
				}
				
				$conflictedPackages[$row['packageID']] = $row;
			}
		}
		
		return $conflictedPackages;
	}
}
?>