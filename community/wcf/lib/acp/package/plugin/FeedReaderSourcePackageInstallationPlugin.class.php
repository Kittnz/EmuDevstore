<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes news feed sources.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.feed.reader
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class FeedReaderSourcePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'feedsource';
	public $tableName = 'feed_source';
	
	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$feedSourceXML = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($feedSourceXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $feedSourceItem) {
						// Extract item properties.
						foreach ($feedSourceItem['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$feedSourceItem[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($feedSourceItem['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for feed source item tag is missing.", 13023);
						}
						
						// default values
						$sourceURL = $updateCycle = '';
						
						// get values
						$sourceName = $feedSourceItem['attrs']['name'];
						if (isset($feedSourceItem['url'])) $sourceURL = $feedSourceItem['url'];
						if (isset($feedSourceItem['cycle'])) $updateCycle = $feedSourceItem['cycle'];
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_feed_source
											(packageID, sourceName, sourceURL, updateCycle)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($sourceName)."',
											'".escapeString($sourceURL)."',
											'".escapeString($updateCycle)."')
							ON DUPLICATE KEY UPDATE 	sourceURL = VALUES(sourceURL),
											updateCycle = VALUES(updateCycle)";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $sourceItem) {
							// check required attributes
							if (!isset($sourceItem['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for feed source item tag is missing.", 13023);
							}
							// Create a string with all item names which should be deleted (comma sparated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($sourceItem['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_feed_source
								WHERE		packageID = ".$this->installation->getPackageID()."
										AND sourceName IN (".$itemNames.")";
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>