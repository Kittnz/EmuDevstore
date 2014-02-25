<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows popular tags in search form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search.tagging
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class SearchFormTagCloudListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_TAGGING == 1 && SEARCH_ENABLE_TAGS == 1) {
			// include files
			require_once(WCF_DIR.'lib/data/tag/TagCloud.class.php');
			
			// get tags
			$tagCloud = new TagCloud(WCF::getSession()->getVisibleLanguageIDArray());
			$tags = $tagCloud->getTags();
			if (count($tags)) {
				WCF::getTPL()->assign('tags', $tags);
				WCF::getTPL()->append('additionalBoxes1', WCF::getTPL()->fetch('searchFormTagCloud'));
			}
		}
	}
}
?>