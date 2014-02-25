<?php
// wbb imports
require_once(WBB_DIR.'lib/data/post/PostEditor.class.php');
require_once(WBB_DIR.'lib/data/thread/Thread.class.php');
require_once(WBB_DIR.'lib/data/board/Board.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows the ip address of a post author.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	page
 * @category 	Burning Board
 */
class IpAddressPage extends AbstractPage {
	public $postID = 0;
	public $post;
	public $thread;
	public $board;
	public $authorIpAddresses = array();
	public $otherUsers = array();
	public $templateName = 'ipAddress';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['postID']))	$this->postID = intval($_REQUEST['postID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get post, thread and board
		$this->post = new PostEditor($this->postID);
		$this->thread = new Thread($this->post->threadID);
		$this->board = Board::getBoard($this->thread->boardID);
		
		// get ip addresses of the author
		$this->authorIpAddresses = PostEditor::getIpAddressByAuthor($this->post->userID, $this->post->username, $this->post->ipAddress);
		
		// get hostnames
		$this->loadHostnames();
		
		// get other users of this ip address
		if ($this->post->ipAddress) {
			$this->otherUsers = PostEditor::getAuthorByIpAddress($this->post->ipAddress, $this->post->userID, $this->post->username);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'post'	=> $this->post,
			'board' => $this->board,
			'thread' => $this->thread,
			'postID' => $this->postID,
			'otherAddresses' => $this->authorIpAddresses,
			'otherUsers' => $this->otherUsers,
			'ipAddress' => array(
				'ipAddress' => $this->post->ipAddress,
				'hostname' => @gethostbyaddr($this->post->ipAddress)
			)
		));
	}
	
	/**
	 * Shows the IP-Adress page.
	 */
	public function show() {
		// check permission
		WCF::getUser()->checkPermission('admin.general.canViewIpAddress');
		
		// show page
		parent::show();
	}
	
	/**
	 * Gets the hostnames of ip addresses
	 */
	protected function loadHostnames() {
		$newIpAddresses = array();
		
		foreach ($this->authorIpAddresses as $ipAddress) {
			$newIpAddresses[] = array('ipAddress' => $ipAddress, 'hostname' => @gethostbyaddr($ipAddress));
		}
		
		$this->authorIpAddresses = $newIpAddresses;
	}
}
?>