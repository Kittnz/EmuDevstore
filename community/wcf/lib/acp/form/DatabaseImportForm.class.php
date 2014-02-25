<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

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
class DatabaseImportForm extends ACPForm {
	public $templateName = 'dbImport';
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.db.import';
	public $neededPermissions = 'admin.maintenance.canImportDB';

	public $dbName = '';
	public $isGzip = false;
	public $fileName = '';
	public $ignoreErrors = false;
	public $fileSize = 0;
	public $isTmpFile = false;
	public $upload;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		if (isset($_POST['ignoreErrors'])) $this->ignoreErrors = intval($_POST['ignoreErrors']);
		if (isset($_POST['importFile'])) $this->fileName = $_POST['importFile'];
		if (isset($_FILES['upload'])) $this->upload = $_FILES['upload'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// upload backup
		if ($this->upload && $this->upload['error'] != 4) {
			if ($this->upload['error'] != 0) {
				throw new UserInputException('upload');
			} 

			// store uploaded file in backup directory and delete it after import
			$this->fileName = $_FILES['upload']['tmp_name'];
			$newName = WCF_DIR."acp/backup/_".$_FILES['upload']['name'];
			
			if (!move_uploaded_file($this->fileName, $newName)) {
				throw new UserInputException('upload');	
			}
			$this->fileName = $newName;
		}
		// local backup
		else {
			if (!empty($_FILES['upload']['tmp_name'])) {
				throw new UserInputException('importFile');
			}
			
			$this->fileName = preg_replace("~^/~", '', $_POST['importFile']);	
			if (!file_exists(WCF_DIR.$this->fileName) && !file_exists($this->fileName) && !file_exists(WCF_DIR."acp/backup/".$this->fileName)) {
				// handle remote file
				$randomFileName = rand().'_import_remote_file';
				if (@copy($this->fileName, WCF_DIR.'acp/backup/'.$randomFileName)) {
					$this->fileName = WCF_DIR.'acp/backup/'.$randomFileName;
					$this->isTmpFile = true;
				}
				else {
					throw new UserInputException('importFile');
				}
			}
			elseif (file_exists(WCF_DIR.$this->fileName)) {
				// path from user input is not absolute. add wcf directory
				$this->fileName = WCF_DIR.$this->fileName;
			}
			elseif (file_exists(WCF_DIR."acp/backup/".$this->fileName)) {
				// no path or it is inside the default directory. add default backup path
				$this->fileName = WCF_DIR."acp/backup/".$this->fileName;
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// test compression 
		$file = new File($this->fileName, 'rb');
		if ($file->read(2) == "\37\213") {
			$this->isGzip = true;
			$zipFile = new ZipFile($this->fileName, 'rb');
			$this->fileSize = $zipFile->getFileSize();
		}
		else $this->fileSize = filesize($this->fileName);
		$file->close();
		
		$this->dbName = WCF::getDB()->getDatabaseName();

		// build session data array
		$sessionData = array();
		$sessionData['isGzip'] = $this->isGzip;
		$sessionData['extendedCommand'] = '';
		$sessionData['importFile'] = $this->fileName;
		$sessionData['isTmpFile'] = $this->isTmpFile;
		$sessionData['ignoreErrors'] = $this->ignoreErrors;
		$sessionData['offset'] = 0;
		$sessionData['errors'] = array('messages' => array(), 'errorDescriptions' => array());
		$sessionData['tableErrors'] = array();
		$sessionData['commandCount'] = 0;
		$sessionData['wcfCharset'] = WCF::getDB()->getCharset();
		$sessionData['importCharset'] = '';
		$sessionData['filesize'] = $this->fileSize;
		$sessionData['remain'] = $sessionData['count'] = $this->fileSize;
		
		WCF::getSession()->register('databaseImportData', $sessionData);
		$this->saved();
		
		WCF::getTPL()->assign(array(
			'pageTitle' => WCF::getLanguage()->get('wcf.acp.db.import.pageHeadline'),
			'url' => 'index.php?action=DatabaseImport&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED,
			'progress' => 0
		));

		WCF::getTPL()->display('worker');
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		// Maximal moegliche Dateigroesse ermitteln
		$maxSize = min(ini_get('post_max_size') + 0, ini_get('upload_max_filesize') + 0);
		WCF::getTPL()->assign('postMaxSize', $maxSize);
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>