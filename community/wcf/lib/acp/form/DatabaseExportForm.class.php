<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/database/DatabaseDumper.class.php');

/**
 * Shows the export database form.
 *
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.system.db
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class DatabaseExportForm extends ACPForm {
	// system
	public $templateName = 'dbExport';
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.db.export';
	public $neededPermissions = 'admin.maintenance.canExportDB';
	
	// data
	public $dbName = '';
	public $loopTimeLimit = 10;
	public $limit = 250;
	public $loggedTables = array();
	
	// parameters
	public $isGzip = 0;
	public $exportAll = 1;
	public $exportTables = array();
	public $backupFileName = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		$this->exportAll = 0;
		if (isset($_POST['backupFileName'])) $this->backupFileName = StringUtil::trim($_POST['backupFileName']);
		if (isset($_POST['exportAll'])) $this->exportAll = intval($_POST['exportAll']);
		if (isset($_POST['isGzip'])) $this->isGzip = intval($_POST['isGzip']);
		if (isset($_POST['exportTables']) && is_array($_POST['exportTables'])) $this->exportTables = $_POST['exportTables'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// export is only from active WCF databse possible
		$this->dbName = WCF::getDB()->getDatabaseName();
		
		// build filename and path
		$path = WCF_DIR.'acp/backup/';
		$iilegalPath = false;
		
		// no user input
		if (empty($this->backupFileName)) {
			$this->backupFileName = $this->dbName.'_'.date('Y_m_d_H_i').'.sql';
		}
		// relative path
		else if (dirname($this->backupFileName) != '.') {
			// check if user try to save file outside of backup directory
			$userPath = explode("/", dirname($this->backupFileName));
			$depth = 0;
			foreach ($userPath as $dir) {
				if (empty($dir)) {}
				elseif ($dir == '..') $depth--;
				else $depth++;
			}
			if ($depth < 0) {
				throw new UserInputException('backupFileName');
			}
			
			if (!file_exists(FileUtil::getRealPath($path.$this->backupFileName))) {
				throw new UserInputException('backupFileName');
			}
		}
		
		if ($this->isGzip) $this->backupFileName .= '.gz';
		
		// tables
		if (!$this->exportAll && !count($this->exportTables)) {
			throw new UserInputException('exportTables');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// build session data array
		$sessionData = array();
		$sessionData['loopTimeLimit'] = $this->loopTimeLimit;
		$sessionData['isGzip'] = $this->isGzip;
		$sessionData['tableName'] = '';
		
		$tablecount = 0;
		$rowCount = 0;
		$sessionData['offset'] = -1;

		// get all tables
		if ($this->exportAll) {
			$this->readLoggedTables();
			$this->exportTables = $this->loggedTables;
		}
		
		// prepare session data
		$sessionData['limit'] = $this->limit;
		$sessionData['backupFile'] = FileUtil::getRealPath(WCF_DIR.'acp/backup/'.$this->backupFileName);
		$sessionData['tables'] = $this->exportTables;
			
		// calculate total steps 
		$tableCount = count($this->exportTables);
		$tables = DatabaseDumper::getTableStates($this->exportTables);
		$rowCount = 0;
		foreach ($tables as $table) {
			$rowCount += $table['Rows'];	
		}
			
		$sessionData['tableCount'] = $tableCount;
		$sessionData['rowCount'] = $rowCount;
		$sessionData['count'] = $sessionData['remain'] = $tableCount + $rowCount;
				
		WCF::getSession()->register('databaseExportData', $sessionData);
		$this->saved();
		
		WCF::getTPL()->assign(array(
			'pageTitle' => WCF::getLanguage()->get('wcf.acp.db.export.pageHeadline'),
			'url' => 'index.php?action=DatabaseExport&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED,
			'progress' => 0
		));

		WCF::getTPL()->display('worker');
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get logged tables
		$this->readLoggedTables();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'loggedTables' => $this->loggedTables,
			'backupFile' => $this->backupFileName,
			'isGzip' => $this->isGzip,
			'exportAll' => $this->exportAll,
			'exportTables' => $this->exportTables
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
	protected function readLoggedTables() {
		$sql = "SELECT	* 
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlColumn = ''
				AND sqlIndex = ''";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->loggedTables[] = $row['sqlTable'];
		}
		sort($this->loggedTables);
	}
}
?>