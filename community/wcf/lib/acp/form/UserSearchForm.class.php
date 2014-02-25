<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserOptionListForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Shows the user search form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserSearchForm extends UserOptionListForm {
	public $templateName = 'userSearch';
	public $menuItemName = 'wcf.acp.menu.link.user.search';
	
	public $staticParameters = array();
	public $matchExactly = array();
	public $matches = array();
	public $userIDs = array();
	public $users = array();
	public $deletedUsers = false;
	public $conditions;
	public $options;
	public $searchID;
	
	// parameters
	public $invertGroupIDs = 0;
	public $sortField = 'username';
	public $sortOrder = 'ASC';
	public $itemsPerPage = 50;
	public $columns = array('email', 'registrationDate');
	public $maxResults = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['staticParameters']) && is_array($_POST['staticParameters'])) {
			$this->staticParameters = $_POST['staticParameters'];
		}
		if (isset($_POST['matchExactly']) && is_array($_POST['matchExactly'])) {
			$this->matchExactly = $_POST['matchExactly'];
		}
		if (!isset($this->staticParameters['groupIDs']) || !is_array($this->staticParameters['groupIDs'])) {
			$this->staticParameters['groupIDs'] = array();
		}
		if (!isset($this->staticParameters['languageIDs']) || !is_array($this->staticParameters['languageIDs'])) {
			$this->staticParameters['languageIDs'] = array();
		}
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		if (isset($_POST['itemsPerPage'])) $this->itemsPerPage = intval($_POST['itemsPerPage']);
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['columns']) && is_array($_POST['columns'])) $this->columns = $_POST['columns'];
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// groups ids default value
		$this->staticParameters['groupIDs'] = array();
		
		// detect user deletion
		if (isset($_REQUEST['deletedUsers'])) {
			$this->deletedUsers = intval($_REQUEST['deletedUsers']);
		}
		
		// search user from passed groupID by group-view 
		if (isset($_GET['groupID'])) {
			$this->staticParameters['groupIDs'][] = intval($_GET['groupID']);
			
			// do search
			try {
				$this->validate();
				$this->save();
			}
			catch (UserInputException $e) {
				$this->errorField = $e->getField();
				$this->errorType = $e->getType();
			}
		}
		
		// language ids default value
		$this->staticParameters['languageIDs'] = array();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		foreach ($this->activeOptions as $name => $option) {
			if (isset($this->values[$name])) {
				$this->activeOptions[$name]['optionValue'] = $this->values[$name];
			}
		}
		
		$this->options = $this->getCategoryOptions('profile');
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'staticParameters' 	=> $this->staticParameters,
			'matchExactly' 		=> $this->matchExactly,
			'users' 		=> $this->users,
			'options' 		=> $this->options,
			'deletedUsers' 		=> $this->deletedUsers,
			'availableGroups'	=> $this->getAvailableGroups(),
			'availableLanguages'	=> $this->getAvailablelanguages(),
			'invertGroupIDs' 	=> $this->invertGroupIDs,
			'sortField'		=> $this->sortField,
			'sortOrder'		=> $this->sortOrder,
			'itemsPerPage'		=> $this->itemsPerPage,
			'columns'		=> $this->columns
		));
	}
	
	/**
	 * @see Form::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem($this->menuItemName);
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canSearchUser');
		
		// get user options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see Form::save()
	 */	
	public function save() {
		parent::save();
		
		// store search result in database
		$data = serialize(array(
			'matches' => $this->matches,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		));
		
		$sql = "INSERT INTO 	wcf".WCF_N."_search
					(userID, searchData, searchDate, searchType)
			VALUES		(".WCF::getUser()->userID.",
					'".escapeString($data)."',
					".TIME_NOW.",
					'users')";
		unset($data); // save memory
		WCF::getDB()->sendQuery($sql);
		unset($sql); // save memory
		
		// get new search id
		$this->searchID = WCF::getDB()->getInsertID();
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect('index.php?page=UserList&searchID='.$this->searchID.'&sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder).'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		// static fields
		if (isset($this->staticParameters['username'])) {
			$this->staticParameters['username'] = StringUtil::trim($this->staticParameters['username']);
		}
		if (isset($this->staticParameters['userID']) && !empty($this->staticParameters['userID'])) {
			$this->staticParameters['userID'] = intval($this->staticParameters['userID']);
		}
		if (isset($this->staticParameters['email'])) {
			$this->staticParameters['email'] = StringUtil::trim($this->staticParameters['email']);
		}
		if (isset($this->staticParameters['groupIDs'])) {
			$this->staticParameters['groupIDs'] = ArrayUtil::toIntegerArray($this->staticParameters['groupIDs']);
		}
		if (isset($this->staticParameters['languageIDs'])) {
			$this->staticParameters['languageIDs'] = ArrayUtil::toIntegerArray($this->staticParameters['languageIDs']);
		}
		
		// dynamic fields
		// no validation necessary
		
		// do search
		$this->search();
		
		if (count($this->matches) == 0) {
			$this->users = array();
			throw new UserInputException('search', 'noMatches');
		}
	}
	
	/**
	 * Search for users which fit to the search values.
	 */
	protected function search() {
		$this->matches = array();
		$sql = "SELECT		user.userID
			FROM		wcf".WCF_N."_user user
			LEFT JOIN	wcf".WCF_N."_user_option_value option_value 
			ON		(option_value.userID = user.userID)";
		
		// build search condition
		$this->conditions = new ConditionBuilder(); 
		
		// static fields
		$this->buildStaticConditions();
		
		// dynamic fields
		$this->buildDynamicConditions();
		
		// call buildConditions event
		EventHandler::fireAction($this, 'buildConditions');

		// do search
		$result = WCF::getDB()->sendQuery($sql.$this->conditions->get(), $this->maxResults);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->matches[] = $row['userID'];	
		}
	}
	
	/**
	 * Builds the static conditions.
	 */
	protected function buildStaticConditions() {
		if (isset($this->staticParameters['username']) && !empty($this->staticParameters['username'])) {
			if (isset($this->matchExactly['username'])) {
				$this->conditions->add("user.username = '".escapeString($this->staticParameters['username'])."'");
			}
			else {
				$this->conditions->add("user.username LIKE '%".addcslashes(escapeString($this->staticParameters['username']), '_%')."%'");
			}
		}
		if (isset($this->staticParameters['userID']) && !empty($this->staticParameters['userID'])) {
			$this->conditions->add("user.userID = ".$this->staticParameters['userID']."");
		}
		if (isset($this->staticParameters['email']) && !empty($this->staticParameters['email'])) {
			if (isset($this->matchExactly['email'])) {
				$this->conditions->add("user.email = '".escapeString($this->staticParameters['email'])."'");
			}
			else {
				$this->conditions->add("user.email LIKE '%".addcslashes(escapeString($this->staticParameters['email']), '_%')."%'");
			}
		}
		if (isset($this->staticParameters['groupIDs']) && count($this->staticParameters['groupIDs']) > 0) {
			$this->conditions->add("user.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_groups WHERE groupID IN (".implode(',', $this->staticParameters['groupIDs'])."))");
		}
		if (isset($this->staticParameters['languageIDs']) && count($this->staticParameters['languageIDs']) > 0) {
			$this->conditions->add("user.languageID IN (".implode(',', $this->staticParameters['languageIDs']).")");
		}
	}
	
	/**
	 * Builds the dynamic conditions.
	 */
	protected function buildDynamicConditions() {
		foreach ($this->activeOptions as $name => $option) {
			$value = isset($this->values[$option['optionName']]) ? $this->values[$option['optionName']] : null;
			$condition = $this->getTypeObject($option['optionType'])->getCondition($option, $value, isset($this->matchExactly[$name]));
			if ($condition !== false) $this->conditions->add($condition);
		}
	}
	
	/**
	 * @see SearchableOptionType::getSearchFormElement()
	 */
	protected function getFormElement($type, &$optionData) {
		return $this->getTypeObject($type)->getSearchFormElement($optionData);
	}
	
	/**
	 * @see DynamicOptionListForm::checkOption()
	 */
	protected function checkOption($optionName) {
		$option = $this->cachedOptions[$optionName];
		return ($option['searchable'] == 1 && !$option['disabled'] && ($option['visible'] == 3 || $option['visible'] < 2));
	}
}
?>
