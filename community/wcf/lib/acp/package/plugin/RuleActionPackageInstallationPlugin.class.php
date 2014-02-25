<?php
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes rule actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	acp.package.plugin
 * @category 	Community Framework (commercial)
 */
class RuleActionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'ruleaction';
	public $tableName = 'pm_rule_action';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$ruleActionXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($ruleActionXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $ruleAction) {
						// Extract item properties.
						foreach ($ruleAction['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$ruleAction[$child['name']] = $child['cdata'];
						}
					
						// default values
						$name = $classFile = '';
						
						// get values
						if (isset($ruleAction['name'])) $name = $ruleAction['name'];
						if (isset($ruleAction['classfile'])) $classFile = $ruleAction['classfile'];
						
						// insert items
						$sql = "INSERT INTO			wcf".WCF_N."_pm_rule_action
											(packageID, ruleAction, ruleActionClassFile)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($name)."',
											'".escapeString($classFile)."')
							ON DUPLICATE KEY UPDATE 	ruleActionClassFile = VALUES(ruleActionClassFile)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through items and delete them.
					$nameArray = array();
					foreach ($block['children'] as $ruleAction) {
						// Extract item properties.
						foreach ($ruleAction['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$ruleAction[$child['name']] = $child['cdata'];
						}
					
						if (empty($ruleAction['name'])) {
							throw new SystemException("Required 'name' attribute for rule action is missing", 13023); 
						}
						$nameArray[] = $ruleAction['name'];
					}
					if (count($nameArray)) {
						$sql = "DELETE FROM	wcf".WCF_N."_pm_rule_action
							WHERE		ruleAction IN ('".implode("','", array_map('escapeString', $nameArray))."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>