<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');

/**
 * UserProfile extends User by functions for displaying a user profile.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user
 * @category 	Community Framework
 */
class UserProfile extends UserSession {
	/**
	 * displayable avatar object.
	 *
	 * @var DisplayableAvatar
	 */
	protected $avatar = null;
	
	/**
	 * user rank object
	 *
	 * @var UserRank
	 */
	protected $rank = null;
	
	/**
	 * list of buddies.
	 * 
	 * @var	array
	 */
	public static $buddies = null;
	
	/**
	 * Creates a new UserProfile object.
	 * 
	 * @see User::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null, $email = null, $sqlSelects = '', $sqlJoins = '') {
		$this->sqlSelects .= $sqlSelects."session.requestURI, session.requestMethod, session.ipAddress, session.userAgent,
						rank.*, avatar.*,"; 
		$this->sqlJoins .= $sqlJoins.	' LEFT JOIN wcf'.WCF_N.'_avatar avatar ON (avatar.avatarID = user.avatarID) '.
						' LEFT JOIN wcf'.WCF_N.'_session session ON (session.userID = user.userID AND session.packageID = '.PACKAGE_ID.' AND session.lastActivityTime > '.(TIME_NOW - USER_ONLINE_TIMEOUT).') '.
						' LEFT JOIN wcf'.WCF_N.'_user_rank rank ON (rank.rankID = user.rankID) ';
					
		if (WCF::getUser()->userID) {
			$this->sqlSelects .= 'hisWhitelist.userID AS buddy, hisBlacklist.userID AS ignoredUser,';
			$this->sqlJoins .= 	' LEFT JOIN wcf'.WCF_N.'_user_whitelist hisWhitelist
							ON (hisWhitelist.userID = user.userID AND hisWhitelist.whiteUserID = '.WCF::getUser()->userID.' AND hisWhitelist.confirmed = 1) '.
						' LEFT JOIN wcf'.WCF_N.'_user_blacklist hisBlacklist
							ON (hisBlacklist.userID = user.userID AND hisBlacklist.blackUserID = '.WCF::getUser()->userID.') ';
		}
		else {
			$this->sqlSelects .= '0 AS buddy, 0 AS ignoredUser,';
		}
		
		parent::__construct($userID, $row, $username, $email);
	}
	
	/**
	 * @see User::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		if (MODULE_AVATAR == 1 && !$this->disableAvatar && (!WCF::getUser()->userID || WCF::getUser()->showAvatar) && ($this->userID == WCF::getUser()->userID || WCF::getUser()->getPermission('user.profile.avatar.canViewAvatar'))) {
			if (MODULE_GRAVATAR == 1 && $this->gravatar) {
				$this->avatar = new Gravatar($this->gravatar);
			}
			else if ($this->avatarID) {
				$this->avatar = new Avatar(null, $data);
			}
		}
		if ($this->rankID) $this->rank = new UserRank(null, $data);
	}
	
	/**
	 * Returns the avatar of this user.
	 * 
	 * @return	DisplayableAvatar
	 */
	public function getAvatar() {
		return $this->avatar;
	}
	
	/**
	 * Returns true, if this user is currently online.
	 * 
	 * @return	boolean
	 */
	public function isOnline() {
		if ($this->lastActivityTime && $this->lastActivityTime > (TIME_NOW - USER_ONLINE_TIMEOUT) && (WCF::getUser()->userID == $this->userID || !$this->invisible || WCF::getUser()->getPermission('admin.general.canViewInvisible') || ($this->invisible == 2 && UserProfile::isBuddy($this->userID)))) {
			return true;
		}
		return false;
	}
	
	/**
	 * Returns the user title of this user.
	 */
	public function getUserTitle() {
		if ($this->userTitle) return StringUtil::encodeHTML($this->userTitle);
		else if ($this->rank) return WCF::getLanguage()->get(StringUtil::encodeHTML($this->rank->rankTitle));
	}
	
	/**
	 * Returns the rank of this user.
	 * 
	 * @return	UserRank
	 */
	public function getRank() {
		return $this->rank;
	}
	
	/**
	 * Returns the old username of this user.
	 */
	public function getOldUsername() {
		if ($this->oldUsername) {
			if ($this->lastUsernameChange + PROFILE_SHOW_OLD_USERNAME * 86400 > TIME_NOW) {
				return $this->oldUsername;
			}
		}
	}
	
	/**
	 * Returns the age of this user profile in days.
	 * 
	 * @return	integer
	 */
	public function getProfileAge() {
		return (TIME_NOW - $this->registrationDate) / 86400;
	}
	
	/**
	 * Returns true, if the active user can send e-mails to this user.
	 * 
	 * @return boolean
	 */
	public function canMail() {
		if ($this->ignoredUser || ($this->onlyBuddyCanMail && !UserProfile::isBuddy($this->userID)) || (!$this->userCanMail && !$this->onlyBuddyCanMail)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true, if the active user can see the profile of this user.
	 * 
	 * @return	boolean
	 */
	public function canViewProfile() {
		return (!$this->protectedProfile || WCF::getUser()->userID == $this->userID || UserProfile::isBuddy($this->userID) || WCF::getUser()->getPermission('admin.general.canViewPrivateUserOptions'));
	}
	
	/**
	 * Returns the age of this user.
	 *
	 * @return	integer
	 */
	public function getAge() {
		if ($this->birthday) {
			// split date
			$year = $month = $day = 0;
			$optionValue = explode('-', $this->birthday);
			if (isset($optionValue[0])) $year = intval($optionValue[0]);
			if (isset($optionValue[1])) $month = intval($optionValue[1]);
			if (isset($optionValue[2])) $day = intval($optionValue[2]);
			
			// calc
			if ($year) {
				$age = DateUtil::formatDate('%Y', null, false, true) - $year;
				if (intval(DateUtil::formatDate('%m', null, false, true)) < intval($month)) $age--;
				else if (intval(DateUtil::formatDate('%m', null, false, true)) == intval($month) && DateUtil::formatDate('%e', null, false, true) < intval($day)) $age--;
				return $age;
			}
		}
		
		return 0;
	}
	
	/**
	 * Returns true, if the given user is a buddy of the active user.
	 * 
	 * @return	boolean
	 */
	public static function isBuddy($userID) {
		if (self::$buddies === null) {
			self::$buddies = array();
			if (WCF::getUser()->buddies) {
				self::$buddies = explode(',', WCF::getUser()->buddies);
			}
		}
		
		if (count(self::$buddies)) {
			return in_array($userID, self::$buddies);
		}
		
		return false;
	}
}
?>