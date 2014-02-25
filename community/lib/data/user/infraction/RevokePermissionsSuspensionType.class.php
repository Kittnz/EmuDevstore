<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/suspension/type/AbstractSuspensionType.class.php');

/**
 * Allows a temporary or permanent revoke of user permissions. 
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.user.infraction
 * @category 	Burning Board
 */
class RevokePermissionsSuspensionType extends AbstractSuspensionType {
	protected static $availablePermissions = null;

	/**
	 * @see SuspensionType::apply()
	 */
	public function apply(User $user, UserSuspension $userSuspension, Suspension $suspension) {
		// get data
		$data = unserialize($suspension->suspensionData);
		if (isset($data['revokePermissions']) && count($data['revokePermissions'])) {
			$updateSQL = '';
			foreach ($data['revokePermissions'] as $permission) {
				if (!empty($updateSQL)) $updateSQL .= ',';
				$updateSQL .= $permission.' = VALUES('.$permission.')';
			}
			
			$sql = "INSERT INTO			wbb".WBB_N."_board_to_user
								(boardID, userID, ".implode(', ', $data['revokePermissions']).")
				SELECT				boardID, ".$user->userID.str_repeat(', 0', count($data['revokePermissions']))."
				FROM				wbb".WBB_N."_board
				ON DUPLICATE KEY UPDATE		".$updateSQL;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * @see SuspensionType::revoke()
	 */
	public function revoke(User $user, UserSuspension $userSuspension, Suspension $suspension) {
		// get data
		$data = unserialize($suspension->suspensionData);
		if (isset($data['revokePermissions']) && count($data['revokePermissions'])) {
			$sql = "UPDATE	wbb".WBB_N."_board_to_user
				SET	".implode(' = -1, ', $data['revokePermissions'])." = -1
				WHERE	userID = ".$user->userID;
			WCF::getDB()->sendQuery($sql);
			
			// delete obsolete
			$sql = "DELETE FROM	wbb".WBB_N."_board_to_user
				WHERE		userID = ".$user->userID."
						AND ".implode(' = -1 AND ', self::getAvailablePermissions())." = -1";
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function readFormParameters() {
		$this->data['revokePermissions'] = array();
		if (isset($_POST['revokePermissions']) && is_array($_POST['revokePermissions'])) {
			$this->data['revokePermissions'] = $_POST['revokePermissions'];
		}
	}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function validate() {
		$availablePermissions = self::getAvailablePermissions();
		if (!count($this->data['revokePermissions'])) {
			throw new UserInputException('revokePermissions');
		}
		foreach ($this->data['revokePermissions'] as $revokePermission) {
			if (!in_array($revokePermission, $availablePermissions)) {
				throw new UserInputException('revokePermissions');
			}
		}
	}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * @see SuspensionType::assignVariables()
	 */
	public function assignVariables() {
		WCF::getTPL()->assign(array(
			'availablePermissions' => self::getAvailablePermissions(),
			'revokePermissions' => (isset($this->data['revokePermissions']) ? $this->data['revokePermissions'] : array())
		));
	}
	
	/**
	 * @see SuspensionType::getTemplateName()
	 */
	public function getTemplateName() {
		return 'revokePermissionsSuspensionType';
	}
	
	/**
	 * Gets the list of available permissions.
	 *
	 * @return	array
	 */
	protected static function getAvailablePermissions() {
		if (self::$availablePermissions === null) {
			self::$availablePermissions = array();
			
			$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_to_user";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if ($row['Field'] != 'boardID' && $row['Field'] != 'userID') {
					self::$availablePermissions[] = $row['Field'];
				}
			}
		}
		
		return self::$availablePermissions;
	}
}
?>