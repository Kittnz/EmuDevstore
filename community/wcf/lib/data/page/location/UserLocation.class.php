<?php
// wcf imports
require_once(WCF_DIR.'lib/data/page/location/Location.class.php');

/**
 * UserLocation is an implementation of Location for the user page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.page.location
 * @category 	Community Framework
 */
class UserLocation implements Location {
	public $cachedUserIDs = array();
	public $users = null;
	
	/**
	 * @see Location::cache()
	 */
	public function cache($location, $requestURI, $requestMethod, $match) {
		$this->cachedUserIDs[] = $match[1];
	}
	
	/**
	 * @see Location::get()
	 */
	public function get($location, $requestURI, $requestMethod, $match) {
		if ($this->users == null) {
			$this->readUsers();
		}
		
		$userID = $match[1];
		if (!isset($this->users[$userID])) {
			return '';
		}
		
		return WCF::getLanguage()->get($location['locationName'], array('$user' => '<a href="index.php?page=User&amp;userID='.$userID.SID_ARG_2ND.'">'.StringUtil::encodeHTML($this->users[$userID]).'</a>'));
	}
	
	/**
	 * Gets users.
	 */
	protected function readUsers() {
		$this->users = array();
		
		if (!count($this->cachedUserIDs)) {
			return;
		}
		
		$sql = "SELECT	userID, username
			FROM	wcf".WCF_N."_user
			WHERE	userID IN (".implode(',', $this->cachedUserIDs).")";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->users[$row['userID']] = $row['username'];
		}
	}
}
?>