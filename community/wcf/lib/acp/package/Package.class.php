<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/acp/package/PackageInstallation.class.php');
	require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
}

/**
 * This class represents a database entry in wcf package table. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class Package extends DatabaseObject  {
	protected		$packageID, $package, $name, $instanceNo, $description, 
				$version, $date, $url, $parentPackageID, $isUnique, 
				$standalone, $author, $authorURL, $dir;
	protected static 	$dependencyTable;
	
	/**
	 * Creates a new Package object.
	 * 
	 * @param	integer		$packageID
	 * @param	array		$row
	 */
	public function __construct($packageID, $row = null) {
		if ($row === null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_package
				WHERE 	packageID = ".$packageID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		$this->packageID = $packageID;
		parent::__construct($row);
	}
	
	/**
	 * @see DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		if (!isset($data['packageID'])) {
			throw new SystemException("unknown package id '".$this->packageID."'", 13013);
		}
		
		$this->packageID 	= $data['packageID'];
		$this->package 		= $data['package'];
		$this->name 		= $data['packageName'];
		$this->instanceNo 	= $data['instanceNo'];
		$this->description 	= $data['packageDescription'];
		$this->version 		= $data['packageVersion'];
		$this->date 		= $data['packageDate'];
		$this->url 		= $data['packageURL'];
		$this->parentPackageID 	= $data['parentPackageID'];
		$this->isUnique 	= $data['isUnique'];
		$this->standalone 	= $data['standalone'];
		$this->author 		= $data['author'];
		$this->authorURL 	= $data['authorURL'];
		$this->dir		= $data['packageDir'];
		
		parent::handleData($data);
	}
	
	/**
	 * Returns the id of this package.
	 * 
	 * @return	integer
	 */
	public function getPackageID() {
		return $this->packageID;
	}
	
	/**
	 * Returns the identifier of this package.
	 * 
	 * @return 	string
	 */
	public function getPackage() {
		return $this->package;
	}
	
	/**
	 * Returns the name of this package.
	 * 
	 * @return	string
	 */
	public function getName() {
		return ($this->instanceName ? $this->instanceName : $this->name);
	}
	
	/**
	 * Returns the abbreviation of the package name.
	 * 
	 * @return	string
	 */
	public function getAbbreviation() {
		$array = explode('.', $this->package);
		return array_pop($array);
	}
	
	/**
	 * Returns the instance number of this package.
	 * 
	 * @return	integer
	 */
	public function getInstanceNo() {
		return $this->instanceNo;
	}
	
	/**
	 * Returns the description of this package.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns the version number of this package.
	 * 
	 * @return	string
	 */
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 * Returns the date of this package as an unix timestamp.
	 * 
	 * @return	integer
	 */
	public function getDate() {
		return $this->date;
	}
	
	/**
	 * Returns the url of the homepage this package.
	 * 
	 * @return	string
	 */
	public function getURL() {
		return $this->url;
	}
	
	/**
	 * Returns the parent package id of this package.
	 * 
	 * @return	integer
	 */
	public function getParentPackageID() {
		return $this->parentPackageID;
	}
	
	/**
	 * Returns true, if this package is an unique package.
	 * 
	 * @return	boolean
	 */
	public function isUnique() {
		return $this->isUnique;
	}
	
	/**
	 * Returns true, if this package is a standalone application.
	 * 
	 * @return	boolean
	 */
	public function isStandalone() {
		return $this->standalone;
	}
	
	/**
	 * Returns the author of this package.
	 * 
	 * @return	string
	 */
	public function getAuthor() {
		return $this->author;
	}
	
	/**
	 * Returns the homepage of the author of this package.
	 * 
	 * @return	string
	 */
	public function getAuthorURL() {
		return $this->authorURL;
	}
	
	/**
	 * Checks if a package name is valid.
	 * A valid package name begins with at least one alphanumeric character or the underscore,
	 * followed by a dot, followed by at least one alphanumeric character or the underscore, 
	 * and the same again, possibly repeatedly. Example: 'com.woltlab.wcf' (this will be the
	 * official WCF packet naming scheme in the future).
	 * Reminder: The '$packageName' variable being examined here contains the 'name' attribute
	 * of the 'package' tag noted in the 'packages.xml' file delivered inside the respective package.
	 *
	 * @param 	string 		$packageName
	 * @return 	boolean 	isValid
	 */
	public static function isValidPackageName($packageName) {
		return preg_match('%^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$%', $packageName);
	}
	
	/**
	 * Rebuilds the dependencies list for the given package id.
	 * 
	 * @param	integer		$packageID
	 */
	public static function rebuildPackageDependencies($packageID) {
		// delete old dependencies
		$sql = "DELETE FROM	wcf".WCF_N."_package_dependency
			WHERE		packageID = ".$packageID;
		WCF::getDB()->sendQuery($sql);
		
		// get all requirements of this package
		$allRequirements = $packageID;
		$sql = "SELECT	requirement
			FROM	wcf".WCF_N."_package_requirement_map
			WHERE	packageID = ".$packageID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$allRequirements .= ','.$row['requirement'];
		}
		
		// find their plugins
		$requirements = $allRequirements;
		do {
			$sql = "SELECT	DISTINCT requirement
				FROM	wcf".WCF_N."_package_requirement_map
				WHERE	packageID IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package
						WHERE	parentPackageID IN (".$requirements.")
					)
					AND requirement NOT IN (".$allRequirements.")";
			$requirements = '';
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!empty($requirements)) $requirements .= ',';
				$requirements .= $row['requirement'];
				$allRequirements .= ','.$row['requirement'];
			}
		}
		while (!empty($requirements));
		
		// rebuild
		// insert requirements
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_dependency
						(packageID, dependency, priority)
			SELECT			".$packageID.", requirement, level
			FROM 			wcf".WCF_N."_package_requirement_map
			WHERE 			requirement IN (".$allRequirements.")
						AND requirement NOT IN (		-- exclude dependencies to other installations of same package
							SELECT	packageID
							FROM	wcf".WCF_N."_package
							WHERE	package = (
									SELECT	package
									FROM	wcf".WCF_N."_package
									WHERE	packageID = ".$packageID."
								)
								AND packageID <> ".$packageID."
						)
			ORDER BY		level DESC";
		WCF::getDB()->sendQuery($sql);
		
		// insert plugins
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_dependency
						(packageID, dependency, priority)
			SELECT			".$packageID.", packageID, 
						IFNULL((
							SELECT	MAX(level) AS level
							FROM	wcf".WCF_N."_package_requirement_map
							WHERE	packageID = package.packageID
						), -1) + 1 AS level
			FROM 			wcf".WCF_N."_package package
			WHERE 			parentPackageID IN (".$allRequirements.")
			ORDER BY		level DESC";
		WCF::getDB()->sendQuery($sql);
		
		// self insert
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_dependency
						(packageID, dependency, priority)
			SELECT			".$packageID.", ".$packageID.", IFNULL((
							SELECT	MAX(priority) + 1
							FROM	wcf".WCF_N."_package_dependency
							WHERE	packageID = ".$packageID."
						), 0)";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Searches all dependent packages for the given package id
	 * and rebuild their package dependencies list.
	 * 
	 * @param	integer		$packageID
	 */
	public static function rebuildParentPackageDependencies($packageID) {
		$sql = "SELECT		packageID, MAX(priority) AS maxPriority
			FROM		wcf".WCF_N."_package_dependency
			WHERE		packageID IN (
						SELECT	packageID
						FROM	wcf".WCF_N."_package_dependency
						WHERE	dependency = ".$packageID."
							AND packageID <> ".$packageID."
						UNION
						SELECT	parentPackageID
						FROM	wcf".WCF_N."_package
						WHERE	packageID = ".$packageID."
					)
			GROUP BY	packageID
			ORDER BY	maxPriority ASC, packageID DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			self::rebuildPackageDependencies($row['packageID']);
		}
	}
	
	/**
	 * Returns a list of all dependent standalone packages for the given package id.
	 * 
	 * @param	integer		$packageID
	 * @return	array
	 */
	protected static function getParentPackageDependencies($packageID) {
		$standalonePackages = array();
		
		// get parent package
		$sql = "SELECT	parentPackageID
			FROM	wcf".WCF_N."_package
			WHERE	packageID = ".$packageID."
				AND parentPackageID > 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset(self::$dependencyTable[$row['parentPackageID']])) {
				self::$dependencyTable[$row['parentPackageID']] = true;
				$standalonePackages = array_merge($standalonePackages, self::getParentPackageDependencies($row['parentPackageID']));
				$standalonePackages[] = $row['parentPackageID'];
			}
		}
		
		// get dependent packages
		$sql = "SELECT		package_requirement.packageID, standalone
			FROM		wcf".WCF_N."_package_requirement package_requirement
			LEFT JOIN 	wcf".WCF_N."_package USING (packageID)
			WHERE		requirement = ".$packageID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			self::$dependencyTable[$row['packageID']] = true;
			$standalonePackages = array_merge($standalonePackages, self::getParentPackageDependencies($row['packageID']));
			if ($row['standalone']) $standalonePackages[] = $row['packageID'];
		}
		
		return $standalonePackages;
	}
	
	/**
	 * Returns true, if this package is required by other packages.
	 * 
	 * @return	boolean
	 */
	public function isRequired() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package_requirement
			WHERE	requirement = ".$this->packageID;
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Returns a list of the requirements of this package.
	 * Contains the content of the <requiredPackages> tag in the package.xml of this package.
	 * 
	 * @return	array
	 */
	public function getRequiredPackages() {
		try {
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.requirement)
				WHERE		package_requirement.packageID = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
		catch (DatabaseException $e) {
			// horizon update workaround
			$sql = "SELECT		package.*
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.requirement)
				WHERE		package_requirement.packageID = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
			
		$packages = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Returns a list of all packages that require this package.
	 * Returns packages that require this package and packages that require these packages.
	 * 
	 * @return	array
	 */
	public function getDependentPackages() {
		try {
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.packageID)
				WHERE		package_requirement.requirement = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
		catch (DatabaseException $e) {
			// horizon update workaround
			$sql = "SELECT		package.*
				FROM		wcf".WCF_N."_package_requirement package_requirement
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_requirement.packageID)
				WHERE		package_requirement.requirement = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
					
		$packages = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Returns a list of all by this package required packages.
	 * Contains required packages and the requirements of the required packages.
	 * 
	 * @return	array
	 */
	public function getDependencies() {
		try {
			$sql = "SELECT		package.*, CASE WHEN instanceName <> '' THEN instanceName ELSE packageName END AS packageName
				FROM		wcf".WCF_N."_package_dependency package_dependency
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_dependency.dependency)
				WHERE		package_dependency.packageID = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}
		catch (DatabaseException $e) {
			// horizon update workaround
			$sql = "SELECT		package.*
				FROM		wcf".WCF_N."_package_dependency package_dependency
				LEFT JOIN	wcf".WCF_N."_package package ON (package.packageID = package_dependency.dependency)
				WHERE		package_dependency.packageID = ".$this->packageID."
				ORDER BY	packageName";
			$result = WCF::getDB()->sendQuery($sql);
		}	
			
		$packages = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packages[] = $row;
		}
		
		return $packages;
	}
	
	/**
	 * Returns the parent package of this package.
	 * Only plugins have a parent package.
	 * Only a standalone package can be a parent package.
	 * 
	 * @return	Package
	 */
	public function getParentPackage() {
		if (!$this->parentPackageID) {
			return null;
		}
		
		return new Package($this->parentPackageID);
	}
	
	/**
	 * Returns the installation dir of this package.
	 * 
	 * @return	string
	 */
	public function getDir() {
		return $this->dir;
	}
	
	/**
	 * Sets the installation dir of this package.
	 * 
	 * @param	string		$dir
	 */
	public function setDir($dir) {
		$this->dir = $dir;
	}
	
	/**
	 * Set the package version.
	 *  
	 * @param	string		$version
	 */
	public function setVersion($version) {
		$sql = "UPDATE 	wcf".WCF_N."_package 
			SET 	packageVersion = '".escapeString($version)."'
			WHERE 	packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql); 
	}
	
	/**
	 * Writes the config.inc.php for a standalone application.
	 * 
	 * @param	integer		$packageID
	 */
	public static function writeConfigFile($packageID) {
		$package = new Package($packageID);
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$package->getDir()));
		$file = new File($packageDir.PackageInstallation::CONFIG_FILE);
		$file->write("<?php\n");
		$currentPrefix = strtoupper($package->getAbbreviation());
		
		// get dependencies (only standalones)
		$sql = "SELECT		package.*, IF(package.packageID = ".$packageID.", 1, 0) AS sortOrder
			FROM		wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.dependency)
			WHERE		package_dependency.packageID = ".$packageID."
					AND package.standalone = 1
					AND package.packageDir <> ''
			ORDER BY	sortOrder DESC,
					package_dependency.priority DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$dependency = new Package(null, $row);
			$dependencyDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$dependency->getDir()));
			$prefix = strtoupper($dependency->getAbbreviation());
			
			$file->write("// ".$dependency->getPackage()." vars\n");
			$file->write("// ".strtolower($prefix)."\n");
			$file->write("if (!defined('".$prefix."_DIR')) define('".$prefix."_DIR', ".($dependency->getPackageID() == $package->getPackageID() ? "dirname(__FILE__).'/'" : "'".$dependencyDir."'").");\n");
			$file->write("if (!defined('RELATIVE_".$prefix."_DIR')) define('RELATIVE_".$prefix."_DIR', ".($dependency->getPackageID() == $package->getPackageID() ? "''" : "RELATIVE_".$currentPrefix."_DIR.'".FileUtil::getRelativePath($packageDir, $dependencyDir)."'").");\n");
			$file->write("if (!defined('".$prefix."_N')) define('".$prefix."_N', '".WCF_N."_".$dependency->getInstanceNo()."');\n");
			$file->write("\$packageDirs[] = ".$prefix."_DIR;\n");
			$file->write("\n");
		}
		
		// write general information
		$file->write("// general info\n");
		$file->write("if (!defined('RELATIVE_WCF_DIR'))	define('RELATIVE_WCF_DIR', RELATIVE_".$currentPrefix."_DIR.'".FileUtil::getRelativePath($packageDir, WCF_DIR)."');\n");
		$file->write("if (!defined('PACKAGE_ID')) define('PACKAGE_ID', ".$package->getPackageID().");\n");
		$file->write("if (!defined('PACKAGE_NAME')) define('PACKAGE_NAME', '".str_replace("'", "\'", $package->getName())."');\n");
		$file->write("if (!defined('PACKAGE_VERSION')) define('PACKAGE_VERSION', '".$package->getVersion()."');\n");
		
		// write end
		$file->write("?>");
		$file->close();
	}
	
	/* 
	 * Check version number of the installed package against the "fromversion" number of the update.
	 * The "fromversion" number may contain wildcards (asterisks) which means that the update covers 
	 * the whole range of release numbers where the asterisk wildcards digits from 0 to 9. For example,
	 * if "fromversion" is "1.1.*" and this package updates to version 1.2.0, all releases from 1.1.0 to 
	 * 1.1.9 may be updated using this package.
	 */
	public static function checkFromversion($currentVersion, $fromversion) {
		if (StringUtil::indexOf($fromversion, '*') !== false) {
			// from version with wildcard
			// use regular expression
			$fromversion = StringUtil::replace('\*', '.*', preg_quote($fromversion, '!'));
			if (preg_match('!^'.$fromversion.'$!i', $currentVersion)) {
				return true;
			}
		}
		else {
			if (self::compareVersion($currentVersion, $fromversion, '=')) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Compares two version number strings.
	 * 
	 * @see version_compare()
	 */
	public static function compareVersion($version1, $version2, $operator = null) {
		$version1 = self::formatVersionForCompare($version1);
		$version2 = self::formatVersionForCompare($version2);
		if ($operator === null) return version_compare($version1, $version2);
		else return version_compare($version1, $version2, $operator);
	}
	
	/**
	 * Formats a package version string for comparing.
	 * 
	 * @param	string		$version
	 * @return 	string		formatted version
	 * @see 	http://www.php.net/manual/en/function.version-compare.php
	 */
	private static function formatVersionForCompare($version) {
		// remove spaces
		$version = str_replace(' ', '', $version);
		
		// correct special version strings
		$version = str_ireplace('dev', 'dev', $version);
		$version = str_ireplace('alpha', 'alpha', $version);
		$version = str_ireplace('beta', 'beta', $version);
		$version = str_ireplace('RC', 'RC', $version);
		$version = str_ireplace('pl', 'pl', $version);
		
		return $version;
	}
	
	/**
	 * Rebuilds the requirement map for the given package id.
	 * 
	 * @param	integer		$packageID
	 */
	public static function rebuildPackageRequirementMap($packageID) {
		// delete old entries
		$sql = "DELETE FROM	wcf".WCF_N."_package_requirement_map
			WHERE		packageID = ".$packageID;
		WCF::getDB()->sendQuery($sql);

		// import requirements of requirements
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_requirement_map
						(packageID, requirement, level)
			SELECT			".$packageID.", requirement, level
			FROM			wcf".WCF_N."_package_requirement_map
			WHERE			packageID IN (
							SELECT	requirement
							FROM	wcf".WCF_N."_package_requirement
							WHERE	packageID = ".$packageID."
						)
			ORDER BY		level DESC";
		WCF::getDB()->sendQuery($sql);
		
		// import requirements
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_requirement_map
						(packageID, requirement, level)
			SELECT			".$packageID.", requirement,
						IFNULL((
							SELECT	MAX(level) AS level
							FROM	wcf".WCF_N."_package_requirement_map
							WHERE	packageID = package_requirement.requirement
						), -1) + 1 AS level 
			FROM			wcf".WCF_N."_package_requirement package_requirement
			WHERE			packageID = ".$packageID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>