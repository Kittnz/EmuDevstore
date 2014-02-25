<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes attachment container types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class AttachmentContainerTypePip extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'attachmentcontainertype';
	public $tableName = 'attachment_container_type';
	
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
					foreach ($block['children'] as $containerType) {
						// Extract item properties.
						foreach ($containerType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$containerType[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($containerType['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for 'act'-tag is missing.", 13023);
						}
						
						// default values
						$typeName = $url = '';
						$isPrivate = 0;
						
						// get values
						$typeName = $containerType['attrs']['name'];
						if (isset($containerType['url'])) $url = $containerType['url'];
						if (isset($containerType['private'])) $isPrivate = intval($containerType['private']);
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_attachment_container_type
											(containerType, isPrivate, url, packageID)
							VALUES				('".escapeString($typeName)."',
											".$isPrivate.",
											'".escapeString($url)."',
											".$this->installation->getPackageID().")
							ON DUPLICATE KEY UPDATE 	isPrivate = VALUES(isPrivate),
											url = VALUES(url)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $containerType) {
							// check required attributes
							if (!isset($containerType['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'act'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($containerType['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_attachment_container_type
								WHERE		containerType IN (".$itemNames.")
										AND packageID = ".$this->installation->getPackageID();
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see	 PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::fireAction($this, 'uninstall');
		
		// get container types
		$containerTypes = array();
		$sql = "SELECT	containerType
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$containerTypes[] = $row['containerType'];
		}
		
		if (count($containerTypes)) {
			require_once(WCF_DIR.'lib/data/attachment/AttachmentEditor.class.php');
			// delete attachments (files)
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_attachment
				WHERE	packageID = ".$this->installation->getPackageID()."
					AND containerType IN ('".implode("','", array_map('escapeString', $containerTypes))."')";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$attachment = new AttachmentEditor(null, $row);
				$attachment->deleteFile();;
			}
			
			// delete attachments (rows)
			$sql = "DELETE FROM	wcf".WCF_N."_attachment
				WHERE		packageID = ".$this->installation->getPackageID()."
						AND containerType IN ('".implode("','", array_map('escapeString', $containerTypes))."')";
			WCF::getDB()->sendQuery($sql);
			
			// delete container
			$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
				WHERE		packageID = ".$this->installation->getPackageID();
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>