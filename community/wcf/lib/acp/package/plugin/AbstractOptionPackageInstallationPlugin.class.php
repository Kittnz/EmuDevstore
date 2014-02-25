<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * Default implementation of some functions for PackageInstallationPlugin using options.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
abstract class AbstractOptionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * Installs option categories and options.
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}

		// create an array with the import and delete instructions from the xml file
		$optionsXML = $xml->getElementTree('data');

		// install or uninstall categories and options.
		foreach ($optionsXML['children'] as $key => $block) {
			if (count($block['children'])) {
				// handle the import instructions
				if ($block['name'] == 'import') {
					// loop through categories and options
					foreach ($block['children'] as $child) {
						// handle categories					
						if ($child['name'] == 'categories') {
							// loop through all categories
							foreach ($child['children'] as $category) {
								
								// check required category name
								if (!isset($category['attrs']['name'])) {
									throw new SystemException("Required 'name' attribute for option category is missing", 13023); 
								}
								
								// default values
								$categoryName = $parentCategoryName = $permissions = $options = '';
								$showOrder = null;
								
								// make xml tags-names (keys in array) to lower case
								$this->keysToLowerCase($category);
								
								// get category data from children (parent, showorder, icon and menuicon)
								foreach ($category['children'] as $data) {
									if (!isset($data['cdata'])) continue;
									$category[$data['name']] = $data['cdata'];
								}
								
								// get and secure values
								$categoryName =  escapeString($category['attrs']['name']);
								if (isset($category['permissions'])) $permissions = $category['permissions'];
								if (isset($category['options'])) $options = $category['options'];
								if (isset($category['parent'])) $parentCategoryName =  escapeString($category['parent']);
								if (!empty($category['showorder'])) $showOrder = intval($category['showorder']);
								if ($showOrder !== null || $this->installation->getAction() != 'update') {
									$showOrder = $this->getShowOrder($showOrder, $parentCategoryName, 'parentCategoryName', '_category');
								}
								
								// if a parent category was set and this parent is not in database 
								// or it is a category from a package from other package environment: don't install further.
								if ($parentCategoryName != '') {
									$sql = "SELECT	COUNT(categoryID) AS count
											FROM	wcf".WCF_N."_".$this->tableName."_category
											WHERE	categoryName = '".escapeString($parentCategoryName)."'";
											/*	AND packageID IN (
													SELECT	dependency
													FROM	wcf".WCF_N."_package_dependency
													WHERE	packageID = ".$this->installation->getPackageID()."
												)";*/
									$parentCategoryCount = WCF::getDB()->getFirstRow($sql);

									// unable to find parent category in dependency-packages: abort installation
									if ($parentCategoryCount['count'] == 0) {
										throw new SystemException("Unable to find parent 'option category' with name '".$parentCategoryName."' for category with name '".$categoryName."'.", 13011);
									}
								}
								
								// save category
								$categoryData = array(
									'categoryName' => $categoryName,
									'parentCategoryName' => $parentCategoryName,
									'showOrder' => $showOrder,
									'permissions' => $permissions,
									'options' => $options
								);
								$this->saveCategory($categoryData, $category);
							}
						}
						// handle options
						elseif ($child['name'] == 'options') {
							// <option> 
							foreach ($child['children'] as $option) {
								// extract <category> <optiontype> <optionvalue> <visible> etc
								foreach ($option['children'] as $_child) {
									$option[$_child['name']] = $_child['cdata'];
								}
								
								// convert character encoding
								if (CHARSET != 'UTF-8') {
									if (isset($option['defaultvalue'])) {
										$option['defaultvalue'] = StringUtil::convertEncoding('UTF-8', CHARSET, $option['defaultvalue']);
									}
									if (isset($option['selectoptions'])) {
										$option['selectoptions'] = StringUtil::convertEncoding('UTF-8', CHARSET, $option['selectoptions']);
									}
								}
								
								// check required category name
								if (!isset($option['categoryname'])) {
									throw new SystemException("Required category for option is missing", 13023); 
								}
								$categoryName = escapeString($option['categoryname']);
								
								// store option name
								$option['name'] = $option['attrs']['name'];
								
								// children info already stored with name => cdata
								// shrink array 
								unset($option['children']);
								
								if (!preg_match("/^[\w-\.]+$/", $option['name'])) {
									$matches = array();
									preg_match_all("/(\W)/", $option['name'], $matches);
									throw new SystemException("The user option '".$option['name']."' has at least one non-alphanumeric character (underscore is permitted): (".implode("), ( ", $matches[1]).").", 13024); 
								}
								$this->saveOption($option, $categoryName);
							}
						}				
					}
				}
				// handle the delete instructions
				else if ($block['name'] == 'delete' && $this->installation->getAction() == 'update') {
					$optionNames = '';
					$categoryNames = '';
					foreach ($block['children'] as $deleteTag) {
						// check required attributes
						if (!isset($deleteTag['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for '".$deleteTag['name']."'-tag is missing", 13023);
						}
						
						if ($deleteTag['name'] == 'option') {
							// build optionnames string
							if (!empty($optionNames)) $optionNames .= ',';
							$optionNames .= "'".escapeString($deleteTag['attrs']['name'])."'";
						}
						elseif ($deleteTag['name'] == 'optioncategory') {
							// build categorynames string
							if (!empty($categoryNames)) $categoryNames .= ',';
							$categoryNames .= "'".escapeString($deleteTag['attrs']['name'])."'";
						}
					}
					// delete options
					if (!empty($optionNames)) {
						$this->deleteOptions($optionNames);
					}
					// elete categories
					if (!empty($categoryNames)) {
						$this->deleteCategories($categoryNames);
					}
				}
			}
		}
	}
	
	/**
	 * @see	PackageInstallationPlugin::hasUninstall()
	 */
	public function hasUninstall() {
		$hasUninstallOptions = parent::hasUninstall();
		$sql = "SELECT 	COUNT(categoryID) AS count
			FROM 	wcf".WCF_N."_".$this->tableName."_category
			WHERE	packageID = ".$this->installation->getPackageID();
		$categoryCount = WCF::getDB()->getFirstRow($sql);
		return ($hasUninstallOptions || $categoryCount['count'] > 0);
	}
	
	/**
	 * Uninstalls option categories and options.
	 */
	public function uninstall() {
		// delete options
		parent::uninstall();
		
		// delete categories
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."_category
			WHERE		packageID = ".$this->installation->getpackageID();
		WCF::getDB()->sendQuery($sql);
	}

	/**
	 * Installs option categories.
	 * 
	 * @param 	array		$category
	 * @param	XML		$categoryXML
	 */
	protected function saveCategory($category, $categoryXML = null) {
		// search existing category
		$sql = "SELECT	categoryID
			FROM	wcf".WCF_N."_".$this->tableName."_category
			WHERE	categoryName = '".escapeString($category['categoryName'])."'
				AND packageID = ".$this->installation->getPackageID();
		$row = WCF::getDB()->getFirstRow($sql);
		if (empty($row['categoryID'])) {
			// insert new category
			$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."_category
						(packageID, categoryName, parentCategoryName, permissions, options".($category['showOrder'] !== null ? ",showOrder" : "").")
				VALUES		(".$this->installation->getPackageID().",
						'".escapeString($category['categoryName'])."',
						'".escapeString($category['parentCategoryName'])."',
						'".escapeString($category['permissions'])."',
						'".escapeString($category['options'])."'
						".($category['showOrder'] !== null ? ",".$category['showOrder'] : "").")";
			WCF::getDB()->sendQuery($sql);
		}
		else {
			// update existing category
			$sql = "UPDATE 	wcf".WCF_N."_".$this->tableName."_category
				SET	parentCategoryName = '".escapeString($category['parentCategoryName'])."',
					permissions = '".escapeString($category['permissions'])."',
					options = '".escapeString($category['options'])."'
					".($category['showOrder'] !== null ? ",showOrder = ".$category['showOrder'] : "")."
				WHERE	categoryID = ".$row['categoryID'];
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Installs options.
	 * 
	 * @param 	array 		$option
	 * @param 	string		$categoryName
	 * @param	integer		$existingOptionID
	 */
	protected abstract function saveOption($option, $categoryName, $existingOptionID = 0);
	
	/**
	 * Deletes options.
	 * 
	 * @param 	string 		$optionNames 
	 */
	protected function deleteOptions($optionNames) {
		// delete options
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		optionName IN (".$optionNames.")
			AND 		packageID = ".$this->installation->getPackageID();
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes option categories.
	 * 
	 * @param 	string 		$categoryNames 
	 */
	protected function deleteCategories($categoryNames) {
		// delete options from the categories
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		categoryName IN (".$categoryNames.")";
		WCF::getDB()->sendQuery($sql);
						
		// delete categories
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."_category
			WHERE		categoryName IN (".$categoryNames.")
			AND 		packageID = ".$this->installation->getPackageID();
		WCF::getDB()->sendQuery($sql);
	}
}
?>