<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardList.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows the start page of the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class IndexPage extends AbstractPage {
	public $templateName = 'index';
	public $tags = array();
	
	/**
	 * @see Page::assignVariables();
	 */
	public function assignVariables() {
		parent::assignVariables();

		$this->renderBoards();
		if (MODULE_TAGGING && THREAD_ENABLE_TAGS && INDEX_ENABLE_TAGS) {
			$this->readTags();
		}
		if (INDEX_ENABLE_STATS) {
			$this->renderStats();
		}
		if (MODULE_USERS_ONLINE && INDEX_ENABLE_ONLINE_LIST) {
			$this->renderOnlineList();
		}
		WCF::getTPL()->assign(array(
			'selfLink' => 'index.php?page=Index'.SID_ARG_2ND_NOT_ENCODED,
			'allowSpidersToIndexThisPage' => true,
			'tags' => $this->tags
		));
		
		if (WCF::getSession()->spiderID) {
			if ($lastChangeTime = @filemtime(WBB_DIR.'cache/cache.stat.php')) {
				@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastChangeTime).' GMT');
			}
		}
	}
	
	/**
	 * Renders the forum stats on the index page.
	 */
	protected function renderStats() {
		$stats = WCF::getCache()->get('stat');
		WCF::getTPL()->assign('stats', $stats);
	}
	
	/**
	 * @see BoardList::renderBoards()
	 */
	protected function renderBoards() {
		$boardList = new BoardList();
		$boardList->maxDepth = BOARD_LIST_DEPTH;
		$boardList->renderBoards();
	}
	
	/**
	 * Wrapper for UsersOnlineList->renderOnlineList()
	 * @see UsersOnlineList::renderOnlineList()
	 */
	protected function renderOnlineList() {
		require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineList.class.php');
		$usersOnlineList = new UsersOnlineList('', true);
		$usersOnlineList->renderOnlineList();
		
		// check users online record
		$usersOnlineTotal = (USERS_ONLINE_RECORD_NO_GUESTS ? $usersOnlineList->usersOnlineMembers : $usersOnlineList->usersOnlineTotal);
		if ($usersOnlineTotal > USERS_ONLINE_RECORD) {
			// save new users online record
			$sql = "UPDATE	wcf".WCF_N."_option
				SET	optionValue = IF(".$usersOnlineTotal." > optionValue, ".$usersOnlineTotal.", optionValue)
				WHERE	optionName = 'users_online_record'
					AND packageID = ".PACKAGE_ID;
			WCF::getDB()->registerShutdownUpdate($sql);
			
			// save new record time
			if (TIME_NOW > USERS_ONLINE_RECORD_TIME) {
				$sql = "UPDATE	wcf".WCF_N."_option
					SET	optionValue = IF(".TIME_NOW." > optionValue, ".TIME_NOW.", optionValue)
					WHERE	optionName = 'users_online_record_time'
						AND packageID = ".PACKAGE_ID;
				WCF::getDB()->registerShutdownUpdate($sql);
			}
			
			// reset options file
			require_once(WCF_DIR.'lib/acp/option/Options.class.php');
			Options::resetFile();
		}
	}
	
	/**
	 * Reads the tags of this board.
	 */
	protected function readTags() {
		// include files
		require_once(WCF_DIR.'lib/data/tag/TagCloud.class.php');
		
		// get tags
		$tagCloud = new TagCloud(WCF::getSession()->getVisibleLanguageIDArray());
		$this->tags = $tagCloud->getTags();
	}
}
?>