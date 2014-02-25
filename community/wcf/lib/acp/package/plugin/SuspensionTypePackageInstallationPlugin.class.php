<?php
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes suspension types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.package.plugin
 * @category 	Community Framework (commercial)
 */
class SuspensionTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'suspensiontype';
	public $tableName = 'user_infraction_suspension_type';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$suspensionTypeXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($suspensionTypeXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $suspensionType) {
						// Extract item properties.
						foreach ($suspensionType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$suspensionType[$child['name']] = $child['cdata'];
						}
					
						// default values
						$name = $classFile = '';
						
						// get values
						if (isset($suspensionType['name'])) $name = $suspensionType['name'];
						if (isset($suspensionType['classfile'])) $classFile = $suspensionType['classfile'];
						
						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_user_infraction_suspension_type
											(packageID, suspensionType, classFile)
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
					foreach ($block['children'] as $suspensionType) {
						// Extract item properties.
						foreach ($suspensionType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$suspensionType[$child['name']] = $child['cdata'];
						}
					
						if (empty($suspensionType['name'])) {
							throw new SystemException("Required 'name' attribute for suspension type is missing", 13023); 
						}
						$nameArray[] = $suspensionType['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_user_infraction_suspension_type
							WHERE		packageID = ".$this->installation->getPackageID()."
									AND suspensionType IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>