<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/MySQLDatabase.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
}

/**
 * This is the database implementation for MySQL4.1 or higher using the mysqli extension.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class MySQLiDatabase extends MySQLDatabase {
	protected $mySQLi;
	protected $result;
	
	/**
	 * Connects to MySQL Server
	 */
	protected function connect() {
		@$this->mySQLi = new MySQLi($this->host, $this->user, $this->password);
		if (mysqli_connect_errno()) {
			throw new DatabaseException("Connecting to MySQL server '".$this->host."' failed.", $this);
		}
		
		// set connection character set
		if (!empty($this->charset)) {
			$this->setCharset($this->charset);
		}
	}

	/**
	 * Sets the charset of the database connection.
	 * 
	 * @param	string		$charset
	 */
	public function setCharset($charset) {
		if (function_exists('mysqli_set_charset')) {
			$this->mySQLi->set_charset($charset);
		}
		else {
			parent::setCharset($charset);
		}
	}

	/**
	 * Selects a MySQL database.
	 */
	protected function selectDatabase() {
		if ($this->mySQLi->select_db($this->database) === false) {
			throw new DatabaseException("Cannot use database ".$this->database, $this);
		}
	}

	/**
	 * Returns MySQL error number for last error.
	 *
	 * @return 	integer		MySQL error number
	 */
	public function getErrorNumber() {
		return $this->mySQLi->errno;
	}

	/**
	 * Returns MySQL error description for last error.
	 *
	 * @return 	string		MySQL error description
	 */
	public function getErrorDesc() {
		return $this->mySQLi->error;
	}

	/**
	 * Sends a database query to MySQL server.
	 *
	 * @param	string		$query 		a database query
	 * @param	integer		$limit
 	 * @param 	integer		$offset
	 * @return 	integer				id of the query result
	 */
	public function sendQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);
		
		$this->queryCount++;
		$this->result = $this->mySQLi->query($query);
		if ($this->result === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		return $this->result;
	}
	
	/**
	 * @see Database::sendUnbufferedQuery()
	 */
	public function sendUnbufferedQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);
		
		$this->queryCount++;
		$this->result = $this->mySQLi->real_query($query);
		if ($this->result === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		return $this->result;
	}

	/**
	 * Gets a row from MySQL database query result.
	 *
	 * @param			$result	
	 * @param	integer		$type 		fetch type
	 * @return 	array				a row from result
	 */
	public function fetchArray($result = null, $type = null) {
		if ($result !== null) $this->result = $result;

		if ($type === null) {
			$type = Database::SQL_ASSOC;
		}

		$row = $this->result->fetch_array($type);
		
		return $row;
	}
	
	/**
	 * Counts number of rows in a result returned by a SELECT query.
	 *
	 * @param			$result	
	 * @return 	integer				number of rows in a result
	 */
	public function countRows($result = null) {
		if ($result !== null) $this->result = $result;
		
		return $this->result->num_rows;
	}
	
	/**
	 * Counts number of affected rows by the last sql statement (INSERT, UPDATE or DELETE).
	 *
	 * @return 	integer				number of affected rows
	 */
	public function getAffectedRows() {
		return $this->mySQLi->affected_rows;
	}

	/**
	 * Returns ID from last insert.
	 *
	 * @param 	string		$table
	 * @param	string		$field
	 * @return 	int		last insert ID
	 */
	public function getInsertID($table = '', $field = '') {
		return $this->mySQLi->insert_id;
	}

	/**
	 * Moves the internal row pointer of the result associated with the specified result identifier to point to the specified row number.
	 *
	 * @param			$result	
	 * @param	integer		$offset		specified row number
	 */
	public function seekResult($result = null, $offset) {
		if ($result !== null) $this->result = $result;

		if ($this->result->data_seek($offset) === false) {
			throw new DatabaseException("Cannot seek result to offset " . $offset, $this);
		}
	}

	/**
	 * Returns the mysql version.
	 * 
	 * @return 	string
	 */
	public function getVersion() {
		return $this->mySQLi->server_info;
	}
	
	/**
	 * @see Database::escapeString()
	 */
	public function escapeString($string) {
		return $this->mySQLi->escape_string($string);
	}
	
	/**
	 * Returns true, if this database type is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported() {
		return function_exists('mysqli_connect');
	}
}
?>