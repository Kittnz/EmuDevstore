<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/PackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageInstallationQueue.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Default implementation of some PackageInstallationPlugin functions.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
abstract class AbstractPackageInstallationPlugin implements PackageInstallationPlugin {
	/**
	 * XML tag name in package.xml
	 *
	 * @var string
	 */
	public $tagName = '';
	
	/**
	 * Database table name.
	 *
	 * @var string
	 */
	public $tableName = '';
	
	/**
	 * Active instance of PackageInstallationQueue
	 *
	 * @var PackageInstallationQueue
	 */
	public $installation = null;
	
	/**
	 * Creates a new AbstractPackageInstallationPlugin object.
	 * 
	 * @param 	PackageInstallationQueue	$installation
	 */
	public function __construct(PackageInstallationQueue $installation) {
		$this->installation = $installation;

		// call construct event
		EventHandler::fireAction($this, 'construct');
	}
	
	/**
	 * @see	 PackageInstallationPlugin::hasInstall()
	 */
	public function hasInstall() {
		// call hasInstall event
		EventHandler::fireAction($this, 'hasInstall');
		
		return $this->installation->XMLTagExists($this->tagName);
	}
	
	/**
	 * @see	 PackageInstallationPlugin::install()
	 */
	public function install() {
		// call install event
		EventHandler::fireAction($this, 'install');
	}
	
	/**
	 * @see	 PackageInstallationPlugin::hasUpdate()
	 */
	public function hasUpdate() {
       		// call hasUpdate event
		EventHandler::fireAction($this, 'hasUpdate');
		
		return $this->hasInstall();
	}
	
	/**
	 * @see	 PackageInstallationPlugin::update()
	 */
	public function update() {
       		// call update event
		EventHandler::fireAction($this, 'update');
				
		return $this->install();
	}
	
	/**
	 * @see	 PackageInstallationPlugin::hasUninstall()
	 */
	public function hasUninstall() {
		// call hasUninstall event
		EventHandler::fireAction($this, 'hasUninstall');
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ".$this->installation->getPackageID();
		$installationCount = WCF::getDB()->getFirstRow($sql);
		return $installationCount['count'];
	}
	
	/**
	 * @see	 PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::fireAction($this, 'uninstall');
		
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ".$this->installation->getPackageID();
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Makes the keys of an array to lower case. 
	 * 
	 * @param	array	$array
	 * @deprecated 		new package installation plugins should not use this function
	 */
	protected static function keysToLowerCase(&$array) {
		$originalKeys = array_keys($array);
		foreach ($originalKeys as $originalKey) {
			if (!isset($array[strtolower($originalKey)])) {
				$array[strtolower($originalKey)] = $array[$originalKey];
				unset($array[$originalKey]);							
			}
		}
	}
}
?>