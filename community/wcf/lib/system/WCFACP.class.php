<?php
// wcf imports
require_once(WCF_DIR.'lib/system/WCF.class.php');
require_once(WCF_DIR.'lib/acp/page/util/menu/ACPMenu.class.php');
require_once(WCF_DIR.'lib/system/template/ACPTemplate.class.php');

/**
 * Extends WCF class with functions for the admin control panel.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system
 * @category 	Community Framework
 */
class WCFACP extends WCF {
	protected static $menuObj;
	
	/**
	 * Calls all init functions of the WCF and the WCFACP class. 
	 */
	public function __construct() {
		@set_time_limit(0);
		if (!defined('TMP_DIR')) define('TMP_DIR', BasicFileUtil::getTempFolder());
		$this->initBenchmark();
		$this->initMagicQuotes();
		$this->initDB();
		$this->initPackage();
		$this->initOptions();
		$this->initCache();
		$this->initSession();
		$this->initLanguage();
		$this->initTPL();
		$this->initMenu();
		$this->initBlacklist();
		$this->initAuth();
	}
	
	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		if ((!isset($_REQUEST['page']) || ($_REQUEST['page'] != 'Logout' && $_REQUEST['page'] != 'ACPCaptcha')) && (isset($_REQUEST['page']) || !isset($_REQUEST['form']) || $_REQUEST['form'] != 'Login')) {
			if (WCF::getUser()->userID == 0) {
				HeaderUtil::redirect('index.php?form=Login&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
				exit;
			}
			else {
				WCF::getUser()->checkPermission('admin.general.canUseAcp');
			}
		}
	}
	
	/**
	 * Starts the session system.
	 */
	protected function initSession() {
		require_once(WCF_DIR.'lib/system/session/SessionFactory.class.php');
		$factory = new SessionFactory();
		self::$sessionObj = $factory->get();
		
		// check if the user changed to another package in the ACP
		if (self::getSession()->packageID != PACKAGE_ID) {
			self::getSession()->updateUserData();
		}
		
		self::$userObj = self::getSession()->getUser();
	}
	
	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		self::$tplObj = new ACPTemplate(self::getLanguage()->getLanguageID());
		$this->assignDefaultTemplateVariables();
	}
	
	/**
	 * @see WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();
		
		self::getTPL()->assign('quickAccessPackages', $this->getQuickAccessPackages());
		self::getTPL()->assign('timezone', DateUtil::getTimezone());
	}
	
	/**
	 * Initialises the acp menu.
	 */
	protected function initMenu() {
		self::$menuObj = new ACPMenu();
		self::getTPL()->assign('menu', self::getMenu());
	}
	
	/**
	 * @see WCF::loadDefaultCacheResources()
	 */
	protected function loadDefaultCacheResources() {
		parent::loadDefaultCacheResources();
		self::getCache()->addResource('packages', WCF_DIR.'cache/cache.packages.php', WCF_DIR.'lib/system/cache/CacheBuilderPackages.class.php');
	}
	
	/**
	 * Initialises the active package.
	 */
	protected function initPackage() {
		// define active package id
		if (!defined('PACKAGE_ID')) {
			$packageID = self::getWcfPackageID();
			define('PACKAGE_ID', $packageID);
		}
		
		/*
		$packageID = 0;
		$packages = WCF::getCache()->get('packages');
		if (isset($_REQUEST['packageID'])) $packageID = intval($_REQUEST['packageID']);
		
		if (!isset($packages[$packageID]) || !$packages[$packageID]['standalone']) {
			// package id is invalid
			$packageID = self::getWcfPackageID();
		}
		
		// define active package id
		if (!defined('PACKAGE_ID')) define('PACKAGE_ID', $packageID);*/ 
	}
	
	/**
	 * Returns the package id of the wcf package.
	 * 
	 * @return	integer
	 */
	public static final function getWcfPackageID() {
		$packageID = 0;
		// try to find package wcf id
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_package
			WHERE	package = 'com.woltlab.wcf'";
		$package = WCF::getDB()->getFirstRow($sql);
		if (isset($package['packageID'])) {
			$packageID = $package['packageID'];
		}
		
		return $packageID;
	}
	
	/**
	 * Return the acp menu object.
	 * 
	 * @return	ACPMenu
	 */
	public static final function getMenu() {
		return self::$menuObj;		
	}
	
	/**
	 * Returns a list of all installed standalone packages.
	 * 
	 * @return	array
	 */
	protected function getQuickAccessPackages() {
		$quickAccessPackages = array();
		$packages = WCF::getCache()->get('packages');
		foreach ($packages as $packageID => $package) {
			if (!$package['standalone']) break;
			if ($package['package'] != 'com.woltlab.wcf') {
				$quickAccessPackages[] = $package;
			}
		}
		
		return $quickAccessPackages;
	}
	
	/**
	 * Checks whether the active user has entered the valid master password.
	 */
	public static function checkMasterPassword() {
		if (defined('MODULE_MASTER_PASSWORD') && MODULE_MASTER_PASSWORD == 1 && !WCF::getSession()->getVar('masterPassword')) {
			if (file_exists(WCF_DIR.'acp/masterPassword.inc.php')) {
				require_once(WCF_DIR.'acp/masterPassword.inc.php');
			}
			if (defined('MASTER_PASSWORD') && defined('MASTER_PASSWORD_SALT')) {
				require_once(WCF_DIR.'lib/acp/form/MasterPasswordForm.class.php');
				new MasterPasswordForm();
				exit;
			}
			else {
				require_once(WCF_DIR.'lib/acp/form/MasterPasswordInitForm.class.php');
				new MasterPasswordInitForm();
				exit;
			}
		}
	}
}
?>