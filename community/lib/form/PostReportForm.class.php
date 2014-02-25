<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/ThreadEditor.class.php');
require_once(WBB_DIR.'lib/data/board/BoardEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Shows the post report form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class PostReportForm extends AbstractForm {
	// system
	public $templateName = 'postReport';
	
	/**
	 * post id
	 * 
	 * @var	integer
	 */
	public $postID = 0;
	
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
	 * board editor object
	 * 
	 * @var	BoardEditor
	 */
	public $board = null;
	
	/**
	 * report id
	 * 
	 * @var	integer
	 */
	public $reportID = 0;
	
	/**
	 * report text
	 * 
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['postID'])) $this->postID = intval($_REQUEST['postID']);
		
		$this->post = new PostEditor($this->postID);
		$this->thread = new ThreadEditor($this->post->threadID);
		$this->board = new BoardEditor($this->thread->boardID);
		
		$this->thread->enter($this->board);
		
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// check whether this post was already reported
		$sql = "SELECT 	postID
			FROM	wbb".WBB_N."_post_report
			WHERE	postID = ".$this->postID;
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['postID'])) {
			throw new NamedUserException(WCF::getLanguage()->get('wbb.report.error.alreadyReported'));
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save report in database
		$sql = "INSERT IGNORE INTO	wbb".WBB_N."_post_report
						(postID, userID, report, reportTime)
			VALUES 			(".$this->postID.", ".WCF::getUser()->userID.",
						'".escapeString($this->text)."', ".TIME_NOW.")";
		WCF::getDB()->sendQuery($sql);
		$this->reportID = WCF::getDB()->getInsertID();
		$this->saved();
		
		HeaderUtil::redirect('index.php?page=Thread&postID='.$this->postID.SID_ARG_2ND_NOT_ENCODED.'#post'.$this->postID);
	}
	
	/**
	 * @see Form::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'text' => $this->text,
			'postID' =>  $this->postID,
			'thread' => $this->thread,
			'board' => $this->board,
			'post' => $this->post
		));
	}
}
?>