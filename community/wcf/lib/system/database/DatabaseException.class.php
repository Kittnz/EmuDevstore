<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
}

/**
 * DatabaseException is a specific SystemException for database errors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class DatabaseException extends SystemException {
	protected $errorNumber;
	protected $errorDesc;
	protected $sqlVersion = null;
	protected $DBType;
	protected $db;
	
	/**
	 * Creates a new DatabaseException.
	 * 
	 * @param	string		$message	error message
	 * @param	Database	$db		affected db object
	 */
	public function __construct($message, Database $db) {
		$this->errorNumber 	= $db->getErrorNumber();
		$this->errorDesc	= $db->getErrorDesc();
		$this->DBType		= $db->getDBType();
		$this->db 		= $db;
		
		parent::__construct($message, $this->errorNumber);
	}
	
	/**
	 * Returns the error number of this exception.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		return $this->errorNumber;
	}
	
	/**
	 * Returns the error description of this exception.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->errorDesc;
	}
	
	/**
	 * Returns the current sql version of the database.
	 * 
	 * @return	string
	 */
	public function getSQLVersion() {
		if ($this->sqlVersion) {
			try {
				$this->sqlVersion = $this->db->getVersion();
			}
			catch (DatabaseException $e) {
				$this->sqlVersion = 'unknown';
			}
		}
		
		return $this->sqlVersion;
	}
	
	/**
	 * Returns the sql type of the active database.
	 * 
	 * @return	string 
	 */
	public function getDBType() {
		return $this->DBType;
	}
	
	/**
	 * Prints the error page.
	 */
	public function show() {
		$this->information .= '<b>sql type:</b> ' . StringUtil::encodeHTML($this->getDBType()) . '<br />';
		$this->information .= '<b>sql error:</b> ' . StringUtil::encodeHTML($this->getErrorDesc()) . '<br />';
		$this->information .= '<b>sql error number:</b> ' . StringUtil::encodeHTML($this->getErrorNumber()) . '<br />';
		$this->information .= '<b>sql version:</b> ' . StringUtil::encodeHTML($this->getSQLVersion()) . '<br />';
		
		parent::show();
	}
}
?>