<?php
require_once(WCF_DIR.'lib/page/AbstractSecurePage.class.php');

/**
 * Handles all PM requests und calls the right function or class.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class PMPage extends AbstractSecurePage {
	public $pmID = null;
	public $folderID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['folderID'])) $this->folderID = intval($_REQUEST['folderID']);
		if (isset($_REQUEST['pmID'])) {
			if (is_array($_REQUEST['pmID'])) {
				$this->pmID = ArrayUtil::toIntegerArray($_REQUEST['pmID']);	
			}
			else {
				$this->pmID = intval($_REQUEST['pmID']);
			}
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}

		// check permission
		WCF::getUser()->checkPermission('user.pm.canUsePm');
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
				
		// handle action request
		switch ($this->action) {
			// mark/unmark pm
			case 'mark':
			case 'unmark':
				if ($this->pmID) {
					require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
					$a = $this->action;
					PMAction::$a($this->pmID);
				}
				break;

			// disable notifications
			case 'disableNotifications':
				require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
				PM::disableNotifications();
				if (!isset($_REQUEST['ajax'])) {
					HeaderUtil::redirect('index.php'.SID_ARG_1ST);
					exit;
				}
				break;
			
			// unmark all	
			case 'unmarkAll':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				PMAction::unmarkAll();
				break;
				
			// empty recycle bin	
			case 'emptyRecycleBin':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				PMAction::emptyRecycleBin();
				break;
				
			// delete
			// edit unread
			// cancel
			// recover
			// download
			// mark as read/unread
			case 'delete':
			case 'edit':
			case 'cancel':
			case 'recover':
			case 'download':
			case 'markAsRead':
			case 'markAsUnread':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				$action = new PMAction($this->pmID, $this->folderID);
				$action->{$this->action}();
				break;
				
			// move to
			case 'moveTo':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				$action = new PMAction($this->pmID);
				$action->moveTo($this->folderID);
				break;
				
			// move marked to
			case 'moveMarkedTo':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				PMAction::moveMarkedTo($this->folderID);
				break;
			
			// download marked
			// delete marked
			// cancel marked
			case 'downloadMarked':
			case 'deleteMarked':
			case 'cancelMarked':
				require_once(WCF_DIR.'lib/data/message/pm/PMAction.class.php');
				$a = $this->action;
				PMAction::$a($this->folderID);
				break;
		}
	}
}
?>