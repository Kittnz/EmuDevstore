<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows stats generator form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.system.stats
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StatsForm extends ACPForm {
	public $templateName = 'stats';
	public $activeMenuItem = 'wcf.acp.menu.link.log.stats';
	public $neededPermissions = 'admin.system.canReadStats';
	
	// data
	public $types = array();
	public $userObj = null;
	
	// parameters
	public $type = '';
	public $fromDay = 0;
	public $fromMonth = 0;
	public $fromYear = '';
	public $untilDay = 0;
	public $untilMonth = 0;
	public $untilYear = '';
	public $sortField = 'date';
	public $sortOrder = 'ASC';
	public $groupBy = 'day';
	public $username = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get types
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_stat_type
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
			ORDER BY	typeName";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->types[$row['typeName']] = new DatabaseObject($row);
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['type'])) $this->type = $_POST['type'];
		if (isset($_POST['sortField'])) $this->sortField = $_POST['sortField'];
		if (isset($_POST['sortOrder'])) $this->sortOrder = $_POST['sortOrder'];
		if (isset($_POST['groupBy'])) $this->groupBy = $_POST['groupBy'];
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		
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
	 * @see	Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// type
		if (!isset($this->types[$this->type])) {
			throw new UserInputException('type');
		}
		
		// sort field
		if ($this->sortField != 'date' && $this->sortField != 'count') $this->sortField = 'date';
		// sort order
		if ($this->sortOrder != 'ASC' && $this->sortOrder != 'DESC') $this->sortOrder = 'ASC';
		// group by
		if ($this->groupBy != 'day' && $this->groupBy != 'week' && $this->groupBy != 'month') $this->groupBy = 'day';
		
		// username
		if (!empty($this->username)) {
			require_once(WCF_DIR.'lib/data/user/User.class.php');
			$this->userObj = new User(null, null, $this->username);
			if (!$this->userObj->userID) {
				throw new UserInputException('username', 'notFound');
			}
		}
	}
	
	/**
	 * @see	Form::save()
	 */
	public function save() {
		parent::save();
		
		// build timestamps
		$untilDate = intval(gmmktime(24, 0, 0, $this->untilMonth, $this->untilDay, intval($this->untilYear)));
		$fromDate = intval(gmmktime(0, 0, 0, $this->fromMonth, $this->fromDay, intval($this->fromYear)));

		// build sql date format
		switch ($this->groupBy) {
			case 'day': 
				$sqlDateFormat = '%Y%m%d';
				break;
			case 'week':
				$sqlDateFormat = '%Y%u';
				break;
			default:
				$sqlDateFormat = '%Y%m';
		}
		
		// get selected timezone
		DateUtil::init();
		$timezoneStr = str_replace('.', ':', sprintf("%+06.2f", DateUtil::$timezone));
		
		// build sql
		$results = array();
		$max = 0;
		$sql = "SELECT		COUNT(*) AS count, 
					DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(".$this->types[$this->type]->dateFieldName."),@@session.time_zone,'".escapeString($timezoneStr)."'),'".$sqlDateFormat."') AS groupBy,
					AVG(".$this->types[$this->type]->dateFieldName.") AS date
			FROM		".$this->types[$this->type]->tableName."
			WHERE		".(($this->userObj !== null && $this->types[$this->type]->userFieldName) ? $this->types[$this->type]->userFieldName." = ".$this->userObj->userID." AND" : '')."
					".$this->types[$this->type]->dateFieldName." > ".$fromDate."
					AND ".$this->types[$this->type]->dateFieldName." < ".$untilDate."
			GROUP BY 	groupBy
			ORDER BY	".$this->sortField." ".$this->sortOrder;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$results[] = $row;
			if ($row['count'] > $max) $max = $row['count'];
		}
		
		WCF::getTPL()->assign(array(
			'results' => $results,
			'max' => $max,
			'dateFormat' => WCF::getLanguage()->get(($this->groupBy == 'day' ? 'wcf.global.dateFormatLocalized' : 'wcf.acp.stats.dateFormat.'.$this->groupBy))
		));
	}
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->untilDay = gmdate('j');
			$this->untilMonth = gmdate('n');
			$this->untilYear = gmdate('Y');
			
			$timestamp = TIME_NOW - 24 * 3600 * 30;
			$this->fromDay = gmdate('j', $timestamp);
			$this->fromMonth = gmdate('n', $timestamp);
			$this->fromYear = gmdate('Y', $timestamp);
		}
	}
	
	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'availableTypes' => $this->types,
			'type' => $this->type,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'groupBy' => $this->groupBy,
			'username' => $this->username
		));
	}
}
?>