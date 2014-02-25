<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * Caches the amount of members, posts and threads, the newest member and the posts per day.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderStat implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();
		
		// amount of members
		$sql = "SELECT 	COUNT(*) AS amount
			FROM 	wcf".WCF_N."_user";
		$result = WCF::getDB()->getFirstRow($sql);
		$data['members'] = $result['amount'];
		
		// amount of posts
		$sql = "SELECT 	COUNT(*) AS amount
			FROM 	wbb".WBB_N."_post";
		$result = WCF::getDB()->getFirstRow($sql);
		$data['posts'] = $result['amount'];
		
		// amount of threads
		$sql = "SELECT 	COUNT(*) AS amount
			FROM 	wbb".WBB_N."_thread";
		$result = WCF::getDB()->getFirstRow($sql);
		$data['threads'] = $result['amount'];
		
		// newest member
		$sql = "SELECT 		*
			FROM 		wcf".WCF_N."_user
			ORDER BY 	userID DESC";
		$result = WCF::getDB()->getFirstRow($sql);
		$data['newestMember'] = new User(null, $result);
		
		// posts per day
		$days = ceil((TIME_NOW - INSTALL_DATE) / 86400);
		if ($days <= 0) $days = 1;
		$data['postsPerDay'] = $data['posts'] / $days;
		
		return $data;
	}
}
?>