<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Extends the search form by forum specific search options.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class ExtendedSearchFormListener implements EventListener {
	public $findThreads = 0;
	public $findUserThreads = 0;

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'readParameters') {
			// handle special search options here
			$action = '';
			if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
			if (empty($action)) return;
			
			// get accessible board ids
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$boardIDArray = Board::getAccessibleBoardIDArray(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
			foreach ($boardIDArray as $key => $boardID) {
				if (WCF::getUser()->isIgnoredBoard($boardID)) {
					unset($boardIDArray[$key]);
				}
				else if (!Board::getBoard($boardID)->searchable) {
					unset($boardIDArray[$key]);
				}
			}
			if (!count($boardIDArray)) return;
			
			switch ($action) {
				case 'unread':
					$sql = "SELECT		thread.threadID
						FROM		wbb".WBB_N."_thread thread
						WHERE		thread.boardID IN (".implode(',', $boardIDArray).")
								AND thread.lastPostTime > ".WCF::getUser()->getLastMarkAllAsReadTime()."
								".(WCF::getUser()->userID ? "
								AND thread.lastPostTime > IFNULL((
									SELECT	lastVisitTime
									FROM 	wbb".WBB_N."_thread_visit
									WHERE 	threadID = thread.threadID
										AND userID = ".WCF::getUser()->userID."
								), 0)
								AND thread.lastPostTime > IFNULL((
									SELECT	lastVisitTime
									FROM 	wbb".WBB_N."_board_visit
									WHERE 	boardID = thread.boardID
										AND userID = ".WCF::getUser()->userID."
								), 0)
								" : '')."
								AND thread.isDeleted = 0
								AND thread.isDisabled = 0
								".(count(WCF::getSession()->getVisibleLanguageIDArray()) ? " AND languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
								AND thread.movedThreadID = 0
						ORDER BY	thread.lastPostTime DESC";
					break;
					
				case 'newPostsSince':
					$since = TIME_NOW;
					if (isset($_REQUEST['since'])) $since = intval($_REQUEST['since']);
					
					$sql = "SELECT		thread.threadID
						FROM		wbb".WBB_N."_thread thread
						WHERE		thread.boardID IN (".implode(',', $boardIDArray).")
								AND thread.lastPostTime > ".$since."
								AND thread.isDeleted = 0
								AND thread.isDisabled = 0
								".(count(WCF::getSession()->getVisibleLanguageIDArray()) ? " AND languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
								AND thread.movedThreadID = 0
						ORDER BY	thread.lastPostTime DESC";
				
					break;
					
				case 'unreplied':
					$sql = "SELECT		threadID
						FROM		wbb".WBB_N."_thread
						WHERE		boardID IN (".implode(',', $boardIDArray).")
								AND isDeleted = 0
								AND isDisabled = 0
								AND movedThreadID = 0
								".(count(WCF::getSession()->getVisibleLanguageIDArray()) ? " AND languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
								AND replies = 0
						ORDER BY	lastPostTime DESC";
					break;
						
				case '24h':
					$sql = "SELECT		threadID
						FROM		wbb".WBB_N."_thread
						WHERE		boardID IN (".implode(',', $boardIDArray).")
								AND lastPostTime > ".(TIME_NOW - 86400)."
								AND isDeleted = 0
								AND isDisabled = 0
								".(count(WCF::getSession()->getVisibleLanguageIDArray()) ? " AND languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
								AND movedThreadID = 0
						ORDER BY	lastPostTime DESC";
					break;
					
				default: return;
			}
			
			// build search hash
			$searchHash = StringUtil::getHash($sql);
			
			// execute query
			$matches = array();
			$result = WCF::getDB()->sendQuery($sql, 1000);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$matches[] = array('messageID' => $row['threadID'], 'messageType' => 'post');
			}
			
			// result is empty
			if (count($matches) == 0) {
				throw new NamedUserException(WCF::getLanguage()->get('wbb.search.error.noMatches'));
			}
			
			// save result in database
			$searchData = array('packageID' => PACKAGE_ID, 'query' => '', 'result' => $matches, 'additionalData' => array('post' => array('findThreads' => 1)), 'sortOrder' => 'DESC', 'sortField' => 'time', 'types' => array('post'));
			$searchData = serialize($searchData);
			
			$sql = "INSERT INTO	wcf".WCF_N."_search
						(userID, searchData, searchDate, searchType, searchHash)
				VALUES		(".WCF::getUser()->userID.",
						'".escapeString($searchData)."',
						".TIME_NOW.",
						'messages',
						'".$searchHash."')";
			WCF::getDB()->sendQuery($sql);
			$searchID = WCF::getDB()->getInsertID();
			
			// forward to result page
			HeaderUtil::redirect('index.php?form=Search&searchID='.$searchID.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
		else if ($eventName == 'readFormParameters') {
			if (isset($_POST['findThreads'])) $this->findThreads = intval($_POST['findThreads']);
			if (isset($_REQUEST['findUserThreads'])) $this->findUserThreads = intval($_REQUEST['findUserThreads']);
			if ($this->findUserThreads == 1) $this->findThreads = 1;
			
			// handle findThreads option
			if ($this->findThreads == 1 && (!count($eventObj->types) || in_array('post', $eventObj->types))) {
				// remove all other searchable message types
				// findThreads only supports post search
				$eventObj->types = array('post');
			}
			else {
				$this->findThreads = /*$_POST['findThreads'] =*/ 0;
			}
		}
		else if ($eventName == 'assignVariables') {
			if ($eventObj instanceof SearchResultPage) {
				$html = '<div class="floatedElement">
						<label for="findThreads">' . WCF::getLanguage()->get('wbb.search.results.display') . '</label>
						<select name="findThreads" id="findThreads">
							<option value="0">' . WCF::getLanguage()->get('wbb.search.results.display.post') . '</option>
							<option value="1"' . ($eventObj->additionalData['post']['findThreads'] == 1 ? ' selected="selected"' : '') . '>' . WCF::getLanguage()->get('wbb.search.results.display.thread') . '</option>
						</select>
					</div>';
				WCF::getTPL()->append('additionalDisplayOptions', $html);
			}
			else {
				$html = '<div class="floatedElement">
						<label for="findThreads">' . WCF::getLanguage()->get('wbb.search.results.display') . '</label>
						<select name="findThreads" id="findThreads">
							<option value="0"' . ($this->findThreads == 0 ? ' selected="selected"' : '') . '>' . WCF::getLanguage()->get('wbb.search.results.display.post') . '</option>
							<option value="1"' . ($this->findThreads == 1 ? ' selected="selected"' : '') . '>' . WCF::getLanguage()->get('wbb.search.results.display.thread') . '</option>
						</select>
					</div>';
				WCF::getTPL()->append('additionalDisplayOptions', $html);
				WCF::getTPL()->append('additionalAuthorOptions', '<label><input type="checkbox" name="findUserThreads" value="1"'.($this->findUserThreads == 1 ? ' checked="checked"' : '').'/> '.WCF::getLanguage()->get('wbb.search.findUserThreads').'</label>');
			}
		}
	}
}
?>