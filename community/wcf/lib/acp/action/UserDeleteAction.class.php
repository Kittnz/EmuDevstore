<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes a user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserDeleteAction extends AbstractAction {
	public $userID = 0;
	public $userIDs = array();
	public $url = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		if (isset($_POST['userIDs']) && is_array($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray($_POST['userIDs']);
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
				
		WCF::getUser()->checkPermission('admin.user.canDeleteUser');
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
			
		if ($this->userID !== 0) {
			$this->userIDs[] = $this->userID;
		}
		
		// active user can't delete himself   
		$activeUserID = WCF::getSession()->getUser()->userID;
		$this->userIDs = array_diff($this->userIDs, array($activeUserID));
		
		// check permission
		if (count($this->userIDs) > 0) {
			$sql = "SELECT	DISTINCT groupID
				FROM	wcf".WCF_N."_user_to_groups
				WHERE	userID IN (".implode(',', $this->userIDs).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!Group::isAccessibleGroup($row['groupID'])) {
					throw new PermissionDeniedException();
				}
			}
		}

		$deletedUsers = UserEditor::deleteUsers($this->userIDs);
		$this->executed();
		
		if (!empty($this->url) && (strpos($this->url, 'searchID=0') !== false || strpos($this->url, 'searchID=') === false)) HeaderUtil::redirect($this->url);
		else HeaderUtil::redirect('index.php?form=UserSearch&deletedUsers='.$deletedUsers.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>
