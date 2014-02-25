<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadAction.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractSecurePage.class.php');

/**
 * Starts the execution moderation actions on threads.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class ThreadActionPage extends AbstractSecurePage {
	public $boardID = 0;
	public $threadID = 0;
	public $topic = '';
	public $prefix = '';
	public $url = '';
	public $board, $thread;
	public static $validFunctions = array('stick', 'unstick', 'changeTopic', 'mark', 'unmark', 'trash', 'delete', 'recover', 'disable', 'enable', 'close', 'closeAll', 'open', 'unmarkAll', 'deleteAll', 'recoverAll', 'move', 'moveWithLink', 'copy', 'merge', 'copyAndMerge', 'moveAndInsert', 'copyAndInsert', 'changePrefix', 'markAsDone', 'markAsUndone');
	public $reason = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['boardID'])) $this->boardID = intval($_REQUEST['boardID']); 
		if (isset($_REQUEST['threadID'])) $this->threadID = ArrayUtil::toIntegerArray($_REQUEST['threadID']);
		if (isset($_REQUEST['reason'])) {
			$this->reason = StringUtil::trim($_REQUEST['reason']);
			if (CHARSET != 'UTF-8') $this->reason = StringUtil::convertEncoding('UTF-8', CHARSET, $this->reason);
		}
		if (isset($_REQUEST['topic'])) {
			$this->topic = StringUtil::trim($_REQUEST['topic']);
			if (CHARSET != 'UTF-8') $this->topic = StringUtil::convertEncoding('UTF-8', CHARSET, $this->topic);
		}
		if (isset($_REQUEST['prefix'])) {
			$this->prefix = $_REQUEST['prefix'];
			if (CHARSET != 'UTF-8') $this->prefix = StringUtil::convertEncoding('UTF-8', CHARSET, $this->prefix);
		}
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
		
		if (!is_array($this->threadID) && $this->threadID != 0) {
			$this->thread = new ThreadEditor($this->threadID);
			$this->boardID = $this->thread->boardID;
			
			if ($this->thread->movedThreadID) {
				$movedThread = new ThreadEditor($this->thread->movedThreadID);
				$movedThread->enter();
			}
			else {
				$this->thread->enter();
			}
		}
		
		if ($this->boardID != 0) {
			$this->board = new BoardEditor($this->boardID);
			if ($this->thread == null) {
				$this->board->enter();
			}
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		if (in_array($this->action, self::$validFunctions)) {
			$threadAction = new ThreadAction($this->board, $this->thread, null, $this->threadID, $this->topic, $this->prefix, $this->url, $this->reason);
			$threadAction->{$this->action}();
		}
	}
}
?>