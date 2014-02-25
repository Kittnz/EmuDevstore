<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderBBCodes implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('all' => array(), 'sourceCodes' => array());
		
		// get bbcodes
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_bbcode
			WHERE	disabled = 0";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// mark core BBCodes 
			if (preg_match("/^(align|b|code|color|font|i|img|list|quote|size|s|u|url)$/", $row['bbcodeTag'])) {
				$row['isCoreBBCode'] = true;	
			}
			else $row['isCoreBBCode'] = false;
			
			$data['all'][$row['bbcodeTag']] = $row;
			$data['all'][$row['bbcodeTag']]['attributes'] = array();
			if ($row['sourceCode']) $data['sourceCodes'][] = $row['bbcodeTag'];
		}
		
		// get attributes
		$sql = "SELECT		attribute.*, bbcode.bbcodeTag
			FROM		wcf".WCF_N."_bbcode_attribute attribute
			LEFT JOIN	wcf".WCF_N."_bbcode bbcode
			ON		(bbcode.bbcodeID = attribute.bbcodeID)
			WHERE		bbcode.disabled = 0
					AND bbcode.bbcodeTag IS NOT NULL
			ORDER BY	attribute.attributeNo";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($data['all'][$row['bbcodeTag']]['wysiwyg']) $row['wysiwyg'] = true;
			else $row['wysiwyg'] = false;
			$data['all'][$row['bbcodeTag']]['attributes'][$row['attributeNo']] = $row;
		}
		return $data;
	}
}
?>