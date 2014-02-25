<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');

/**
 * This PIP executes an individual php script.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class ScriptPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'script';
	
	/** 
	 * Runs a script.
	 */
	public function install() {
		parent::install();

		// get installation path of package
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageID = ".$this->installation->getPackageID();
		$packageDir = WCF::getDB()->getFirstRow($sql);
		$packageDir = $packageDir['packageDir'];
		
		// get relative path of script
		$scriptTag = $this->installation->getXMLTag('script');
		$path = FileUtil::getRealPath(WCF_DIR.$packageDir);
		
		// run script
		$this->run($path.$scriptTag['cdata']);
		
		// delete script
		if (@unlink($path.$scriptTag['cdata'])) {
			// delete file log entry
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_file_log
				WHERE		packageID = ".$this->installation->getPackageID()."
						AND filename = '".escapeString($scriptTag['cdata'])."'";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	private function run($scriptPath) {
		include($scriptPath);
	}
	
	/**
	 * Returns false. Scripts can't be uninstalled.
	 * 
	 * @return 	boolean 	false
	 */
	public function hasUninstall() {
		return false;
	}
	
	/** 
	 * Does nothing.
	 */
	public function uninstall() {}
}
?>