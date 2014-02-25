<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/style/Style.class.php');

/**
 * Caches the styles and style variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderStyle implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('default' => 0, 'styles' => array(), 'packages' => array());

		// get all styles
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style
			ORDER BY	styleName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['isDefault']) $data['default'] = $row['styleID'];
			$row['variables'] = array();
			
			// get variable
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style_variable
				WHERE	styleID = ".$row['styleID'];
			$result2 = WCF::getDB()->sendQuery($sql);
			while ($row2 = WCF::getDB()->fetchArray($result2)) {
				$row['variables'][$row2['variableName']] = $row2['variableValue'];
			}
			
			$data['styles'][$row['styleID']] = new Style(null, $row);
		}
		
		// get style to packages
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style_to_package
			ORDER BY	packageID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($data['packages'][$row['packageID']])) {
				$data['packages'][$row['packageID']] = array('default' => 0, 'disabled' => array());
			}
			
			if ($row['isDefault']) {
				$data['packages'][$row['packageID']]['default'] = $row['styleID'];
			}
			$data['packages'][$row['packageID']]['disabled'][$row['styleID']] = $row['disabled'];
		}
		
		return $data;
	}
}
?>