<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Shows the result of a user search.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UserListPage extends SortablePage {
	// system
	public $itemsPerPage = 50;
	public $defaultSortField = 'username';
	public $templateName = 'userList';
	
	// parameters
	public $searchID = 0;
	
	// data
	public $userIDs = array();
	public $markedUsers = array();
	public $users = array();
	public $url = '';
	public $columns = array('email', 'registrationDate');
	public $outputObjects = array();
	public $options = array();
	public $columnValues = array();
	public $columnHeads = array();
	public $sqlConditions = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['searchID'])) {
			$this->searchID = intval($_REQUEST['searchID']);
			if ($this->searchID) $this->readSearchResult();
			if (!count($this->userIDs)) {
				throw new IllegalLinkException();
			}
			if (!empty($this->sqlConditions)) $this->sqlConditions .= ' AND ';
			$this->sqlConditions .= "user_table.userID IN (".implode(',', $this->userIDs).")";
		}
		
		// get user options
		$this->readUserOptions();
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'email':
			case 'userID':
			case 'registrationDate':
			case 'username': break;
			default: 
				if (!isset($this->options[$this->sortField])) {
					$this->sortField = $this->defaultSortField;
				}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get marked users
		$this->markedUsers = WCF::getSession()->getVar('markedUsers');
		if ($this->markedUsers == null || !is_array($this->markedUsers)) $this->markedUsers = array();
		
		// get columns heads
		$this->readColumnsHeads();
		
		// get users
		$this->readUsers();
		
		// build page url
		$this->url = 'index.php?page=UserList&searchID='.$this->searchID.'&action='.rawurlencode($this->action).'&pageNo='.$this->pageNo.'&sortField='.$this->sortField.'&sortOrder='.$this->sortOrder.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'searchID' => $this->searchID,
			'markedUsers' => count($this->markedUsers),
			'url' => $this->url,
			'columnHeads' => $this->columnHeads,
			'columnValues' => $this->columnValues
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.'.($this->searchID ? 'search' : 'list'));
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canSearchUser');
		
		parent::show();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user user_table
			".(!empty($this->sqlConditions) ? 'WHERE '.$this->sqlConditions : '');
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * Gets the list of results.
	 */
	protected function readUsers() {
		// get user ids
		$userIDs = array();
		$sql = "SELECT		user_table.userID
			FROM		wcf".WCF_N."_user user_table
			".(isset($this->options[$this->sortField]) ? "LEFT JOIN wcf".WCF_N."_user_option_value USING (userID)" : '')."
			".(!empty($this->sqlConditions) ? 'WHERE '.$this->sqlConditions : '')."
			ORDER BY	".(($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'userOption'.$this->options[$this->sortField]['optionID'] : $this->sortField)." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql, $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$userIDs[] = $row['userID'];
		}

		// get user data
		if (count($userIDs)) {
			$sql = "SELECT		option_value.*, user_table.*,
						GROUP_CONCAT(groupID SEPARATOR ',') AS groupIDs
				FROM		wcf".WCF_N."_user user_table
				LEFT JOIN	wcf".WCF_N."_user_option_value option_value
				ON		(option_value.userID = user_table.userID)
				LEFT JOIN	wcf".WCF_N."_user_to_groups groups
				ON		(groups.userID = user_table.userID)
				WHERE		user_table.userID IN (".implode(',', $userIDs).")
				GROUP BY	user_table.userID
				ORDER BY	".(($this->sortField != 'email' && isset($this->options[$this->sortField])) ? 'option_value.userOption'.$this->options[$this->sortField]['optionID'] : 'user_table.'.$this->sortField)." ".$this->sortOrder;
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$accessible = Group::isAccessibleGroup(explode(',', $row['groupIDs']));
				$row['accessible'] = $accessible;
				$row['deletable'] = ($accessible && WCF::getUser()->getPermission('admin.user.canDeleteUser') && $row['userID'] != WCF::getUser()->userID) ? 1 : 0;
				$row['editable'] = ($accessible && WCF::getUser()->getPermission('admin.user.canEditUser')) ? 1 : 0;
				$row['isMarked'] = intval(in_array($row['userID'], $this->markedUsers));
				
				$this->users[] = new User(null, $row);
			}
			
			// get special columns
			foreach ($this->users as $key => $user) {
				foreach ($this->columns as $column) {
					if (isset($this->options[$column])) {
						if ($this->options[$column]['outputClass']) {
							$outputObj = $this->getOutputObject($this->options[$column]['outputClass']);
							$this->columnValues[$user->userID][$column] = $outputObj->getOutput($user, $this->options[$column], $user->{$column});
						}
						else {
							$this->columnValues[$user->userID][$column] = StringUtil::encodeHTML($user->{$column});
						}
					}
					else {
						switch ($column) {
							case 'email':
								$this->columnValues[$user->userID][$column] = '<a href="mailto:'.StringUtil::encodeHTML($user->email).'">'.StringUtil::encodeHTML($user->email).'</a>';
								break;
							case 'registrationDate':
								$this->columnValues[$user->userID][$column] = DateUtil::formatDate(null, $user->{$column});
								break;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Gets the result of the search with the given search id.
	 */
	protected function readSearchResult() {
		// get user search from database
		$sql = "SELECT	searchData
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ".$this->searchID."
				AND userID = ".WCF::getUser()->userID."
				AND searchType = 'users'";
		$search = WCF::getDB()->getFirstRow($sql);
		if (!isset($search['searchData'])) {
			throw new IllegalLinkException();
		}
		
		$data = unserialize($search['searchData']);
		$this->userIDs = $data['matches'];
		$this->itemsPerPage = $data['itemsPerPage'];
		$this->columns = $data['columns'];
		unset($data);
	}
	
	/**
	 * Gets the user options from cache.
	 */
	protected function readUserOptions() {
		// add cache resource
		$cacheName = 'user-option-'.PACKAGE_ID;
		WCF::getCache()->addResource($cacheName, WCF_DIR.'cache/cache.'.$cacheName.'.php', WCF_DIR.'lib/system/cache/CacheBuilderOption.class.php');
		
		// get options
		$this->options = WCF::getCache()->get($cacheName, 'options');
	}
	
	/**
	 * Reads the column heads.
	 */
	protected function readColumnsHeads() {
		foreach ($this->columns as $column) {
			if (isset($this->options[$column])) {
				$this->columnHeads[$column] = 'wcf.user.option.'.$column;
			}
			else {
				$this->columnHeads[$column] = 'wcf.user.'.$column;
			}
		}
	}
	
	/**
	 * Returns an object of the requested option output type.
	 * 
	 * @param	string			$type
	 * @return	UserOptionOutput
	 */
	protected function getOutputObject($className) {
		if (!isset($this->outputObjects[$className])) {
			// include class file
			$classPath = WCF_DIR.'lib/data/user/option/'.$className.'.class.php';
			if (!file_exists($classPath)) {
				throw new SystemException("unable to find class file '".$classPath."'", 11000);
			}
			require_once($classPath);
			
			// create instance
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			$this->outputObjects[$className] = new $className();
		}
		
		return $this->outputObjects[$className];
	}
}
?>