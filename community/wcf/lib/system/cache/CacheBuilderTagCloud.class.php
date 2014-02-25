<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/tag/Tag.class.php');

/**
 * Caches the tag cloud.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderTagCloud implements CacheBuilder {
	/**
	 * list of tags
	 * 
	 * @var	array<Tag>
	 */
	protected $tags = array();

	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID, $languageIDs) = explode('-', $cacheResource['cache']);
		$data = array();

		// get all taggable types
		$sql = "SELECT		taggable.taggableID, taggable.name
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_tag_taggable taggable
			WHERE 		taggable.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['name']] = $row['taggableID'];
		}

		if (count($itemIDs) > 0) {
			// get tag ids
			$tagIDs = array();
			$sql = "SELECT		COUNT(*) AS counter, object.tagID
				FROM 		wcf".WCF_N."_tag_to_object object
				WHERE 		object.taggableID IN (".implode(',', $itemIDs).")
						AND object.languageID IN (".$languageIDs.")
				GROUP BY 	object.tagID
				ORDER BY 	counter DESC";
			$result = WCF::getDB()->sendQuery($sql, 500);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$tagIDs[$row['tagID']] = $row['counter'];
			}
			
			// get tags
			if (count($tagIDs)) {
				$sql = "SELECT		name, tagID
					FROM		wcf".WCF_N."_tag
					WHERE		tagID IN (".implode(',', array_keys($tagIDs)).")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$row['counter'] = $tagIDs[$row['tagID']];
					$this->tags[StringUtil::toLowerCase($row['name'])] = new Tag(null, $row);
				}

				// sort by counter
				uasort($this->tags, array('self', 'compareTags'));
							
				$data = $this->tags;
			}
		}

		return $data;
	}

	protected static function compareTags($tagA, $tagB) {
		if ($tagA->counter > $tagB->counter) return -1;
		if ($tagA->counter < $tagB->counter) return 1;
		return 0;
	}
}
?>