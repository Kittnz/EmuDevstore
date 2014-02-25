<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/setup/Installer.class.php');
}

/**
 * FileInstaller is an implementation of Installer.
 * It uses simple file operations to extract the files.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class FileInstaller extends Installer {
	/**
	 * @see Installer::checkTargetDir()
	 */
	protected function checkTargetDir() {}
	
	/**
	 * @see Installer::createTargetDir()
	 */
	protected function createTargetDir() {
		if (!@is_dir($this->targetDir)) {
			if (!FileUtil::makePath($this->targetDir, (IS_APACHE_MODULE ? 0777 : 0755))) {
				throw new SystemException("Could not create dir '".$this->targetDir."'", 11011);
			}
		}
		if (IS_APACHE_MODULE || !is_writeable($this->targetDir)) {
			$this->makeWriteable($this->targetDir);
		}
	}
	
	/**
	 * @see Installer::createDir()
	 */
	protected function createDir($dir) {
		if (!@is_dir($this->targetDir.$dir)) {
			$oldumask = umask(0);
			if (!@mkdir($this->targetDir.$dir, 0755, true)) {
				throw new SystemException("Could not create dir '".$this->targetDir.$dir."'", 11011);
			}
			umask($oldumask);
		}
		if (IS_APACHE_MODULE || !is_writeable($this->targetDir.$dir)) {
			$this->makeWriteable($this->targetDir.$dir);
		}
	}
	
	/**
	 * @see Installer::createFile()
	 */
	protected function createFile($file, $index, Tar $tar) {
		$tar->extract($index, $this->targetDir.$file);
		if (IS_APACHE_MODULE || !is_writeable($this->targetDir.$file)) {
			$this->makeWriteable($this->targetDir.$file);
		}
	}
	
	/**
	 * @see Installer::close()
	 */
	protected function close() {}
	
	/**
	 * Makes a file or directory writeable.
	 * 
	 * @param	string		$target
	 */
	protected function makeWriteable($target) {
		if (!preg_match('/^WIN/i', PHP_OS)) {
			if (!@chmod($target, 0777)) {
				//throw new SystemException("Could not chmod file '".$target."'", 11005);
			}
		}
	}
	
	/**
	 * @see Installer::touchFile()
	 */
	public function touchFile($file) {
		@touch($this->targetDir.$file);
		$this->makeWriteable($this->targetDir.$file);
	}
}
?>