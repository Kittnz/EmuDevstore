<?php

/**
 * File storage for product files
 * 
 * @author Maxi Arnicke <maxi.arnicke@gmail.com>
 * @package Emu-Devstore
 * @link http://emu-devstore.com
 */

class FilestorageException extends Exception {
	const ERR_FILETYPE = 1;
	const ERR_FTP = 2;
}

class Filestorage
{
	protected $ftpConn = null;
	
	/**
	 * Returns the FTP connection - lazy loading.
	 *
	 * @return resource FTP stream
	 */
	protected function getFtp()
	{
		if ($this->ftpConn == null)
		{
			$CI =& get_instance();
			$this->ftpConn = ftp_connect($CI->config->item('ftp_hostname'));
		
			if ( ! ftp_login($this->ftpConn, $CI->config->item('ftp_username'), $CI->config->item('ftp_password')))
				throw new FilestorageException('FTP login failed.', FilestorageException::ERR_FTP);
		
			ftp_pasv($this->ftpConn, true);
		}
		
		return $this->ftpConn;
	}
	
	public function __destruct()
	{
		if ($this->ftpConn !== null)
			ftp_close($this->ftpConn);
	}
	
	public function delete($file)
	{
		$ftp = $this->getFtp();
		
		return ftp_delete($ftp, $file);
	}
	
	/**
	 * Validates and uploads a file.
	 *
	 * @param string $file
	 * @param string $new_name
	 * @param array $allowed_filetypes
	 */
	public function upload($file, $new_name, $allowed_types = null)
	{
		if ($allowed_types !== null)
		{
			if ( ! self::checkFileType($file, $allowed_types))
				throw new FilestorageException('Wrong file type.', FilestorageException::ERR_FILETYPE);
		}
		
		$ftp = $this->getFtp();

		if ( ! ftp_put($ftp, $new_name, $file['tmp_name'], FTP_BINARY)) 
			throw new FilestorageException('FTP file upload failed.', FilestorageException::ERR_FILETYPE);
		
		return true;
	}
	
	/**
	 * Returns an unique filename.
	 *
	 * @param string $title
	 * @param string $ext
	 * @param string $suffix
	 * @return string
	 */
	public function getName($title, $ext, $suffix = null)
	{
		if ( ! function_exists('url_title')) {
			$CI =& get_instance();
			$CI->load->helper('url');
		}
		
		return url_title(trim($title), '_').'_'.uniqid().($suffix !== null ? '_'.$suffix : '').'.'.$ext;
	}
	
	protected static function checkFileType($file, $allowed)
	{
		if (is_string($allowed))
			$allowed = array($allowed);
		
		return in_array(strtolower(end(explode('.', $file['name']))), $allowed);
	}
}