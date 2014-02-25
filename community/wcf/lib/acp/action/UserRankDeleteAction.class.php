<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a user rank.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.rank
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class UserRankDeleteAction extends AbstractAction {
	public $rankID = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['rankID'])) $this->rankID = intval($_REQUEST['rankID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.rank.canDeleteRank');
		
		// delete rank
		require_once(WCF_DIR.'lib/data/user/rank/UserRankEditor.class.php');
		$userRank = new UserRankEditor($this->rankID);	
		if (!$userRank->rankID) {
			throw new IllegalLinkException();
		}
		$userRank->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=UserRankList&deletedRankID='.$this->rankID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>