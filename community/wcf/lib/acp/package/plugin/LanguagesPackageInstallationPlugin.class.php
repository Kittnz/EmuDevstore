<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageArchive.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * This PIP installs, updates or deletes language and their categories and items.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class LanguagesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'languages';
	public $tableName = 'language_item';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		$instructions = $this->installation->getInstructions();
		$languages = $instructions['languages'];
		
		// Install each <language>-tag from package.xml
		foreach ($languages as $language) {
			if ($xml = $this->readLanguage($language)) {
				// check required attributes
				if (!isset($language['languagecode'])) {
					throw new SystemException("required 'languagecode' attribute for 'languages' tag is missing in '".PackageArchive::INFO_FILE."'", 13001);
				}
				// check language encoding
				if (!Language::isSupported($language['languagecode'])) {
					// unsupported encoding
					continue;
				}

				// Get language
				$language = LanguageEditor::getLanguageByCode($language['languagecode']);
				if ($language === null) {
					// unknown language
					continue;
				}
				
				// import xml
				// don't update language files if package is standalone
				$language->updateFromXML($xml, $this->installation->getPackageID(), !$this->installation->getPackage()->isStandalone());  
				
				// add language to this package.
				$sql = "INSERT IGNORE INTO	wcf".WCF_N."_language_to_packages
								(languageID, packageID)
					VALUES			(".$language->getLanguageID().", 
								".$this->installation->getPackageID().")";
				WCF::getDB()->sendQuery($sql);
			}
		}
	}
	
	/**
	 * Returns true if the uninstalling package got to uninstall languages, categories or items.
	 * 
	 * @return 	boolean 			hasUnistall
	 */
	public function hasUninstall() {
		if (parent::hasUninstall()) return true;
		
		$sql = "SELECT	COUNT(languageID) as count
			FROM	wcf".WCF_N."_language_to_packages
			WHERE	packageID = ".$this->installation->getPackageID();
		$languageCount = WCF::getDB()->getFirstRow($sql);
		return $languageCount['count'] > 0;
	}
	
	/** 
	 * Deletes languages, categories or items which where installed by the package.
	 */
	public function uninstall() {
		parent::uninstall();
		
		// delete language to package relation
		$sql = "DELETE FROM	wcf".WCF_N."_language_to_packages
			WHERE		packageID = ".$this->installation->getPackageID();
		WCF::getDB()->sendQuery($sql);
		
		// delete language items
		// Get all items and their categories
		// which where installed from this package.
		$sql = "SELECT	languageItemID, languageCategoryID, languageID
			FROM	wcf".WCF_N."_language_item
			WHERE	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		$categoryIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[] = $row['languageItemID'];
			
			// Store categories
			$categoryIDs[$row['languageCategoryID']] = true;
		}
		
		if (count($itemIDs) > 0) {
			$sql = "DELETE	FROM wcf".WCF_N."_language_item
				WHERE	languageItemID IN (".implode(", ", $itemIDs).")
				AND	packageID = ".$this->installation->getPackageID();
			WCF::getDB()->sendQuery($sql);
			$this->deleteEmptyCategories(array_keys($categoryIDs), $this->installation->getPackageID());
		}
	}
	
	/**
	 * Extracts the language file and parses it with
     	 * SimpleXML. If the specified language file
	 * was not found, an error message is thrown.
	 * 
	 * @param	string		$language
	 * @return 	XML 		xml
	 */
	protected function readLanguage($language) { 
		// No <language>-tag in the instructions in package.xml
		if (!isset($language['cdata']) || !$language['cdata']) {
			return false;
		}
		// search language files in package archive
		// throw error message if not found
		if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($language['cdata'])) === false) {
			throw new SystemException("language file '".($language['cdata'])."' not found.", 13025);
		}
		
		// extract language file and parse with SimpleXML
		$xml = new XML();
		$xml->loadString($this->installation->getArchive()->getTar()->extractToString($fileIndex));
		return $xml;
	}
	
	/**
	 * Deletes categories which where changed by an update or deinstallation in case they are now empty.
	 * 
	 * @param	integer		$categoryIDs 
	 * @param 	integer		$packageID
	 */
	protected function deleteEmptyCategories($categoryIDs, $packageID) {
		// Get empty categories which where changed by this package.
		$sql = "SELECT		COUNT(item.languageItemID) AS count,
					language_category.languageCategoryID,
					language_category.languageCategory
			FROM		wcf".WCF_N."_language_category language_category
			LEFT JOIN	wcf".WCF_N."_language_item item
			ON		(item.languageCategoryID = language_category.languageCategoryID)
			WHERE		language_category.languageCategoryID IN (".implode(', ', $categoryIDs).")
			GROUP BY	language_category.languageCategoryID";
		$result = WCF::getDB()->sendQuery($sql);
		$categoriesToDelete = array(); 
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['count'] == 0) {
				$categoriesToDelete[$row['languageCategoryID']] = $row['languageCategory'];
			}			
		}
		
		// Delete categories from DB.
		if (count($categoriesToDelete) > 0) {
			$sql = "DELETE FROM	wcf".WCF_N."_language_category
				WHERE 		languageCategory IN
						('".implode("', '", $categoriesToDelete)."')";
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>