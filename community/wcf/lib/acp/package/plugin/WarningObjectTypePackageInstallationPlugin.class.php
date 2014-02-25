<?php
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes warning object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.package.plugin
 * @category 	Community Framework (commercial)
 */
class WarningObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'warningobjecttype';
	public $tableName = 'user_infraction_warning_object_type';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$warningObjectTypeXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($warningObjectTypeXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $warningObjectType) {
						// Extract item properties.
						foreach ($warningObjectType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$warningObjectType[$child['name']] = $child['cdata'];
						}
					
						// default values
						$name = $classFile = '';
						
						// get values
						if (isset($warningObjectType['name'])) $name = $warningObjectType['name'];
						if (isset($warningObjectType['classfile'])) $classFile = $warningObjectType['classfile'];
						
						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_user_infraction_warning_object_type
											(packageID, objectType, classFile)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($name)."',
											'".escapeString($classFile)."')
							ON DUPLICATE KEY UPDATE 	classFile = VALUES(classFile)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through items and delete them.
					$nameArray = array();
					foreach ($block['children'] as $warningObjectType) {
						// Extract item properties.
						foreach ($warningObjectType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$warningObjectType[$child['name']] = $child['cdata'];
						}
					
						if (empty($warningObjectType['name'])) {
							throw new SystemException("Required 'name' attribute for warning object type is missing", 13023); 
						}
						$nameArray[] = $warningObjectType['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_warning_object_type
							WHERE		packageID = ".$this->installation->getPackageID()."
									AND objectType IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>