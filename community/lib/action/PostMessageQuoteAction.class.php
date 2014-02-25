<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractMessageQuoteAction.class.php');
require_once(WCF_DIR.'lib/data/message/multiQuote/MultiQuoteManager.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

/**
 * Saves quotes of a post.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	action
 * @category 	Burning Board
 */
class PostMessageQuoteAction extends AbstractMessageQuoteAction {
	/**
	 * post editor object
	 *
	 * @var	PostEditor
	 */
	public $post = null;
	
	/**
	 * thread editor object
	 *
	 * @var	ThreadEditor
	 */
	public $thread = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get post
		$this->post = new PostEditor($this->objectID);
		if (!$this->post->postID) {
			throw new IllegalLinkException();
		}
		// get thread
		$this->thread = new ThreadEditor($this->post->threadID);
		$this->thread->enter();
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ((!is_array($this->text) && $this->text == '') || (is_array($this->text) && !count($this->text))) {
			$this->text = $this->post->message;
		}
		if (!is_array($this->text)) {
			$this->text = array($this->text);
		}
		
		// store quotes
		foreach ($this->text as $key => $string) {
			MultiQuoteManager::storeQuote($this->objectID, 'post', $string, $this->post->username, 'index.php?page=Thread&postID='.$this->objectID.'#post'.$this->objectID, $this->post->threadID, ((strlen($key) == 40 && preg_match('/^[a-f0-9]+$/', $key)) ? $key : ''));
		}
		MultiQuoteManager::saveStorage();
		$this->executed();
	}
}
?>