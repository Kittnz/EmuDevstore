<?php
// define current wcf version
define('WCF_VERSION', '1.1.10 (Tempest)');

// define current unix timestamp
define('TIME_NOW', time());

// define constants
define('IS_APACHE_MODULE', function_exists('apache_get_version'));

// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/cache/CacheHandler.class.php');
	require_once(WCF_DIR.'lib/system/language/Language.class.php');
	require_once(WCF_DIR.'lib/system/template/Template.class.php');
	require_once(WCF_DIR.'lib/system/benchmark/Benchmark.class.php');
	require_once(WCF_DIR.'lib/core.functions.php');
}

/**
 * WCF is the central class for the community framework.
 * It holds the database connection, access to template and language engine.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCF {
	/**
	 * database object
	 * 
	 * @var Database
	 */
	protected static $dbObj;
	
	/**
	 * cache handler object
	 *
	 * @var CacheHandler
	 */
	protected static $cacheObj;
	
	/**
	 * session object..
	 *
	 * @var Session
	 */
	protected static $sessionObj;
	
	/**
	 * current user object
	 *
	 * @var UserSession
	 */
	protected static $userObj;
	
	/**
	 * language object
	 *
	 * @var Language
	 */
	protected static $languageObj;
	
	/**
	 * template object
	 *
	 * @var Template
	 */
	protected static $tplObj;
	
	/**
	 * benchmark object
	 *
	 * @var Benchmark
	 */
	protected static $benchmarkObj;
	
	/**
	 * cache of package dependencies
	 * 
	 * @var	array
	 */
	protected static $packageDependencyCache = null;
	
	/**
	 * Calls all init functions of the WCF class. 
	 */
	public function __construct() {
		if (!defined('TMP_DIR')) define('TMP_DIR', BasicFileUtil::getTempFolder());
		$this->initBenchmark();
		$this->initMagicQuotes();
		$this->initDB();
		$this->initOptions();
		$this->initCache();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initBlacklist();
	}
	
	/**
	 * Replacement of the "__destruct()" method. 
	 * Seems that under specific conditions (windows) the destructor is not called automatically.
	 * So we use the php register_shutdown_function to register an own destructor method. 
	 * Flushs the output, updates the session and executes the shutdown queries.
	 */
	public static function destruct() {
		// flush ouput
		if (ob_get_level() && ini_get('output_handler')) {
			ob_flush();
		}
		else {
			flush();
		}
		
		// update session
		if (is_object(self::getSession())) {
			self::getSession()->update();
		}
		
		// close cache source
		if (is_object(self::getCache()) && is_object(self::getCache()->getCacheSource())) {
			self::getCache()->getCacheSource()->close();
		}
		
		// send shutdown queries
		if (is_object(self::getDB())) {
			self::getDB()->sendShutdownUpdates();
		}
	}
	
	/**
	 * Initialises the benchmark system.
	 */
	protected function initBenchmark() {
		self::$benchmarkObj = new Benchmark();
	}
	
	/**
	 * Returns the benchmark object.
	 * 
	 * @return	Benchmark
	 */
	public static final function getBenchmark() {
		return self::$benchmarkObj;
	}
	
	/**
	 * Removes slashes in superglobal gpc data arrays if 'magic quotes gpc' is enabled.
	 */
	protected function initMagicQuotes() {
		if (function_exists('get_magic_quotes_gpc')) {
			if (@get_magic_quotes_gpc()) {
				if (count($_REQUEST)) {
					$_REQUEST = ArrayUtil::stripslashes($_REQUEST);
				}
				if (count($_POST)) {
					$_POST = ArrayUtil::stripslashes($_POST);
				}
				if (count($_GET)) {
					$_GET = ArrayUtil::stripslashes($_GET);
				}
				if (count($_COOKIE)) {
					$_COOKIE = ArrayUtil::stripslashes($_COOKIE);
				}
				if (count($_FILES)) {
					foreach ($_FILES as $name => $attributes) {
						foreach ($attributes as $key => $value) {
							if ($key != 'tmp_name') {
								$_FILES[$name][$key] = ArrayUtil::stripslashes($value);
							}
						}
					}
				}
			}
		}
	
		if (function_exists('set_magic_quotes_runtime')) {
			@set_magic_quotes_runtime(0);
		}
	}
	
	/**
	 * Returns the database object.
	 * 
	 * @return	Database
	 */
	public static final function getDB() {
		return self::$dbObj;		
	}
	
	/**
	 * Returns the cache handler object.
	 * 
	 * @return	CacheHandler
	 */
	public static final function getCache() {
		return self::$cacheObj;		
	}
	
	/**
	 * Returns the session object.
	 * 
	 * @return	Session
	 */
	public static final function getSession() {
		return self::$sessionObj;
	}
	
	/**
	 * Returns the user object.
	 * 
	 * @return	UserSession
	 */
	public static final function getUser() {
		return self::$userObj;
	}
	
	/**
	 * Returns the language object.
	 * 
	 * @return 	Language
	 */
	public static final function getLanguage() {
		return self::$languageObj;
	}
	
	/**
	 * Returns the template object.
	 * 
	 * @return	Template
	 */
	public static final function getTPL() {
		return self::$tplObj;
	}
	
	/**
	 * Returns the active request object.
	 *
	 * @return	RequestHandler
	 */
	public static final function getRequest() {
		return RequestHandler::getActiveRequest();
	}
	
	/**
	 * Calls the show method on the given exception.
	 * 
	 * @param	Exception	$e	
	 */
	public static final function handleException(Exception $e) {
		if ($e instanceof PrintableException) {
			$e->show();
			exit;
		}
		
		print $e;
	}
	
	/**
	 * Catches php errors and throws instead a system exception.
	 * 
	 * @param	integer		$errorNo
	 * @param	string		$message
	 * @param	string		$filename
	 * @param	integer		$lineNo
	 */
	public static final function handleError($errorNo, $message, $filename, $lineNo) { 
		if (error_reporting() != 0) {
			$type = 'error';
			switch ($errorNo) {
				case 2: $type = 'warning';
					break;
				case 8: $type = 'notice';
					break;
			}
			
			throw new SystemException('PHP '.$type.' in file '.$filename.' ('.$lineNo.'): '.$message, 0);
		}
	}
	
	/**
	 * Loads the database configuration and creates a new connection to the database.
	 */
	protected function initDB() {
		// get configuration
		$dbHost = $dbUser = $dbPassword = $dbName = $dbCharset = '';
		$dbClass = 'MySQLDatabase';
		require_once(WCF_DIR.'config.inc.php');
		
		// create database connection
		require_once(WCF_DIR.'lib/system/database/'.$dbClass.'.class.php');
		self::$dbObj = new $dbClass($dbHost, $dbUser, $dbPassword, $dbName, $dbCharset);
	}
	
	/**
	 * Initialises the cache handler and loads the default cache resources.
	 */
	protected function initCache() {
		self::$cacheObj = new CacheHandler();
		$this->loadDefaultCacheResources();
	}
	
	/**
	 * Loads the default cache resources.
	 */
	protected function loadDefaultCacheResources() {
		self::getCache()->addResource('languages', WCF_DIR.'cache/cache.languages.php', WCF_DIR.'lib/system/cache/CacheBuilderLanguages.class.php');
		self::getCache()->addResource('spiders', WCF_DIR.'cache/cache.spiders.php', WCF_DIR.'lib/system/cache/CacheBuilderSpiders.class.php');
		if (defined('PACKAGE_ID')) {
			self::getCache()->addResource('packageDependencies-'.PACKAGE_ID, WCF_DIR.'cache/cache.packageDependencies-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderPackageDependencies.class.php');
		}
	}
	
	/**
	 * Includes the options file.
	 * If the option file doesn't exist, the rebuild of it is started.
	 */
	protected function initOptions() {
		// get options file name
		$optionsFile = $this->getOptionsFilename();
		
		// create options file if doesn't exist
		if (!file_exists($optionsFile) || filemtime($optionsFile) <= 1) {
			require_once(WCF_DIR.'lib/acp/option/Options.class.php');
			Options::rebuildFile($optionsFile);
		}
		require_once($optionsFile);
	}
	
	/**
	 * Returns the name of the options file.
	 * 
	 * @return	string		name of the options file
	 */
	protected function getOptionsFilename() {
		return WCF_DIR.'options.inc.php';
	}
	
	/**
	 * Starts the session system.
	 */
	protected function initSession() {
		if (!defined('NO_IMPORTS')) require_once(WCF_DIR.'lib/system/session/CookieSessionFactory.class.php');
		$factory = new CookieSessionFactory();
		self::$sessionObj = $factory->get();
		self::$userObj = self::getSession()->getUser();
	}
	
	/**
	 * Initialises the language engine.
	 */
	protected function initLanguage() {
		if (isset($_GET['l']) && !self::getUser()->userID) {
			self::getSession()->setLanguageID(intval($_GET['l']));
		}
		
		self::$languageObj = new Language(self::getSession()->getLanguageID());
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = new Template(self::getLanguage()->getLanguageID());
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * Executes the blacklist.
	 */
	protected function initBlacklist() {
		if (defined('BLACKLIST_IP_ADDRESSES') && BLACKLIST_IP_ADDRESSES != '') {
			if (!StringUtil::executeWordFilter(WCF::getSession()->ipAddress, BLACKLIST_IP_ADDRESSES)) {
				throw new PermissionDeniedException();
			}
		}
		if (defined('BLACKLIST_USER_AGENTS') && BLACKLIST_USER_AGENTS != '') {
			if (!StringUtil::executeWordFilter(WCF::getSession()->userAgent, BLACKLIST_USER_AGENTS)) {
				throw new PermissionDeniedException();
			}
		}
		if (defined('BLACKLIST_HOSTNAMES') && BLACKLIST_HOSTNAMES != '') {
			if (!StringUtil::executeWordFilter(@gethostbyaddr(WCF::getSession()->ipAddress), BLACKLIST_HOSTNAMES)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Assigns some default variables to the template engine.
	 */
	protected function assignDefaultTemplateVariables() {
		self::getTPL()->registerPrefilter('lang');
		self::getTPL()->assign('this', $this);
	}
	
	/**
	 * Wrapper for the getter methods of this class.
	 * 
	 * @param	string		$name
	 * @return	mixed		value
	 */
	public function __get($name) {
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		
		throw new SystemException("method '".$method."' does not exist in class WCF");
	}
	
	/**
	 * Changes the active language.
	 * 
	 * @param	integer		$languageID
	 */
	public static final function setLanguage($languageID) {
		self::$languageObj = new Language($languageID);
	}
	
	/**
	 * Returns the id of a specific package in the active dependencies.
	 * 
	 * @param	string		$package	package identifier
	 * @return	mixed
	 */
	public static final function getPackageID($package) {
		if (defined('PACKAGE_ID')) {
			if (self::$packageDependencyCache === null) {
				self::$packageDependencyCache = self::getCache()->get('packageDependencies-'.PACKAGE_ID);
			}
			
			if (isset(self::$packageDependencyCache[$package])) {
				return self::$packageDependencyCache[$package];
			}
		}
		return null;
	}
}
?>