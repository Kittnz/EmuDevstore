<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarningEditor.class.php');

/**
 * Deletes a user warning.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class UserWarningDeleteAction extends AbstractAction {
	/**
	 * user warning id
	 *
	 * @var	integer
	 */
	public $userWarningID = 0;
	
	/**
	 * user warning editor object
	 *
	 * @var	UserWarningEditor
	 */
	public $userWarning;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userWarningID'])) $this->userWarningID = intval($_REQUEST['userWarningID']);
		$this->userWarning = new UserWarningEditor($this->userWarningID);
		if (!$this->userWarning->userWarningID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.infraction.canDeleteWarning');
		
		// delete warning
		$this->userWarning->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=UserWarningList&deletedUserWarningID='.$this->userWarningID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>