<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageInstallationFileHandler.class.php');

/**
 * ACPTemplatesFileHandler is a FileHandler implementation for the installation of ACP-template files.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class ACPTemplatesFileHandler extends PackageInstallationFileHandler {
	protected $tableName = '_acp_template';
	
	/**
	 * @see FileHandler::checkFiles()
	 */
	public function checkFiles(&$files) {
		if ($this->packageInstallation->getPackage()->getPackage() != 'com.woltlab.wcf') {
			$packageID = $this->packageInstallation->getPackageID();
			
			// build sql string with ACP-templateNames
			$fileCondition = '';
			$fileNames = array();
			foreach ($files as $file) {
				$fileName = preg_replace("%\.tpl$%", "", $file);
				$fileNames[] = $fileName;
				if (!empty($fileCondition)) $fileCondition .= ',';
				$fileCondition .= "'".escapeString($fileName)."'";
			}
			
			// check if files are existing already
			if (!empty($fileCondition)) {
				// get by other packages registered files
				$sql = "SELECT		*
					FROM		wcf".WCF_N.$this->tableName."
					WHERE		packageID <> ".$packageID."
							AND packageID IN (
								SELECT	packageID
								FROM 	wcf".WCF_N."_package
								WHERE 	packageDir = '".escapeString($this->packageInstallation->getPackage()->getDir())."'
								AND 	standalone = 0
							)
					AND 		templateName IN (".$fileCondition.")";
				$result = WCF::getDB()->sendQuery($sql);
				$lockedFiles = array();
				while ($row = WCF::getDB()->fetchArray($result)) {
					$lockedFiles[$row['templateName']] = $row['packageID'];
				}
				
				// check if files from installing package are in conflict with already installed files
				if (!$this->packageInstallation->getPackage()->isStandalone() && count($lockedFiles) > 0) {
					foreach ($fileNames as $key => $file) {
						if (isset($lockedFiles[$file]) && $packageID != $lockedFiles[$file]) {
							$owningPackage = new Package($lockedFiles[$file]);
							throw new SystemException("A non-standalone package can't overwrite template files. Only an update from the package which owns the template can do that. (Package '".$this->packageInstallation->getPackage()->getPackage()."' tries to overwrite template '".$file."', which is owned by package '".$owningPackage->getPackage()."')", 13026);
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
		$packageID = $this->packageInstallation->getPackageID();
		foreach ($files as $file) {
			if (!empty($fileInserts)) $fileInserts .= ',';
			
			// remove suffix and file extension
			$templateName = preg_replace('%.tpl$%', '', $file);
			$fileInserts .= "(".$packageID.", '".escapeString($templateName)."')";
		}
		
		if (!empty($fileInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N.$this->tableName."
							(packageID, templateName)
				VALUES			".$fileInserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>