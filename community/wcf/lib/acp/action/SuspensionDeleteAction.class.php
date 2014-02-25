<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/SuspensionEditor.class.php');

/**
 * Deletes a suspension.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class SuspensionDeleteAction extends AbstractAction {
	/**
	 * suspension id
	 *
	 * @var	integer
	 */
	public $suspensionID = 0;
	
	/**
	 * suspension editor object
	 *
	 * @var	SuspensionEditor
	 */
	public $suspension;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['suspensionID'])) $this->suspensionID = intval($_REQUEST['suspensionID']);
		$this->suspension = new SuspensionEditor($this->suspensionID);
		if (!$this->suspension->suspensionID || $this->suspension->suspensions != 0) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.infraction.canDeleteSuspension');
		
		// delete suspension
		$this->suspension->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=SuspensionList&deletedSuspensionID='.$this->suspensionID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>