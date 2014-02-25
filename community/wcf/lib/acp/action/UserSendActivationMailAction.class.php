<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Sends activation mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserSendActivationMailAction extends AbstractAction {
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canEnableUser');
		
		// get user ids
		$userIDs = WCF::getSession()->getVar('markedUsers');	
		if (!is_array($userIDs)) $userIDs = array();
		
		if (count($userIDs) > 0) {
			// check permission
			$sql = "SELECT	DISTINCT groupID
				FROM	wcf".WCF_N."_user_to_groups
				WHERE	userID IN (".implode(',', $userIDs).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!Group::isAccessibleGroup($row['groupID'])) {
					throw new PermissionDeniedException();
				}
			}
			
			// save config in session
			$userMailData = WCF::getSession()->getVar('userMailData');
			if ($userMailData === null) $userMailData = array();
			$mailID = count($userMailData);
			$userMailData[$mailID] = array(
				'action' => '',
				'userIDs' => implode(',', $userIDs)
			);
			WCF::getSession()->register('userMailData', $userMailData);
			
			// unmark users
			UserEditor::unmarkAll();
			$this->executed();
			
			// show worker template
			WCF::getTPL()->assign(array(
				'pageTitle' => WCF::getLanguage()->get('wcf.acp.user.sendActivationMail'),
				'url' => 'index.php?action=UserActivationMail&mailID='.$mailID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED
			));
			WCF::getTPL()->display('worker');
			exit;
		}
		else {
			$this->executed();
		}
		
		HeaderUtil::redirect('index.php?form=UserSearch&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>