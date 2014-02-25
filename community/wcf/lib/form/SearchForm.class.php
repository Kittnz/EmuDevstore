<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/search/SearchEngine.class.php');
require_once(WCF_DIR.'lib/form/CaptchaForm.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * SearchForm handles given search request and shows the extended search form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	form
 * @category 	Community Framework
 */
class SearchForm extends CaptchaForm {
	// system
	public $templateName = 'search';
	public $sortField = SEARCH_DEFAULT_SORT_FIELD;
	public $sortOrder = SEARCH_DEFAULT_SORT_ORDER;
	public $useCaptcha = SEARCH_USE_CAPTCHA;
	
	/**
	 * id of an existing search
	 * 
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * data of an existing search
	 * 
	 * @var	array
	 */
	public $searchData = array();
	
	/**
	 * search engine object
	 * 
	 * @var	SearchEngine
	 */
	public $engine = '';
	
	/**
	 * the search results
	 * 
	 * @var	array
	 */
	public $result = array();
	
	/**
	 * the hash of a search
	 * 
	 * @var	string
	 */
	public $searchHash = '';
	
	/**
	 * list of available message types
	 * 
	 * @var	array
	 */
	public $availableTypes = array();
	
	// form parameters
	/**
	 * the search query
	 * 
	 * @var	string
	 */
	public $query = '';
	
	/**
	 * a default search query
	 * 
	 * @var	string
	 */
	public $defaultQuery = '';
	
	/**
	 * selected message types
	 * 
	 * @var	array
	 */
	public $types = array();
	
	/**
	 * name of a user
	 * 
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * id of a user
	 * 
	 * @var	integer
	 */
	public $userID = 0;
	
	public $nameExactly = 1;
	public $subjectOnly = 0;
	public $fromDay = 0;
	public $fromMonth = 0;
	public $fromYear = '';
	public $untilDay = 0;
	public $untilMonth = 0;
	public $untilYear = '';
	public $submit = false;
	public $modify = false;
	protected $userIDs = null;
	protected $dates = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['modify'])) $this->modify = (bool)intval($_REQUEST['modify']);
		if (isset($_REQUEST['searchID'])) {
			$this->searchID = intval($_REQUEST['searchID']);
			if (!$this->modify) {
				// searchID given. show result page
				require_once(WCF_DIR.'lib/page/SearchResultPage.class.php');
				new SearchResultPage($this->searchID);
				exit;
			}
		}
				
		if (isset($_REQUEST['q'])) $this->query = StringUtil::trim($_REQUEST['q']);
		if (isset($_REQUEST['defaultQuery'])) $this->defaultQuery = StringUtil::trim($_REQUEST['defaultQuery']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['types']) && is_array($_REQUEST['types'])) $this->types = $_REQUEST['types'];
		$this->submit = (count($_POST) || !empty($this->query) || !empty($this->username) || $this->userID);
		
		// modify existing search
		if ($this->modify) {
			// get search data
			$sql = "SELECT 	*
				FROM	wcf".WCF_N."_search
				WHERE	searchID = ".$this->searchID."
					AND searchType = 'messages'
					AND userID = ".WCF::getUser()->userID;
			$search = WCF::getDB()->getFirstRow($sql);
			if (!isset($search['searchID']) || ($search['userID'] && $search['userID'] != WCF::getUser()->userID)) {
				throw new IllegalLinkException();
			}
			
			$this->searchData = unserialize($search['searchData']);
			if (empty($this->searchData['alterable'])) {
				throw new IllegalLinkException();
			}
			$this->query = $this->searchData['query'];
			$this->sortOrder = $this->searchData['sortOrder'];
			$this->sortField = $this->searchData['sortField'];
			$this->nameExactly = $this->searchData['nameExactly'];
			$this->fromDay = $this->searchData['fromDay'];
			$this->fromMonth = $this->searchData['fromMonth'];
			$this->fromYear = $this->searchData['fromYear'];
			$this->untilDay = $this->searchData['untilDay'];
			$this->untilMonth = $this->searchData['untilMonth'];
			$this->untilYear = $this->searchData['untilYear'];
			$this->username = $this->searchData['username'];
			$this->userID = $this->searchData['userID'];
			$this->types = $this->searchData['types'];
			
			if (count($_POST)) {
				$this->submit = true;
			}
		}
		
		// sort order
		if (isset($_REQUEST['sortField'])) {
			$this->sortField = $_REQUEST['sortField'];
		}
			
		switch ($this->sortField) {
			case 'subject':
			case 'time':
			case 'username': break;
			case 'relevance': if (!$this->submit || !empty($this->query)) break;
			default: 
				if (!$this->submit || !empty($this->query)) $this->sortField = 'relevance';
				else $this->sortField = 'time';
		}
		
		if (isset($_REQUEST['sortOrder'])) {
			$this->sortOrder = $_REQUEST['sortOrder'];
			switch ($this->sortOrder) {
				case 'ASC':
				case 'DESC': break;
				default: $this->sortOrder = 'DESC';
			}
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->nameExactly = 0;
		if (isset($_POST['nameExactly'])) $this->nameExactly = intval($_POST['nameExactly']);
		if (isset($_POST['subjectOnly'])) $this->subjectOnly = intval($_POST['subjectOnly']);
		
		// date area
		if (isset($_POST['fromDay'])) $this->fromDay = intval($_POST['fromDay']);
		if (isset($_POST['fromMonth'])) $this->fromMonth = intval($_POST['fromMonth']);
		if (isset($_POST['fromYear'])) {
			$this->fromYear = intval($_POST['fromYear']);
			if (empty($this->fromYear)) $this->fromYear = '';
		}
		if (isset($_POST['untilDay'])) $this->untilDay = intval($_POST['untilDay']);
		if (isset($_POST['untilMonth'])) $this->untilMonth = intval($_POST['untilMonth']);
		if (isset($_POST['untilYear'])) {
			$this->untilYear = intval($_POST['untilYear']);
			if (empty($this->untilYear)) $this->untilYear = '';
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// get search conditions
		$conditions = $this->getConditions();
		
		// check query and author
		if (empty($this->query) && empty($this->username) && !$this->userID) {
			throw new UserInputException('q');
		}
		
		// build search hash
		$this->searchHash = StringUtil::getHash(serialize(array($this->query, $this->types, !$this->subjectOnly, $conditions, $this->sortField.' '.$this->sortOrder, PACKAGE_ID)));
		
		// check search hash
		if (!empty($this->query)) {
			$sql = "SELECT	searchID
				FROM	wcf".WCF_N."_search
				WHERE	searchHash = '".$this->searchHash."'
					AND userID = ".WCF::getUser()->userID."
					AND searchType = 'messages'
					AND searchDate > ".(TIME_NOW - 1800);
			$row = WCF::getDB()->getFirstRow($sql);
			if (isset($row['searchID'])) {
				HeaderUtil::redirect('index.php?form=Search&searchID='.$row['searchID'].'&highlight='.urlencode($this->query).SID_ARG_2ND_NOT_ENCODED);
				exit;
			}
		}
		
		// do search
		$this->result = $this->engine->search($this->query, $this->types, $conditions, $this->sortField.' '.$this->sortOrder);
		
		// result is empty
		if (count($this->result) == 0) {
			$this->throwNoMatchesException();
		}
	}
	
	/**
	 * Throws a NamedUserException on search failure.
	 */
	public function throwNoMatchesException() {
		if (empty($this->query)) throw new NamedUserException(WCF::getLanguage()->get('wcf.search.error.user.noMatches'));
		else throw new NamedUserException(WCF::getLanguage()->get('wcf.search.error.noMatches', array('$query' => StringUtil::encodeHTML($this->query))));
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// get additional data
		$additionalData = array();
		foreach (SearchEngine::$searchTypeObjects as $type => $typeObject) {
			if (($data = $typeObject->getAdditionalData()) !== null) {
				$additionalData[$type] = $data;
			}
		}
		
		// save result in database
		$this->searchData = array(
			'packageID' => PACKAGE_ID,
			'query' => $this->query,
			'result' => $this->result,
			'additionalData' => $additionalData,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'nameExactly' => $this->nameExactly,
			'subjectOnly' => $this->subjectOnly,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear,
			'username' => $this->username,
			'userID' => $this->userID,
			'types' => $this->types,
			'alterable' => (!$this->userID ? 1 : 0)
		);
		
		if ($this->searchID) {
			$sql = "UPDATE	wcf".WCF_N."_search
				SET	searchData = '".escapeString(serialize($this->searchData))."',
					searchDate = ".TIME_NOW.",
					searchType = 'messages',
					searchHash = '".$this->searchHash."'
				WHERE	searchID = ".$this->searchID;
			WCF::getDB()->sendQuery($sql);
		}
		else {
			$sql = "INSERT INTO	wcf".WCF_N."_search
						(userID, searchData, searchDate, searchType, searchHash)
				VALUES		(".WCF::getUser()->userID.",
						'".escapeString(serialize($this->searchData))."',
						".TIME_NOW.",
						'messages',
						'".$this->searchHash."')";
			WCF::getDB()->sendQuery($sql);
			$this->searchID = WCF::getDB()->getInsertID();
		}
		$this->saved();
		
		// forward to result page
		HeaderUtil::redirect('index.php?form=Search&searchID='.$this->searchID.'&highlight='.urlencode($this->query).SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
		
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		$this->engine = new SearchEngine();
		
		try {
			parent::submit();
		}
		catch (NamedUserException $e) {
			WCF::getTPL()->assign('errorMessage', $e->getMessage());
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// set current date
			$this->untilDay = intval(DateUtil::formatDate('%d', null, false, true));
			$this->untilMonth = intval(DateUtil::formatDate('%m', null, false, true));
			$this->untilYear = intval(DateUtil::formatDate('%Y', null, false, true));
		}
		
		$this->availableTypes = SearchEngine::getSearchTypes();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		SearchEngine::loadSearchTypeObjects();
		foreach (SearchEngine::$searchTypeObjects as $typeObject) {
			$typeObject->show($this);
		}
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'query' => $this->query,
			'defaultQuery' => $this->defaultQuery,
			'username' => $this->username,
			'types' => SearchEngine::$searchTypeObjects,
			'selectedTypes' => $this->types,
			'subjectOnly' => $this->subjectOnly,
			'nameExactly' => $this->nameExactly,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!count($_POST) && $this->submit) {
			if ($this->userID) $this->useCaptcha = false;
			$this->submit();
		}
		
		parent::show();
	}
	
	/**
	 * Returns the conditions for a search in the table of the selected search types.
	 */
	protected function getConditions() {
		$conditionsArray = array();
		SearchEngine::loadSearchTypeObjects();
		if (!count($this->types)) $this->types = array_keys(SearchEngine::$searchTypeObjects);
		foreach ($this->types as $key => $type) {
			if (!isset(SearchEngine::$searchTypeObjects[$type])) {
				throw new SystemException('unknown search type '.$type, 101001);
			}
			
			$typeObject = SearchEngine::$searchTypeObjects[$type];
			$defaultConditions = $conditions = '';
			try {
				if (!$typeObject->isAccessible()) {
					throw new PermissionDeniedException();
				}

				// default conditions
				// user ids
				if (($userIDs = $this->getUserIDs())) {
					$defaultConditions = $typeObject->getUserIDFieldName().' IN ('.implode(',', $userIDs).')';
				}
				
				// dates
				if (($dates = $this->getDates())) {
					if (!empty($defaultConditions)) $defaultConditions .= ' AND ';
					$defaultConditions .= $typeObject->getTimeFieldName().' BETWEEN '.$dates['from'].' AND '.$dates['until'];
				}
				
				if (!empty($defaultConditions)) {
					$defaultConditions = '('.$defaultConditions.')';
				}
				
				// special conditions
				$conditions = $typeObject->getConditions($this);
			}
			catch (PermissionDeniedException $e) {
				unset($this->types[$key]);
				continue;
			}
			
			$conditionsArray[$type] = $defaultConditions . (!empty($defaultConditions) && !empty($conditions) ? ' AND ' : '') . $conditions;
		}
		
		if (!count($this->types)) {
			$this->throwNoMatchesException();
		}
		
		return $conditionsArray;
	}
	
	/**
	 * Returns user ids.
	 * 
	 * @return 	array
	 */
	public function getUserIDs() {
		if ($this->userIDs === null) {
			$this->userIDs = array();
			
			// username
			if (!empty($this->username)) {
				$userIDs = '';
				$sql = "SELECT	userID
					FROM	wcf".WCF_N."_user
					WHERE	username ".($this->nameExactly ? "= '".escapeString($this->username)."'" : "LIKE '%".escapeString($this->username)."%'");
				$result = WCF::getDB()->sendQuery($sql, 100);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$this->userIDs[] = $row['userID'];
				}
				
				if (!count($this->userIDs)) {
					$this->throwNoMatchesException();
				}
			}
			
			// userID
			if ($this->userID) {
				$this->userIDs[] = $this->userID;
			}
		}
		
		return $this->userIDs;
	}
	
	/**
	 * Returns dates.
	 * 
	 * @return 	array
	 */
	public function getDates() {
		if ($this->dates === null) {
			$this->dates = array();
			
			// date search
			if (!empty($this->fromYear) && !empty($this->untilYear)) {
				$fromYear = $this->fromYear;
				$untilYear = $this->untilYear;
				if ($fromYear < 100) $fromYear += 1900;
				if ($untilYear < 100) $untilYear += 1900;
				if (checkdate($this->fromMonth, $this->fromDay, $fromYear) && checkdate($this->untilMonth, $this->untilDay, $untilYear)) {
					$fromDate = gmmktime(0, 0, 0, $this->fromMonth, $this->fromDay, $fromYear);
					$untilDate = gmmktime(23, 59, 59, $this->untilMonth, $this->untilDay, $untilYear);
					if ($fromDate > 0 && $untilDate > 0) {
						$this->dates['from'] = $fromDate;
						$this->dates['until'] = $untilDate;
					}
				}
			}
		}
		
		return $this->dates;
	}
}
?>