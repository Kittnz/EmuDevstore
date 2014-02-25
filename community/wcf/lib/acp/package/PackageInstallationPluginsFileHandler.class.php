<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageInstallationFileHandler.class.php');

/**
 * PackageInstallationPluginsFileHandler is a FileHandler implementation for the installation of package installation plugins.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
class PackageInstallationPluginsFileHandler extends PackageInstallationFileHandler {
	/**
	 * @see FileHandler::checkFiles()
	 */
	public function checkFiles(&$files) {}
	
	/**
	 * @see FileHandler::logFiles()
	 */
	public function logFiles(&$files) {
		$pluginInserts = '';
		foreach ($files as $file) {
			if (!empty($pluginInserts)) $pluginInserts .= ',';
			$pluginInserts .= "('".escapeString(basename($file, '.class.php'))."', ".$this->packageInstallation->getPackageID().")";
		}
		
		if (!empty($pluginInserts)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_installation_plugin
							(pluginName, packageID)
				VALUES			".$pluginInserts;
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>