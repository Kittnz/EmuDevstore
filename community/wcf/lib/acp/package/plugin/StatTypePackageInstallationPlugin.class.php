<?php
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes statistic types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.system.stats
 * @subpackage	acp.package.plugin
 * @category 	Community Framework (commercial)
 */
class StatTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'stattype';
	public $tableName = 'stat_type';
	
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
							throw new SystemException("Required 'name' attribute for statistic type is missing", 13023);
						}
						if (!isset($acpMenuItem['tablename'])) {
							throw new SystemException("Required 'tablename' attribute for statistic type is missing", 13023);
						}
						if (!isset($acpMenuItem['datefieldname'])) {
							throw new SystemException("Required 'datefieldname' attribute for statistic type is missing", 13023);
						}
						
						// default values
						$userFieldName = '';
						
						// get values
						$name = $acpMenuItem['attrs']['name'];
						$tableName = $acpMenuItem['tablename'];
						$dateFieldName = $acpMenuItem['datefieldname'];
						if (isset($acpMenuItem['userfieldname'])) $userFieldName = $acpMenuItem['userfieldname'];
						
						// fix table name
						$standalonePackage = $this->installation->getPackage();
						if ($standalonePackage->getParentPackageID()) {
							// package is a plugin; get parent package
							$standalonePackage = $standalonePackage->getParentPackage();
						}
						
						if ($standalonePackage->isStandalone() == 1) {
							// package is standalone
							$packageAbbr = $standalonePackage->getAbbreviation();
							$tablePrefix = WCF_N.'_'.$standalonePackage->getInstanceNo().'_';
							
							// Replace the variable xyz1_1 with $tablePrefix in the table names.  
							$tableName = str_replace($packageAbbr.'1_1_', $packageAbbr.$tablePrefix, $tableName);
						}
						
						// replace wcf1_ with the actual WCF_N value 
						$tableName = str_replace("wcf1_", "wcf".WCF_N."_", $tableName);
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_stat_type
											(typeName, packageID, tableName, dateFieldName, userFieldName)
							VALUES				('".escapeString($name)."',
											".$this->installation->getPackageID().",
											'".escapeString($tableName)."',
											'".escapeString($dateFieldName)."',
											'".escapeString($userFieldName)."')
							ON DUPLICATE KEY UPDATE 	tableName = VALUES(tableName),
											dateFieldName = VALUES(dateFieldName),
											userFieldName = VALUES(userFieldName)";
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
								throw new SystemException("Required 'name' attribute for 'stattype'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($acpMenuItem['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_stat_type
								WHERE		packageID = ".$this->installation->getPackageID()."
										AND typeName IN (".$itemNames.")";
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>