<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/io/Tar.class.php');
	require_once(WCF_DIR.'lib/system/setup/FileHandler.class.php');
}

/**
 * Installer extracts folders and files from a tar archive.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
abstract class Installer {
	protected $targetDir, $source, $folder, $fileHandler;
	
	/**
	 * Creates a new Installer object.
	 * 
	 * @param	string		$targetDir	extract the files in this dir
	 * @param	string		$source		name of the source tar archive
	 * @param 	FileHandler	$fileHandler
	 * @param	string		$folder		extract only the files from this subfolder 
	 */
	public function __construct($targetDir, $source, $fileHandler = null, $folder = '') {
		$this->targetDir = FileUtil::addTrailingSlash($targetDir);
		$this->source = $source;
		$this->folder = $folder;
		$this->fileHandler = $fileHandler;
		$this->install();
	}
	
	/**
	 * Checks whether the target dir is a valid path.
	 */
	protected abstract function checkTargetDir();
	
	/**
	 * Creates the target directory if necessary.
	 */
	protected abstract function createTargetDir();
	
	/**
	 * Creates a directory in the target directory.
	 * 
	 * @param	string		$dir
	 */
	protected abstract function createDir($dir);
	
	/**
	 * Touches a file in the target directory.
	 * 
	 * @param	string		$file
	 */
	public abstract function touchFile($file);
	
	/**
	 * Creates a file in the target directory.
	 * 
	 * @param	string		$file
	 * @param	integer		$index
	 * @param	Tar	$tar
	 */
	protected abstract function createFile($file, $index, Tar $tar);
	
	/**
	 * Closes all connections and open files.
	 */
	protected abstract function close();
	
	/**
	 * Starts the extracting of the files.
	 */
	protected function install() {
		$this->checkTargetDir();
		$this->createTargetDir();
		
		// open source archive
		$tar = new Tar($this->source);
		
		// distinct directories and files
		$directories = array();
		$files = array();
		foreach ($tar->getContentList() as $index => $file) {
			if (empty($this->folder) || StringUtil::indexOf($file['filename'], $this->folder) === 0) {
				if (!empty($this->folder)) {
					$file['filename'] = StringUtil::replace($this->folder, '', $file['filename']); 
				}
				
				// remove leading slash
				$file['filename'] = FileUtil::removeLeadingSlash($file['filename']);
				if ($file['type'] == 'folder') {
					// remove trailing slash
					$directories[] = FileUtil::removeTrailingSlash($file['filename']);
				}
				else {
					$files[$index] = $file['filename'];
				}
			}
		}
		
		$this->checkFiles($files);

		// now create the directories
		$errors = array();
		foreach ($directories as $dir) {
			try {
				$this->createDir($dir);
			}
			catch (SystemException $e) {
				$errors[] = array('file' => $dir, 'code' => $e->getCode(), 'message' => $e->getMessage());
			}
		}

		// now untar all files
		foreach ($files as $index => $file) {
			try {
				$this->createFile($file, $index, $tar);
			}
			catch (SystemException $e) {
				$errors[] = array('file' => $file, 'code' => $e->getCode(), 'message' => $e->getMessage());
			}
		}
		if (count($errors) > 0) {
			throw new SystemException('error(s) during the installation of the files.', 11111, $errors);
		}
		
		$this->logFiles($files);

		// close tar
		$tar->close();
	}
	
	/**
	 * Checkes whether the given files overwriting locked existing files.
	 * 
	 * @param	array		$files		list of files
	 */
	protected function checkFiles(&$files) {
		if ($this->fileHandler != null && $this->fileHandler instanceof FileHandler) {
			$this->fileHandler->checkFiles($files);
		}
	}
	
	/**
	 * Logs the extracted files.
	 * 
	 * @param	array		$files		list of files
	 */
	protected function logFiles(&$files) {
		if ($this->fileHandler != null && $this->fileHandler instanceof FileHandler) {
			$this->fileHandler->logFiles($files);
		}
	}
}
?>