<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/WarningEditor.class.php');

/**
 * Deletes a warning.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class WarningDeleteAction extends AbstractAction {
	/**
	 * warning id
	 *
	 * @var	integer
	 */
	public $warningID = 0;
	
	/**
	 * warning editor object
	 *
	 * @var	WarningEditor
	 */
	public $warning;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['warningID'])) $this->warningID = intval($_REQUEST['warningID']);
		$this->warning = new WarningEditor($this->warningID);
		if (!$this->warning->warningID || $this->warning->warnings != 0) {
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
		$this->warning->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=WarningList&deletedWarningID='.$this->warningID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>