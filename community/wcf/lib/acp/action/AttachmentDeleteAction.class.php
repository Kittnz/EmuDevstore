<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/attachment/AttachmentEditor.class.php');

/**
 * Deletes an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.attachment
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AttachmentDeleteAction extends AbstractAction {
	/**
	 * attachment id
	 *
	 * @var	integer
	 */
	public $attachmentID = 0;
	
	/**
	 * attachment editor object
	 *
	 * @var	AttachmentEditor
	 */
	public $attachment;
	
	/**
	 * forward url
	 *
	 * @var string
	 */
	public $url = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['attachmentID'])) $this->attachmentID = intval($_REQUEST['attachmentID']);
		$this->attachment = new AttachmentEditor($this->attachmentID);
		if (!$this->attachment->attachmentID) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['url'])) $this->url = StringUtil::trim($_REQUEST['url']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.attachment.canDeleteAttachment');
		
		// delete warning
		$this->attachment->delete();
		$this->executed();
		
		// forward to list page
		if (!empty($this->url)) {
			HeaderUtil::redirect($this->url.'&deletedAttachmentID='.$this->attachmentID.SID_ARG_2ND_NOT_ENCODED);
		}
		else {
			HeaderUtil::redirect('index.php?page=AdminAttachmentListPage&deletedAttachmentID='.$this->attachmentID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		}
		exit;
	}
}
?>