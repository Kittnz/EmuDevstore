<?php
/**
 * PackageInstallationQueue represents a database entry in the package_installation_queue table.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageInstallationQueue {
	/**
	 * id of queue entry
	 *
	 * @var integer
	 */
	protected $queueID;
	
	/**
	 * action
	 *
	 * @var string
	 */
	protected $action;
	
	/**
	 * true, if action is cancelable
	 *
	 * @var boolean
	 */
	protected $cancelable;
	
	/**
	 * true, if the user has to confirm the action
	 *
	 * @var boolean
	 */
	protected $confirmInstallation;
	
	/**
	 * id of parent queue entry
	 *
	 * @var integer
	 */
	protected $parentQueueID;
	
	/**
	 * id of package
	 *
	 * @var integer
	 */
	protected $packageID;
	
	/**
	 * package object
	 *
	 * @var Package
	 */
	protected $package;
	
	/**
	 * process number
	 *
	 * @var integer
	 */
	protected $processNo;
	
	/**
	 * type of package
	 *
	 * @var string
	 */
	protected $packageType;
	protected $done;
	protected $nextStep = '';
	public $step = '';
	protected $stepAfterPIP = '';
	protected $pipSortOrder = 'DESC';
	
	/**
	 * Creates a new PackageInstallationQueue object.
	 * 
	 * @param	integer		$queueID
	 */
	public function __construct($queueID) {
		// disable gzip during package installations
		if (!defined('HTTP_DISABLE_GZIP')) define('HTTP_DISABLE_GZIP', 1);
		
		// init
		$this->queueID = $queueID;
		$this->getInstallationInfo();
		$this->assignQueueInfo();
	}
	
	/**
	 * Returns an object of the package installation plugin with the given name.
	 * 
	 * @param	string					$pluginName
	 * @return	PackageInstallationPlugin
	 */
	protected function getPackageInstallationPlugin($pluginName) {
		$classFile = WCF_DIR.'lib/acp/package/plugin/'.$pluginName.'.class.php';
		if (!file_exists($classFile)) {
			throw new SystemException('Unable to find file '.$classFile, 11002);
		}
		require_once($classFile);
		return new $pluginName($this);
	}
	
	/**
	 * Loads all registered needed package installation plugins.
	 */
	protected function loadPackageInstallationPlugins() {
		$sql = "SELECT		pluginName, 
					CASE WHEN pluginName = 'FilesPackageInstallationPlugin' THEN 2
					ELSE 
						CASE WHEN pluginName = 'SqlPackageInstallationPlugin' THEN 1
						ELSE	0
						END
					END AS priority2
			FROM 		wcf".WCF_N."_package_installation_plugin
			ORDER BY	priority2 ".$this->pipSortOrder.",
					priority ".$this->pipSortOrder.",
					pluginName";
		$result = WCF::getDB()->sendQuery($sql);
		$neededPlugins = array();
		$functionName = 'has'.ucfirst($this->action);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$plugin = $this->getPackageInstallationPlugin($row['pluginName']);
			if ($plugin->{$functionName}($this)) {
				$neededPlugins[] = $row['pluginName'];
			}
		}
		
		WCF::getSession()->register('queueID'.$this->queueID.'PIPs', $neededPlugins);
	}
	
	/**
	 * executes a package installation plugin
	 *
	 * @param 	string 		$pluginName
	 */
	protected function executePackageInstallationPlugin($pluginName) {
		$plugin = $this->getPackageInstallationPlugin($pluginName);
		$plugin->{$this->action}();
	}
	
	/**
	 * Returns the name of the next step.
	 * 
	 * @param	string		$currentStep
	 * @return	string		$nextStep
	 */
	public function getNextPackageInstallationPlugin($currentStep) {
		$plugins = WCF::getSession()->getVar('queueID'.$this->queueID.'PIPs');
		if (!is_array($plugins)) {
			throw new SystemException('can not find needed package installation plugins', 13010);
		}
		
		$index = 0;
		if (!empty($currentStep)) {
			$index = array_search($currentStep, $plugins);
			if ($index === false) {
				throw new SystemException($currentStep.' is not a valid package installation plugin', 13009);
			}
			$index++;
		}
		
		if (isset($plugins[$index])) {
			return $plugins[$index];
		}
		else {
			// no more plugins
			// proceed regular setup
			return $this->stepAfterPIP;
		}
	}
	
	/**
	 * Loads all information about this installation.
	 * 
	 * @return 	array
	 */
	protected function getInstallationInfo() {
		// get step
		if (isset($_REQUEST['step'])) {
			$this->step = $_REQUEST['step'];
		}
		
		// get package installation information
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_queue
			LEFT JOIN	wcf".WCF_N."_package USING (packageID)
			WHERE		queueID = ".$this->queueID."
					AND userID = ".WCF::getUser()->userID;
		$info = WCF::getDB()->getFirstRow($sql);
		if (!isset($info['queueID'])) {
			throw new IllegalLinkException();
		}
		
		$this->action = $info['action'];
		$this->cancelable = $info['cancelable'];
		$this->confirmInstallation = $info['confirmInstallation'];
		$this->parentQueueID = $info['parentQueueID'];
		$this->packageID = $info['packageID'];
		$this->processNo = $info['processNo'];
		$this->packageType = $info['packageType'];
		$this->done = $info['done'];
		
		return $info;
	}
	
	/**
	 * Opens the package installation queue and 
	 * starts the installation, update or uninstallation of the first entry.
	 * 
	 * @param	integer		$parentQueueID
	 * @param 	integer		$processNo
	 */
	public static function openQueue($parentQueueID = 0, $processNo = 0) {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_queue
			WHERE		userID = ".WCF::getUser()->userID."
					AND parentQueueID = ".$parentQueueID."
					".($processNo != 0 ? "AND processNo = ".$processNo : "")."
					AND done = 0
			ORDER BY	queueID";
		$packageInstallation = WCF::getDB()->getFirstRow($sql);
		if (!isset($packageInstallation['queueID'])) {
			HeaderUtil::redirect('index.php?page=PackageList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
		else {
			HeaderUtil::redirect('index.php?page=Package&action='.$packageInstallation['action'].'&queueID='.$packageInstallation['queueID'].'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
	
	/**
	 * Returns the package id of this package installation.
	 * 
	 * @return	integer
	 */
	public function getPackageID() {
		return $this->packageID;
	}
	
	/**
	 * Returns the current installation action.
	 * 
	 * @return	string
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * Calculates the current state of the progress bar.
	 * 
	 * @param	integer		$currentStep
	 */
	protected function calcProgress($currentStep) {
		// count number of packages
		$sql = "SELECT	done
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE	parentQueueID = 0
				AND processNo = ".$this->processNo;
		$result = WCF::getDB()->sendQuery($sql);
		$packages = WCF::getDB()->countRows($result);
		$donePackages = 0;
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['done'] == 1) $donePackages++;
		}
		
		if ($this->done) {
			$donePackages--;
		}
		
		// calculate part of total install
		$part = 100 / $packages;
		
		// calculate progress
		$totalSteps = $this->getTotalStep();
		$currentStep = $this->getCurrentStep($currentStep);
		$progress = round($part / $totalSteps * $currentStep + $part * $donePackages, 0);
		WCF::getTPL()->assign('progress', $progress);
	}
	
	/**
	 * Returns the number of all installation steps.
	 * 
	 * @return	integer
	 */
	protected function getTotalStep() {
		return 7;
	}
	
	/**
	 * Returns the number of the current installation step.
	 * 
	 * @param 	integer		$currentStep
	 * @return 	integer
	 */
	protected function getCurrentStep($currentStep) {
		return $currentStep;
	}
	
	/**
	 * Assigns the information of this queue entry in the template engine.
	 */
	protected function assignQueueInfo() {
		WCF::getTPL()->assign(array(
			'action' => $this->action,
			'queueID' => $this->queueID,
			'step' => $this->step,
			'cancelable' => $this->cancelable
		));
	}
	
	/**
	 * Assigns the information of this package in the template engine.
	 */
	protected function assignPackageInfo() {}
	
	/**
	 * Returns a new process number for package installation queue.
	 * 
	 * @return	integer
	 */
	public static function getNewProcessNo() {
		$sql = "SELECT	MAX(processNo) AS processNo
			FROM	wcf".WCF_N."_package_installation_queue";
		$result = WCF::getDB()->getFirstRow($sql);
		return intval($result['processNo']) + 1;
	}
	
	/**
	 * Returns the active package of this installation.
	 * 
	 * @return	Package
	 */
	public function getPackage() {
		return $this->package;
	}
	
	/**
	 * Checks if PHP's safe_mode is enabled, and if so, cares for ftp access.
	 * 
	 * @return	resource	$ftp
	 */
	public function checkSafeMode() {
		if ((FileUtil::getSafeMode() == 1) && function_exists('ftp_connect')) {
			// has this form already been submitted?
			if (isset($_POST['send']) && !empty($_POST['send'])) $send = $_POST['send'];
			else $send = false;
			$ftpHost = '';
			$ftpUser = '';
			$ftpPassword = '';
			$errorField = '';
			$errorType = '';
			
			try {
				if ($send) {
					// get ftp hostname, username and password from POST data, if available
					if (isset($_POST['ftpHost']) && !empty($_POST['ftpHost'])) {
						$ftpHost = $_POST['ftpHost'];
						WCF::getSession()->register('ftpHost', $ftpHost);
					}
					if (isset($_POST['ftpUser']) && !empty($_POST['ftpUser'])) {
						$ftpUser = $_POST['ftpUser'];
						WCF::getSession()->register('ftpUser', $ftpUser);
					}
					if (isset($_POST['ftpPassword']) && !empty($_POST['ftpPassword'])) {
						$ftpPassword = $_POST['ftpPassword'];
						WCF::getSession()->register('ftpPassword', $ftpPassword);
					}
				}
				// else try to read them from session variables; if still not available,
				// mark the respective field as being erroneous.
				if (empty($ftpHost)) {
					$ftpHost = WCF::getSession()->getVar('ftpHost');
					if (empty($ftpHost) && $send == true) throw new UserInputException('ftpHost');
				}
				if (empty($ftpUser)) {
					$ftpUser = WCF::getSession()->getVar('ftpUser');
					if (empty($ftpUser) && $send == true) throw new UserInputException('ftpUser');
				}
				if (empty($ftpPassword)) {
					$ftpPassword = WCF::getSession()->getVar('ftpPassword');
				}
				if (!empty($ftpHost) && !empty($ftpUser)) {
					// open ftp connection.
					try {
						$ftp = FTPUtil::initFtpAccess($ftpHost, $ftpUser, $ftpPassword);
						return $ftp;
					}
					catch (SystemException $e) {
						$errCode = $e->getCode();
						switch ($errCode) {
							case 14000:
								throw new UserInputException('ftpHost', 'cannotConnect');
							case 14002:
								throw new UserInputException('ftpUser', 'cannotLogin');
						}
					}
				}
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
				// go back to the prompt and tell the user that something went wrong.
				FTPUtil::promptFtpAccess($ftpHost, $ftpUser, $ftpPassword, $errorField, $errorType);
			}
			
			// if ftp username and password are not available, prompt the user.
			if (empty($ftpUser) && empty($ftpPassword)) {
				FTPUtil::promptFtpAccess();
			}
			
		} else {
			return null;
		}
	}
	
	/**
	 * Displays a system exception in the paackageinstallation exception template.
	 * 
	 * @param 	SystemException		$e
	 */
	protected function showPackageInstallationException(SystemException $e) {
		$dbException = false;
		$sqlError = '';
		$sqlErrorNumber = 0;
		$sqlVersion = '';
			
		if ($e instanceof DatabaseException) {
			$dbException = true;
			$sqlError = $e->getErrorDesc();
			$sqlErrorNumber = $e->getErrorNumber();
			$sqlVersion = $e->getSQLVersion();
		}
		
		WCF::getTPL()->assign(array(
			'dbException' 		=> $dbException,
			'sqlError' 		=> $sqlError,
			'sqlErrorNumber' 	=> $sqlErrorNumber,
			'sqlVersion' 		=> $sqlVersion,
			'errorMessage' 		=> $e->getMessage(),
			'errorDescription' 	=> $e->getDescription(),
			'phpVersion' 		=> phpversion(),
			'wcfVersion' 		=> WCF_VERSION,
			'file' 			=> $e->getFile().' ('.$e->getLine().')',
			'errorCode' 		=> $e->getCode(),
			'date' 			=> gmdate('m/d/Y h:ia'),
			'requestUri' 		=> isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
			'httpReferer' 		=> isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
			'stackTrace' 		=> $e->getTraceAsString(),
		));
		WCF::getTPL()->append(array(
			'packageName' 		=> ''
		));
		
		WCF::getTPL()->display('packageInstallationException');
		exit;
	}
	
	/**
	 * Checks the package installation queue for outstanding entries.
	 */
	public static function checkPackageInstallationQueue() {
		$sql = "SELECT	COUNT(*) as packages
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE 	userID = ".WCF::getUser()->userID."
				AND parentQueueID = 0
				AND done = 0";
		$queue = WCF::getDB()->getFirstRow($sql);
		if ($queue['packages'] > 0) {
			HeaderUtil::redirect('index.php?page=Package&action=openQueue&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
	
	/**
	 * Clears relevant caches after package installation, update or uninstallation.
	 */
	protected function makeClear() {
		require_once(WCF_DIR.'lib/acp/option/Options.class.php');
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

		// get dependent (standalone) packages
		$sql = "SELECT		package.packageID, packageDir
			FROM		wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.packageID)
			WHERE		package_dependency.dependency = ".$this->packageID."
					AND package.standalone = 1";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// delete relevant language files
			LanguageEditor::deleteLanguageFiles('*', '*', $row['packageID']);
			
			if (!empty($row['packageDir'])) {
				// get real path
				$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$row['packageDir']));
				
				// reset options.inc.php
				$filename = $packageDir.Options::FILENAME;
				if (file_exists($filename)) {
					@unlink($filename);
				}
				
				// clear application cache
				WCF::getCache()->clear($packageDir.'cache', '*.php', true);
			}
		}
		
		// clear general options file
		@unlink(WCF_DIR.'options.inc.php');
		
		// clear general cache
		WCF::getCache()->clear(WCF_DIR.'cache', '*.php', true);
		
		// delete compiled templates
		require_once(WCF_DIR.'lib/system/template/ACPTemplate.class.php');
		ACPTemplate::deleteCompiledACPTemplates();
		Template::deleteCompiledTemplates();
	}
}
?>