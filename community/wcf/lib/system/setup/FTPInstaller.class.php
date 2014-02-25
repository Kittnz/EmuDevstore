<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/io/FTP.class.php');
	require_once(WCF_DIR.'lib/system/setup/Installer.class.php');
}

/**
 * FTPInstaller is an implementation of Installer.
 * It uses an ftp connection to extract the files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class FTPInstaller extends Installer {
	protected $ftp, $ftpPath, $dummyUploadFile;

	/**
	 * Creates a new FTPInstaller object.
	 * 
	 * @param	string		$targetDir	the full path on the machine the ftp server is running	
	 * @param	string		$source		name of the source tar archive
	 * @param	FTP		$ftp		active ftp connection
	 * @param 	FileHandler	$fileHandler
	 * @param	string		$folder		extract only the files from this subfolder 
	 */
	public function __construct($targetDir, $source, FTP $ftp, $fileHandler = null, $folder = '') {
		$this->ftp = $ftp;
		parent::__construct($targetDir, $source, $fileHandler, $folder);
	}
	
	/**
	 * @see Installer::checkTargetDir()
	 */
	protected function checkTargetDir() {
		$this->ftpPath = FTPUtil::getRelativeFtpPath($this->ftp, $this->targetDir);
		if ($this->ftpPath === null) {
			throw new SystemException("cannot detect relative path coming from absolute path '".$this->targetDir."' at ftp server.", 14004);
		}
	}
	
	/**
	 * @see Installer::createTargetDir()
	 */
	protected function createTargetDir() {
		if (!FTPUtil::pathExists($this->ftp, $this->ftpPath)) {
			if (!FTPUtil::makePath($this->ftp, $this->ftpPath, (IS_APACHE_MODULE ? 0777 : 0755))) {
				throw new SystemException("cannot create directory '".$this->ftpPath."' at ftp server.", 14005);
			}
		}
		else {
			if (IS_APACHE_MODULE || !is_writeable($this->targetDir)) {
				$this->makeWriteable($this->ftpPath);
			}
		}
		// create dummy upload file
		$this->dummyUploadFile = FileUtil::getTemporaryFilename('ftpDummy_', '.dat');
		@touch($this->dummyUploadFile);
	}
	
	/**
	 * @see Installer::createDir()
	 */
	protected function createDir($dir) {
		if (!file_exists($this->targetDir.$dir)) {
			if (!@$this->ftp->mkdir($this->ftpPath.$dir)) {
				throw new SystemException("cannot create directory '".$this->ftpPath.$dir."' at ftp server.", 14006);
			}
		}
		if (IS_APACHE_MODULE || !is_writeable($this->targetDir.$dir)) {
			$this->makeWriteable($this->ftpPath.$dir);
		}
	}
	
	/**
	 * @see Installer::createFile()
	 */
	protected function createFile($file, $index, Tar $tar) {
		if (!file_exists($this->targetDir.$file)) {
			if (!$this->ftp->put($this->ftpPath.$file, $this->dummyUploadFile, FTP_ASCII)) {
				throw new SystemException("cannot create file '".$this->ftpPath.$file."' at ftp server.", 14007);
			}
		}
		if (IS_APACHE_MODULE || !is_writeable($this->targetDir.$file)) {
			$this->makeWriteable($this->ftpPath.$file);
		}
		$tar->extract($index, $this->targetDir.$file);
	}
	
	/**
	 * @see Installer::close()
	 */
	protected function close() {
		unlink($this->dummyUploadFile);
	}
	
	/**
	 * Makes a file or directory writeable.
	 * 
	 * @param	string		$target
	 */
	protected function makeWriteable($target) {
		if (!preg_match('/^WIN/i', PHP_OS)) {
			if (!@$this->ftp->chmod(0777, $target)) {
				throw new SystemException("cannot make '".$target."' writable at ftp server.", 14008);
			}
		}
	}
	
	/**
	 * @see Installer::touchFile()
	 */
	public function touchFile($file) {
		if (!file_exists($this->targetDir.$file)) {
			if (!$this->ftp->put($this->ftpPath.$file, $this->dummyUploadFile, FTP_ASCII)) {
				throw new SystemException("cannot create file '".$this->ftpPath.$file."' at ftp server.", 14007);
			}
		}
		$this->makeWriteable($this->ftpPath.$file);
	}
}
?>