<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * TemplatePack represents a template pack. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category 	Community Framework
 */
class TemplatePack extends DatabaseObject {
	protected static $templatePackStructure = null;
	protected static $selectList = null;

	/**
	 * Creates a new TemplatePack object.
	 * 
	 * @param	integer		$templatePackID
	 * @param	array<mixed>	$row
	 */
	public function __construct($templatePackID, $row = null) {
		if ($templatePackID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_template_pack
				WHERE	templatePackID = ".$templatePackID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns a list of installed template packs.
	 * 
	 * @return	array
	 */
	public static function getTemplatePacks() {
		$templatePacks = array();
		$sql = "SELECT		templatePackID, templatePackName
			FROM		wcf".WCF_N."_template_pack
			ORDER BY	templatePackName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$templatePacks[$row['templatePackID']] = $row['templatePackName'];
		}
		
		return $templatePacks;
	}
	
	/**
	 * Creates a select list.
	 * 
	 * @param	array<intger>	$ignore
	 * @return	array
	 */
	public static function getSelectList($ignore = array()) {
		if (self::$templatePackStructure === null) {
			self::$templatePackStructure = array();
			$sql = "SELECT		templatePackID, templatePackName, parentTemplatePackID
				FROM		wcf".WCF_N."_template_pack
				ORDER BY	templatePackName";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				self::$templatePackStructure[$row['parentTemplatePackID']][] = new TemplatePack(null, $row);
			}
			
		}
		
		self::$selectList = array();
		self::makeSelectList(0, 0, $ignore);
		
		return self::$selectList;
	}
	
	/**
	 * Generates the select list.
	 * 
	 * @param	integer		$parentID		id of the parent template pack
	 * @param	integer		$depth 			current list depth
	 * @param	array		$ignore			list of template pack ids to ignore in result
	 */
	protected static function makeSelectList($parentID = 0, $depth = 0, $ignore = array()) {
		if (!isset(self::$templatePackStructure[$parentID])) return;
		
		foreach (self::$templatePackStructure[$parentID] as $templatePack) {
			if (!empty($ignore) && in_array($templatePack->templatePackID, $ignore)) continue;
			
			// we must encode html here because the htmloptions plugin doesn't do it
			$title = StringUtil::encodeHTML($templatePack->templatePackName);
			if ($depth > 0) $title = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth). ' ' . $title;
			
			self::$selectList[$templatePack->templatePackID] = $title;
			self::makeSelectList($templatePack->templatePackID, $depth + 1, $ignore);
		}
	}
}
?>