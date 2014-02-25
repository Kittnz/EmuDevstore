<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/FilesFileHandler.class.php');

/**
 * This PIP installs, updates or deletes by a package delivered files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class FilesPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'files';
	public $tableName = 'package_installation_file_log';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		$dir = $this->installation->getPackage()->getDir();
		if (empty($dir)) {
			if ($this->installation->getPackage()->getParentPackageID() > 0) {
				// plugin
				// use parents package dir
				$dir = $this->installation->getPackage()->getParentPackage()->getDir();
			}
			else if ($this->installation->getPackage()->isStandalone() == 1 && $this->installation->getPackage()->getPackage() != 'com.woltlab.wcf' && $this->installation->getAction() == 'install') {
				// standalone package
				// prompt package dir
				$dir = $this->promptPackageDir();
			}
			
			// save package dir
			if (!empty($dir)) {
				$sql = "UPDATE	wcf".WCF_N."_package
					SET	packageDir = '".escapeString($dir)."'
					WHERE	packageID = ".$this->installation->getPackageID();
				WCF::getDB()->sendQuery($sql);
				$this->installation->getPackage()->setDir($dir);
			}
		}
		
		// absolute path to package dir
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$dir));
		
		// extract files.tar to temp folder
		$tag = $this->installation->getXMLTag('files');
		$sourceFile = $this->installation->getArchive()->extractTar($tag['cdata'], 'files_');
		
		// create file handler
		$fileHandler = new FilesFileHandler($this->installation);
		
		// extract content of files.tar
		try {
			$fileInstaller = $this->installation->extractFiles($packageDir, $sourceFile, $fileHandler);
		}
		catch (SystemException $e) {
			if (!@file_exists(WCF_DIR.'acp/templates/packageInstallationFileInstallationFailed.tpl')) {
				// workaround for wcf 1.0 to 1.1 update
				throw $e;
			}
			else {
				WCF::getTPL()->assign(array(
					'exception' => $e
				));
				WCF::getTPL()->display('packageInstallationFileInstallationFailed');
				exit;
			}
		}
		
		// if this a standalone package, write config.inc.php for this package
		if ($this->installation->getPackage()->isStandalone() == 1 && $this->installation->getPackage()->getPackage() != 'com.woltlab.wcf' && $this->installation->getAction() == 'install') {
			// touch file
			$fileInstaller->touchFile(PackageInstallation::CONFIG_FILE);
			
			// create file
			Package::writeConfigFile($this->installation->getPackageID());
			
			// log file
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_file_log
						(packageID, filename)
				VALUES		(".$this->installation->getPackageID().", 'config.inc.php')";
			WCF::getDB()->sendQuery($sql);
		}
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
		
		// update acp style file
		StyleUtil::updateStyleFile();
	}
	
	/**
	 * Prompts for installation directory.
	 *
	 * @return	string		package dir
	 */
	protected function promptPackageDir() {
		$packageDir = $errorField = $errorType = '';
		if (isset($_POST['send'])) {
			if (isset($_POST['packageDir'])) {
				$packageDir = StringUtil::trim($_POST['packageDir']);
			}

			// error handling
			try {
				$dir = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($packageDir));
				
				// package can not be installed into the wcf directory
				if (FileUtil::unifyDirSeperator(WCF_DIR) == $dir) {
					throw new UserInputException('packageDir', 'wcfDirLocked');
				}
				
				// this package is a standalone package and needs its own package directory
				$relativePackageDir = FileUtil::getRelativePath(WCF_DIR, $dir);
				$sql = "SELECT 	COUNT(*) AS count
					FROM	wcf".WCF_N."_package
					WHERE	packageDir = '".escapeString($relativePackageDir)."'";
				$alreadyInstalled = WCF::getDB()->getFirstRow($sql);
				if ($alreadyInstalled['count'] > 0) {
					throw new UserInputException('packageDir', 'alreadyInstalled');
				}
				
				// check writing property
				if (@file_exists($dir) && !@is_writable($dir)) {
					throw new UserInputException('packageDir', 'notWritable');
				}
				
				return $relativePackageDir;
			}
			catch (UserInputException $e) {
				$errorField = $e->getField();
				$errorType = $e->getType();
			}
		}
		else {
			// make default dir
			//$packageNameParts = explode('.', $this->installation->getPackage()->getPackage());
			//$packageDir = FileUtil::getRealPath(WCF_DIR.'../'.$packageNameParts[count($packageNameParts) - 1]);
			$packageDir = FileUtil::getRealPath(WCF_DIR.'../');
		}
		
		// domain
		$domainName = '';
		if (!empty($_SERVER['SERVER_NAME'])) $domainName = 'http://' . $_SERVER['SERVER_NAME'];
		// port
		if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) $domainName .= ':' . $_SERVER['SERVER_PORT'];
		// wcf url
		$wcfUrl = '';
		if (!empty($_SERVER['REQUEST_URI'])) $wcfUrl = FileUtil::removeTrailingSlash(FileUtil::getRealPath(FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash(dirname($_SERVER['REQUEST_URI']))).'/'.RELATIVE_WCF_DIR));
		
		WCF::getTPL()->assign(array(
			'packageDir' => $packageDir,
			'errorField' => $errorField,
			'errorType' => $errorType,
			'domainName' => $domainName,
			'wcfUrl' => $wcfUrl,
			'wcfDir' => FileUtil::unifyDirSeperator(WCF_DIR)
		));
		WCF::getTPL()->display('packageInstallationPromptPackageDir');
		exit;
	}
	
	/**
	 * Uninstalls the files of this package.
	 */
	public function uninstall() {
		// get absolute package dir
		$packageDir = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator(realpath(WCF_DIR.$this->installation->getPackage()->getDir())));
		
		// create file list
		$files = array();
		
		// get files from log
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_installation_file_log
			WHERE 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$files[] = $row['filename'];
		}
		
		if (count($files) > 0) {
			// delete files
			$this->installation->deleteFiles($packageDir, $files);
			
			// delete log entries
			parent::uninstall();
		}
	}
}
?>