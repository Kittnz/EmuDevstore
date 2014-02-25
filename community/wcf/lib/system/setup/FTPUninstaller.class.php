<?php
// wcf imports
require_once(WCF_DIR.'lib/system/setup/Uninstaller.class.php');

/**
 * FTPUninstaller is an implementation of Uninstaller.
 * It uses simple FTP operations to delete files and directories via FTP.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class FTPUninstaller extends Uninstaller {
	protected $ftp, $ftpPath;
	
	/**
	 * Creates a new FTPUninstaller object.
	 * 
	 * @param	string		$targetDir		directory from the deleting files
	 * @param	array		$files			delete files
	 * @param	FTP		$ftp			active ftp connection
	 * @param	boolean		$deleteEmptyTargetDir	delete target dir if empty
	 * @param	boolean		$deleteEmptyDirectories	delete sub-directories if empty
	 */
	public function __construct($targetDir, $files, FTP $ftp, $deleteEmptyTargetDir, $deleteEmptyDirectories) {
		$this->ftp = $ftp;
		$this->ftpPath = FTPUtil::getRelativeFtpPath($this->ftp, $targetDir);
		
		if (!$this->ftpPath) {
			throw new SystemException(WCF::getLanguage()->get('warnings.couldNotFindFTPPath', array('{$dir}' => $targetDir)));
		}
		parent::__construct($this->ftpPath, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories);
	}
	
	/**
	 * Checks if the target directory is a valid directory.
	 */
	protected function checkTargetDir() {
		return true; // we simply return true here because we already checked this.
	}
	
	/**
	 * Returns true if a directory is emtpy.
	 * 
	 * @param	string		$directory
	 * @return	boolean 			true if dir is empty
	 */
	protected function isEmpty($dir) {
		// TODO: ftp_nlist uses system tmp dir, which can cause "open_basedir restriction in effect" errors: http://bugs.php.net/bug.php?id=41779
		if (!count($this->ftp->nlist($dir))) {
			return true;
		}
		return false;
	}
	
	/**
	 * @see Uninstaller::deleteFile()
	 */
	protected function deleteFile($file) {
		@$this->ftp->delete($file);
	}
	
	/**
	 * @see Uninstaller::deleteDir()
	 */
	protected function deleteDir($dir) {
		@$this->ftp->rmdir($dir);
	}
	
}
?>