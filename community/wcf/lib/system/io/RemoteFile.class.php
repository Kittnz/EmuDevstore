<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/io/File.class.php');
}

/**
 * The RemoteFile class opens a connection to a remote host as a file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
class RemoteFile extends File {
	protected $host;
	protected $port;
	protected $errorNumber = 0;
	protected $errorDesc = '';
	
	/**
	 * Opens a new connection to a remote host.
	 * 
	 * @param 	string		$host
	 * @param 	string		$port
	 * @param 	integer		$timeout
	 * @param	array		$options
	 */
	public function __construct($host, $port, $timeout = 30, $options = array()) {
		$this->host = $host;
		$this->port = $port;
		if (count($options)) {
			$context = stream_context_create($options);
			$this->resource = fsockopen($host, $port, $this->errorNumber, $this->errorDesc, $timeout, $context);
		}
		else {
			$this->resource = fsockopen($host, $port, $this->errorNumber, $this->errorDesc, $timeout);
		}
		if ($this->resource === false) {
			throw new SystemException('Can not connect to ' . $host, 14000);
		}
	}
	
	/**
	 * Returns the error number of the last error.
	 * 
	 * @return 	integer
	 */
	public function getErrorNumber() {
		return $this->errorNumber;
	}
	
	/**
	 * Returns the error description of last error.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->errorDesc;
	}
}
?>