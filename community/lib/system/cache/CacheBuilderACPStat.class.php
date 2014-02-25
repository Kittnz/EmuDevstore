<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the acp statistics.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.cache
 * @category 	Burning Board
 */
class CacheBuilderACPStat implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array();
		
		// get installation age
		$installationAge = (TIME_NOW - INSTALL_DATE) / 86400;
		if ($installationAge < 1) $installationAge = 1;
		
		// members
		$sql = "SELECT	COUNT(*) AS members
			FROM	wcf".WCF_N."_user";
		$row = WCF::getDB()->getFirstRow($sql);
		$data['members'] = $row['members'];
		
		// threads
		$sql = "SELECT	COUNT(*) AS threads
			FROM	wbb".WBB_N."_thread";
		$row = WCF::getDB()->getFirstRow($sql);
		$data['threads'] = $row['threads'];
		$data['threadsPerDay'] = $row['threads'] / $installationAge;
		
		// posts
		$sql = "SELECT	COUNT(*) AS posts
			FROM	wbb".WBB_N."_post";
		$row = WCF::getDB()->getFirstRow($sql);
		$data['posts'] = $row['posts'];
		$data['postsPerDay'] = $row['posts'] / $installationAge;
		
		// attachments
		$sql = "SELECT	COUNT(*) AS attachments,
				IFNULL((SUM(attachmentSize) + SUM(thumbnailSize)), 0) AS attachmentsSize
			FROM	wcf".WCF_N."_attachment
			WHERE	packageID = ".PACKAGE_ID;
		$row = WCF::getDB()->getFirstRow($sql);
		$data['attachments'] = $row['attachments'];
		$data['attachmentsSize'] = $row['attachmentsSize'];
		
		// private messages
		$sql = "SELECT	COUNT(*) AS privateMessages
			FROM	wcf".WCF_N."_pm";
		$row = WCF::getDB()->getFirstRow($sql);
		$data['privateMessages'] = $row['privateMessages'];
		
		// database entries and size
		$data['databaseSize'] = 0;
		$data['databaseEntries'] = 0;
		$sql = "SHOW TABLE STATUS";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$data['databaseSize'] += $row['Data_length'] + $row['Index_length'];
			$data['databaseEntries'] += $row['Rows'];
		}
		
		return $data;
	}
}
?>