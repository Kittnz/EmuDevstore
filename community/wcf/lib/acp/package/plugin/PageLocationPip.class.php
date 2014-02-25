<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes page locations.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.page
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class PageLocationPip extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'pagelocation';
	public $tableName = 'page_location';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$acpMenuXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall acp-menu items.
		foreach ($acpMenuXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through acp-menu items and create or update them.
					foreach ($block['children'] as $acpMenuItem) {
						// Extract item properties.
						foreach ($acpMenuItem['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$acpMenuItem[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($acpMenuItem['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for page location is missing", 13023);
						}
						if (!isset($acpMenuItem['pattern'])) {
							throw new SystemException("Required 'pattern' attribute for page location is missing", 13023);
						}
						
						
						// default values
						$classPath = '';
						
						// get values
						$name = $acpMenuItem['attrs']['name'];
						$pattern = $acpMenuItem['pattern'];
						if (isset($acpMenuItem['classpath'])) $classPath = $acpMenuItem['classpath'];
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_page_location
											(locationName, locationPattern, packageID, classPath)
							VALUES				('".escapeString($name)."',
											'".escapeString($pattern)."',
											".$this->installation->getPackageID().",
											'".escapeString($classPath)."')
							ON DUPLICATE KEY UPDATE 	locationPattern = VALUES(locationPattern),
											classPath = VALUES(classPath)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through acp-menu items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $acpMenuItem) {
							// check required attributes
							if (!isset($acpMenuItem['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'pagelocation'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($acpMenuItem['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_page_location
								WHERE		locationName IN (".$itemNames.")
										AND packageID = ".$this->installation->getPackageID();
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>