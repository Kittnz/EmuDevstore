<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserProfileMenu.class.php');

/**
 * Manages the user profile pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user
 * @category 	Community Framework
 */
class UserProfileFrame {
	/**
	 * user profile container.
	 * 
	 * @var	object 
	 */
	public $container = null;
	
	/**
	 * user id
	 *
	 * @var integer
	 */
	public $userID = 0;
	
	/**
	 * user name
	 * 
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * user information
	 *
	 * @var UserProfile
	 */
	public $user = null;
	
	/**
	 * sql select parameters
	 *
	 * @var string
	 */
	public $sqlSelects = '';
	
	/**
	 * sql select joins
	 *
	 * @var string
	 */
	public $sqlJoins = '';
	
	/**
	 * friend connection
	 *
	 * @var array<UserProfile>
	 */
	public $connection = array();
	
	/**
	 * friend network
	 *
	 * @var array<integer>
	 */
	public $network = array();
	
	/**
	 * list of user status symbols
	 * 
	 * @var	array<string>
	 */
	public $userSymbols = array();
	
	/**
	 * Creates a new UserProfileFrame.
	 *
	 * @param	object		$container
	 * @param	integer		$userID
	 */
	public function __construct($container = null, $userID = null) {
		$this->container = $container;
		
		// get user id
		if ($userID !== null) {
			$this->userID = $userID;
		}
		else if (!empty($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
		}
		else if (!empty($_REQUEST['username'])) {
			$this->username = StringUtil::trim($_REQUEST['username']);
		}
		
		$this->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_user_whitelist WHERE userID = user.userID AND confirmed = 1) AS friends,";
		
		// set default sql
		if (WCF::getUser()->userID) {
			$this->sqlSelects .= 'myBlacklist.blackUserID, CASE WHEN myWhitelist.confirmed = 1 THEN myWhitelist.whiteUserID ELSE 0 END AS whiteUserID, CASE WHEN myWhitelist.confirmed = 0 THEN myWhitelist.whiteUserID ELSE 0 END AS invitedUserID,';
			$this->sqlJoins .= 	' LEFT JOIN wcf'.WCF_N.'_user_blacklist myBlacklist
							ON (myBlacklist.userID = '.WCF::getUser()->userID.' AND myBlacklist.blackUserID = user.userID) '.
						' LEFT JOIN wcf'.WCF_N.'_user_whitelist myWhitelist
							ON (myWhitelist.userID = '.WCF::getUser()->userID.' AND myWhitelist.whiteUserID = user.userID) ';
		}
		
		// init frame
		$this->init();
	}
	
	/**
	 * Initializes the profile.
	 */
	public function init() {
		// call init event
		EventHandler::fireAction($this, 'init');
		
		// get user information
		if (!empty($this->userID)) {
			$this->user = new UserProfile($this->userID, null, null, null, $this->sqlSelects, $this->sqlJoins);
		}
		else if (!empty($this->username)) {
			$this->user = new UserProfile(null, null, $this->username, null, $this->sqlSelects, $this->sqlJoins);
		}
		else {
			throw new IllegalLinkException();
		}
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		$this->userID = $this->user->userID;
		$this->username = $this->user->username;
		
		// set page menu
		PageMenu::setActiveMenuItem('wcf.header.menu.memberslist');
		
		// set user profile menu
		UserProfileMenu::getInstance()->userID = $this->userID;
		
		// user symbols
		$this->initUserSymbols();
		
		// friends
		if (WCF::getUser()->userID) {
			if (WCF::getUser()->userID != $this->userID) {
				// get friend connection
				$connectionUserIDArray = array();
				
				if ($this->user->buddy) {
					$connectionUserIDArray[] = WCF::getUser()->userID;
				}
				else if (WCF::getUser()->buddies) {
					// one step
					$sql = "SELECT		friends_2nd.userID
						FROM		wcf".WCF_N."_user_whitelist my_friends,
								wcf".WCF_N."_user_whitelist friends_2nd
						WHERE		my_friends.userID = ".WCF::getUser()->userID." AND my_friends.confirmed = 1
								AND friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1
								AND friends_2nd.whiteUserID = ".$this->userID;
					$row = WCF::getDB()->getFirstRow($sql);
					if (!empty($row['userID'])) {
						$connectionUserIDArray[] = WCF::getUser()->userID;
						$connectionUserIDArray[] = $row['userID'];
					}
					else {
						// two steps
						$sql = "SELECT		friends_2nd.userID AS 1st, friends_3rd.userID AS 2nd
							FROM		wcf".WCF_N."_user_whitelist my_friends,
									wcf".WCF_N."_user_whitelist friends_2nd,
									wcf".WCF_N."_user_whitelist friends_3rd
							WHERE		my_friends.userID = ".WCF::getUser()->userID." AND my_friends.confirmed = 1
									AND friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1
									AND friends_3rd.userID = friends_2nd.whiteUserID AND friends_3rd.confirmed = 1
									AND friends_3rd.whiteUserID = ".$this->userID;
						$row = WCF::getDB()->getFirstRow($sql);
						if (!empty($row['1st'])) {
							$connectionUserIDArray[] = WCF::getUser()->userID;
							$connectionUserIDArray[] = $row['1st'];
							$connectionUserIDArray[] = $row['2nd'];
						}
						else {
							// three steps
							$sql = "SELECT		friends_2nd.userID AS 1st, friends_3rd.userID AS 2nd, friends_4th.userID AS 3rd
								FROM		wcf".WCF_N."_user_whitelist my_friends,
										wcf".WCF_N."_user_whitelist friends_2nd,
										wcf".WCF_N."_user_whitelist friends_3rd,
										wcf".WCF_N."_user_whitelist friends_4th
								WHERE		my_friends.userID = ".WCF::getUser()->userID." AND my_friends.confirmed = 1
										AND friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1
										AND friends_3rd.userID = friends_2nd.whiteUserID AND friends_3rd.confirmed = 1
										AND friends_4th.userID = friends_3rd.whiteUserID AND friends_4th.confirmed = 1
										AND friends_4th.whiteUserID = ".$this->userID;
							$row = WCF::getDB()->getFirstRow($sql);
							if (!empty($row['1st'])) {
								$connectionUserIDArray[] = WCF::getUser()->userID;
								$connectionUserIDArray[] = $row['1st'];
								$connectionUserIDArray[] = $row['2nd'];
								$connectionUserIDArray[] = $row['3rd'];
							}
							/*else {
								// four steps
								$sql = "SELECT		friends_2nd.userID AS 1st, friends_3rd.userID AS 2nd, friends_4th.userID AS 3rd, friends_5th.userID AS 4th
									FROM		wcf".WCF_N."_user_whitelist my_friends,
											wcf".WCF_N."_user_whitelist friends_2nd,
											wcf".WCF_N."_user_whitelist friends_3rd,
											wcf".WCF_N."_user_whitelist friends_4th,
											wcf".WCF_N."_user_whitelist friends_5th
									WHERE		my_friends.userID = ".WCF::getUser()->userID." AND my_friends.confirmed = 1
											AND friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1
											AND friends_3rd.userID = friends_2nd.whiteUserID AND friends_3rd.confirmed = 1
											AND friends_4th.userID = friends_3rd.whiteUserID AND friends_4th.confirmed = 1
											AND friends_5th.userID = friends_4th.whiteUserID AND friends_5th.confirmed = 1
											AND friends_5th.whiteUserID = ".$this->userID;
								$row = WCF::getDB()->getFirstRow($sql);
								if (!empty($row['1st'])) {
									$connectionUserIDArray[] = WCF::getUser()->userID;
									$connectionUserIDArray[] = $row['1st'];
									$connectionUserIDArray[] = $row['2nd'];
									$connectionUserIDArray[] = $row['3rd'];
									$connectionUserIDArray[] = $row['4th'];
								}
							}*/
						}
					}
				}
				
				if (count($connectionUserIDArray) > 0) {
					$sql = "SELECT		avatar.*, user_table.*
						FROM		wcf".WCF_N."_user user_table
						LEFT JOIN 	wcf".WCF_N."_avatar avatar
						ON 		(avatar.avatarID = user_table.avatarID)
						WHERE		user_table.userID IN (".implode(',', $connectionUserIDArray).")";
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						$this->connection[array_search($row['userID'], $connectionUserIDArray)] = new UserProfile(null, $row);
					}
					
					// sorting
					ksort($this->connection);
					$this->connection = array_reverse($this->connection);
				}
			}
			else {
				// get network
				$sql = "SELECT	COUNT(*) AS count
					FROM	wcf".WCF_N."_user_whitelist
					WHERE	userID = ".$this->userID."
						AND confirmed = 1";
				$row = WCF::getDB()->getFirstRow($sql);
				if ($row['count']) {
					$this->network['friends'] = $row['count'];
					$sql = "SELECT		COUNT(DISTINCT friends_2nd.whiteUserID) AS count
						FROM		wcf".WCF_N."_user_whitelist my_friends
						LEFT JOIN	wcf".WCF_N."_user_whitelist friends_2nd
						ON		(friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1)
						WHERE		my_friends.userID = ".$this->userID."
								AND my_friends.confirmed = 1";
					$row = WCF::getDB()->getFirstRow($sql);
					if ($row['count']) {
						$this->network['friendsOfFriends'] = $row['count'];
						$sql = "SELECT		COUNT(DISTINCT friends_3rd.whiteUserID) AS count
							FROM		wcf".WCF_N."_user_whitelist my_friends
							LEFT JOIN	wcf".WCF_N."_user_whitelist friends_2nd
							ON		(friends_2nd.userID = my_friends.whiteUserID AND friends_2nd.confirmed = 1)
							LEFT JOIN	wcf".WCF_N."_user_whitelist friends_3rd
							ON		(friends_3rd.userID = friends_2nd.whiteUserID AND friends_3rd.confirmed = 1)
							WHERE		my_friends.userID = ".$this->userID."
									AND my_friends.confirmed = 1";
						$row = WCF::getDB()->getFirstRow($sql);
						if ($row['count']) {
							$this->network['friends3rdGrade'] = $row['count'];
						}
						else {
							$this->network['friends3rdGrade'] = 0;
						}
					}
					else {
						$this->network['friendsOfFriends'] = 0;
					}
				}
			}
		}
		
		// check permission
		WCF::getUser()->checkPermission('user.profile.canView');
		try {
			if ($this->user->ignoredUser) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.profile.error.ignoredUser', array('username' => $this->user->username)));
			}
			if (!$this->user->canViewProfile()) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.profile.error.protectedProfile', array('username' => $this->user->username)));
			}
		}
		catch (NamedUserException $e) {
			WCF::getTPL()->assign(array(
				'user' => $this->user,
				'userID' => $this->userID,
				'connection' => $this->connection,
				'network' => $this->network,
				'userSymbols' => $this->userSymbols,
				'errorMessage' => $e->getMessage()
			));
			WCF::getTPL()->display('userProfileAccessDenied');
			exit;
		}
	}
	
	/**
	 * Returns the user id.
	 * 
	 * @return 	integer
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * Returns the user object.
	 * 
	 * @return 	UserProfile
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Assigns variables to the template engine.
	 */
	public function assignVariables() {
		// call assignVariables event
		EventHandler::fireAction($this, 'assignVariables');
		
		if (!$this->getUser()->friends || !$this->getUser()->shareWhitelist) {
			// remove friends tab
			foreach (UserProfileMenu::getInstance()->menuItems as $parentMenuItem => $items) {
				foreach ($items as $key => $item) {
					if ($item['menuItem'] == 'wcf.user.profile.menu.link.friends') {
						unset(UserProfileMenu::getInstance()->menuItems[$parentMenuItem][$key]);
					}
				}
			}
		}
		
		// assign variables
		WCF::getTPL()->assign(array(
			'user' => $this->user,
			'userID' => $this->userID,
			'connection' => $this->connection,
			'network' => $this->network,
			'userSymbols' => $this->userSymbols
		));
	}
	
	/**
	 * Initializes the user status symbols.
	 */
	protected function initUserSymbols() {
		// gender icon
		if ($this->user->getUserOption('gender')) {
			$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.gender.'.($this->user->getUserOption('gender') == 1 ? 'male' : 'female'), array('username' => $this->user->username));
			$this->userSymbols[] = '<img src="'.StyleManager::getStyle()->getIconPath('gender'.($this->user->getUserOption('gender') == 1 ? 'Male' : 'Female').'S.png').'" alt="" title="'.$title.'" /> <span class="hidden">'.$title.'</span>'; 
		}
		
		// birthday icon
		if ($this->user->birthday) {
			if (substr($this->user->birthday, 5) == DateUtil::formatDate('%m-%d', TIME_NOW, false, true)) {
				$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.birthday', array('username' => $this->user->username, 'age' => $this->user->getAge()));
				$this->userSymbols[] = '<img src="'.StyleManager::getStyle()->getIconPath('birthdayS.png').'" alt="" title="'.$title.'" /> <span class="hidden">'.$title.'</span>';
			}
		}
		
		// friend icon
		if ($this->user->buddy) {
			$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.friend', array('username' => $this->user->username));
			$this->userSymbols[] = '<img src="'.StyleManager::getStyle()->getIconPath('friendsS.png').'" alt="" title="'.$title.'" /> <span class="hidden">'.$title.'</span>';
		}
		
		// banned icon
		if ($this->user->banned) {
			$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.banned', array('username' => $this->user->username));
			$this->userSymbols[] = '<img src="'.StyleManager::getStyle()->getIconPath('bannedS.png').'" alt="" title="'.$title.'" /> <span class="hidden">'.$title.'</span>';
		}
	}
}
?>