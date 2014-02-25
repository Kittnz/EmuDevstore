<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes style attributes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class StyleAttributesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'styleattributes';
	public $tableName = 'style_variable_to_attribute';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$styleAttributeXML = $xml->getElementTree('data');
		 
		// Loop through the array and install or uninstall acp-menu items.
		foreach ($styleAttributeXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through acp-menu items and create or update them.
					foreach ($block['children'] as $styleAttribute) {
						// Extract item properties.
						foreach ($styleAttribute['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$styleAttribute[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($styleAttribute['value'])) {
							throw new SystemException("Required 'value' attribute for style attribute item is missing", 13023);
						}
						
						// default values
						$cssSelector = $attributeName = $variableName = '';
						
						// get values
						if (isset($styleAttribute['selector'])) $cssSelector = $styleAttribute['selector'];
						if (isset($styleAttribute['name'])) $attributeName = $styleAttribute['name'];
						if (isset($styleAttribute['value'])) $variableName = $styleAttribute['value'];
						
						// save item
						$sql = "INSERT IGNORE INTO	wcf".WCF_N."_style_variable_to_attribute
										(packageID, cssSelector, attributeName, variableName)
							VALUES			(".$this->installation->getPackageID().",
										'".escapeString($cssSelector)."',
										'".escapeString($attributeName)."',
										'".escapeString($variableName)."')";
						WCF::getDB()->sendQuery($sql);
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through acp-menu items and delete them.
						$sqlConditions = '';
						foreach ($block['children'] as $styleAttribute) {
							foreach ($styleAttribute['children'] as $child) {
								if (!isset($child['cdata'])) continue;
								$styleAttribute[$child['name']] = $child['cdata'];
							}
						
							// check required attributes
							if (!isset($styleAttribute['value'])) {
								throw new SystemException("Required 'value' attribute for style attribute item is missing", 13023);
							}

							// default values
							$cssSelector = $attributeName = $variableName = '';
							
							// get values
							if (isset($styleAttribute['selector'])) $cssSelector = $styleAttribute['selector'];
							if (isset($styleAttribute['name'])) $attributeName = $styleAttribute['name'];
							if (isset($styleAttribute['value'])) $variableName = $styleAttribute['value'];
							
							// Create a string with all item names which should be deleted (comma separated).
							if (!empty($sqlConditions)) $sqlConditions .= ' OR ';
							
							$sqlConditions .= "(	packageID = ".$this->installation->getPackageID()."
										AND cssSelector = '".escapeString($cssSelector)."'
										AND attributeName = '".escapeString($attributeName)."'
										AND variableName = '".escapeString($variableName)."')";
						}
						// Delete items.
						if (!empty($sqlConditions)) {
							$sql = "DELETE FROM	wcf".WCF_N."_style_variable_to_attribute
								WHERE		".$sqlConditions;
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
		
		$this->updateStyleFiles();
	}
	
	/**
	 * @see	 PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		$this->updateStyleFiles();
	}
	
	/**
	 * Updates styles files of all styles.
	 */
	protected function updateStyleFiles() {
		require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
		
		// get all styles
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_style";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$style = new StyleEditor(null, $row);
			$style->writeStyleFile();
		}
	}
}
?>