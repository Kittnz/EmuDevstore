<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractAttachmentListPage.class.php');
require_once(WCF_DIR.'lib/data/attachment/AdminAttachmentList.class.php');

/**
 * Shows a list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.attachment
 * @subpackage	acp.page
 * @category 	Community Framework (commercial)
 */
class AdminAttachmentListPage extends AbstractAttachmentListPage {
	// system
	public $templateName = 'attachmentList';
	
	// parameter
	public $username = '';
	public $deletedAttachmentID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		$this->attachmentList = new AdminAttachmentList();
		
		parent::readParameters();
		
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['deletedAttachmentID'])) $this->deletedAttachmentID = intval($_REQUEST['deletedAttachmentID']);
	}
	
	/**
	 * @see AbstractAttachmentListPage::setSQLConditions()
	 */
	protected function setSQLConditions() {
		parent::setSQLConditions();
		
		if (!empty($this->username)) {
			$user = new User(null, null, $this->username);
			if ($user->userID) {
				$this->attachmentList->sqlConditions .= " AND attachment.userID = ".$user->userID;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'deletedAttachmentID' => $this->deletedAttachmentID
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.attachment.view');
		
		// check permission
		WCF::getUser()->checkPermission('admin.attachment.canDeleteAttachment');
		
		parent::show();
	}
}
?>