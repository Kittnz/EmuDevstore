<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageInstallationFileHandler.class.php');

/**
 * FilesFileHandler is a FileHandler implementation for the installation of regular files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class FilesFileHandler extends PackageInstallationFileHandler {
	/**
	 * @see FileHandler::checkFiles()
	 */
	public function checkFiles(&$files) {
		if ($this->packageInstallation->getPackage()->getPackage() != 'com.woltlab.wcf') {
			$fileCondition = '';
			foreach ($files as $file) {
				if (!empty($fileCondition)) $fileCondition .= ',';
				$fileCondition .= "'".escapeString($file)."'";
			}
			
			if (!empty($fileCondition)) {
				// get by other packages registered files
				$sql = "SELECT		file_log.filename, package.packageDir
					FROM		wcf".WCF_N."_package_installation_file_log file_log
					LEFT JOIN	wcf".WCF_N."_package package
					ON		(package.packageID = file_log.packageID)
					WHERE		file_log.packageID <> ".$this->packageInstallation->getPackageID()."
							AND file_log.filename IN (".$fileCondition.")";
				$result = WCF::getDB()->sendQuery($sql);
				$lockedFiles = array();
				while ($row = WCF::getDB()->fetchArray($result)) {
					$lockedFiles[$row['packageDir'].$row['filename']] = true;
				}
				
				// check delivered files
				if (count($lockedFiles) > 0) {
					$dir = $this->packageInstallation->getPackage()->getDir();
					foreach ($files as $key => $file) {
						if (isset($lockedFiles[$dir.$file])) {
							unset($files[$key]);
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see FileHandler::logFiles()
	 */
	public function logFiles(&$files) {
		$fileInserts = '';
		foreach ($files as $file) {
			if (!empty($fileInserts)) $fileInserts .= ',';
			$fileInserts .= "(".$this->packageInstallation->getPackageID().", '".escapeString($file)."')";
		}
		
		if (!empty($fileInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_installation_file_log
							(packageID, filename)
				VALUES			".$fileInserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>