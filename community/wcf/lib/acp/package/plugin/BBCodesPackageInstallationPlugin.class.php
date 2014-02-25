<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class BBCodesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'bbcodes';
	public $tableName = 'bbcode';
	
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
							if (!isset($child['cdata']) || $child['name'] == 'attributes') continue;
							$item[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($item['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for bbcode tag is missing.", 13023);
						}
						
						// default values
						$htmlOpen = $htmlClose = $textOpen = $textClose = $className = $wysiwygIcon = '';
						$allowedChildren = 'all';
						$wysiwyg = $sourceCode = $disabled = 0;
						
						// get values
						$name = $item['attrs']['name'];
						if (isset($item['htmlopen'])) $htmlOpen = $item['htmlopen'];
						if (isset($item['htmlclose'])) $htmlClose = $item['htmlclose'];
						if (isset($item['textopen'])) $textOpen = $item['textopen'];
						if (isset($item['textclose'])) $textClose = $item['textclose'];
						if (isset($item['allowedchildren'])) $allowedChildren = $item['allowedchildren'];
						if (isset($item['classname'])) $className = $item['classname'];
						if (isset($item['wysiwyg'])) $wysiwyg = intval($item['wysiwyg']);
						if (isset($item['wysiwygicon'])) $wysiwygIcon = $item['wysiwygicon'];
						if (isset($item['sourcecode'])) $sourceCode = intval($item['sourcecode']);
						if (isset($item['disabled'])) $disabled = intval($item['disabled']);
						if (!empty($className)) $wysiwyg = 0;
						
						// install bbcodes
						$sql = "INSERT INTO			wcf".WCF_N."_bbcode
											(bbcodeTag, packageID, htmlOpen, htmlClose, textOpen, textClose, allowedChildren, className, wysiwyg, wysiwygIcon, sourceCode, disabled)
							VALUES				('".escapeString($name)."',
											".$this->installation->getPackageID().",
											'".escapeString($htmlOpen)."',
											'".escapeString($htmlClose)."',
											'".escapeString($textOpen)."',
											'".escapeString($textClose)."',
											'".escapeString($allowedChildren)."',
											'".escapeString($className)."',
											".$wysiwyg.",
											'".escapeString($wysiwygIcon)."',
											".$sourceCode.",
											".$disabled.")
							ON DUPLICATE KEY UPDATE 	htmlOpen = VALUES(htmlOpen),
											htmlClose = VALUES(htmlClose),
											textOpen = VALUES(textOpen),
											textClose = VALUES(textClose),
											allowedChildren = VALUES(allowedChildren),
											className = VALUES(className),
											wysiwyg = VALUES(wysiwyg),
											wysiwygIcon = VALUES(wysiwygIcon),
											sourceCode = VALUES(sourceCode),
											disabled = VALUES(disabled)";
						WCF::getDB()->sendQuery($sql);
						$bbcodeID = WCF::getDB()->getInsertID();
						if (!$bbcodeID) {
							$sql = "SELECT	bbcodeID
								FROM	wcf".WCF_N."_bbcode
								WHERE	bbcodeTag = '".escapeString($name)."'";
							$row = WCF::getDB()->getFirstRow($sql);
							$bbcodeID = $row['bbcodeID'];
						}
						
						// delete old bbcode attributes
						if ($this->installation->getAction() == 'update') {
							$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
								WHERE		bbcodeID = ".$bbcodeID;
							WCF::getDB()->sendQuery($sql);
						}
						
						// install attributes
						foreach ($item['children'] as $attributes) {
							if ($attributes['name'] != 'attributes') continue;
							
							foreach ($attributes['children'] as $attribute) {
								foreach ($attribute['children'] as $attributeChild) {
									$attribute[$attributeChild['name']] = $attributeChild['cdata'];
								}
								
								// check required attributes
								if (!isset($attribute['attrs']['name'])) {
									throw new SystemException("Required 'name' attribute for atrribute tag is missing.", 13023);
								}
								
								// default values
								$html = $text = $validationPattern = '';
								$required = $useText = 0;
						
								// get values
								$name = intval($attribute['attrs']['name']);
								if (isset($attribute['html'])) $html = $attribute['html'];
								if (isset($attribute['text'])) $text = $attribute['text'];
								if (isset($attribute['validationpattern'])) $validationPattern = $attribute['validationpattern'];
								if (isset($attribute['required'])) $required = intval($attribute['required']);
								if (isset($attribute['usetext'])) $useText = intval($attribute['usetext']);
								
								$sql = "INSERT INTO			wcf".WCF_N."_bbcode_attribute
													(bbcodeID, attributeNo, attributeHtml, attributeText, validationPattern, required, useText)
									VALUES				(".$bbcodeID.",
													".$name.",
													'".escapeString($html)."',
													'".escapeString($text)."',
													'".escapeString($validationPattern)."',
													".$required.",
													".$useText.")
									ON DUPLICATE KEY UPDATE 	attributeHtml = VALUES(attributeHtml),
													attributeText = VALUES(attributeText),
													validationPattern = VALUES(validationPattern),
													required = VALUES(required),
													useText = VALUES(useText)";
								WCF::getDB()->sendQuery($sql);
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// delete attributes
		$sql = "DELETE FROM	wcf".WCF_N."_bbcode_attribute
			WHERE		bbcodeID IN (
						SELECT	bbcodeID
						FROM	wcf".WCF_N."_bbcode
						WHERE	packageID = ".$this->installation->getPackageID()."
					)";
		WCF::getDB()->sendQuery($sql);
		
		parent::uninstall();
	}
}
?>