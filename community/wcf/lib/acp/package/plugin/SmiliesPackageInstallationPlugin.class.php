<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes smilies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class SmiliesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'smilies';
	public $tableName = 'smiley';
	
	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$xmlContent = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall items.
		foreach ($xmlContent['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $item) {
						// Extract item properties.
						foreach ($item['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$item[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($item['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for smiley tag is missing.", 13023);
						}
						
						// default values
						$title = $path = '';
						$showOrder = null;
						
						// get values
						$name = $item['attrs']['name'];
						if (isset($item['title'])) $title = $item['title'];
						if (isset($item['path'])) $path = $item['path'];
						if (isset($item['showorder'])) $showOrder = intval($item['showorder']);
						$showOrder = $this->getShowOrder($showOrder);
						
						// Insert or update items. 
						// Update through the mysql "ON DUPLICATE KEY"-syntax. 
						$sql = "INSERT INTO			wcf".WCF_N."_smiley
											(packageID, smileyPath, smileyTitle, smileyCode, showOrder)
							VALUES				(".$this->installation->getPackageID().",
											'".escapeString($path)."',
											'".escapeString($title)."',
											'".escapeString($name)."',
											".$showOrder.")
							ON DUPLICATE KEY UPDATE 	smileyPath = VALUES(smileyPath),
											smileyTitle = VALUES(smileyTitle),
											showOrder = VALUES(showOrder)";
						WCF::getDB()->sendQuery($sql);
					}
				}
			}
		}
	}
}
?>