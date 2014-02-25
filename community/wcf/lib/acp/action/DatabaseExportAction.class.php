<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/WorkerAction.class.php');
require_once(WCF_DIR.'lib/system/database/DatabaseDumper.class.php');

/**
 * Exports or imports database data.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.system.db
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class DatabaseExportAction extends WorkerAction {
	public $action = 'DatabaseExport';
	protected $downloadFile = '';
	
	/**
	 * Creates a new DatabaseExportAction object.
	 */
	public function __construct() {
		WCF::getUser()->checkPermission('admin.maintenance.canExportDB');	
		parent::__construct();
	}
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// parameters
		if (isset($_GET['downloadFile'])) $this->downloadFile = $_GET['downloadFile'];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// export is not finished. export further
		if (empty($this->downloadFile)) {
			// get session data
			$sessionData = WCF::getSession()->getVar('databaseExportData');
			$loopTimeLimit = $sessionData['loopTimeLimit'];
			$isGzip = $sessionData['isGzip'];
			$limit = $sessionData['limit'];
			$tableName = $sessionData['tableName'];
			$offset = $sessionData['offset'];
			$importErrors = '';
			$stepInfo = array();
			
			// start export operations
			$loopStart = time();
			
			$backupFile = $sessionData['backupFile'];
			$tables = $sessionData['tables'];
				
			// TODO: check if file could be opened (e.g. if directory exists)
			// open backupfile
			if ($isGzip) $file = new ZipFile($backupFile, $offset == -1 ? 'wb' : 'ab');
			else $file = new File($backupFile, $offset == -1 ? 'wb' : 'ab');
			
			// export database operations (only up to $limit)
			$stepInfo = DatabaseDumper::export($file, $tables, $limit, $loopTimeLimit, $loopStart, $offset, $tableName);
				
			// delete completed tables from session
			foreach ($stepInfo['completedTables'] as $table) {
				$key = array_search($table, $sessionData['tables']);
				if ($key !== false) {
					unset($sessionData['tables'][$key]);
				}
			}
			
			$loopEnd = time();
			$duration = $loopEnd - $loopStart;
			
			// check if limit should be changed (more or less db-operations per loop)
			if ($stepInfo['resetLimit']) $sessionData['limit'] = 250; 
			elseif ($duration != $loopTimeLimit) {
				// higher export step size
				if ($duration > 0 ) {
					$sessionData['limit'] = round($limit * ($loopTimeLimit / $duration), 0);
				}
				else $sessionData['limit'] = $limit * 10;				
			}
			
			// refresh session data	
			$sessionData['tableName'] = $stepInfo['tableName'];	
			$sessionData['offset'] = $stepInfo['offset'];	
			$sessionData['remain'] -= $stepInfo['done'];
			
			// show finish 
			if ($sessionData['remain'] <= 0) {
				
				// cleanup session data. save backupFile to session
				WCF::getSession()->register('databaseExportData', $backupFile);
				
				WCF::getTPL()->assign(array(
						'export' => true,
						'success' => true,
						'totalTables' => $sessionData['tableCount'],
						'totalRecords' => $sessionData['rowCount'],
						'backupFile' => $backupFile
					)
				);
				WCF::getTPL()->append('message', WCF::getTPL()->fetch('dbMessage'));
				
				$title = 'wcf.acp.db.progress.finish';
				$this->calcProgress(($sessionData['count'] - $sessionData['remain']), $sessionData['count']);
				$this->finish($title, 'index.php?form=DatabaseExport&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);	
			}
			
			WCF::getSession()->register('databaseExportData', $sessionData);
			
			// refresh progressbar and go to the next step
			$title = 'wcf.acp.db.export.progress.working';
			$this->calcProgress(($sessionData['count'] - $sessionData['remain']), $sessionData['count']);
			$this->nextLoop($title);
		}
		// open download dialog for the exported file
		else {
			$fileName = basename($this->downloadFile);
			$backupFile = WCF::getSession()->getVar('databaseExportData');
			WCF::getSession()->unregister('databaseExportData');
			if ($this->downloadFile == $backupFile) { 
				// file type
				header('Content-Type: application/octet-stream');
				
				// file name
				header('Content-Disposition: attachment; filename="'.$fileName.'"');
				
				// send file size
				header('Content-Length: '.filesize($this->downloadFile));
			
				// no cache headers
				header('Pragma: no-cache');
				header('Expires: 0');
				
				// send file
				readfile($this->downloadFile);
			}
			else {
				throw new SystemException("Expected parameter for download file: ".$this->downloadFile, 102000);
			}
		}
	}
}
?>