<?php
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Show the instant messenger information of a user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.user.messenger
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class MessengerPage extends AbstractPage {
	public $userID = 0;
	public $user;
	public $identifier;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// validate action
		switch ($this->action) {
			case 'icq':
			case 'msn':
			case 'aim':
			case 'yim':
			case 'skype':
			case 'jabber':
				break;
			default: throw new IllegalLinkException();
		}
		
		// set template name
		$this->templateName = 'messenger'.ucfirst($this->action);
		
		// user id
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new UserProfile($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->identifier = $this->user->{$this->action};
		if (!$this->identifier) {
			throw new IllegalLinkException();
		}
		
		// check permissions
		WCF::getUser()->checkPermission('user.profile.canView');
		if ($this->user->ignoredUser) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.profile.error.ignoredUser', array('$username' => StringUtil::encodeHTML($this->user->username))));
		}
		if (!$this->user->canViewProfile()) {
			throw new IllegalLinkException();
		}
		
		if ($this->action == 'icq') {
			$this->identifier = StringUtil::replace('-', '', $this->identifier);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// show page
		WCF::getTPL()->assign(array(
			'user' => $this->user,
			'identifier' => $this->identifier,
			'type' => $this->action
		));
	}
}
?>