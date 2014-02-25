<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/language/Language.class.php');
}

/**
 * LanguageEditor imports the xml file of a language pack and
 * creates the needed language files. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category 	Community Framework
 */
class LanguageEditor extends Language {
	/**
	 * Creates a new LanguageEditor object.
	 * 
	 * @param 	integer		$languageID
	 */
	public function __construct($languageID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_language
			WHERE	languageID = ".$languageID;
		$this->data = WCF::getDB()->getFirstRow($sql);
		if (!empty($this->data)) {
			$this->languageID = $this->data['languageID'];
		}
	}
	
	/**
	 * Imports language items from an XML file into a new or a current language.
	 * Updates the relevant language files automatically.
	 *
	 * @param 	XML 		$xml
	 * @param	integer		$packageID
	 * @return 	LanguageEditor	language	
	 */
	public static function importFromXML(XML $xml, $packageID) {
		$languageCode = self::readLanguageCodeFromXML($xml);
		
		// try to find an existing language with the given language code
		$language = self::getLanguageByCode($languageCode);
		
		// create new language
		if ($language === null) {
			$language = self::create($languageCode);
		}
		
		// import xml
		$language->updateFromXML($xml, $packageID);
		
		// return language object
		return $language;
	}
	
	/**
	 * Imports language items from an XML file into this language.
	 * Updates the relevant language files automatically.
	 *
	 * @param 	XML 		$xml
	 * @param	integer		$packageID
	 * @param 	boolean		$updateFiles	
	 */
	public function updateFromXML(XML $xml, $packageID, $updateFiles = true) {
		// Compile an array with XML::getElementTree
		$languageXML = $xml->getElementTree('language');

		// get categories from xml
		$usedCategories = array();
		foreach ($languageXML['children'] as $languageCategory) {
			$usedCategories[$languageCategory['attrs']['name']] = 0;
		}
		
		// get existing categories
		if (!count($usedCategories)) return;
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategory IN ('".implode("','", array_map('escapeString', array_keys($usedCategories)))."')";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$usedCategories[$row['languageCategory']] = $row['languageCategoryID'];
		}
		
		// create new categories
		foreach ($usedCategories as $name => $id) {
			if ($id == 0) {
				$sql = "INSERT INTO	wcf".WCF_N."_language_category
							(languageCategory)
					VALUES		('".escapeString($name)."')";
				WCF::getDB()->sendQuery($sql);
				$usedCategories[$name] = WCF::getDB()->getInsertID();
			}
		}
		
		// loop categories to import items from xml
		$items = array();
		foreach ($languageXML['children'] as $languageCategory) {
			// import categories
			$categoryName = $languageCategory['attrs']['name'];
			$categoryID = $usedCategories[$categoryName];
			
			// import items from xml
			foreach ($languageCategory['children'] as $languageItem) {
				// get values
				$itemName = $languageItem['attrs']['name'];
				$itemValue = $languageItem['cdata'];
				
				// simple_xml returns values always UTF-8 encoded
				// manual decoding to charset necessary
				if ($this->data['languageEncoding'] != 'UTF-8') {
					$itemValue = StringUtil::convertEncoding('UTF-8', CHARSET, $itemValue);
				}
				
				$items[$itemName] = array('name' => $itemName, 'value' => $itemValue, 'categoryID' => $categoryID);
			}
		}
		
		if (count($items)) {
			// find existing items
			$existingItems = array();
			$sql = "SELECT	languageItem, languageItemID
				FROM	wcf".WCF_N."_language_item
				WHERE	languageItem IN ('".implode("','", array_map('escapeString', array_keys($items)))."')
					AND packageID = ".$packageID."
					AND languageID = ".$this->languageID;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$existingItems[strtolower($row['languageItem'])] = $row['languageItemID'];
			}
			
			$itemInserts = '';
			foreach ($items as $item) {
				if (isset($existingItems[strtolower($item['name'])])) {
					// update
					$sql = "UPDATE	wcf".WCF_N."_language_item
						SET	languageItemValue = '".escapeString($item['value'])."',
							languageCategoryID = ".$item['categoryID'].",
							languageUseCustomValue = 0,
							languageItem = '".escapeString($item['name'])."'
						WHERE	languageItemID = ".$existingItems[strtolower($item['name'])];
					WCF::getDB()->sendQuery($sql);
				}
				else {
					if (!empty($itemInserts)) $itemInserts .= ',';
					$itemInserts .= "(".$this->languageID.", '".escapeString($item['name'])."', '".escapeString($item['value'])."', ".$item['categoryID'].", ".$packageID.")";
				}
			}
			
			// save items
			if (!empty($itemInserts)) {
				$sql = "INSERT INTO			wcf".WCF_N."_language_item
									(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
					VALUES				".$itemInserts;
				WCF::getDB()->sendQuery($sql);
			}
		}
	
		// update the relevant language files
		if ($updateFiles) {
			self::deleteLanguageFiles($this->languageID);
		}
		
		// delete relevant template compilations
		$this->deleteCompiledTemplates();
	}
	
	/**
	 * Updates the language items of a language category.
	 * 
	 * @param	array		$items
	 * @param	integer		$categoryID
	 * @param	integer		$packageID
	 * @param 	array		$useCustom
	 */
	public function updateItems($items, $categoryID = 0, $packageID = PACKAGE_ID, $useCustom = array()) {
		if (!count($items)) return;
		
		// try to find category id
		if ($categoryID == 0) {
			$keys = array_keys($items);
			$item = $keys[0];
			$explodedItem = explode('.', $item);
			
			for ($i = (count($explodedItem) > 4 ? 4 : count($explodedItem)); $i >= 2; $i--) {
				$sql = "SELECT	languageCategoryID
					FROM	wcf".WCF_N."_language_category
					WHERE	languageCategory = '".escapeString(implode('.', array_slice($explodedItem, 0, $i)))."'";
				$row = WCF::getDB()->getFirstRow($sql);
				if (!empty($row['languageCategoryID'])) {
					$categoryID = $row['languageCategoryID'];
					break;
				}
			}
		}
		
		if (!$categoryID) {
			return false;
		}
		
		// find existing language items
		$itemsToPackages = array();
		$sql = "SELECT		languageItem, language_item.packageID
			FROM		wcf".WCF_N."_language_item language_item,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		language_item.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
					AND languageItem IN ('".implode("','", array_map('escapeString', array_keys($items)))."')
					AND languageID = ".$this->languageID."
			ORDER BY 	package_dependency.priority ASC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemsToPackages[strtolower($row['languageItem'])] = $row['packageID'];
		}
		
		// save items
		$itemInserts = '';
		foreach ($items as $item => $value) {
			if (isset($itemsToPackages[strtolower($item)])) {
				// update existing item
				$sql = "UPDATE	wcf".WCF_N."_language_item
					SET	languageCustomItemValue = '".escapeString($value)."',
						languageUseCustomValue = ".(isset($useCustom[$item]) ? $useCustom[$item] : 0).",
						languageHasCustomValue = ".($value != '' ? 1 : 0).",
						languageItem = '".escapeString($item)."'
					WHERE	languageItem = '".escapeString($item)."'
						AND packageID = ".$itemsToPackages[strtolower($item)]."
						AND languageID = ".$this->languageID;
				WCF::getDB()->sendQuery($sql);
			}
			else {
				// create new item
				if (!empty($itemInserts)) $itemInserts .= ',';
				$itemInserts .= "(".$this->languageID.", '".escapeString($item)."', '".escapeString($value)."', ".$categoryID.", ".$packageID.")";
			}
		}
		
		if (!empty($itemInserts)) {
			$sql = "INSERT INTO			wcf".WCF_N."_language_item
								(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
				VALUES				".$itemInserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// get language category
		$sql = "SELECT	languageCategory
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategoryID = ".$categoryID;
		$row = WCF::getDB()->getFirstRow($sql);
		
		// update the relevant language files
		self::deleteLanguageFiles($this->languageID, $row['languageCategory'], $packageID);
		
		// delete relevant template compilations
		$this->deleteCompiledTemplates();
	}
	
	/**
	 * Deletes relevant template compilations.
	 */
	public function deleteCompiledTemplates() {
		// templates
		$filenames = glob(WCF_DIR.'templates/compiled/*_'.$this->languageID.'_*.php');
		if ($filenames) foreach ($filenames as $filename) @unlink($filename);
		
		// acp templates
		$filenames = glob(WCF_DIR.'acp/templates/compiled/*_'.$this->languageID.'_*.php');
		if ($filenames) foreach ($filenames as $filename) @unlink($filename);
	}
	
	/**
	 * Makes this language to the default language.
	 */
	public function makeDefault() {
		// remove old default language
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 0
			WHERE 	isDefault = 1";
		WCF::getDB()->sendQuery($sql);
		
		// make this language to default
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 1
			WHERE 	languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		
		// rebuild language cache
		self::clearCache();
	}
	
	/**
	 * Write the languages files.
	 * 
	 * @param 	array		$categoryIDs
	 * @param 	array		$packageIDs
	 */
	protected function writeLanguageFiles($categoryIDs, $packageIDs) {
		// get categories
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_language_category
			WHERE 	languageCategoryID IN (".implode(',', $categoryIDs).")";
		$categories = WCF::getDB()->sendQuery($sql);
		while ($category = WCF::getDB()->fetchArray($categories)) {
			$categoryName = $category['languageCategory'];
			$categoryID = $category['languageCategoryID'];
			
			// loop packages
			foreach ($packageIDs as $packageID) {
				// get language items
				if ($packageID === 0) {
					// update after wcf installation
					$sql = "SELECT 	languageItem, languageItemValue, languageCustomItemValue, languageUseCustomValue
						FROM	wcf".WCF_N."_language_item
						WHERE	languageID = ".$this->languageID."
							AND languageCategoryID = ".$categoryID."
							AND packageID = 0";
				}
				else {
					// update after regular package installation or update or manual import
					$sql = "SELECT		languageItem, languageItemValue, languageCustomItemValue, languageUseCustomValue
						FROM		wcf".WCF_N."_language_item language_item,
								wcf".WCF_N."_package_dependency package_dependency
						WHERE 		language_item.packageID = package_dependency.dependency
								AND languageID = ".$this->languageID."
								AND languageCategoryID = ".$categoryID."
								AND package_dependency.packageID = ".$packageID."
						ORDER BY 	package_dependency.priority ASC";
				}
				
				$result = WCF::getDB()->sendQuery($sql);
				$items = array();
				while ($row = WCF::getDB()->fetchArray($result)) {
					if ($row['languageUseCustomValue'] == 1) {
						$items[$row['languageItem']] = $row['languageCustomItemValue'];
					}
					else {
						$items[$row['languageItem']] = $row['languageItemValue'];
					}
				}
				
				if (count($items) > 0) {
					$file = new File(WCF_DIR.'language/'.$packageID.'_'.$this->languageID.'_'.$categoryName.'.php');
					@$file->chmod(0777);
					$file->write("<?php\n/**\n* WoltLab Community Framework\n* language: ".$this->data['languageCode']."\n* encoding: ".$this->data['languageEncoding']."\n* category: ".$categoryName."\n* generated at ".gmdate("r")."\n* \n* DO NOT EDIT THIS FILE\n*/\n");
					
					foreach ($items as $languageItem => $languageItemValue) {
						$file->write("\$this->items[\$this->languageID]['".$languageItem."'] = '".str_replace("'", "\'", $languageItemValue)."';\n");
						
						// compile dynamic language variables
						if ($categoryName != 'wcf.global' && strpos($languageItemValue, '{') !== false) {
							$file->write("\$this->dynamicItems[\$this->languageID]['".$languageItem."'] = '".str_replace("'", "\'", self::getScriptingCompiler()->compileString($languageItem, $languageItemValue))."';\n");
						}
					}
					
					$file->write("?>");
					$file->close();
				}
			}
		}
	}
	
	/**
	 * Updates the language files for the given category.
	 * 
	 * @param	mixed		$categoryIDs	id of a category or an array with multiple categories
	 * @param	mixed 		$packageID
	 */
	public function updateCategory($categoryIDs = null, $packageID = null) {
		if ($categoryIDs === null) {
			// get all categories
			$categoryIDs = array();
			$sql = "SELECT	languageCategoryID
				FROM	wcf".WCF_N."_language_category";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$categoryIDs[] = $row['languageCategoryID'];
			}
		}
		else if (!is_array($categoryIDs)) {
			$categoryIDs = array($categoryIDs);
		}
		
		$this->writeLanguageFiles($categoryIDs, array($packageID));
	}
	
	/**
	 * Creates a new language.
	 * Takes an associative array with language information
	 * (generated by readLanguageCodeFromXML()).
	 * Returns an object of the new language.
	 *
	 * @param 	string 			$languageCode
	 * @return 	LanguageEditor
	 */
	public static function create($languageCode) {
		$sql = "INSERT INTO	wcf".WCF_N."_language 
					(languageCode, languageEncoding) 
			VALUES 		('".escapeString($languageCode)."',
					'".escapeString(CHARSET)."')";
		WCF::getDB()->sendQuery($sql);	
		$languageID = WCF::getDB()->getInsertID();
		
		// rebuild language cache
		self::clearCache();
		
		// return new language	
		return new LanguageEditor($languageID);
	}
	
	/**
	 * Takes an XML object and returns the specific language code.
	 *
	 * @param 	XML 		$xml
	 * @return 	string 		language code
	 */
	public static function readLanguageCodeFromXML(XML $xml) {
		// get attributes
		$attributes = $xml->getAttributes();
		
		if (!isset($attributes['languagecode'])) {
			throw new SystemException("missing attribute 'languagecode' in language file", 13023);
		}
		
		return $attributes['languagecode'];
	}
	
	/**
	 * Tries to find an existing language with the given language code.
	 * Returns null, if no language was found.
	 * 
	 * @param 	string 			$languageCode
	 * @return	LanguageEditor
	 */
	public static function getLanguageByCode($languageCode) {
		$sql = "SELECT	languageID
			FROM	wcf".WCF_N."_language
			WHERE	languageCode = '".escapeString($languageCode)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['languageID'])) return new LanguageEditor($row['languageID']);
		return null;
	}
	
	/**
	 * Updates all language files of the given package id.
	 */
	public static function updateAll() {
		self::deleteLanguageFiles();
	}
	
	/**
	 * Copies the language items of this language to an other language.
	 * 
	 * @param	Language	$destination	destination language
	 */
	public function copy($destination) {
		// copy language items
		$sql = "REPLACE INTO	wcf".WCF_N."_language_item
					(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			SELECT		".$destination->getLanguageID().", languageItem, languageItemValue, languageCategoryID, packageID
			FROM		wcf".WCF_N."_language_item
			WHERE		languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		
		// delete language files
		self::deleteLanguageFiles($destination->languageID);
		
		// delete relevant template compilations
		$destination->deleteCompiledTemplates();
	}
	
	/**
	 * Deletes this language completely.
	 */
	public function delete() {
		// delete database entries
		$sql = "DELETE FROM	wcf".WCF_N."_language
			WHERE		languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		$sql = "DELETE FROM	wcf".WCF_N."_language_to_packages
			WHERE		languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	languageID = 0
			WHERE	languageID = ".$this->languageID;
		WCF::getDB()->sendQuery($sql);
		
		// delete language files
		self::deleteLanguageFiles($this->languageID);
		
		// delete compiled templates
		$this->deleteCompiledTemplates();
		
		// clear cache
		self::clearCache();
	}
	
	/**
	 * Exports this language.
	 */
	public function export($packageIDArray = array(), $exportCustomValues = false) {
		// bom
		echo "\xEF\xBB\xBF";

		// header
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<language xmlns=\"http://www.woltlab.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.woltlab.com/XSD/language.xsd\" languagecode=\"".$this->getLanguageCode()."\">\n";
		
		// get items
		$items = array();
		if (count($packageIDArray)) {
			$sql = "SELECT		languageItem, " . ($exportCustomValues ? "CASE WHEN languageUseCustomValue > 0 THEN languageCustomItemValue ELSE languageItemValue END AS languageItemValue" : "languageItemValue") . ", languageCategory
				FROM		wcf".WCF_N."_language_item language_item
				LEFT JOIN	wcf".WCF_N."_language_category language_category
				ON		(language_category.languageCategoryID = language_item.languageCategoryID)
				WHERE 		language_item.packageID IN (".implode(',', $packageIDArray).")
						AND language_item.languageID = ".$this->languageID;
		}
		else {
			$sql = "SELECT		languageItem, " . ($exportCustomValues ? "CASE WHEN languageUseCustomValue > 0 THEN languageCustomItemValue ELSE languageItemValue END AS languageItemValue" : "languageItemValue") . ", languageCategory
				FROM		wcf".WCF_N."_package_dependency package_dependency,
						wcf".WCF_N."_language_item language_item
				LEFT JOIN	wcf".WCF_N."_language_category language_category
				ON		(language_category.languageCategoryID = language_item.languageCategoryID)
				WHERE 		language_item.packageID = package_dependency.dependency
						AND language_item.languageID = ".$this->languageID."
						AND package_dependency.packageID = ".PACKAGE_ID."
				ORDER BY 	package_dependency.priority ASC";
		}
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$items[$row['languageCategory']][$row['languageItem']] = $row['languageItemValue'];
		}
		
		// sort categories
		ksort($items);
		
		foreach ($items as $category => $categoryItems) {
			// sort items
			ksort($categoryItems);
			
			// category header
			echo "\t<category name=\"".$category."\">\n";
			
			// items
			foreach ($categoryItems as $item => $value) {
				if (CHARSET != 'UTF-8') {
					$value = StringUtil::convertEncoding(CHARSET, 'UTF-8', $value);
				}
				echo "\t\t<item name=\"".$item."\"><![CDATA[".StringUtil::escapeCDATA($value)."]]></item>\n";
			}
			
			// category footer
			echo "\t</category>\n";
		}
		
		// footer
		echo "</language>";
	}
	
	/**
	 * Searches in language items.
	 * 
	 * @param	string		$search		search query
	 * @param	string		$replace
	 * @param	integer		$languageID
	 * @param	boolean		$useRegex
	 * @param	boolean		$caseSensitive
	 * @param	boolean		$searchVariableName
	 * @return	array		results 
	 */
	public static function search($search, $replace = null, $languageID = null, $useRegex = 0, $caseSensitive = 0, $searchVariableName = 0) {
		// get available language items
		$results = array();
		$availableLanguageItems = array();
		$sql = "SELECT		languageItemID, languageItem, languageID
			FROM		wcf".WCF_N."_language_item language_item,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		language_item.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
					".($languageID !== null ? "AND languageID = ".$languageID : "")."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$availableLanguageItems[$row['languageID']][$row['languageItem']] = $row['languageItemID'];
		}
		
		// get ids
		if (!count($availableLanguageItems)) return $results;
		$languageItemIDs = '';
		foreach ($availableLanguageItems as $languageItems) {
			if (!empty($languageItemIDs)) $languageItemIDs .= ',';
			$languageItemIDs .= implode(',', $languageItems);
		}
	
		// build condition
		$searchCondition = '';
		
		// case sensitive
		if ($caseSensitive) $searchCondition .= 'BINARY ';
		
		// search field
		if ($searchVariableName) $searchCondition .= 'languageItem ';
		else $searchCondition .= 'languageItemValue ';
		
		// regex
		if ($useRegex) {
			$searchCondition .= "REGEXP '".escapeString($search)."'";
		}
		else {
			$searchCondition .= "LIKE '%".addcslashes(escapeString($search), '_%')."%'";
		}
		
		if (!$searchVariableName) {
			$searchCondition .= ' OR '.($caseSensitive ? 'BINARY ' : '').'languageCustomItemValue ';
			// regex
			if ($useRegex) {
				$searchCondition .= "REGEXP '".escapeString($search)."'";
			}
			else {
				$searchCondition .= "LIKE '%".addcslashes(escapeString($search), '_%')."%'";
			}
		}
		
		// search
		$updatedItems = array();
		$sql = "SELECT		languageItemID, languageItem, languageID, languageCategoryID, languageItemValue, languageCustomItemValue
			FROM		wcf".WCF_N."_language_item
			WHERE		languageItemID IN (".$languageItemIDs.")
					AND (".$searchCondition.")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($replace !== null) {
				// search and replace
				$matches = 0;
				if ($useRegex) {
					$newValue = preg_replace('/'.$search.'/s'.(!$caseSensitive ? 'i' : ''), $replace, ($row['languageCustomItemValue'] ? $row['languageCustomItemValue'] : $row['languageItemValue']), -1, $matches);
				}
				else {
					if ($caseSensitive) $newValue = StringUtil::replace($search, $replace, ($row['languageCustomItemValue'] ? $row['languageCustomItemValue'] : $row['languageItemValue']), $matches);
					else $newValue = StringUtil::replaceIgnoreCase($search, $replace, ($row['languageCustomItemValue'] ? $row['languageCustomItemValue'] : $row['languageItemValue']), $matches);
				}
				
				if ($matches > 0) {
					// update value
					if (!isset($updatedItems[$row['languageID']])) $updatedItems[$row['languageID']] = array();
					if (!isset($updatedItems[$row['languageID']][$row['languageCategoryID']])) $updatedItems[$row['languageID']][$row['languageCategoryID']] = array();
					$updatedItems[$row['languageID']][$row['languageCategoryID']][$row['languageItem']] = $newValue;
					
					// save matches
					$row['matches'] = $matches;
				}
			}
			
			$results[] = $row;
		}
		
		// save updates
		if (count($updatedItems) > 0) {
			foreach ($updatedItems as $languageID => $categories) {
				$language = new LanguageEditor($languageID);
				
				foreach ($categories as $categoryID => $items) {
					$useCustom = array();
					foreach (array_keys($items) as $item) {
						$useCustom[$item] = 1;
					}
					
					$language->updateItems($items, $categoryID, PACKAGE_ID, $useCustom);
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * Creates a new language category.
	 * 
	 * @param	string		$category
	 * @return	integer		category id
	 */
	public static function createCategory($category) {
		$sql = "INSERT INTO	wcf".WCF_N."_language_category
					(languageCategory)
			VALUES		('".escapeString($category)."')";
		WCF::getDB()->sendQuery($sql);
		
		return WCF::getDB()->getInsertID();
	}
	
	/**
	 * Deletes a language variable.
	 * 
	 * @param	string		$languageItem
	 */
	public static function deleteVariable($languageItem) {
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem = '".escapeString($languageItem)."'";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes empty language categories.
	 * 
	 * @return 	array		$deletedCategories
	 */
	public static function deleteEmptyCategories() {
		$deletedCategories = array();
		
		// delete files
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategoryID NOT IN (
					SELECT	DISTINCT languageCategoryID
					FROM	wcf".WCF_N."_language_item
				)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$deletedCategories[$row['languageCategoryID']] = $row['languageCategory'];
			self::deleteLanguageFiles('*', $row['languageCategory']);
		}
		
		// delete database entries
		$sql = "DELETE FROM	wcf".WCF_N."_language_category
			WHERE		languageCategoryID NOT IN (
						SELECT	DISTINCT languageCategoryID
						FROM	wcf".WCF_N."_language_item
					)";
		WCF::getDB()->sendQuery($sql);
		
		// return list of deleted categories
		return $deletedCategories;
	}
	
	/**
	 * Enables the multilingualism feature for given languages.
	 * 
	 * @param	array		$languageIDs
	 */
	public static function enableMultilingualism($languageIDs = array()) {
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	hasContent = 0";
		WCF::getDB()->sendQuery($sql);
		
		if (count($languageIDs)) {
			$sql = "UPDATE	wcf".WCF_N."_language
				SET	hasContent = 1
				WHERE	languageID IN (".implode(',', $languageIDs).")";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Deletes the language cache.
	 * 
	 * @param 	string		$languageID
	 * @param 	string		$category
	 * @param 	string		$packageID
	 */
	public static function deleteLanguageFiles($languageID = '*', $category = '*', $packageID = '*') {
		$files = @glob(WCF_DIR."language/".$packageID."_".$languageID."_".$category.".php");
		if (is_array($files)) {
			foreach ($files as $filename) {
				@unlink($filename);
			}
		}
	} 
}
?>