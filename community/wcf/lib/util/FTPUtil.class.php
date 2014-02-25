<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/io/FTP.class.php');
}

/**
 * Contains ftp-related functions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class FTPUtil {
	/**
	 * Checks wheater a path on the ftp server exists or not.
	 *
	 * @param 	FTP 		$ftpConnection
	 * @param 	string 		$path
	 * @return 	boolean 	$exists
	 */
	public static function pathExists(FTP $ftp, $path) {
		$currentDir = $ftp->pwd();
		$result = @$ftp->chdir($path);
		@$ftp->chdir($currentDir);
		return $result;	
	}


	/**
	 * Creates a path on an ftp-server.
	 * Parent directories do not need to exist as
	 * they will be created (if necessary).
	 * Returns true on success, otherwise false.
	 *
	 * @param 	FTP	 	$ftpConnection
	 * @param 	string 		$path
	 * @param 	integer 	$chmod
	 * @return 	boolean 	$success
	 */
	public static function makePath(FTP $ftp, $path, $chmod = 0777) {
		$currentDir = $ftp->pwd();
		// directory already exists, abort
		if (self::pathExists($ftp, $path)) {
			return true;
		}

		// check if parent directory exists
		$parent = dirname($path);
		if ($parent != $path/* && StringUtil::length(FileUtil::addTrailingSlash($currentDir)) <= StringUtil::length(FileUtil::addTrailingSlash($parent))*/) {
			// parent directory does not exist either
			// we have to create the parent directory first
			if (!self::pathExists($ftp, $parent)) {
				// could not create parent directory either => abort
				if (!self::makePath($ftp, $parent, $chmod)) {
					return false;
				}
			}

			// well, the parent directory exists or has been created
			// lets create this path
			if (!@$ftp->mkdir($path)) {
				return false;
			}
			if (!@$ftp->chmod($chmod, $path)) {
				//return false;
			}

			@$ftp->chdir($currentDir);
			return true;
		}

		@$ftp->chdir($currentDir);
		return false;
	}

	/**
	 * Searches an absolute filesystem path on a ftp-server.
	 * Returns a path on success, otherwise false.
	 * THIS METHOD SEEMS TO BE USELESS AND MIGHT BE DELETED.
	 *
	 * @param 	FTP	 	$ftpConnection
	 * @param 	string 		$path
	 * @return	mixed 		$result
	 */
	public static function searchFTPPath(FTP $ftp, $path) {
		$path = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($path));
		$ftpPath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeperator($ftp->pwd()));

		// ftpPath contains path (probably windows server)
		if (StringUtil::length($ftpPath) > 1 && StringUtil::toLowerCase($ftpPath) == StringUtil::toLowerCase(StringUtil::substring($path, 0, StringUtil::length($ftpPath)))) {
			return $path;
		}
		else {
			$index = 0;
			$foundPath = $ftpPath;
			$pathSegments = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($path)));
			$rawlist = self::getRawlist($ftp);
			$found = false;
			for ($i = 0; $i < count($pathSegments); $i++) {
				foreach ($rawlist as $entry) {
					if ($entry['type'] != 'folder') {
						continue;
					}

					if ($pathSegments[$i] == $entry['filename']) {
						$found = true;
						$foundPath .= FileUtil::addTrailingSlash($pathSegments[$i]);
						for ($i = $i + 1; $i < count($pathSegments); $i++) {
							$foundPath .= FileUtil::addTrailingSlash($pathSegments[$i]);
						}

						break (2);
					}
				}
			}

			if (!$found) {
				return false;
			}
			else {
				return $foundPath;
			}
		}
	}

	/**
	 * Gets a parsed rawlist from ftp.
	 *
	 * @param 	FTP	 	$ftpConnection
	 * @param 	string 		$directory
	 * @return 	array 		$rawlist
	 */
	public static function getRawlist(FTP $ftp, $directory = '') {
		$currentDir = $ftp->pwd();
		if (!@$ftp->chdir($directory)) {
			// try again without leading slash
			$directory = FileUtil::removeLeadingSlash($directory);
			if (!@$ftp->chdir($directory)) {
				$directory = $currentDir;
				if (!@$ftp->chdir($directory)) {
					return false;
				}
			}
		}
		$rawlist = @$ftp->rawlist('-a');
		if (count($rawlist) <= 1) {
			$rawlist = @$ftp->rawlist('');
		}
		$rawlist = self::parseRawlist($rawlist);

		@$ftp->chdir($currentDir);

		return $rawlist;
	}

	/**
	 * Parses a rawlist given by a ftp-server
	 * and creates an easy to read array.
	 *
	 * @param 	array 		$rawlist
	 * @return  	array 		
	 */
	protected static function parseRawlist($rawlist) {
		$parsed = array();
		foreach ($rawlist as $list) {
			$entry = $_matches = array();

			// strict rule
			if (preg_match('%([\-dl])([rwxst\-]{9})[ ]+([0-9]+)[ ]+([^ ]+)[ ]+(.+)[ ]+([0-9]+)[ ]+([a-zA-Z]+[ ]+[0-9]+)[ ]+([0-9:]+)[ ]+(.*)%', $list, $_matches)) {
				$entry['scanrule']	= 'rule-1';
				$entry['type']		= ($_matches[1] == 'd' ? 'folder' : 'file');
				$entry['filename']	= $_matches[9];
				$entry['size']		= $_matches[6];
				$entry['owner']		= $_matches[4];
				$entry['group']		= $_matches[5];
				$entry['permissions']	= $_matches[2];
				$entry['mtime']		= $_matches[7].' '.$_matches[8];
			}

			// less strict rule
			elseif (preg_match('%([\-dl])([rwxst\-]{9})[ ]+(.*)[ ]+([a-zA-Z0-9 ]+)[ ]+([0-9:]+)[ ]+(.*)%', $list, $_matches)) {
				$entry['scanrule']	= 'rule-2';
				$entry['type']		= ($_matches[1] == 'd' ? 'folder' : 'file');
				$entry['filename']	= $_matches[6];
				$entry['size']		= $_matches[3];
				$entry['permissions']	= $_matches[2];
				$entry['mtime']		= $_matches[4].' '.$_matches[5];
			}

			// Windows specific rule
			elseif (preg_match('%([0-9/\-]+)[ ]+([0\--9:AMP]+)[ ]+([0-9]*)[ ]+(.*)%', $list, $_matches)) {
				$entry['scanrule']	= 'rule-3';
				$entry['size']		= $_matches[3];
				$entry['type']		= ($entry['size'] == '' ? 'folder' : 'file');
				$entry['filename']	= StringUtil::trim(StringUtil::replace('<DIR>', '', $_matches[4]));
				$entry['owner']		= '';
				$entry['group']		= '';
				$entry['permissions']	= '';
				$entry['mtime']		= $_matches[1].' '.$_matches[2];
			}

			// AS-400 specific rule
			elseif (preg_match('%([a-zA-Z0-9_\-]+)[ ]+([0-9]+)[ ]+([0-9/\-]+)[ ]+([0-9:]+)[ ]+([a-zA-Z0-9_ \-\*]+)[ /]+([^/]+)%', $list, $_matches)) {
				$entry['scanrule']	= 'rule-4';
				$entry['type']		= ($_matches[5] != '*STMF' ? 'folder' : 'file');
				$entry['filename']	= $_matches[6];
				$entry['size']		= $_matches[2];
				$entry['owner']		= $_matches[1];
				$entry['group']		= '';
				$entry['permissions']	= '';
				$entry['mtime']		= $_matches[3].' '.$_matches[4];
			}

			// no rule applicable, return raw entry
			else {
				$entry['scanrule']	= 'no-rule';
				$entry['type'] 		= 'unknown';
				$entry['filename'] 	= $list;

			}

			// skip '.' and '..' directories
			if ($entry['filename'] == '.' || $entry['filename'] == '..') {
				continue;
			}

			$parsed[] = $entry;
		}

		return $parsed;
	}
	
	/**
	 * Downloads a package archive from an ftp URL.
	 * 
	 * @param	string		$ftpUrl
	 * @param	string		$prefix
	 * @return	string		path to the dowloaded file
	 */
	public static function downloadFileFromFtp($ftpUrl, $prefix = 'package') {
		$parsedUrl = parse_url($ftpUrl);
		$host = $parsedUrl['host'];
		$path = $parsedUrl['path'];
		
		$ftpConnection = self::initFtpAccess($host);
		// for now, we silently assume that fopen() is available.
		$extension = strrchr($path, '.');
		$newFileName = FileUtil::getTemporaryFilename($prefix.'_', $extension);			
		$localFileHandle = fopen($newFileName, 'wb'); // the file to write.
		// get the requested remote file and write to the local system.
		if (!$ftpConnection->fget($localFileHandle, $path, FTP_BINARY)) {
			fclose($localFileHandle);
			unlink($newFileName);
			throw new SystemException("cannot get requested file from ftp host '".$host."'", 14003);				
		} else {
			fclose($localFileHandle);
			return $newFileName;				
		}
	}
	
	/**
	 * Inits ftp access.
	 * 
	 * @param 	string 		$ftpHost
	 * @param	string		$ftpUser
	 * @param	string		$ftpPassword
	 * @return 	FTP		the opened ftp resource
	 */
	public static function initFtpAccess($ftpHost = 'localhost', $ftpUser = 'anonymous', $ftpPassword = '') {
		// login into ftp server.
		@$ftp = new FTP($ftpHost);
		if (!@$ftp->login($ftpUser, $ftpPassword)) {
			throw new SystemException("cannot login into ftp server '".$ftpHost."'", 14002);
		} else {
			return $ftp;
		}
	}
	
	/**
	 * Prompts for ftp username and password.
	 * 
	 * @param 	string 		$ftpHost
	 * @param	string		$ftpUser
	 * @param	string		$ftpPassword
	 * @param	string		$errorField
	 * @param	string		$errorType
	 */
	public static function promptFtpAccess($ftpHost = 'localhost', $ftpUser = 'anonymous', $ftpPassword = '', $errorField = '', $errorType = '') {
		// prompt access data for ftp login.
		WCF::getTPL()->assign(array(
			'ftpHost' => $ftpHost,
			'ftpUser' => $ftpUser,
			'ftpPassword' => $ftpPassword,
			'errorField'  => $errorField,
			'errorType'  => $errorType
		));
		WCF::getTPL()->display('packageInstallationPromptFtpUser');
		exit;
	}
	
	/**
	 * Searches a relative path emanating from an absolute installation path.
	 * 
	 * @param 	FTP 		$ftp
	 * @param 	string 		$installPath
	 * @return 	string		$relativeFtpPath
	 */
	public static function getRelativeFtpPath(FTP $ftp, $installPath) {
		// write a zero byte test file to the presumptive root of the ftp server.
		$installPath = FileUtil::unifyDirSeperator($installPath);
		$destFile = self::writeDummyFile($ftp);
		$pathPrefix = '';
		if (!$destFile) {
			// loop through folders to find a writable directory
			$pathSegments = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($installPath)));
			foreach ($pathSegments as $path) {
				if (@$ftp->chdir($path)) {
					$pathPrefix .= $path . '/';
					if (($destFile = self::writeDummyFile($ftp))) {
						for ($i = 0; $i < substr_count($pathPrefix, '/'); $i++) $ftp->cdup();
						break;
					}
				}
				else if (!empty($pathPrefix)) {
					return null;
				}
			}
		}
		
		// search given installation path for that file.
		$pathSegments = explode('/', FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($installPath)));
		while (count($pathSegments) != 0) {
			if (preg_match('/^WIN/i', PHP_OS)) {
				$currentDir = FileUtil::addTrailingSlash(implode($pathSegments, '/'));
			} else {
				$currentDir = '/'.FileUtil::addTrailingSlash(implode($pathSegments, '/'));
			}
			if (@file_exists($currentDir.$destFile)) {
				$basePath = $currentDir;
				break;
			} else {
				array_pop($pathSegments);
			}
		}
		if ($pathPrefix.$destFile) {
			$ftp->delete($pathPrefix.$destFile);
		}
		
		// return the rest of the path which must be the relative path.
		$relativeFtpPath = FileUtil::addTrailingSlash($pathPrefix.str_replace($currentDir, '', $installPath));
		return $relativeFtpPath;
	}
	
	/**
	 * Writes a dummy file in the selected ftp directory.
	 * Returns the file of the dummy file or null on failure.
	 * 
	 * @param 	FTP 		$ftp
	 * @return	string		$filename
	 */
	protected static function writeDummyFile(FTP $ftp) {
		$dummyUploadFile = FileUtil::getTemporaryFilename('ftpDummy_', '.dat');
		@touch($dummyUploadFile);
		$destFile = FileUtil::removeLeadingSlash(strrchr($dummyUploadFile, '/'));
		if (@!$ftp->put($destFile, $dummyUploadFile, FTP_ASCII)) {
			@unlink($dummyUploadFile);
			return null;
		}
		@unlink($dummyUploadFile);
		return $destFile;
	}
	
}
?>