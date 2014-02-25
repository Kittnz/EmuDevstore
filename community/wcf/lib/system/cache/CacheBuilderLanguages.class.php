<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches languages, language to packages relation, package to languages relation
 * and the id of the default language. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderLanguages implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$languageToPackages = array();
		$data = array(
			'languages' => array(), 
			'packages' => array(),
			'default' => 0,
			'categories' => array()
		);
		
		// get language to packages
		$sql = "SELECT 		package.languageID, package.packageID
			FROM		wcf".WCF_N."_language_to_packages package
			LEFT JOIN	wcf".WCF_N."_language language
			ON		(language.languageID = package.languageID)
			ORDER BY	language.isDefault DESC, language.languageCode";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// package to languages
			if (!isset($data['packages'][$row['packageID']])) {
				$data['packages'][$row['packageID']] = array();
			}
			$data['packages'][$row['packageID']][] = $row['languageID'];
			
			// language to packages
			if (!isset($languageToPackages[$row['languageID']])) {
				$languageToPackages[$row['languageID']] = array();
			}
			$languageToPackages[$row['languageID']][] = $row['packageID'];
		}
		
		// get languages
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_language";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// language data
			$data['languages'][$row['languageID']] = $row;
			
			// language to packages
			if (!isset($languageToPackages[$row['languageID']])) {
				$languageToPackages[$row['languageID']] = array();
			}
			$data['languages'][$row['languageID']]['packages'] = $languageToPackages[$row['languageID']];
			
			// default language
			if ($row['isDefault']) {
				$data['default'] = $row['languageID'];
			}
		}
		
		// get language categories
		$sql = "SELECT 	*
			FROM	wcf".WCF_N."_language_category";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// package to languages
			$data['categories'][$row['languageCategory']] = $row;
		}

		return $data;
	}
}
?>