<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategory.class.php');

/**
 * Caches the smileys.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderSmileys implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('categories' => array(), 'smileys' => array());
		
		// get categories
		$sql = "SELECT		smiley_category.*,
					(SELECT COUNT(*) AS count FROM wcf".WCF_N."_smiley WHERE smileyCategoryID = smiley_category.smileyCategoryID) AS smileys
			FROM 		wcf".WCF_N."_smiley_category smiley_category
			ORDER BY 	showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['categories'][] = new SmileyCategory(null, $row);
		}
		
		// get smileys
		$sql = "SELECT		*
			FROM 		wcf".WCF_N."_smiley
			ORDER BY 	showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($data['smileys'][$row['smileyCategoryID']])) $data['smileys'][$row['smileyCategoryID']] = array();
			$data['smileys'][$row['smileyCategoryID']][] = new Smiley(null, $row);
		}
		
		return $data;
	}
}
?>