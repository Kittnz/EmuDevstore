<?php
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnline.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * UsersOnlineList renders the online list under a specific condition for template output.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	data.user.usersOnline
 * @category 	Community Framework (commercial)
 */
class UsersOnlineList extends UsersOnline {
	public $usersOnlineTotal = 0;
	public $usersOnlineInvisible = 0;
	public $usersOnlineMembers = 0;
	public $usersOnlineGuests = 0;
	public $usersOnline = array();
	public $getSpiders = false;
	
	/**
	 * Creates a new UsersOnlineList object.
	 *
	 * @param 	string		$condition	Condition is used in the where cause to select only specific sessions from database.
	 */
	public function __construct($condition = '', $enableOwnView = false) {
		if (!empty($condition)) {
			$this->sqlConditions .= ' AND '.$condition.' ';
		}
		$this->enableOwnView = $enableOwnView;
	}
	
	/**
	 * @see UsersOnline::handleRow()
	 */
	protected function handleRow($row, User $user) {
		if ($row['userID']) {
			// members
			$this->usersOnlineMembers++;
			
			if ($this->isVisible($row, $user)) {
				$this->usersOnline[] = array('userID' => $row['userID'], 'username' => $this->getFormattedUsername($row, $user));
			}
			else {
				$this->usersOnlineInvisible++;
			}
		}
		else {
			// guest
			if (!isset($this->guestIpAddresses[$row['ipAddress']])) {
				$this->usersOnlineGuests++;
				$this->guestIpAddresses[$row['ipAddress']] = true;
			}
			else {
				return;
			}
		}
		
		$this->usersOnlineTotal++;
	}
	
	/**
	 * Renders the user online list on a page.
	 */
	public function renderOnlineList() {
		// get users online
		$this->getUsersOnline();
		
		// assign variables
		if ($this->usersOnlineTotal > 0) {
			WCF::getTPL()->assign('usersOnline', $this->usersOnline);
			WCF::getTPL()->assign('usersOnlineTotal', $this->usersOnlineTotal);
			WCF::getTPL()->assign('usersOnlineInvisible', $this->usersOnlineInvisible);
			WCF::getTPL()->assign('usersOnlineMembers', $this->usersOnlineMembers);
			WCF::getTPL()->assign('usersOnlineGuests', $this->usersOnlineGuests);
			WCF::getTPL()->assign('usersOnlineMarkings', $this->getUsersOnlineMarkings());
		}
	}
	
	/**
	 * Returns the number of users online.
	 * 
	 * @return	integer
	 */
	public function getUsersOnlineTotal() {
		return $this->usersOnlineTotal;
	}
}
?>