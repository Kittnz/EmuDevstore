<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Enables a user option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.option
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class UserOptionEnableAction extends AbstractAction {
	public $userOptionID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['optionID'])) $this->optionID = intval($_REQUEST['optionID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
				
		// check permission
		WCF::getUser()->checkPermission('admin.user.option.canEditOption');
		
		// enable user option
		require_once(WCF_DIR.'lib/data/user/option/UserOptionEditor.class.php');
		$userOption = new UserOptionEditor($this->optionID);	
		if (!$userOption->optionID) {
			throw new IllegalLinkException();
		}
		$userOption->enable();
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.user-option-*');
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=UserOptionList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>