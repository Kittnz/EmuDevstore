<?php
require_once(WCF_DIR.'lib/page/AbstractAttachmentListPage.class.php');
require_once(WCF_DIR.'lib/data/attachment/UserAttachmentList.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows a list of user attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.attachment
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UserAttachmentListPage extends AbstractAttachmentListPage {
	// system
	public $templateName = 'userAttachmentList';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		$this->attachmentList = new UserAttachmentList(WCF::getUser()->userID);
		
		parent::readParameters();
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.management.attachment');
		
		parent::show();
	}
}
?>