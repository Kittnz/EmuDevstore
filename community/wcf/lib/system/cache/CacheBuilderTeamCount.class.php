<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the number of team members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.membersList.team
 * @subpackage	system.cache
 * @category 	Community Framework (commercial)
 */
class CacheBuilderTeamCount implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$sql = "SELECT		COUNT(*) AS count
 			FROM 		wcf".WCF_N."_group usergroup
 			LEFT JOIN 	wcf".WCF_N."_user_to_groups user_to_groups 
			ON		(user_to_groups.groupID = usergroup.groupID)
			LEFT JOIN 	wcf".WCF_N."_user user_table
			ON		(user_table.userID = user_to_groups.userID)
			WHERE 		usergroup.showOnTeamPage = 1
					AND user_table.userID IS NOT NULL";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
}
?>