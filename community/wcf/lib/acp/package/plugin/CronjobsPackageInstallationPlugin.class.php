<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes cronjobs.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class CronjobsPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'cronjobs';
	public $tableName = 'cronjobs';
	
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
		require_once(WCF_DIR.'lib/data/cronjobs/CronjobEditor.class.php');
		
		// Loop through the array and install or uninstall cronjobs.
		foreach ($xml['children'] as $key => $block) {
			if (count($block['children'])) {
				// TODO: handle delete block first
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $cronjobInfo) {
						// Extract item properties.
						foreach ($cronjobInfo['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$cronjobInfo[$child['name']] = $child['cdata'];
						}
						
						// default values.
						$classPath = $description = $startMinute = $startHour = $startDom = $startMonth = $startDow = '';
						$execMultiple = $canBeEdited = $canBeDisabled = 0;
						$active = 1;
						
						// make xml tags-names (keys in array) to lower case
						$this->keysToLowerCase($cronjobInfo);
						
						// get values.
						if (isset($cronjobInfo['classpath'])) $classPath = $cronjobInfo['classpath'];
						if (isset($cronjobInfo['description'])) $description = $cronjobInfo['description'];
						if (isset($cronjobInfo['startminute'])) $startMinute = $cronjobInfo['startminute'];
						if (isset($cronjobInfo['starthour'])) $startHour = $cronjobInfo['starthour'];
						if (isset($cronjobInfo['startdom'])) $startDom = $cronjobInfo['startdom'];
						if (isset($cronjobInfo['startmonth'])) $startMonth = $cronjobInfo['startmonth'];
						if (isset($cronjobInfo['startdow'])) $startDow = $cronjobInfo['startdow'];
						if (isset($cronjobInfo['execmultiple'])) $execMultiple = intval($cronjobInfo['execmultiple']);
						if (isset($cronjobInfo['active'])) $active = intval($cronjobInfo['active']);
						if (isset($cronjobInfo['canbeedited'])) $canBeEdited = intval($cronjobInfo['canbeedited']);
						if (isset($cronjobInfo['canbedisabled'])) $canBeDisabled = intval($cronjobInfo['canbedisabled']);
						
						// validate values
						CronjobEditor::validate($startMinute, $startHour, $startDom, $startMonth, $startDow);
						
						// save cronjob 
						$sql = "INSERT INTO	wcf".WCF_N."_cronjobs 
									(classPath, packageID, description, 
									startMinute, startHour, startDom, 
									startMonth, startDow, nextExec, execMultiple, 
									active, canBeEdited, canBeDisabled) 
							VALUES 
									('".escapeString($classPath)."', 
									".$this->installation->getPackageID().", 
									'".escapeString($description)."', 
									'".escapeString($startMinute)."', 
									'".escapeString($startHour)."', 
									'".escapeString($startDom)."', 
									'".escapeString($startMonth)."', 
									'".escapeString($startDow)."', 
									".TIME_NOW.", 
									".escapeString($execMultiple).",
									".escapeString($active).",
									".escapeString($canBeEdited).", 
									".escapeString($canBeDisabled).")";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$cronjobNames = '';
						foreach ($block['children'] as $cronjobInfo) {
							// check required attributes
							if (!isset($cronjobInfo['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'cronjob'-tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($cronjobNames)) $cronjobNames .= ',';
							$cronjobNames .= "'".escapeString($cronjobInfo['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($cronjobNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_cronjobs
								WHERE		classPath IN (".$cronjobNames.")
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