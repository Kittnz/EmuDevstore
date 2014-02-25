<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractUserSignatureAction.class.php');

/**
 * Disables the user signature. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	action
 * @category 	Community Framework (commercial)
 */
class UserSignatureDisableAction extends AbstractUserSignatureAction {
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if ($this->user->disableSignature == 1) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// disable signature
		$this->user->updateFields(array('disableSignature' => 1));
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=User&userID='.$this->userID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>