<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/Cronjob.class.php');

/**
 * Reads rss news feeds.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.feed.reader
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
class FeedReaderCronjob implements Cronjob {
	/**
	 * @see Cronjob::execute()
	 */
	public function execute($data) {
		require_once(WCF_DIR.'lib/data/feed/FeedReaderSource.class.php');
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_feed_source
			WHERE	lastUpdate + updateCycle < ".TIME_NOW;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$source = new FeedReaderSource($row);
			$source->update();
		}
	}
}
?>