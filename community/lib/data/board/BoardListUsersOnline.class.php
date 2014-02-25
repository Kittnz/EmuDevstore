<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnline.class.php');

/**
 * Show users online in the board list.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.board
 * @category 	Burning Board
 */
class BoardListUsersOnline extends UsersOnline {
	public $getSpiders = false;
	public $sqlSelects = 'session.boardID,';
	public $sqlConditions = ' AND session.boardID <> 0 ';
	public $boardUsersOnline = array();

	/**
	 * Creates a new BoardListUsersOnline object.
	 */
	public function __construct() {
		$this->getUsersOnline();
	}

	/**
	 * @see UsersOnline::handleRow()
	 */
	protected function handleRow($row, User $user) {
		if ($row['userID']) {
			if ($this->isVisible($row, $user)) {
				if (!isset($this->boardUsersOnline[$row['boardID']]['users'])) {
					$this->boardUsersOnline[$row['boardID']]['users'] = array();
				}
			
				$this->boardUsersOnline[$row['boardID']]['users'][] = array('userID' => $row['userID'], 'username' => $this->getFormattedUsername($row, $user));
			}
		}
		else {
			// guest
			if (!isset($this->guestIpAddresses[$row['ipAddress']])) {
				$this->guestIpAddresses[$row['ipAddress']] = true;
				if (!isset($this->boardUsersOnline[$row['boardID']]['guests'])) {
					$this->boardUsersOnline[$row['boardID']]['guests'] = 0;
				}
				$this->boardUsersOnline[$row['boardID']]['guests']++;
			}
		}
	}
	
	/**
	 * Gets users online.
	 */
	public function getBoardUsersOnline() {
		return $this->boardUsersOnline;
	}
}
?>