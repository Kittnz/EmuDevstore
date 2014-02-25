<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes help items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class HelpPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'help';
	public $tableName = 'help_item';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$helpXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall help items.
		foreach ($helpXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through help items and create or update them.
					foreach ($block['children'] as $helpItemData) {
						// Extract item properties.
						foreach ($helpItemData['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$helpItemData[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($helpItemData['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for help item tag is missing.", 13023);
						}
						
						// default values
						$parentHelpItem = $refererPattern = $permissions = $options = '';
						$showOrder = null;
						
						// make xml tags-names (keys in array) to lower case
						$this->keysToLowerCase($helpItemData);
						
						// get values
						$helpItem = $helpItemData['attrs']['name'];
						if (isset($helpItemData['refererpattern'])) $refererPattern = $helpItemData['refererpattern'];
						if (isset($helpItemData['parent'])) $parentHelpItem = $helpItemData['parent'];
						if (!empty($helpItemData['showorder'])) $showOrder = intval($helpItemData['showorder']);
						$showOrder = $this->getShowOrder($showOrder, $parentHelpItem, 'parentHelpItem');
						if (isset($helpItemData['permissions'])) $permissions = $helpItemData['permissions'];
						if (isset($helpItemData['options'])) $options = $helpItemData['options'];
						
						// If a parent link was set and this parent is not in database 
						// or it is a link from a package from other package environment: don't install further.
						if (!empty($parentHelpItem)) {
							$sql = "SELECT	COUNT(*) as count
								FROM 	wcf".WCF_N."_help_item
								WHERE	helpItem = '".escapeString($parentHelpItem)."'";
							$row = WCF::getDB()->getFirstRow($sql);
							if ($row['count'] == 0) {
								throw new SystemException("Unable to find parent help item with name '".$parentHelpItem."' for help item with name '".$helpItem."'.", 13011);
							}
						}
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_help_item
											(packageID, helpItem, parentHelpItem, refererPattern, showOrder, permissions, options)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($helpItem)."',
											'".escapeString($parentHelpItem)."',
											'".escapeString($refererPattern)."',
											".$showOrder.",
											'".escapeString($permissions)."',
											'".escapeString($options)."')
							ON DUPLICATE KEY UPDATE 	parentHelpItem = VALUES(parentHelpItem),
											refererPattern = VALUES(refererPattern),
											showOrder = VALUES(showOrder),
											permissions = VALUES(permissions),
											options = VALUES(options)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through help items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $helpItemData) {
							// check required attributes
							if (!isset($helpItemData['attrs']['name'])) {
								throw new SystemException("Required name attribute for '".$helpItemData['name']."'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($helpItemData['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_help_item
								WHERE		helpItem IN (".$itemNames.")
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