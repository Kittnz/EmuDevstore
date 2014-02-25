<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the rule actions for private messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.cache
 * @category 	Community Framework (commercial)
 */
class CacheBuilderPMRuleActions implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();
		
		$sql = "SELECT		action.*, package.packageDir
			FROM		wcf".WCF_N."_pm_rule_action action
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = action.packageID)";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['ruleActionClassName'] = StringUtil::getClassName($row['ruleActionClassFile']);
			$data[] = $row;
		}
		
		return $data;
	}
}
?>