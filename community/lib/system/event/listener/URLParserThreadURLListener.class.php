<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/URLBBCode.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/URLParser.class.php');

/**
 * Parses URLs to board threads and posts.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class URLParserThreadURLListener implements EventListener {
	protected $postIDToThreadID = array();
	protected $threads = array();
	protected $threadURLPattern = 'index\.php\?page=Thread&threadID=([0-9]+)';
	protected $postURLPattern = 'index\.php\?page=Thread&postID=([0-9]+)';
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (empty(URLParser::$text)) return;
		
		// reset data
		$this->postIDToThreadID = $this->threads = array();
		$threadIDs = $postIDs = array();
		
		// get page urls
		$pageURLs = URLBBCode::getPageURLs();
		$pageURLs = '(?:'.implode('|', array_map(create_function('$a', 'return preg_quote($a, \'~\');'), $pageURLs)).')';
		
		// build search pattern		
		$threadIDPattern = "~\[url\](".$pageURLs."/?".$this->threadURLPattern.".*?)\[/url\]~i";
		$postIDPattern = "~\[url\](".$pageURLs."/?".$this->postURLPattern.".*?)\[/url\]~i";
		
		// find thread ids
		if (preg_match_all($threadIDPattern, URLParser::$text, $matches)) {
			$threadIDs = $matches[2];
		}
		
		// find post ids
		if (preg_match_all($postIDPattern, URLParser::$text, $matches)) {
			$postIDs = $matches[2];
		}
		
		if (count($threadIDs) > 0 || count($postIDs) > 0) {
			// get thread ids
			if (count($postIDs)) {
				$sql = "SELECT	postID, threadID
					FROM 	wbb".WBB_N."_post
					WHERE 	postID IN (".implode(",", $postIDs).")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->postIDToThreadID[$row['postID']] = $row['threadID'];
					$threadIDs[] = $row['threadID'];
				}
			}
			
			// get accessible boards
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			$boardIDs = Board::getAccessibleBoards();
			if (empty($boardIDs)) return;
			
			// get topics and prefixes :)
			if (count($threadIDs)) {
				// remove duplicates
				$threadIDs = array_unique($threadIDs);
				
				$sql = "SELECT	threadID, prefix, topic
					FROM 	wbb".WBB_N."_thread
					WHERE 	threadID IN (".implode(",", $threadIDs).")
				 		AND boardid IN (0".$boardIDs.")";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->threads[$row['threadID']] = $row;
				}
			}
			
			if (count($this->threads) > 0) {
				// insert topics
				URLParser::$text = preg_replace_callback($threadIDPattern, array($this, 'buildThreadURLCallback'), URLParser::$text);
				URLParser::$text = preg_replace_callback($postIDPattern, array($this, 'buildPostURLCallback'), URLParser::$text);
			}
		}
	}
	
	private function buildThreadURLCallback($match) {
		return $this->buildURLTag($match[1], $match[2]);
	}
	
	private function buildPostURLCallback($match) {
		return $this->buildURLTag($match[1], $match[2], true);
	}
	
	/**
	 * Builds the url bbcode tag.
	 * 
	 * @param	string		$url
	 * @param	integer		$id
	 * @param	boolean		$isPost
	 */
	protected function buildURLTag($url, $id, $isPost = false) {
		if ($isPost) {
			if (isset($this->postIDToThreadID[$id])) $id = $this->postIDToThreadID[$id];
			else $id = 0;
		}
		
		if ($id != 0 && isset($this->threads[$id])) {
			return (!empty($this->threads[$id]['prefix']) ? '[b]'.StringUtil::decodeHTML(WCF::getLanguage()->get(StringUtil::encodeHTML($this->threads[$id]['prefix']))).'[/b] ' : '').'[url=\''.$url.'\']'.$this->threads[$id]['topic'].'[/url]';
		}
		
		return '[url]'.$url.'[/url]';
	}
}
?>