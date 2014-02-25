<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MyFriendsListPage.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfileFrame.class.php');

/**
 * Shows a list of all friends of a user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class UserFriendListPage extends MyFriendsListPage {
	// system
	public $templateName = 'userFriendList';
	
	/**
	 * user profile frame
	 * 
	 * @var UserProfileFrame
	 */
	public $frame = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get profile frame
		$this->frame = new UserProfileFrame($this);
		if (!$this->frame->getUser()->shareWhitelist) {
			throw new IllegalLinkException();
		}
		$this->userID = $this->frame->getUserID();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$this->frame->assignVariables();
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		UserProfileMenu::getInstance()->setActiveMenuItem('wcf.user.profile.menu.link.friends');
		
		parent::show();
	}
}
?>