<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Avatar.class.php');

// wbb imports
require_once(WBB_DIR.'lib/data/user/AbstractWBBUserSession.class.php');

/**
 * Represents a user session in the forum.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	data.user
 * @category 	Burning Board
 */
class WBBUserSession extends AbstractWBBUserSession {
	protected $closedCategories;
	protected $ignoredBoards;
	protected $boardVisits;
	protected $boardSubscriptions;
	protected $ignores = null;
	protected $outstandingNotifications = null;
	protected $subscriptionsUnreadCount = null;
	protected $hasSubscriptions = null;
	protected $outstandingGroupApplications = null;
	protected $outstandingModerations = null;
	protected $invitations = null;
	
	/**
	 * displayable avatar object.
	 *
	 * @var DisplayableAvatar
	 */
	protected $avatar = null;
	
	/**
	 * @see UserSession::__construct()
	 */
	public function __construct($userID = null, $row = null, $username = null) {
		$this->sqlSelects .= "	wbb_user.*, avatar.*, wbb_user.userID AS wbbUserID,
					GROUP_CONCAT(DISTINCT whitelist.whiteUserID ORDER BY whitelist.whiteUserID ASC SEPARATOR ',') AS buddies,
					GROUP_CONCAT(DISTINCT blacklist.blackUserID ORDER BY blacklist.blackUserID ASC SEPARATOR ',') AS ignoredUser,
					(SELECT COUNT(*) FROM wcf".WCF_N."_user_whitelist WHERE whiteUserID = user.userID AND confirmed = 0 AND notified = 0) AS numberOfInvitations,";
		$this->sqlJoins .= " 	LEFT JOIN wbb".WBB_N."_user wbb_user ON (wbb_user.userID = user.userID)
					LEFT JOIN wcf".WCF_N."_user_whitelist whitelist ON (whitelist.userID = user.userID AND whitelist.confirmed = 1)
					LEFT JOIN wcf".WCF_N."_user_blacklist blacklist ON (blacklist.userID = user.userID)
					LEFT JOIN wcf".WCF_N."_avatar avatar ON (avatar.avatarID = user.avatarID) ";
		parent::__construct($userID, $row, $username);
	}
	
	/**
	 * @see User::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		if (MODULE_AVATAR == 1 && !$this->disableAvatar && $this->showAvatar) {
			if (MODULE_GRAVATAR == 1 && $this->gravatar) {
				$this->avatar = new Gravatar($this->gravatar);
			}
			else if ($this->avatarID) {
				$this->avatar = new Avatar(null, $data);
			}
		}
	}
	
	/**
	 * Updates the user session.
	 */
	public function update() {
		// update global last activity timestamp
		WBBUserSession::updateLastActivityTime($this->userID);
		
		if (!$this->wbbUserID) {
			// define default values
			$this->data['boardLastVisitTime'] = TIME_NOW;
			$this->data['boardLastActivityTime'] = TIME_NOW;
			$this->data['boardLastMarkAllAsReadTime'] = TIME_NOW - VISIT_TIME_FRAME;
			
			// create wbb user record
			$sql = "INSERT IGNORE INTO	wbb".WBB_N."_user
							(userID, boardLastVisitTime, boardLastActivityTime, boardLastMarkAllAsReadTime)
				VALUES			(".$this->userID.", ".$this->boardLastVisitTime.", ".$this->boardLastActivityTime.", ".$this->boardLastMarkAllAsReadTime.")";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		else {
			WBBUserSession::updateBoardLastActivityTime($this->userID);
		}
		
		$this->getClosedCategories();
		$this->getIgnoredBoards();
		$this->getBoardSubscriptions();
		$this->getBoardVisits();
	}
	
	/**
	 * Initialises the user session.
	 */
	public function init() {
		parent::init();
		
		$this->invitations = $this->ignores = $this->outstandingNotifications = $this->subscriptionsUnreadCount = $this->hasSubscriptions = $this->outstandingModerations = null;
	}
	
	/**
	 * @see UserSession::getGroupData()
	 */
	protected function getGroupData() {
		parent::getGroupData();
		
		// get user permissions (board_to_user)
		$userPermissions = array();
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_to_user
			WHERE		userID = ".$this->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['userID']);
			$userPermissions[$boardID] = $row;
		}
		
		if (count($userPermissions)) {
			require_once(WBB_DIR.'lib/data/board/Board.class.php');
			Board::inheritPermissions(0, $userPermissions);
		
			foreach ($userPermissions as $boardID => $row) {
				foreach ($row as $key => $val) {
					if ($val != -1) {
						$this->boardPermissions[$boardID][$key] = $val;
					}
				}
			}
		}
		
		// get group leader status
		if (MODULE_MODERATED_USER_GROUP == 1) {
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_group_leader leader, wcf".WCF_N."_group usergroup
				WHERE	(leader.leaderUserID = ".$this->userID."
					OR leader.leaderGroupID IN (".implode(',', $this->getGroupIDs())."))
					AND leader.groupID = usergroup.groupID";
			$row = WCF::getDB()->getFirstRow($sql);
			$this->groupData['wcf.group.isGroupLeader'] = ($row['count'] ? 1 : 0);
		}
	}
	
	/**
	 * Returns true, if the active user ignores the given user.
	 * 
	 * @return	boolean
	 */
	public function ignores($userID) {
		if ($this->ignores === null) {
			if ($this->ignoredUser) {
				$this->ignores = explode(',', $this->ignoredUser);
			}
			else {
				$this->ignores = array();
			}
		}
		
		return in_array($userID, $this->ignores);
	}
	
	/**
	 * Sets the global board last visit timestamp.
	 */
	public function setLastVisitTime($timestamp) {
		$this->data['boardLastVisitTime'] = $timestamp;
		if (($timestamp - VISIT_TIME_FRAME) > $this->boardLastMarkAllAsReadTime) {
			$this->data['boardLastMarkAllAsReadTime'] = ($timestamp - VISIT_TIME_FRAME);
		}
		
		$sql = "UPDATE	wbb".WBB_N."_user
			SET	boardLastVisitTime = ".$timestamp.",
				boardLastActivityTime = ".TIME_NOW.",
				boardLastMarkAllAsReadTime = ".$this->boardLastMarkAllAsReadTime."
			WHERE	userID = ".$this->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Sets the last mark all as read timestamp.
	 */
	public function setLastMarkAllAsReadTime($timestamp) {
		$this->data['boardLastMarkAllAsReadTime'] = $timestamp;
		
		$sql = "UPDATE	wbb".WBB_N."_user
			SET	boardLastMarkAllAsReadTime = ".$timestamp."
			WHERE	userID = ".$this->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Loads the board visits of this user from database.
	 */
	protected function getBoardVisits() {
		$this->boardVisits = array();
		
		$sql = "SELECT	boardID, lastVisitTime
			FROM 	wbb".WBB_N."_board_visit
			WHERE 	userID = ".$this->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->boardVisits[$row['boardID']] = $row['lastVisitTime'];
		}
	}
	
	/**
	 * Returns the board visit of this user for the board with the given board id.
	 * 
	 * @param	integer		$boardID
	 * @return	integer		board visit of this user for the board with the given board id
	 */
	public function getBoardVisitTime($boardID) {
		$boardVisitTime = 0;
		if (isset($this->boardVisits[$boardID])) $boardVisitTime = $this->boardVisits[$boardID];
		
		if ($boardVisitTime < $this->getLastMarkAllAsReadTime()) {
			$boardVisitTime = $this->getLastMarkAllAsReadTime();
		}
		
		return $boardVisitTime;
	}
	
	/**
	 * Sets the board visit of this user for the board with the given board id.
	 *
	 * @param	integer		$boardID
	 */
	public function setBoardVisitTime($boardID) {
		$sql = "REPLACE INTO	wbb".WBB_N."_board_visit
					(userID, boardID, lastVisitTime)
			VALUES		(".$this->userID.",
					".$boardID.",
					".TIME_NOW.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		WCF::getSession()->resetUserData();
		
		$this->boardVisits[$boardID] = TIME_NOW;
	}
	
	/**
	 * Loads the closed categories of this user from database.
	 */
	protected function getClosedCategories() {
		$this->closedCategories = array();
		
		$sql = "SELECT 	*
			FROM 	wbb".WBB_N."_board_closed_category_to_user
			WHERE 	userID = ".$this->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->closedCategories[$row['boardID']] = $row['isClosed'];
		}
	}
	
	/**
	 * Returns true, if the category with the given board id is closed by this user.
	 *
	 * @param	integer		$boardID
	 * @return	boolean
	 */
	public function isClosedCategory($boardID) {
		if (!isset($this->closedCategories[$boardID])) return 0;
		return $this->closedCategories[$boardID];
	}
	
	/**
	 * Loads the ignored boards of this user from database.
	 */
	protected function getIgnoredBoards() {
		$this->ignoredBoards = array();
		
		$sql = "SELECT 	*
			FROM 	wbb".WBB_N."_board_ignored_by_user
			WHERE 	userID = ".$this->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->ignoredBoards[$row['boardID']] = $row['boardID'];
		}
	}
	
	/**
	 * Returns true, if the board with the given board id is ignored by this user.
	 *
	 * @param	integer		$boardID
	 * @return	boolean
	 */
	public function isIgnoredBoard($boardID) {
		if (isset($this->ignoredBoards[$boardID])) return 1;
		return 0;
	}
	
	/**
	 * Closes the category with the given board id for this user.
	 *
	 * @param	integer		$boardID
	 * @param	integer		$close		1 closes the category
	 *						-1 opens the category
	 */
	public function closeCategory($boardID, $close = 1) {
		require_once(WBB_DIR.'lib/data/board/Board.class.php');
		$board = Board::getBoard($boardID);
		if (!$board->isCategory()) {
			throw new IllegalLinkException();
		}
		
		$sql = "REPLACE INTO	wbb".WBB_N."_board_closed_category_to_user
					(userID, boardID, isClosed)
			VALUES		(".$this->userID.",
					".$boardID.",
					".$close.")";
		WCF::getDB()->registerShutdownUpdate($sql);
		WCF::getSession()->resetUserData();

		$this->closedCategories[$boardID] = $close;
	}
	
	/**
	 * Loads the subscribed boards of this user from database.
	 */
	protected function getBoardSubscriptions() {
		$this->boardSubscriptions = array();
		
		$sql = "SELECT	*
			FROM 	wbb".WBB_N."_board_subscription
			WHERE 	userID = ".$this->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->boardSubscriptions[$row['boardID']] = $row;
		}
	}
	
	/**
	 * Returns true, if the board with the given board id is a subscribed board of this user.
	 *
	 * @param	integer		$boardID
	 * @return	boolean		true, if the board with the given board id is a subscribed board of this user
	 */
	public function isBoardSubscription($boardID) {
		if (isset($this->boardSubscriptions[$boardID])) return true;
		return false;
	}
	
	/**
	 * Subscribes the board with the given board id for this user.
	 *
	 * @param	integer		$boardID
	 */
	public function subscribeBoard($boardID) {
		if (!$this->isBoardSubscription($boardID)) {
			$sql = "REPLACE INTO	wbb".WBB_N."_board_subscription
						(userID, boardID, enableNotification)
				VALUES 		(".$this->userID.",
						".$boardID.",
						".WCF::getUser()->enableEmailNotification.")";
			WCF::getDB()->registerShutdownUpdate($sql);
			WCF::getSession()->resetUserData();
			WCF::getSession()->unregister('hasSubscriptions');
			
			$this->boardSubscriptions[$boardID] = array('userID' => $this->userID, 'boardID' => $boardID, 'enableNotification' => WCF::getUser()->enableEmailNotification, 'emails' => 0);
		}
	}
	
	/**
	 * Unsubscribes the board with the given board id for this user.
	 *
	 * @param	integer		$boardID
	 */
	public function unsubscribeBoard($boardID) {
		if ($this->isBoardSubscription($boardID)) {
			$sql = "DELETE FROM	wbb".WBB_N."_board_subscription
				WHERE 		userID = ".$this->userID." AND boardID = ".$boardID;
			WCF::getDB()->registerShutdownUpdate($sql);
			WCF::getSession()->resetUserData();
			WCF::getSession()->unregister('hasSubscriptions');
			
			unset($this->boardSubscriptions[$boardID]);
		}
	}
	
	/**
	 * Updates the subscription of the board with the given board for this user.
	 *
	 * @param	integer		$boardID
	 */
	public function updateBoardSubscription($boardID) {
		if (isset($this->boardSubscriptions[$boardID]) && $this->boardSubscriptions[$boardID]['emails'] > 0) {
			$sql = "UPDATE	wbb".WBB_N."_board_subscription
				SET 	emails = 0
				WHERE 	userID = ".$this->userID."
					AND boardID = ".$boardID;
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Sets the thread visit of this user for the thread with the given thread id.
	 *
	 * @param	integer		$threadID
	 */
	public function setThreadVisitTime($threadID, $timestamp = TIME_NOW) {
		$sql = "REPLACE INTO	wbb".WBB_N."_thread_visit
					(userID, threadID, lastVisitTime)
			VALUES 		(".$this->userID.",
					".$threadID.",
					".$timestamp.")";
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Updates the global last activity timestamp in user database.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$timestamp
	 */
	public static function updateLastActivityTime($userID, $timestamp = TIME_NOW) {
		// update lastActivity in wcf user table
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	lastActivityTime = ".$timestamp."
			WHERE	userID = ".$userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * Updates the board last activity timestamp in user database.
	 * 
	 * @param	integer		$userID
	 * @param	integer		$timestamp
	 */
	public static function updateBoardLastActivityTime($userID, $timestamp = TIME_NOW) {
		// update boardLastActivity in wbb user table
		$sql = "UPDATE	wbb".WBB_N."_user
			SET	boardLastActivityTime = ".$timestamp."
			WHERE	userID = ".$userID;
		WCF::getDB()->registerShutdownUpdate($sql);
	}
	
	/**
	 * @see	PM::getOutstandingNotifications()
	 */
	public function getOutstandingNotifications() {
		if ($this->outstandingNotifications === null) {
			require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
			$this->outstandingNotifications = PM::getOutstandingNotifications(WCF::getUser()->userID);
		}
		
		return $this->outstandingNotifications;
	}
	
	/**
	 * Returns the number of unread subscribed threads.
	 * 
	 * @return	integer
	 */
	public function getSubscriptionsUnreadCount() {
		if ($this->subscriptionsUnreadCount === null) {
			$this->subscriptionsUnreadCount = 0;
			
			// update subscriptions status
			$lastSubscriptionsStatusUpdateTime = intval(WCF::getSession()->getVar('lastSubscriptionsStatusUpdateTime'));
			if ($lastSubscriptionsStatusUpdateTime < TIME_NOW - 180) {
				require_once(WBB_DIR.'lib/data/thread/SubscribedThread.class.php');
				$this->subscriptionsUnreadCount = SubscribedThread::getUnreadCount();
				
				// save status
				WCF::getSession()->register('subscriptionsUnreadCount', $this->subscriptionsUnreadCount);
				WCF::getSession()->register('lastSubscriptionsStatusUpdateTime', TIME_NOW);
			}
			else {
				$this->subscriptionsUnreadCount = intval(WCF::getSession()->getVar('subscriptionsUnreadCount'));
			}
		}
		
		return $this->subscriptionsUnreadCount;
	}
	
	/**
	 * Returns true, if the user has subscriptions.
	 */
	public function hasSubscriptions() {
		if ($this->hasSubscriptions === null) {
			$this->hasSubscriptions = WCF::getSession()->getVar('hasSubscriptions');
			if ($this->hasSubscriptions === null) {
				$this->hasSubscriptions = false;
				$sql = "SELECT	COUNT(*) AS count
					FROM	wbb".WBB_N."_thread_subscription
					WHERE	userID = ".$this->userID;
				$row = WCF::getDB()->getFirstRow($sql);
				if ($row['count']) $this->hasSubscriptions = true;
				else {
					$sql = "SELECT	COUNT(*) AS count
						FROM	wbb".WBB_N."_board_subscription
						WHERE	userID = ".$this->userID;
					$row = WCF::getDB()->getFirstRow($sql);
					if ($row['count']) $this->hasSubscriptions = true;
				}
				
				WCF::getSession()->register('hasSubscriptions', $this->hasSubscriptions);
			}
		}
		
		return $this->hasSubscriptions;
	}
	
	/**
	 * Returns true, if the user is a group leader.
	 * 
	 * @return	boolean
	 */
	public function isGroupLeader() {
		if (MODULE_MODERATED_USER_GROUP == 1) {
			return $this->getPermission('wcf.group.isGroupLeader');
		}
		return false;
	}
	
	/**
	 * Returns the number of outstanding group applications.
	 * 
	 * @return	integer
	 */
	public function getOutstandingGroupApplications() {
		if (MODULE_MODERATED_USER_GROUP == 1) {
			if ($this->outstandingGroupApplications === null) {
				$this->outstandingGroupApplications = WCF::getSession()->getVar('outstandingGroupApplications');
				if ($this->outstandingGroupApplications === null) {
					$this->outstandingGroupApplications = 0;
					$sql = "SELECT	COUNT(*) AS count
						FROM 	wcf".WCF_N."_group_application
						WHERE 	groupID IN (
								SELECT	groupID
								FROM	wcf".WCF_N."_group_leader leader
								WHERE	leader.leaderUserID = ".$this->userID."
									OR leader.leaderGroupID IN (".implode(',', $this->getGroupIDs()).")
							)
							AND applicationStatus IN (0,1)";
					$row = WCF::getDB()->getFirstRow($sql);
					$this->outstandingGroupApplications = $row['count'];
					
					WCF::getSession()->register('outstandingGroupApplications', $this->outstandingGroupApplications);
				}
			}
			
			return $this->outstandingGroupApplications;
		}
		
		return 0;
	}
	
	/**
	 * Returns true, if the user is a moderator.
	 * 
	 * @return	integer
	 */
	public function isModerator() {
		return ($this->getPermission('mod.board.canEnableThread') || $this->getPermission('mod.board.canEnablePost') || $this->getPermission('mod.board.canEditPost'));
	}
	
	/**
	 * Returns the number of outstanding thread / post moderations.
	 * 
	 * @return	integer
	 */
	public function getOutstandingModerations() {
		if ($this->outstandingModerations === null) {
			$this->outstandingModerations = WCF::getSession()->getVar('outstandingModerations');
			if ($this->outstandingModerations === null) {
				$this->outstandingModerations = 0;
				require_once(WBB_DIR.'lib/data/board/Board.class.php');
				
				// disabled threads
				$boardIDs = Board::getModeratedBoards('canEnableThread');
				if (!empty($boardIDs)) {
					$sql = "SELECT	COUNT(*) AS count
						FROM	wbb".WBB_N."_thread
						WHERE	isDisabled = 1
							AND boardID IN (".$boardIDs.")
							AND movedThreadID = 0";
					$row = WCF::getDB()->getFirstRow($sql);
					$this->outstandingModerations += $row['count'];
				}

				// disabled posts
				$boardIDs = Board::getModeratedBoards('canEnablePost');
				if (!empty($boardIDs)) {
					$sql = "SELECT		COUNT(*) AS count
						FROM		wbb".WBB_N."_post post
						LEFT JOIN	wbb".WBB_N."_thread thread
						ON		(thread.threadID = post.threadID)
						WHERE		post.isDisabled = 1
								AND thread.boardID IN (".$boardIDs.")";
					$row = WCF::getDB()->getFirstRow($sql);
					$this->outstandingModerations += $row['count'];
				}
				
				// reported posts
				$boardIDs = Board::getModeratedBoards('canEditPost');
				$boardIDs2 = Board::getModeratedBoards('canReadDeletedPost');
				if (!empty($boardIDs)) {
					$sql = "SELECT		COUNT(*) AS count
						FROM		wbb".WBB_N."_post_report report
						LEFT JOIN	wbb".WBB_N."_post post
						ON		(post.postID = report.postID)
						LEFT JOIN	wbb".WBB_N."_thread thread
						ON		(thread.threadID = post.threadID)
						WHERE		thread.boardID IN (".$boardIDs.")
								AND (post.isDeleted = 0".(!empty($boardIDs2) ? " OR thread.boardID IN (".$boardIDs2.")" : '').")";
					$row = WCF::getDB()->getFirstRow($sql);
					$this->outstandingModerations += $row['count'];
				}
				
				WCF::getSession()->register('outstandingModerations', $this->outstandingModerations);
			}
		}
			
		return $this->outstandingModerations;
	}
	
	/**
	 * Returns the last mark all as read timestamp.
	 * 
	 * @return	integer
	 */
	public function getLastMarkAllAsReadTime() {
		return $this->boardLastMarkAllAsReadTime;
	}
	
	/**
	 * @see	PM::getOutstandingNotifications()
	 */
	public function getInvitations() {
		if ($this->invitations === null) {
			$this->invitations = array();
			$sql = "SELECT		user_table.userID, user_table.username
				FROM		wcf".WCF_N."_user_whitelist whitelist
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON		(user_table.userID = whitelist.userID)
				WHERE		whitelist.whiteUserID = ".$this->userID."
						AND whitelist.confirmed = 0
						AND whitelist.notified = 0
				ORDER BY	whitelist.time";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->invitations[] = new User(null, $row);
			}
		}
		
		return $this->invitations;
	}
	
	/**
	 * Returns the avatar of this user.
	 * 
	 * @return	DisplayableAvatar
	 */
	public function getAvatar() {
		return $this->avatar;
	}
}
?>