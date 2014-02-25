<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'eventlistener';
	public $tableName = 'event_listener';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$eventXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall event listeners.
		foreach ($eventXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through event listeners and create or update them.
					foreach ($block['children'] as $event) {
						// Extract item properties.
						foreach ($event['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$event[$child['name']] = $child['cdata'];
						}
					
						// default values
						$eventClassName = $eventName = $listenerClassFile = '';
						$environment = 'user';
						$inherit = $nice = 0;
						
						// make xml tags-names (keys in array) to lower case
						$this->keysToLowerCase($event);
						
						// get values
						if (isset($event['eventclassname'])) $eventClassName = $event['eventclassname'];
						if (isset($event['eventname'])) $eventName = $event['eventname'];
						if (isset($event['listenerclassfile'])) $listenerClassFile = $event['listenerclassfile'];
						if (isset($event['environment']) && $event['environment'] == 'admin') $environment = 'admin';
						if (isset($event['inherit'])) $inherit = intval($event['inherit']);
						if (isset($event['nice'])) $nice = intval($event['nice']);
						if ($nice < -128) $nice = -128;
						else if ($nice > 127) $nice = 127;
						
						// insert items
						// update inherit value for duplicates 
						$sql = "INSERT INTO			wcf".WCF_N."_event_listener
											(packageID, environment, eventClassName, eventName, listenerClassFile, inherit, niceValue)
							VALUES				(".$this->installation->getPackageID().",
											'".$environment."',
											'".escapeString($eventClassName)."',
											'".escapeString($eventName)."',
											'".escapeString($listenerClassFile)."',
											".$inherit.",
											".$nice.")
							ON DUPLICATE KEY UPDATE 	inherit = VALUES(inherit),
											niceValue = VALUES(niceValue)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					// Loop through event listeners and delete them.
					$conditions = '';
					foreach ($block['children'] as $event) {
						// Extract item properties.
						foreach ($event['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$event[$child['name']] = $child['cdata'];
						}
					
						// default values
						$eventClassName = $eventName = $listenerClassFile = '';
						$environment = 'user';
						$inherit = 0;
						
						// make xml tags-names (keys in array) to lower case
						$this->keysToLowerCase($event);
						
						// get values
						if (isset($event['eventclassname'])) $eventClassName = $event['eventclassname'];
						if (isset($event['eventname'])) $eventName = $event['eventname'];
						if (isset($event['listenerclassfile'])) $listenerClassFile = $event['listenerclassfile'];
						if (isset($event['environment']) && $event['environment'] == 'admin') $environment = 'admin';
						if (isset($event['inherit'])) $inherit = intval($event['inherit']);
						
						if (!empty($conditions)) $conditions .= ' OR ';
						$conditions .= "(packageID = ".$this->installation->getPackageID()." AND environment = '".$environment."' AND eventClassName = '".escapeString($eventClassName)."' AND eventName = '".escapeString($eventName)."' AND listenerClassFile = '".escapeString($listenerClassFile)."' AND inherit = ".$inherit.")";
					}
					// Delete listeners
					if (!empty($conditions)) {
						$sql = "DELETE FROM	wcf".WCF_N."_event_listener
							WHERE		".$conditions;
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
	
	/**
	 * @see	 PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		// clear cache immediately
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.eventListener-*.php');
	}
}
?>