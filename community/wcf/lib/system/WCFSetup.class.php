<?php
// define
define('PACKAGE_ID', '0');
define('HTTP_CONTENT_TYPE_XHTML', 0);
define('HTTP_ENABLE_NO_CACHE_HEADERS', 0);
define('HTTP_ENABLE_GZIP', 0);
define('HTTP_GZIP_LEVEL', 0);
define('CACHE_SOURCE_TYPE', 'disk');
define('ENABLE_SESSION_DATA_CACHE', 0);
define('MODULE_MASTER_PASSWORD', 1);

// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/MySQLDatabase.class.php');
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
	require_once(WCF_DIR.'lib/system/database/QueryParser.class.php');
	require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
	require_once(WCF_DIR.'lib/system/language/SetupLanguage.class.php');
	require_once(WCF_DIR.'lib/system/template/SetupTemplate.class.php');
	require_once(WCF_DIR.'lib/system/cache/SetupCacheHandler.class.php');
	require_once(WCF_DIR.'lib/system/setup/FileInstaller.class.php');
	require_once(WCF_DIR.'lib/system/setup/FTPInstaller.class.php');
}

/**
 * WCFSetup executes the installation of the basic wcf systems.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCFSetup extends WCF {
	protected static $availableLanguages = array();
	protected static $selectedLanguageCode = 'en';
	protected static $selectedLanguages = array();
	protected static $wcfDir = '';
	protected static $charset = 'UTF-8';
	protected static $ftpConnection = null;
	protected static $installedFiles = array();
	protected static $setupPackageName = 'WoltLab Community Framework';
	protected static $developerMode = 0;
	
	/**
	 * Calls all init functions of the WCFSetup class and starts the setup process. 
	 */
	public function __construct() {
		@set_time_limit(0);
		$this->initMagicQuotes();
		$this->getDeveloperMode();
		$this->getCharacterEncoding();
		$this->getLanguageSelection();
		$this->getWCFDir();
		$this->initLanguage();
		$this->initTPL();
		self::getLanguage()->loadLanguage();
		$this->initCache();
		$this->getPackageName();
		
		// start setup
		$this->setup();
	}
	
	/**
	 * Gets the status of the developer mode.
	 */
	protected static function getDeveloperMode() {
		if (isset($_GET['dev'])) self::$developerMode = intval($_GET['dev']);
		else if (isset($_POST['dev'])) self::$developerMode = intval($_POST['dev']);
	}
	
	/**
	 * Determines the correct character encoding.
	 */
	protected static function getCharacterEncoding() {
		// get selected encoding
		if (isset($_REQUEST['charset'])) {
			self::$charset = $_REQUEST['charset'];
		}
		
		// check for valid encoding
		switch (self::$charset) {
			case 'UTF-8': 
				// check for multi-byte extension
				if (extension_loaded('mbstring')) {
					break;
				}
			default: 
				//if (function_exists('iconv')) {
				if (extension_loaded('mbstring')) {	
					if (isset(Language::$supportedCharsets[self::$charset])) {
						if (!Language::$supportedCharsets[self::$charset]['multibyte'] || extension_loaded('mbstring')) {
							break;
						}
					}
					
					// find preferred single-byte language
					self::$availableLanguages = self::getAllLanguages();
					self::$selectedLanguageCode = Language::getPreferredLanguage(self::$availableLanguages, self::$selectedLanguageCode);
					foreach (Language::$supportedCharsets as $charset => $data) {
						if (!$data['multibyte'] && in_array(self::$selectedLanguageCode, $data['languages'])) {
							self::$charset = $charset;
							break 2;
						}
					}
				}
				
				// default
				self::$charset = 'ISO-8859-1';
		}
	}
	
	/**
	 * Gets the selected language.
	 */
	protected static function getLanguageSelection() {
		self::$availableLanguages = self::getAllLanguages();
		if (self::$charset != 'UTF-8') {
			self::$availableLanguages = array_intersect(self::$availableLanguages, Language::$supportedCharsets[self::$charset]['languages']);
		}
		
		if (isset($_REQUEST['languageCode']) && in_array($_REQUEST['languageCode'], self::$availableLanguages)) {
			self::$selectedLanguageCode = $_REQUEST['languageCode'];
		}
		else {
			self::$selectedLanguageCode = Language::getPreferredLanguage(self::$availableLanguages, self::$selectedLanguageCode);
		}
		
		if (isset($_POST['selectedLanguages']) && is_array($_POST['selectedLanguages'])) {
			self::$selectedLanguages = $_POST['selectedLanguages'];
		}
	}
	
	/**
	 * Gets the selected wcf dir from request.
	 */
	protected static function getWCFDir() {
		if (isset($_REQUEST['wcfDir']) && $_REQUEST['wcfDir'] != '') {
			self::$wcfDir = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($_REQUEST['wcfDir']));
			if (@file_exists(self::$wcfDir)) {
				define('RELATIVE_WCF_DIR', FileUtil::getRelativePath(INSTALL_SCRIPT_DIR, self::$wcfDir));
			}
		}
		
		define('WCF_DIR', self::$wcfDir);
	}
		
	/**
	 * Initialises the language engine.
	 */
	protected function initLanguage() {
		self::$languageObj = new SetupLanguage(self::$selectedLanguageCode, self::$charset);
	}
	
	/**
	 * Initialises the cache handler and loads the default cache resources.
	 */
	protected function initCache() {
		self::$cacheObj = new SetupCacheHandler();
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = new SetupTemplate(array_search(self::$selectedLanguageCode, self::$availableLanguages) + (self::$charset == 'UTF-8' ? 1000 : 2000), TMP_DIR, TMP_DIR, TMP_DIR);
		self::getTPL()->registerPrefilter('lang');
		self::getTPL()->assign(array(
			'tmpFilePrefix' => TMP_FILE_PREFIX,
			'languageCode' => self::$selectedLanguageCode,
			'selectedLanguages' => self::$selectedLanguages,
			'wcfDir' => self::$wcfDir,
			'developerMode' => self::$developerMode
		));
	}
	
	/**
	 * Returns all languages from WCFSetup.tar.gz.
	 * 
	 * @return	array
	 */
	protected static function getAllLanguages() {
		$languages = $match = array();
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if (strpos($file['filename'], 'setup/lang/') === 0 && substr($file['filename'], -4) == '.xml' && preg_match('!^setup_([a-z]{2}(?:-[A-Za-z0-9]+)?(?:_[A-Za-z]{2})?)$!', basename($file['filename'], '.xml'), $match)) {
				$languages[] = $match[1];
			}
		}
		$tar->close();

		// sort languages by language code
		sort($languages);

		return $languages;
	}
	
	/**
	 * Calculates the current state of the progress bar.
	 * 
	 * @param	integer		$currentStep
	 */
	protected function calcProgress($currentStep) {
		// count delivered packages
		$packages = 1; // one for wcf setup
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && StringUtil::indexOf($file['filename'], 'install/packages/') === 0) {
				$packages++;
			}
		}
		$tar->close();
		
		// calculate part of total install
		$part = 100 / $packages;
		
		// calculate progress
		$progress = round($part / 11 * $currentStep, 0);
		self::getTPL()->assign('progress', $progress);
	}
	
	/**
	 * Executes the setup steps.
	 */
	protected function setup() {
		// get current step
		if (isset($_REQUEST['step'])) $step = $_REQUEST['step'];
		else $step = 'selectSetupLanguage';

		// execute current step
		switch ($step) {
			case 'selectSetupLanguage':
				if (!self::$developerMode) {
					$this->calcProgress(0);
					$this->selectSetupLanguage();
					break;
				}
				
			case 'showLicense':
				if (!self::$developerMode) {
					$this->calcProgress(1);
					$this->showLicense();
					break;
				}
				
			case 'showSystemRequirements':
				if (!self::$developerMode) {
					$this->calcProgress(2);
					$this->showSystemRequirements();
					break;
				}
				
			case 'searchWcfDir':
				$this->calcProgress(3);
				$this->searchWcfDir();
				break;
				
			case 'unzipFiles':
				$this->calcProgress(4);
				$this->unzipFiles();
				break;
				
			case 'selectLanguages':
				$this->calcProgress(5);
				$this->selectLanguages();
				break;
				
			case 'configureDB':
				$this->calcProgress(6);
				$this->configureDB();
				break;
				
			case 'createDB':
				$this->calcProgress(7);
				$this->createDB();
				break;
				
			case 'logFiles':
				$this->calcProgress(8);
				$this->logFiles();
				break;
				
			case 'installLanguage':
				$this->calcProgress(9);
				$this->installLanguage();
				break;
				
			case 'createUser':
				$this->calcProgress(10);
				$this->createUser();
				break;
				
			case 'installPackages':
				$this->calcProgress(11);
				$this->installPackages();
				break;
		}
	}
	
	/**
	 * Shows the first setup page.
	 */
	protected function selectSetupLanguage() {
		// build language list
		$languages = array();
		foreach (self::$availableLanguages as $languageCode) {
			$languages[$languageCode] = self::getLanguage()->get('wcf.global.language.'.$languageCode).' ('.$languageCode.')';
		}
		
		// sort languages
		StringUtil::sort($languages);
		
		WCF::getTPL()->assign('availableLanguages', $languages);
		WCF::getTPL()->assign('nextStep', 'showLicense');
		WCF::getTPL()->display('stepSelectSetupLanguage');		
	}
	
	/**
	 * Shows the license agreement.
	 */
	protected function showLicense() {
		if (isset($_POST['send'])) {
			if (isset($_POST['accepted'])) {
				$this->gotoNextStep('showSystemRequirements');
				exit;
			}
			else {
				WCF::getTPL()->assign('missingAcception', true);
			}
		
		}
		
		if (file_exists(TMP_DIR.TMP_FILE_PREFIX.'license_'.self::$selectedLanguageCode.'.txt')) {
			$license = file_get_contents(TMP_DIR.TMP_FILE_PREFIX.'license_'.self::$selectedLanguageCode.'.txt');
		}
		else {
			$license = file_get_contents(TMP_DIR.TMP_FILE_PREFIX.'license_en.txt');
		}
		
		if (self::$charset != 'UTF-8') $license = StringUtil::convertEncoding('UTF-8', self::$charset, $license);
		WCF::getTPL()->assign('license', $license);
		WCF::getTPL()->assign('nextStep', 'showLicense');
		WCF::getTPL()->display('stepShowLicense');
	}
	
	/**
	 * Shows the system requirements.
	 */
	protected function showSystemRequirements() {
		$system = array();
		
		// php version
		$system['phpVersion']['value'] = phpversion();
		$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['phpVersion']['value']);
		$system['phpVersion']['result'] = (version_compare($comparePhpVersion, '5.0.0') >= 0);
		$system['phpVersion']['result2'] = (version_compare($comparePhpVersion, '5.0.5') >= 0);
		
		// mysql version
		//$system['mySQLVersion']['value'] = function_exists('mysql_get_client_info') ? mysql_get_client_info() : '0.0.0';
		//$compareMySQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['mySQLVersion']['value']);
		$system['mySQLVersion']['result'] = function_exists('mysql_connect') || class_exists('mysqli', false);// (version_compare($compareMySQLVersion, '4.1.2') >= 0);

		// upload_max_filesize
		$system['uploadMaxFilesize']['value'] = ini_get('upload_max_filesize');
		$system['uploadMaxFilesize']['result'] = (intval($system['uploadMaxFilesize']['value']) > 0);
		
		// gdlib version
		$system['gdLib']['value'] = '0.0.0';
		if (function_exists('gd_info')) {
			$temp = gd_info();
			$match = array();
			if (preg_match('!([0-9]+\.[0-9]+(?:\.[0-9]+)?)!', $temp['GD Version'], $match)) {
				if (preg_match('/^[0-9]+\.[0-9]+$/', $match[1])) $match[1] .= '.0';
				$system['gdLib']['value'] = $match[1];
			}
		}
		$system['gdLib']['result'] = (version_compare($system['gdLib']['value'], '2.0.0') >= 0);
		
		// mb string
		$system['mbString']['result'] = extension_loaded('mbstring');
		
		// safe mode
		$system['safeMode']['result'] = (FileUtil::getSafeMode() != 1);
		
		// ftp
		$system['ftp']['result'] = extension_loaded('ftp');
		
		WCF::getTPL()->assign('system', $system);
		WCF::getTPL()->assign('nextStep', 'searchWcfDir');
		WCF::getTPL()->display('stepShowSystemRequirements');
	}
	
	/**
	 * Searches the wcf dir.
	 */
	protected function searchWcfDir() {
		$foundDirectory = '';
		if (self::$wcfDir) {
			$wcfDir = self::$wcfDir;
		}
		else {
			if ($foundDirectory = FileUtil::scanFolder(INSTALL_SCRIPT_DIR, "WCF.class.php", true)) {
				$foundDirectory = $wcfDir = FileUtil::unifyDirSeperator(dirname(dirname(dirname($foundDirectory))).'/');
			}
			else {
				$wcfDir = FileUtil::unifyDirSeperator(INSTALL_SCRIPT_DIR).'wcf/';
			}
		}
		
		// domain
		$domainName = '';
		if (!empty($_SERVER['SERVER_NAME'])) $domainName = 'http://' . $_SERVER['SERVER_NAME'];
		// port
		if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) $domainName .= ':' . $_SERVER['SERVER_PORT'];
		// script url
		$installScriptUrl = '';
		if (!empty($_SERVER['REQUEST_URI'])) $installScriptUrl = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash(FileUtil::unifyDirSeperator(dirname($_SERVER['REQUEST_URI']))));
		
		WCF::getTPL()->assign(array(
			'nextStep' => 'unzipFiles',
			'foundDirectory' => $foundDirectory,
			'wcfDir' => $wcfDir,
			'domainName' => $domainName,
			'installScriptUrl' => $installScriptUrl,
			'installScriptDir' => FileUtil::unifyDirSeperator(INSTALL_SCRIPT_DIR)
		));
		
		WCF::getTPL()->display('stepSearchWcfDir');
	}
	
	/**
	 * Unzips the files of the wcfsetup tar archive.
	 */
	protected function unzipFiles() {
		// WCF seems to be installed, skip installation of
		// files, database and admin account
		// and go directly to the installation of packages
		if (@is_file(self::$wcfDir.'lib/system/WCF.class.php')) {
			$this->gotoNextStep('installPackages');
			exit;
		}
		// WCF not yet installed, install files first
		else {
			$this->promptFtp();
			
			try {
				$this->installFiles();
			}
			catch (Exception $e) {
				WCF::getTPL()->assign('exception', $e);
				$this->searchWcfDir();
				return;
			}
			
			$this->gotoNextStep('selectLanguages');
		}
	}
	
	/**
	 * Shows the page for choosing the installed languages.
	 */
	protected function selectLanguages() {
		$errorField = $errorType = '';
		$allLanguages = $this->getAllLanguages();
		$illegalLanguages = array();
		
		// build visible language list
		$languages = array();
		foreach ($allLanguages as $languageCode) {
			$languages[$languageCode] = self::getLanguage()->get('wcf.global.language.'.$languageCode).' ('.$languageCode.')';
		}
		
		// skip step in developer mode
		// select all available languages automatically
		if (self::$developerMode) {
			self::$selectedLanguages = array();
			foreach ($allLanguages as $languageCode) {
				if (Language::isSupported($languageCode)) {
					self::$selectedLanguages[] = $languageCode;
				}
			}
			
			self::getTPL()->assign('selectedLanguages', self::$selectedLanguages);
			$this->gotoNextStep('configureDB');
			exit;
		}
		
		// sort languages
		StringUtil::sort($languages);
		
		// start error handling
		if (isset($_POST['send'])) {
			try {
				// no languages selected
				if (count(self::$selectedLanguages) == 0) {
					throw new UserInputException('selectedLanguages');
				}
				
				// illegal selection
				// language is not available in the active charset
				foreach (self::$selectedLanguages as $language) {
					if (!Language::isSupported($language)) {
						$illegalLanguages[] = $language;
					}
				}
				
				if (count($illegalLanguages) > 0) {
					throw new UserInputException('selectedLanguages', 'notAvailable');
				}
				
				// no errors
				// go to next step
				$this->gotoNextStep('configureDB');
				exit;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		else {
			self::$selectedLanguages[] = self::$selectedLanguageCode;
			WCF::getTPL()->assign('selectedLanguages', self::$selectedLanguages);
		}
		
		WCF::getTPL()->assign(array(
			'illegalLanguages' => $illegalLanguages,
			'errorField' => $errorField,
			'errorType' => $errorType,
			'charsets' => Language::$supportedCharsets,
			'languages' => $languages,
			'disableMultiByte' => !extension_loaded('mbstring'),
			//'iconvNotAvailable' => !function_exists('iconv'),
			'nextStep' => 'selectLanguages'
		));
		WCF::getTPL()->display('stepSelectLanguages');
	}
	
	/**
	 * Shows the page for configurating the database connection.
	 */
	protected function configureDB() {
		$dbHost = 'localhost';
		$dbUser = 'root';
		$dbPassword = '';
		$dbName = 'wcf';
		$dbNumber = '1';
		$dbClass = 'MySQLDatabase';
		if (!function_exists('mysql_connect')) $dbClass = 'MySQLiDatabase';
		$overwriteTables = false;
		
		if (isset($_POST['send'])) {
			if (isset($_POST['dbHost'])) $dbHost = $_POST['dbHost'];
			if (isset($_POST['dbUser'])) $dbUser = $_POST['dbUser'];
			if (isset($_POST['dbPassword'])) $dbPassword = $_POST['dbPassword'];
			if (isset($_POST['dbName'])) $dbName = $_POST['dbName'];
			if (isset($_POST['overwriteTables'])) $overwriteTables = intval($_POST['overwriteTables']);
			//Should the user not be prompted if converted or default n match an
			//existing installation number? By now the existing installation
			//will be overwritten just so!
			if (isset($_POST['dbNumber'])) $dbNumber = intval($_POST['dbNumber']);
			
			// test connection
			try {
				$db = new $dbClass($dbHost, $dbUser, $dbPassword, $dbName, Database::$dbCharsets[self::$charset], false, false);
				
				// check mysql version
				$mySQLVersion = $db->getVersion();
				$compareMySQLVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $mySQLVersion);
				if (!(version_compare($compareMySQLVersion, '4.1.2') >= 0)) {
					throw new SystemException("Insufficient MySQL version '".$mySQLVersion."'. MySQL 4.1.2 or greater is needed.");
				}
				
				// check for table conflicts
				$conflictedTables = $this->getConflictedTables($db, $dbNumber);
				
				// write config.inc
				if (empty($conflictedTables) || $overwriteTables || self::$developerMode) {
					// connection successfully established
					// write configuration to config.inc.php
					$file = new File(WCF_DIR.'config.inc.php');
					$file->write("<?php\n");
					$file->write("\$dbHost = '".StringUtil::replace("'", "\\'", $dbHost)."';\n");
					$file->write("\$dbUser = '".StringUtil::replace("'", "\\'", $dbUser)."';\n");
					$file->write("\$dbPassword = '".StringUtil::replace("'", "\\'", $dbPassword)."';\n");
					$file->write("\$dbName = '".StringUtil::replace("'", "\\'", $dbName)."';\n");
					$file->write("\$dbCharset = '".StringUtil::replace("'", "\\'", Database::$dbCharsets[self::$charset])."';\n");
					$file->write("\$dbClass = '".StringUtil::replace("'", "\\'", $dbClass)."';\n");
					$file->write("if (!defined('WCF_N')) define('WCF_N', $dbNumber);\n?>");
					$file->close();
				
					// go to next step
					$this->gotoNextStep('createDB');
					exit;
				}
				// show configure temnplate again
				else { 
					WCF::getTPL()->assign('conflictedTables', $conflictedTables);
				}
			}
			catch (SystemException $e) {
				WCF::getTPL()->assign('exception', $e);
			}
		}
		WCF::getTPL()->assign(array(
			'dbHost' => $dbHost,
			'dbUser' => $dbUser,
			'dbPassword' => $dbPassword,
			'dbName' => $dbName,
			'dbNumber' => $dbNumber,
			'nextStep' => 'configureDB'
		));
		WCF::getTPL()->display('stepConfigureDB');
	}
	
	
	/**
	 * Checks if in the chosen database are tables in conflict with the wcf tables
	 * which will be created in the next step.
	 * 
	 * @param	Database	$db
	 * @param 	integer		$dbNumber
	 */
	protected function getConflictedTables($db, $dbNumber) {
		
		// get content of the sql structure file
		$sql = file_get_contents(TMP_DIR.TMP_FILE_PREFIX.'mysql.sql');
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = StringUtil::replace('wcf1_', 'wcf'.$dbNumber.'_', $sql);
		
		// get all tablenames which should be created
		preg_match_all("%CREATE\s+TABLE\s+(\w+)%", $sql, $matches);
		
		// get all installed tables from chosen database
		$existingTables = $db->getTableNames();
		
		// check if existing tables are in conflict with wcf tables
		$conflictedTables = array();
		foreach ($existingTables as $existingTableName) {
			foreach ($matches[1] as $wcfTableName) {
				if ($existingTableName == $wcfTableName) {
					$conflictedTables[] = $wcfTableName;
				}
			}	
		}
		return $conflictedTables;
	}
	
	/**
	 * Creates the database structure of the wcf.
	 */
	protected function createDB() {
		$this->initDB();
		
		// get content of the sql structure file
		$sql = file_get_contents(TMP_DIR.TMP_FILE_PREFIX.'mysql.sql');
		
		// installation number value 'n' (WCF_N) must be reflected in the executed sql queries
		$sql = StringUtil::replace('wcf1_', 'wcf'.WCF_N.'_', $sql);
		
		// replace charset configuration
		if (Database::$dbCharsets[self::$charset] != 'utf8') {
			$sql = StringUtil::replace('DEFAULT CHARSET=utf8', 'DEFAULT CHARSET='.Database::$dbCharsets[self::$charset], $sql);
		}
		
		// execute sql queries
		$tables = QueryParser::sendQueries($sql);
		
		// log sql queries
		foreach ($tables as $tableName) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(sqlTable)
				VALUES		('".escapeString($tableName)."')";
			self::getDB()->sendQuery($sql);
		}
		
		$this->gotoNextStep('logFiles');
	}
	
	/**
	 * Logs the unzipped files.
	 */
	protected function logFiles() {
		$this->initDB();
		
		$this->getInstalledFiles(WCF_DIR);
		$acpTemplateInserts = $fileInserts = ''; 
		foreach (self::$installedFiles as $file) {
			$match = array();
			if (preg_match('!/acp/templates/([^/]+)\.tpl$!', $file, $match)) {
				// acp template
				if (!empty($acpTemplateInserts)) $acpTemplateInserts .= ',';
				$acpTemplateInserts .= "('".escapeString($match[1])."')";
			}
			else {
				// regular file
				if (!empty($fileInserts)) $fileInserts .= ',';
				$fileInserts .= "('".escapeString(StringUtil::replace(WCF_DIR, '', $file))."')";
			}
		}
		
		// save acp template log
		if (!empty($acpTemplateInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_acp_template
							(templateName)
				VALUES			".$acpTemplateInserts;
			self::getDB()->sendQuery($sql);
		}
		
		// save file log
		if (!empty($fileInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_installation_file_log
							(filename)
				VALUES			".$fileInserts;
			self::getDB()->sendQuery($sql);
		}
		
		$this->gotoNextStep('installLanguage');
	}
	
	/**
	 * Scans the given dir for installed files.
	 * 
	 * @param 	string		$dir
	 */
	protected function getInstalledFiles($dir) {
		if ($files = glob($dir.'*')) {
			foreach ($files as $file) {
				if (is_dir($file)) {
					$this->getInstalledFiles(FileUtil::addTrailingSlash($file));
				}
				else {
					self::$installedFiles[] = FileUtil::unifyDirSeperator($file);
				}
			}
		}
	}
	
	/**
	 * Installs the selected languages.
	 */
	protected function installLanguage() {
		$this->initDB();
		
		foreach (self::$selectedLanguages as $language) {
			// get language.xml file name
			$filename = TMP_DIR.TMP_FILE_PREFIX.$language.'.xml';
			
			// check the file
			if (!file_exists($filename)) {
				throw new SystemException("unable to find language file '".$filename."'", 11002);
			}
			
			// open the file
			$xml = new XML($filename);
			
			// import xml
			LanguageEditor::importFromXML($xml, 0);
		}
		
		// set default language
		$language = LanguageEditor::getLanguageByCode(in_array(self::$selectedLanguageCode, self::$selectedLanguages) ? self::$selectedLanguageCode : self::$selectedLanguages[0]);
		$language->makeDefault();
		
		// assign all languages to package id 0
		$sql = "INSERT INTO	wcf".WCF_N."_language_to_packages
					(languageID, packageID)
			SELECT 		languageID, 0
			FROM		wcf".WCF_N."_language";
		WCF::getDB()->sendQuery($sql);
		
		// rebuild language cache
		WCF::getCache()->clearResource('languages');
		
		// go to next step
		$this->gotoNextStep('createUser');
	}
	
	/**
	 * Shows the page for creating the admin account.
	 */
	protected function createUser() {
		$errorType = $errorField = $username = $email = $confirmEmail = $password = $confirmPassword = '';
		
		$username = '';
		$email = $confirmEmail = '';
		$password = $confirmPassword = '';
		
		if (isset($_POST['send']) || self::$developerMode) {
			if (isset($_POST['send'])) {
				if (isset($_POST['username'])) 		$username = StringUtil::trim($_POST['username']);
				if (isset($_POST['email'])) 		$email = StringUtil::trim($_POST['email']);
				if (isset($_POST['confirmEmail'])) 	$confirmEmail = StringUtil::trim($_POST['confirmEmail']);
				if (isset($_POST['password'])) 		$password = $_POST['password'];
				if (isset($_POST['confirmPassword'])) 	$confirmPassword = $_POST['confirmPassword'];
			}
			else {
				$username = $password = $confirmPassword = 'root';
				$email = $confirmEmail = 'woltlab@woltlab.com';
			}
			
			// error handling
			try {
				// username
				if (empty($username)) {
					throw new UserInputException('username');
				}
				if (!UserUtil::isValidUsername($username)) {
					throw new UserInputException('username', 'notValid');
				}
				
				// e-mail address
				if (empty($email)) {
					throw new UserInputException('email');
				}
				if (!UserUtil::isValidEmail($email)) {
					throw new UserInputException('email', 'notValid');
				}
				
				// confirm e-mail address
				if ($email != $confirmEmail) {
					throw new UserInputException('confirmEmail', 'notEqual');
				}
				
				// password
				if (empty($password)) {
					throw new UserInputException('password');
				}
				
				// confirm e-mail address
				if ($password != $confirmPassword) {
					throw new UserInputException('confirmPassword', 'notEqual');
				}
				
				// no errors
				// init database connection
				$this->initDB();
				
				// get language id
				$languageID = 0;
				$sql = "SELECT	languageID
					FROM	wcf".WCF_N."_language
					WHERE	languageCode = '".escapeString(self::$selectedLanguageCode)."'";
				$row = self::getDB()->getFirstRow($sql);
				if (isset($row['languageID'])) $languageID = $row['languageID'];
				
				// create user
				$user = UserEditor::create($username, $email, $password, array(1, 3, 4), array(), array('languageID' => $languageID), array(), false);
				
				// go to next step
				$this->gotoNextStep('installPackages');
				exit;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		
		WCF::getTPL()->assign(array(
			'errorField' => $errorField,
			'errorType' => $errorType,
			'username' => $username,
			'email' => $email,
			'confirmEmail' => $confirmEmail,
			'password' => $password,
			'confirmPassword' => $confirmPassword,
			'nextStep' => 'createUser'
		));
		WCF::getTPL()->display('stepCreateUser');
	}
	
	/**
	 * Registers with wcf setup delivered packages in the package installation queue.
	 */
	protected function installPackages() {
		// init database connection
		$this->initDB();
		
		// enable regular cache
		self::$cacheObj = new CacheHandler();
		
		// get admin account
		//$admin = User::getNewest();
		$admin = new UserSession(1);
		
		// get delivered packages
		$wcfPackageFile = '';
		$otherPackages = array();
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && StringUtil::indexOf($file['filename'], 'install/packages/') === 0) {
				$packageFile = basename($file['filename']);
				$packageName = preg_replace('!\.(tar\.gz|tgz|tar)$!', '', $packageFile);
				
				if ($packageName == 'com.woltlab.wcf') {
					$wcfPackageFile = $packageFile;
				}
				else {
					$isStrato = false;//(!empty($_SERVER['DOCUMENT_ROOT']) && (strpos($_SERVER['DOCUMENT_ROOT'], 'strato') !== false));
					if (!$isStrato && preg_match('!\.(tar\.gz|tgz)$!', $packageFile)) {
						// try to unzip zipped package files
						if (FileUtil::uncompressFile(TMP_DIR.TMP_FILE_PREFIX.$packageFile, TMP_DIR.TMP_FILE_PREFIX.$packageName.'.tar')) {
							@unlink(TMP_DIR.TMP_FILE_PREFIX.$packageFile);
							$packageFile = $packageName.'.tar';
						}
					}
					
					$otherPackages[$packageName] = $packageFile;
				}
			}
		}
		$tar->close();
		
		// register packages in queue
		// get new process id
		$sql = "SELECT	MAX(processNo) AS processNo
			FROM	wcf".WCF_N."_package_installation_queue";
		$result = self::getDB()->getFirstRow($sql);
		$processID = intval($result['processNo']) + 1;
		$insertSQL = '';
		
		// search existing wcf package
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_package
			WHERE	package = 'com.woltlab.wcf'";
		$row = self::getDB()->getFirstRow($sql);
		if (!$row['count']) {
			if (empty($wcfPackageFile)) {
				throw new SystemException('the essential package com.woltlab.wcf is missing.', 11007);
			}
			
			// register virtual wcfsetup package for the calculation of the progress bar during the package installation
			$insertSQL = "(".$processID.", ".$admin->userID.", 'com.woltlab.wcfsetup', '', 0, 1, 'setup')";
			// register essential wcf package
			$insertSQL .= ",(".$processID.", ".$admin->userID.", 'com.woltlab.wcf', '".escapeString(TMP_DIR.TMP_FILE_PREFIX.$wcfPackageFile)."', 0, 0, 'setup')";
		}
		
		// register all other delivered packages
		asort($otherPackages);
		foreach ($otherPackages as $packageName => $packageFile) {
			if (!empty($insertSQL)) $insertSQL .= ',';
			$insertSQL .= "(".$processID.", ".$admin->userID.", '".escapeString($packageName)."', '".escapeString(TMP_DIR.TMP_FILE_PREFIX.$packageFile)."', 1, 0, 'setup')";
		}
		
		if (!empty($insertSQL)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_queue
						(processNo, userID, package, archive, cancelable, done, installationType)
				VALUES		".$insertSQL;
			self::getDB()->sendQuery($sql);
		}
		
		// login as admin
		$factory = new SessionFactory();
		$session = $factory->create();
		$session->changeUser($admin);
		$session->register('masterPassword', 1);
		$session->update();
		self::getDB()->sendShutdownUpdates();
		
		// TODO: print message if delete fails
		$installPhpDeleted = @unlink('./install.php');
		$testPhpDeleted = @unlink('./test.php');
		$wcfSetupTarDeleted = @unlink('./WCFSetup.tar.gz');
		
		// print page
		WCF::getTPL()->assign(array(
			'installPhpDeleted' => $installPhpDeleted,
			'wcfSetupTarDeleted' => $wcfSetupTarDeleted 
		));
		WCF::getTPL()->display('stepInstallPackages');
		
		// delete tmp files
		if (($files = glob(TMP_DIR.TMP_FILE_PREFIX.'*')) !== false) {
			foreach ($files as $file) {
				if (!preg_match("%.*\.tar(\.gz)?$%", $file)) @unlink($file);
			}
		}
	}
	
	/**
	 * Goes to the next step.
	 * 
	 * @param	string		$nextStep
	 */
	protected function gotoNextStep($nextStep) {
		WCF::getTPL()->assign('nextStep', $nextStep);
		WCF::getTPL()->display('stepNext');
	}
	
	/**
	 * Prompts for ftp access data.
	 */
	protected function promptFtp() {
		// safe mode is not active or ftp extension is not loaded
		// skip ftp prompting
		if ((FileUtil::getSafeMode() != 1) || !extension_loaded('ftp')) {
			return;
		}
		
		// get username and password
		if (isset($_POST['ftpHost'])) $ftpHost = $_POST['ftpHost'];
		else $ftpHost = 'localhost';
		if (isset($_POST['ftpUser'])) $ftpUser = $_POST['ftpUser'];
		else $ftpUser = '';
		if (isset($_POST['ftpPassword'])) $ftpPassword = $_POST['ftpPassword'];
		else $ftpPassword = '';
		
		$error = false;
		if (!empty($ftpUser)) {
			try {
				// create new ftp connection
				self::$ftpConnection = new FTP($ftpHost);
				self::$ftpConnection->login($ftpUser, $ftpPassword);
				return;
			}
			catch (SystemException $e) {
				$error = true; 
			}
		}
		
		WCF::getTPL()->assign('ftpPassword', $ftpPassword);
		WCF::getTPL()->assign('ftpUser', $ftpUser);
		WCF::getTPL()->assign('ftpHost', $ftpHost);
		WCF::getTPL()->assign('error', $error);
		WCF::getTPL()->assign('nextStep', 'unzipFiles');
		WCF::getTPL()->display('stepPromptFtp');
		exit;
	}
	
	/**
	 * Installs the files of the tar archive.
	 */
	protected static function installFiles() {
		$folder = 'install/files/';
		
		if (self::$ftpConnection !== null) {
			new FTPInstaller(self::$wcfDir, SETUP_FILE, self::$ftpConnection, null, $folder);
		}
		else {
			new FileInstaller(self::$wcfDir, SETUP_FILE, null, $folder);
		}
	}
	
	/**
	 * Gets the package name of the first standalone application in WCFSetup.tar.gz.
	 */
	protected static function getPackageName() {
		// get package name
		$tar = new Tar(SETUP_FILE);
		foreach ($tar->getContentList() as $file) {
			if ($file['type'] != 'folder' && StringUtil::indexOf($file['filename'], 'install/packages/') === 0) {
				$packageFile = basename($file['filename']);
				$packageName = preg_replace('!\.(tar\.gz|tgz|tar)$!', '', $packageFile);
				
				if ($packageName != 'com.woltlab.wcf') {
					try {
						$archive = new PackageArchive(TMP_DIR.TMP_FILE_PREFIX.$packageFile);
						$archive->openArchive();
						self::$setupPackageName = $archive->getPackageInfo('packageName');
						$archive->getTar()->close();
						break;
					}
					catch (SystemException $e) {}
				}
			}
		}
		$tar->close();
		
		// assign package name
		WCF::getTPL()->assign('setupPackageName', self::$setupPackageName);
	}
}
?>