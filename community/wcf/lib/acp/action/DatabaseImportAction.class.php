<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/WorkerAction.class.php');
require_once(WCF_DIR.'lib/system/database/DatabaseDumper.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

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
class DatabaseImportAction extends WorkerAction {
	public $action = 'DatabaseImport';
	public $limit = 5;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		WCF::getUser()->checkPermission('admin.maintenance.canImportDB');
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// get session data
		$sessionData = WCF::getSession()->getVar('databaseImportData');
		$filesize = $sessionData['filesize'];
		$isGzip = $sessionData['isGzip'];
		$extendedCommand = $sessionData['extendedCommand'];
		$offset = $sessionData['offset'];
		$charset = $sessionData['importCharset'];
		$ignoreErrors = $sessionData['ignoreErrors'];
		$importErrors = '';
		$stepInfo = array();
		
		// start export operations
		$loopStart = time();
		
		$importFile = $sessionData['importFile'];
			
		if ($isGzip) $file = new ZipFile($importFile, 'rb');
		else $file = new File($importFile, 'rb');

		// import database operations (only up to $this->limit)
		$loopInfo = DatabaseDumper::import($file, $filesize, $isGzip, $this->limit, $loopStart, $offset, $charset, $extendedCommand, $ignoreErrors);
		
		$file->close();
		
		// store charset 
		if (!empty($loopInfo['charset']) && $loopInfo['charset'] != $sessionData['wcfCharset']) {
			$sessionData['importCharset'] = $loopInfo['charset'];
		}

		// delete aftereffected erros (no insert errors will be displayed if the table caused an error before) 
		$tableErrors = $sessionData['tableErrors'];
		foreach ($loopInfo['errors']['messages'] as $key => $message) {
			if (preg_match("/CREATE TABLE `?(\w+)`?/i", $message, $match)) {
				$tableErrors[] = $match[1];
			}
			elseif (preg_match("/(INSERT|REPLACE).*?INTO `?(\w+)`?/i", $message, $match)) {
				if (in_array($match[1], $tableErrors)) {
					unset($loopInfo['errors']['messages'][$key]);
					unset($loopInfo['errors']['errorDescriptions'][$key]);
				}
				else $tableErrors[] = $match[1];
			}
		}
			
		// save errors
		$errors = array(
			'messages' => array_merge($sessionData['errors']['messages'], $loopInfo['errors']['messages']),
			'errorDescriptions' => array_merge($sessionData['errors']['errorDescriptions'], $loopInfo['errors']['errorDescriptions'])
			
		);			

		$sessionData['errors'] = $errors;
		$sessionData['tableErrors'] = $tableErrors;

		// refresh session data	
		$sessionData['extendedCommand'] = $loopInfo['extendedCommand'];	
		$sessionData['offset'] = $loopInfo['offset'];	
		$sessionData['remain'] -= $loopInfo['done'];
		$sessionData['commandCount'] += $loopInfo['commandCount'];
		
		// calculate progressbar
		$this->calcProgress(($sessionData['count'] - $sessionData['remain']), $sessionData['count']);
		
		// show finish 
		if ($sessionData['remain'] <= 0) {
			// reset charset for database connection
			if (!empty($sessionData['importCharset'])) {
				WCF::getDB()->setCharset($sessionData['wcfCharset']);
			}
			
			// cleanup session data
			WCF::getSession()->unregister('databaseImportData');
			
			// delete imported upload/remote file
			if ($sessionData['isTmpFile']) @unlink($importFile);
			
			// clear all standalone caches
			// get standalone package directories
			$sql = "SELECT	packageDir 
				FROM	wcf".WCF_N."_package
				WHERE	standalone = 1
					AND packageDir <> ''";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				// check if standalone package got cache directory				
				$realPackageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$row['packageDir']));
				if (file_exists($realPackageDir.'cache')) {
					// delete all cache files
					WCF::getCache()->clear($realPackageDir.'cache', '*.php', true);
				}
			}
			// clear wcf cache
			WCF::getCache()->clear(WCF_DIR.'cache', '*.php', true);
			
			// delete all language files
			LanguageEditor::updateAll();
			
			// set data for template
			WCF::getTPL()->assign(array(
					'import' => true,
					'success' => (empty($errors['messages']) && $sessionData['commandCount'] > 0),
					'commandCount' => $sessionData['commandCount'],
					'errors' => $errors
				)
			);
			WCF::getTPL()->append('message', WCF::getTPL()->fetch('dbMessage'));
			
			// show finish template
			$title = 'wcf.acp.db.progress.finish';
			$this->finish($title, 'index.php?form=DatabaseImport&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		}
		WCF::getSession()->register('databaseImportData', $sessionData);
		
		// next loop
		$title = 'wcf.acp.db.import.progress.working';
		$this->nextLoop($title);
	}
}
?>
