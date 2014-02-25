<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the rule condition types for private messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.cache
 * @category 	Community Framework (commercial)
 */
class CacheBuilderPMRuleConditionTypes implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();
		
		$sql = "SELECT		condition_type.*, package.packageDir
			FROM		wcf".WCF_N."_pm_rule_condition_type condition_type
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = condition_type.packageID)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['ruleConditionTypeClassName'] = StringUtil::getClassName($row['ruleConditionTypeClassFile']);
			$data[] = $row;
		}
		
		return $data;
	}
}
?>