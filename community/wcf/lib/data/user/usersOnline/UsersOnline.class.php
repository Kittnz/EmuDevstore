<?php
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Gets a list of users online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	data.user.usersOnline
 * @category 	Community Framework (commercial)
 */
abstract class UsersOnline {
	public $getSpiders = true;
	public $sqlSelects = '';
	public $sqlJoins = '';
	public $sqlConditions = '';
	public $sqlOrderBy = 'session.username';
	public $enableOwnView = false;
	protected $guestIpAddresses = array();
	
	/**
	 * Gets a list of users online.
	 */
	public function getUsersOnline() {
		$sql = "SELECT 		".$this->sqlSelects."
					user_option.userOption".User::getUserOptionID('invisible').", session.userID, session.username as guestname, session.ipAddress,
					session.userAgent, session.lastActivityTime, session.requestURI, session.sessionID,
					session.requestMethod, session.spiderID, groups.userOnlineMarking, user.username 
			FROM 		wcf".WCF_N."_session session
			LEFT JOIN 	wcf".WCF_N."_user user
			ON		(user.userID = session.userID)
			LEFT JOIN 	wcf".WCF_N."_user_option_value user_option
			ON		(user_option.userID = session.userID)
			LEFT JOIN 	wcf".WCF_N."_group groups
			ON		(groups.groupID = user.userOnlineGroupID)
			".$this->sqlJoins."
			WHERE 		session.packageID = ".PACKAGE_ID."
					AND session.lastActivityTime > ".(TIME_NOW - USER_ONLINE_TIMEOUT)."
					".($this->getSpiders ? '' : 'AND session.spiderID = 0')."
					".(!$this->enableOwnView ? ("AND session.sessionID <> '".WCF::getSession()->sessionID."'".(WCF::getUser()->userID ? " AND session.userID <> ".WCF::getUser()->userID : '')) : '')."
					".$this->sqlConditions." 
			ORDER BY 	".$this->sqlOrderBy;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->handleRow($row, new User(null, $row));
		}
	}
	
	/**
	 * Formats a user result set.
	 * 
	 * @param	array		$row
	 * @param	User	$user
	 */
	protected abstract function handleRow($row, User $user);
	
	/**
	 * Returns true, if the given user is visible.
	 * 
	 * @return	boolean
	 */
	protected function isVisible($row, User $user) {
		return (WCF::getUser()->userID == $user->userID || !$user->invisible || WCF::getUser()->getPermission('admin.general.canViewInvisible') || ($user->invisible == 2 && UserProfile::isBuddy($user->userID)));
	}
	
	/**
	 * Formats the username of the given user.
	 * 
	 * @param	array		$row
	 * @param	User	$user
	 * @return	string		formatted username
	 */
	public static function getFormattedUsername($row, User $user) {
		$row['username'] = StringUtil::encodeHTML($row['username']);
		
		if (UserProfile::isBuddy($user->userID)) {
			$row['username'] = '<span class="buddy">'.$row['username'].'</span>';
		}
		
		if (!empty($row['userOnlineMarking'])) {
			$row['username'] = sprintf($row['userOnlineMarking'], $row['username']);
		}
		
		if ($user->invisible) {
			$row['username'] .= WCF::getLanguage()->get('wcf.usersOnline.invisible');
		}
		
		return $row['username'];
	}
	
	/**
	 * Returns a list of the users online markings.
	 * 
	 * @return	array
	 */
	public static function getUsersOnlineMarkings() {
		$usersOnlineMarkings = $showOnTeamPage = $teamPagePosition = array();
		
		// get groups
		WCF::getCache()->addResource('groups', WCF_DIR.'cache/cache.groups.php', WCF_DIR.'lib/system/cache/CacheBuilderGroups.class.php');
		$groups = WCF::getCache()->get('groups', 'groups');
		foreach ($groups as $group) {
			if ($group['userOnlineMarking'] != '%s') {
				if (isset($group['showOnTeamPage']) && isset($group['teamPagePosition'])) {
					$showOnTeamPage[] = $group['showOnTeamPage'];
					$teamPagePosition[] = $group['teamPagePosition'];
				}
				
				$usersOnlineMarkings[] = sprintf($group['userOnlineMarking'], StringUtil::encodeHTML(WCF::getLanguage()->get($group['groupName'])));
			}
		}
		
		// sort list
		if (count($showOnTeamPage)) {
			array_multisort($showOnTeamPage, SORT_DESC, $teamPagePosition, $usersOnlineMarkings);
		}
		
		if (WCF::getUser()->userID && WCF::getUser()->buddies) {
			$usersOnlineMarkings[] = '<span class="buddy">'.WCF::getLanguage()->get('wcf.usersOnline.marking.friends').'</span>';
		}
	
		return $usersOnlineMarkings;
	}
}
?>