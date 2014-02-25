<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes searchable message types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class SearchableMessageTypePip extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'searchablemessagetypes';
	public $tableName = 'searchable_message_type';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$xml = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall acp-menu items.
		foreach ($xml['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $messageType) {
						// Extract item properties.
						foreach ($messageType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$messageType[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($messageType['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for 'smt'-tag is missing.", 13023);
						}
						
						// default values
						$typeName = $classPath = '';
						
						// get values
						$typeName = $messageType['attrs']['name'];
						if (isset($messageType['classpath'])) $classPath = $messageType['classpath'];
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_searchable_message_type
											(typeName, classPath, packageID)
							VALUES				('".escapeString($typeName)."',
											'".escapeString($classPath)."',
											".$this->installation->getPackageID().")
							ON DUPLICATE KEY UPDATE 	classPath = VALUES(classPath)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $messageType) {
							// check required attributes
							if (!isset($messageType['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'smt'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($messageType['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_searchable_message_type
								WHERE		typeName IN (".$itemNames.")
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