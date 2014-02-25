<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Provides default implementations for signature actions. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class AbstractUserSignatureAction extends AbstractSecureAction {
	/**
	 * user id
	 *
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user editor object
	 *
	 * @var	UserEditor
	 */
	public $user = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check module
		if (MODULE_USER_SIGNATURE != 1) {
			throw new IllegalLinkException();
		}
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canEditUser');
		
		// get user
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new UserEditor($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
}
?>