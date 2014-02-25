<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

/**
 * Shows the export operations form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.system.db
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class DatabaseOperationsForm extends ACPForm {
	// sytem
	public $templateName = 'dbOperations';
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.db.manage';
	public $neededPermissions = 'admin.maintenance.canManageDB';
	public static $availableActions = array('check', 'optimize', 'repair', 'analyze');
	
	// data
	public $loggedTables = array();
	public $tables = array();
	public $totalRows = 0;
	public $totalDataLength = 0;
	public $totalIndexLength = 0;
	public $totalDataFree = 0;
	
	// parameters
	public $action = '';
	public $tablenameArray = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		// get logged tables
		$this->readLoggedTables();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['action'])) $this->action = $_POST['action'];
		if (isset($_POST['tablenameArray']) && is_array($_POST['tablenameArray'])) $this->tablenameArray = $_POST['tablenameArray'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// action
		if (!in_array($this->action, self::$availableActions)) {
			throw new UserInputException('action');
		}
		
		// tables
		foreach ($this->tablenameArray as $key => $tablename) {
			if (!isset($this->loggedTables[$tablename])) unset($this->tablenameArray[$key]);
		}
		
		if (!count($this->tablenameArray)) {
			throw new UserInputException('tablenameArray');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$sql = strtoupper($this->action)." TABLE ".implode(', ', $this->tablenameArray);
		$result = WCF::getDB()->sendQuery($sql);
		$results = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Msg_text'] != 'OK' && $row['Msg_text'] != 'Table is already up to date') {
				$results[] = $row;
			}
		}
		$this->saved();
		
		// reset values
		$this->tablenameArray = array();
		$this->action = '';
		
		// show success message
		WCF::getTPL()->assign('results', $results);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get tables
		$this->readTables();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'tables' => $this->tables,
			'availableActions' => self::$availableActions,
			'action' => $this->action,
			'tablenameArray' => $this->tablenameArray,
			'totalRows' => $this->totalRows,
			'totalDataLength' => $this->totalDataLength,
			'totalIndexLength' => $this->totalIndexLength,
			'totalDataFree' => $this->totalDataFree
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
	
	/**
	 * Gets a list of all installed sql tables.
	 */
	protected function readTables() {
		$sql = "SHOW TABLE STATUS";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (isset($this->loggedTables[$row['Name']])) {
				$this->tables[$row['Name']] = $row;
				
				// stats
				$this->totalRows++;
				$this->totalDataLength += $row['Data_length'];
				$this->totalIndexLength += $row['Index_length'];
				$this->totalDataFree += $row['Data_free'];
			}
		}
		ksort($this->tables);
	}
	
	/**
	 * Gets a list of all installed sql tables.
	 */
	protected function readLoggedTables() {
		$sql = "SELECT	* 
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlColumn = ''
				AND sqlIndex = ''";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->loggedTables[$row['sqlTable']] = $row['sqlTable'];
		}
	}
}
?>