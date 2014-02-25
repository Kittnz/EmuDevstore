<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes taggables.
 * 
 * @author	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class TaggablePip extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'taggable';
	public $tableName = 'tag_taggable';
	
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
					foreach ($block['children'] as $taggable) {
						// Extract item properties.
						foreach ($taggable['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$taggable[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($taggable['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for 'taggable'-tag is missing.", 13023);
						}
						
						// default values
						$typeName = $classPath = '';
						
						// get values
						$typeName = $taggable['attrs']['name'];
						if (isset($taggable['classpath'])) $classPath = $taggable['classpath'];
						
						// check if the taggable exist already and was installed by this package
						$sql = "SELECT	taggableID
							FROM 	wcf".WCF_N."_".$this->tableName."
							WHERE 	name = '".escapeString($typeName)."'
							AND	packageID = ".$this->installation->getPackageID();
						$row = WCF::getDB()->getFirstRow($sql);
						if (empty($row['taggableID'])) {
							$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
										(packageID, name, classPath)
								VALUES		(".$this->installation->getPackageID().",
										'".escapeString($typeName)."',
										'".escapeString($classPath)."')";
							WCF::getDB()->sendQuery($sql);
						}
						else {
							$sql = "UPDATE  wcf".WCF_N."_".$this->tableName."
								SET	classPath = '".escapeString($classPath)."'
								WHERE	taggableID = ".$row['taggableID'];
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $taggable) {
							// check required attributes
							if (!isset($taggable['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'taggable'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($taggable['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
								WHERE		name IN (".$itemNames.")
								AND 		packageID = ".$this->installation->getPackageID();
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>