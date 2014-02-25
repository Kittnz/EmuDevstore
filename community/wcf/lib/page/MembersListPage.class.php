<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptions.class.php');
require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');

/**
 * Shows a list of all members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class MembersListPage extends SortablePage {
	public static $defaultLetters = '#ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	public $templateName = 'membersList';
	public $itemsPerPage = MEMBERS_LIST_USERS_PER_PAGE;
	public $defaultSortField = MEMBERS_LIST_DEFAULT_SORT_FIELD;
	public $defaultSortOrder = MEMBERS_LIST_DEFAULT_SORT_ORDER;
	
	public $defaultSortFields = array('registrationDate', 'email', 'username', 'lastActivity', 'avatar', 'language');
	public $specialSortFields = array();
	public $realSortField = '';
	public $sqlSelects = '';
	public $sqlJoins = '';
	public $sqlConditions = '';
	public $letter = '';
	public $activeFields = array();
	public $searchID = 0;
	public $userIDs = '';
	public $userOptions;
	public $userTable = '';
	public $userTableAlias = 'user';
	public $letters = array();
	public $headers = array();
	public $members = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get available letters
		$defaultLetters = WCF::getLanguage()->get('wcf.user.membersList.letters');
		if (!empty($defaultLetters) && $defaultLetters != 'wcf.user.membersList.letters') self::$defaultLetters = $defaultLetters;
		
		// get user options
		$this->userOptions = new UserOptions('medium');
		
		// letter
		if (isset($_REQUEST['letter']) && StringUtil::length($_REQUEST['letter']) == 1 && StringUtil::indexOf(self::$defaultLetters, $_REQUEST['letter']) !== false) {
			$this->letter = $_REQUEST['letter'];
		}
		
		// active fields
		$this->activeFields = explode(',', MEMBERS_LIST_COLUMNS);
		if (MODULE_AVATAR != 1 && ($key = array_search('avatar', $this->activeFields)) !== false) {
			unset($this->activeFields[$key]);
		}
		
		// search id
		if (isset($_REQUEST['searchID'])) {
			$this->searchID = intval($_REQUEST['searchID']);
			if ($this->searchID != 0) $this->getSearchResult();
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		$this->realSortField = $this->sortField;
		switch ($this->sortField) {
			case 'registrationDate':
			case 'email':
			case 'username': break;
			case 'lastActivity':
				$this->realSortField = 'lastActivityTimeSortField';
				break;
			case 'avatar':
				$this->realSortField = 'avatarID';
				break;
			case 'language':
				$this->realSortField = 'languageID';
				break;
			default: 
				
				if (!empty($this->letter)) {
					$this->sortField = $this->realSortField = 'username';
				}
				else {
					if (in_array($this->sortField, $this->specialSortFields)) {
						break;
					}
				
					if (in_array($this->sortField, $this->activeFields) && ($option = $this->userOptions->getOption($this->sortField))) {
						$this->realSortField = 'userOption'.$option['optionID'];
					}
					else {
						$this->sortField = $this->realSortField = $this->defaultSortField;
						if ($this->sortField != 'email' && ($option = $this->userOptions->getOption($this->sortField))) {
							$this->realSortField = 'userOption'.$option['optionID'];
						}
						else if ($this->sortField == 'avatar') $this->realSortField = 'avatarID';
						else if ($this->sortField == 'lastActivity') $this->realSortField = 'lastActivityTimeSortField';
					}
				}
		}
		
		// sort by primary user table field
		if (in_array($this->sortField, array('registrationDate', 'email', 'username', 'avatar', 'lastActivity', 'language'))) {
			$this->userTable = 'wcf'.WCF_N.'_user';
			$this->sqlSelects .= 'user_option.*,';
			$this->sqlJoins .= '	LEFT JOIN wcf'.WCF_N.'_user_option_value user_option
						ON (user_option.userID = user.userID) ';
		}
		// sort by user option
		else if (($option = $this->userOptions->getOption($this->sortField))) {
			$this->userTable = 'wcf'.WCF_N.'_user_option_value';
			$this->userTableAlias = 'wcf_user';
			$this->sqlSelects .= 'wcf_user.*,';
			$this->sqlJoins .= '	LEFT JOIN wcf'.WCF_N.'_user wcf_user
						ON (wcf_user.userID = user.userID) ';
		}
		// sort by separate user table
		else {
			$this->userTableAlias = 'wcf_user';
			$this->sqlSelects .= 'wcf_user.*,';
			$this->sqlJoins .= '	LEFT JOIN wcf'.WCF_N.'_user wcf_user
						ON (wcf_user.userID = user.userID) ';
			$this->sqlSelects .= 'user_option.*,';
			$this->sqlJoins .= '	LEFT JOIN wcf'.WCF_N.'_user_option_value user_option
						ON (user_option.userID = user.userID) ';
		}
	}
	
	/**
	 * Gets the result of the search with the given search id.
	 */
	protected function getSearchResult() {
		// get user search from database
		$sql = "SELECT	searchData
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ".$this->searchID."
				AND userID = ".WCF::getUser()->userID."
				AND searchType = 'members'";
		$search = WCF::getDB()->getFirstRow($sql);
		if (!isset($search['searchData'])) {
			throw new IllegalLinkException();
		}
		
		$this->userIDs = implode(',', unserialize($search['searchData']));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// remove deleted fields
		foreach ($this->activeFields as $key => $field) {
			if (preg_match("/^userOption\d+$/", $field) && !$this->userOptions->getOption($field)){
				unset($this->activeFields[$key]);
			}
		}
		
		$this->readMembers();
		$this->loadLetters();
		$this->loadHeaders();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// show page
		WCF::getTPL()->assign(array(
			'letters' => $this->letters,
			'letter' => rawurlencode($this->letter),
			'members' => $this->members,
			'fields' => $this->activeFields,
			'header' => $this->headers,
			'searchID' => $this->searchID,
			'hasFriends' => $this->hasFriends()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active header menu item
		PageMenu::setActiveMenuItem('wcf.header.menu.memberslist');
		
		// check permission
		WCF::getUser()->checkPermission('user.membersList.canView');
		
		if (MODULE_MEMBERS_LIST != 1) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
	
	/**
	 * Gets the list of available letters.
	 */
	protected function loadLetters() {
		for ($i = 0, $j = StringUtil::length(self::$defaultLetters); $i < $j; $i++) {
			$this->letters[] = StringUtil::substring(self::$defaultLetters, $i, 1);
		}
	}
	
	/**
	 * Gets the list of column headers.
	 */
	protected function loadHeaders() {
		foreach ($this->activeFields as $field) {
			$sortable = false;
			$name = '';
			if ($this->userOptions->getOption($field)) {
				$sortable = true;
				$name = 'wcf.user.option.'.$field;
			}
			else {
				if (in_array($field, $this->defaultSortFields) || in_array($field, $this->specialSortFields)) $sortable = true;
				$name = 'wcf.user.'.$field;
			}
			
			if (!empty($name)) {
				$this->headers[] = array('field' => $field, 'name' => $name, 'sortable' => $sortable);
			}
		}
	}
	
	/**
	 * Returns the data of a member.
	 * 
	 * @param	array		$row
	 * @return	array 
	 */
	protected function getMember($row) {
		$user = new UserProfile(null, $row);
		$username = StringUtil::encodeHTML($row['username']);
		$protectedProfile = ($user->protectedProfile && WCF::getUser()->userID != $user->userID);
		$userData = array('user' => $user, 'encodedUsername' => $username, 'protectedProfile' => $protectedProfile);
		
		foreach ($this->activeFields as $field) {
			switch ($field) {
				// default fields
				case 'username':
					$userData['username'] = '<div class="containerIconSmall">';
					if ($user->isOnline()) {
						$title = WCF::getLanguage()->get('wcf.user.online', array('$username' => $username));
						$userData['username'] .= '<img src="'.StyleManager::getStyle()->getIconPath('onlineS.png').'" alt="'.$title.'" title="'.$title.'" />';
					}
					else {
						$title = WCF::getLanguage()->get('wcf.user.offline', array('$username' => $username));
						$userData['username'] .= '<img src="'.StyleManager::getStyle()->getIconPath('offlineS.png').'" alt="'.$title.'" title="'.$title.'" />';
					}
					$userData['username'] .= '</div><div class="containerContentSmall">';
					$title = WCF::getLanguage()->get('wcf.user.viewProfile', array('$username' => $username));
					$userData['username'] .= '<p><a href="index.php?page=User&amp;userID='.$row['userID'].SID_ARG_2ND.'" title="'.$title.'">'.$username.'</a></p>';
					if (MODULE_USER_RANK == 1 && $user->getUserTitle()) {
						$userData['username'] .= '<p class="smallFont">'.$user->getUserTitle().' '.($user->getRank() ? $user->getRank()->getImage() : '').'</p>';
					}
					$userData['username'] .= '</div>';
					
					break;
					
				case 'registrationDate':
					$userData['registrationDate'] = DateUtil::formatDate(null, $row['registrationDate']);
					break;
					
				case 'lastActivity':
					$userData['lastActivity'] = '';
					if ($user->invisible != 1 || WCF::getUser()->getPermission('admin.general.canViewInvisible')) {
						$userData['lastActivity'] = DateUtil::formatTime(null, $row['lastActivityTime']);
					}
					break;
					
				case 'avatar':
					if ($user->getAvatar() && ($row['userID'] == WCF::getUser()->userID || WCF::getUser()->getPermission('user.profile.avatar.canViewAvatar'))) {
						$user->getAvatar()->setMaxHeight(50);
						$title = WCF::getLanguage()->get('wcf.user.viewProfile', array('$username' => $username));
						$userData['avatar'] = '<a href="index.php?page=User&amp;userID='.$row['userID'].SID_ARG_2ND.'" title="'.$title.'">'.$user->getAvatar()->__toString().'</a>';
					}
					else $userData['avatar'] = '';
					break;
					
				case 'language':
					if ($row['languageID'] && $row['languageCode']) {
						$userData['language'] = '<img src="'.RELATIVE_WCF_DIR.'icon/language'.ucfirst($row['languageCode']).'S.png" alt="'.WCF::getLanguage()->get('wcf.global.language.'.$row['languageCode']).'" title="'.WCF::getLanguage()->get('wcf.global.language.'.$row['languageCode']).'" />';
					}
					else $userData['language'] = '';
					break;
					
				// user options	
				default:
					$userData[$field] = '';
					$option = $this->userOptions->getOptionValue($field, $user);
					if (!$protectedProfile && $option) {
						$userData[$field] = $option['optionValue'];
					}
			}
		}
		
		return $userData;
	}
	
	/**
	 * Builds sql conditions.
	 */
	protected function buildSqlConditions() {
		if (!empty($this->letter)) {
			if (!empty($this->sqlConditions)) {
				$this->sqlConditions .= ' AND ';
			}
			if ($this->letter == '#') {
				$this->sqlConditions .= " SUBSTRING(username,1,1) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')";
			}
			else {
				$this->sqlConditions .= " BINARY UPPER(SUBSTRING(username,1,1)) = '".escapeString($this->letter)."'";
			}
		}
		
		if (!empty($this->userIDs)) {
			if (!empty($this->sqlConditions)) {
				$this->sqlConditions .= ' AND ';
			}
			$this->sqlConditions .= " user.userID IN (".$this->userIDs.")";
		}
	}
	
	/**
	 * Counts the number of users.
	 * 
	 * @return	integer
	 */
	public function countItems() {
		parent::countItems();
		
		$this->buildSqlConditions();
		
		// count members
		$sql = "SELECT	COUNT(*) AS count
			FROM	".$this->userTable." user
			".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets user ids for active page.
	 * 
	 * @return	string
	 */
	protected function getUserIDs() {
		$userIDs = '';
		if ($this->sortField == 'lastActivity') {
			$sql = "SELECT		user.userID,
						".(!WCF::getUser()->getPermission('admin.general.canViewInvisible') ? "IF(userOption".User::getUserOptionID('invisible')." = 1, 0, lastActivityTime) AS lastActivityTimeSortField" : 'lastActivityTime AS lastActivityTimeSortField')."
				FROM		".$this->userTable." user
				".(!WCF::getUser()->getPermission('admin.general.canViewInvisible') ? "
				LEFT JOIN	wcf".WCF_N."_user_option_value user_option
				ON		(user_option.userID = user.userID)
				": '')."
				".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
				ORDER BY	".$this->realSortField." ".$this->sortOrder.
				($this->realSortField != 'username' ? ', user.userID' : '');
		}
		else {
			$sql = "SELECT		user.userID
				FROM		".$this->userTable." user
				".(!empty($this->sqlConditions) ? "WHERE ".$this->sqlConditions : '')."
				ORDER BY	user.".$this->realSortField." ".$this->sortOrder.
				($this->realSortField != 'username' ? ', user.userID' : '');
		}
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!empty($userIDs)) $userIDs .= ',';
			$userIDs .= $row['userID'];
		}
		
		return $userIDs;
	}
	
	/**
	 * Gets the list of members for the current page number.
	 */
	protected function readMembers() {
		if ($this->items) {
			// get user ids for active page
			$userIDs = $this->getUserIDs();
			
			// get users
			if (!empty($userIDs)) {
				$sql = "SELECT		".$this->sqlSelects."
							".(in_array('avatar', $this->activeFields) ? "avatar.*," : '')."
							".(in_array('language', $this->activeFields) ? "language.languageCode," : '')."
							user.*,
							rank.*,
							".(!WCF::getUser()->getPermission('admin.general.canViewInvisible') ? "IF(userOption".User::getUserOptionID('invisible')." = 1, 0, lastActivityTime) AS lastActivityTimeSortField" : 'lastActivityTime AS lastActivityTimeSortField')."
					FROM		".$this->userTable." user
					".$this->sqlJoins."
					".(in_array('avatar', $this->activeFields) ? "
					LEFT JOIN	wcf".WCF_N."_avatar avatar
					ON		(avatar.avatarID = ".$this->userTableAlias.".avatarID)
					" : '')."
					".(in_array('language', $this->activeFields) ? "
					LEFT JOIN	wcf".WCF_N."_language language
					ON		(language.languageID = ".$this->userTableAlias.".languageID)
					" : '')."
					LEFT JOIN 	wcf".WCF_N."_user_rank rank
					ON		(rank.rankID = ".$this->userTableAlias.".rankID)
					WHERE		user.userID IN (".$userIDs.")
					ORDER BY	".($this->sortField != 'lastActivity' ? "user." : '').$this->realSortField." ".$this->sortOrder.
					($this->realSortField != 'username' ? ', user.userID' : '');
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (empty($row['username'])) continue;
					$this->members[] = $this->getMember($row);
				}
			}
		}
	}
	
	/**
	 * Returns true, if the active user has friends.
	 * 
	 * @return	boolean
	 */
	public static function hasFriends() {
		if (!WCF::getUser()->userID) return 0;
		
		// count members
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_whitelist
			WHERE	userID = ".WCF::getUser()->userID."
				AND confirmed = 1";
		$row = WCF::getDB()->getFirstRow($sql);
		return ($row['count'] > 0);
	}
}
?>