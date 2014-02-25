<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the merged group options of a group combination.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderGroupPermissions implements CacheBuilder {
	protected $typeObjects = array();
	
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID, $groupIDs) = explode('-', $cacheResource['cache']);
		$data = array();
		
		// get all options and filter options with low priority
		if ($packageID == 0) {
			// during the installation of the package wcf
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_group_option
				WHERE 		packageID = 0";
		}
		else {
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_group_option option_table,
						wcf".WCF_N."_package_dependency package_dependency
				WHERE 		option_table.packageID = package_dependency.dependency
						AND package_dependency.packageID = ".$packageID."
				ORDER BY	package_dependency.priority";
		}
		$result = WCF::getDB()->sendQuery($sql);
		$options = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		if (count($options) > 0) {
			// get needed options
			$sql = "SELECT		option_table.optionName, option_table.optionType, option_value.optionValue
				FROM		wcf".WCF_N."_group_option_value option_value
				LEFT JOIN	wcf".WCF_N."_group_option option_table
				ON		(option_table.optionID = option_value.optionID)
				WHERE		option_value.groupID IN (".$groupIDs.")
						AND option_value.optionID IN (".implode(',', $options).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($data[$row['optionName']])) {
					$data[$row['optionName']] = array('type' => $row['optionType'], 'values' => array());
				}
				
				$data[$row['optionName']]['values'][] = $row['optionValue'];
			}
			
			// merge values
			foreach ($data as $optionName => $option) {
				if (count($option['values']) == 1) {
					$result = $option['values'][0];
				}
				else {
					$typeObj = $this->getTypeObject($option['type']);
					$result = $typeObj->merge($option['values']);
				}
				
				// unset false values
				if ($result === false) {
					unset($data[$optionName]);
				}
				else {
					$data[$optionName] = $result;
				}
			}
		}
		
		$data['groupIDs'] = $groupIDs;
		return $data;
	}
	
	/**
	 * Returns an object of the requested group option type.
	 * 
	 * @param	string			$type
	 * @return	OptionType
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'GroupOptionType'.ucfirst(strtolower($type));
			$classPath = WCF_DIR.'lib/acp/group/'.$className.'.class.php';
			
			// include class file
			if (!file_exists($classPath)) {
				throw new SystemException("unable to find class file '".$classPath."'", 11000);
			}
			require_once($classPath);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
?>