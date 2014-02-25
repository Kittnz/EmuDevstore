<?php
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes rule condition types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	acp.package.plugin
 * @category 	Community Framework (commercial)
 */
class RuleConditionTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'ruleconditiontype';
	public $tableName = 'pm_rule_condition_type';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$ruleConditionTypeXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($ruleConditionTypeXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $ruleConditionType) {
						// Extract item properties.
						foreach ($ruleConditionType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$ruleConditionType[$child['name']] = $child['cdata'];
						}
					
						// default values
						$name = $classFile = '';
						
						// get values
						if (isset($ruleConditionType['name'])) $name = $ruleConditionType['name'];
						if (isset($ruleConditionType['classfile'])) $classFile = $ruleConditionType['classfile'];
						
						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_pm_rule_condition_type
											(packageID, ruleConditionType, ruleConditionTypeClassFile)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($name)."',
											'".escapeString($classFile)."')
							ON DUPLICATE KEY UPDATE 	ruleConditionTypeClassFile = VALUES(ruleConditionTypeClassFile)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through items and delete them.
					$nameArray = array();
					foreach ($block['children'] as $ruleConditionType) {
						// Extract item properties.
						foreach ($ruleConditionType['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$ruleConditionType[$child['name']] = $child['cdata'];
						}
					
						if (empty($ruleConditionType['name'])) {
							throw new SystemException("Required 'name' attribute for rule condition type is missing", 13023); 
						}
						$nameArray[] = $ruleConditionType['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_pm_rule_condition_type
							WHERE		ruleConditionType IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>