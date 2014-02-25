<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
}

/**
 * This is the database implementation for SQLite.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class SQLite2Database extends Database {
	/**
	 * Does nothing.
	 */
	protected function connect() {}

	/**
	 * Selects a SQLite database.
	 */
	protected function selectDatabase() {
		$errorMessage = '';
		if ($this->usePConnect) {
			$this->linkID = @sqlite_popen($this->database, 0666, $errorMessage);
		}
		else {
			$this->linkID = @sqlite_open($this->database, 0666, $errorMessage);
		}
		
		if ($this->linkID === false) {
			throw new DatabaseException("Cannot use database ".$this->database . "\n" . $errorMessage, $this);		
		}
	}

	/**
	 * Does nothing.
	 */
	public function createDatabase() {}

	/**
	 * Returns SQLite error number for last error.
	 *
	 * @return 	integer		SQLite error number
	 */
	public function getErrorNumber() {
		return @sqlite_last_error($this->linkID);
	}

	/**
	 * Returns SQLite error description for last error.
	 *
	 * @return 	string		SQLite error description
	 */
	public function getErrorDesc() {
		return @sqlite_error_string($this->getErrorNumber());
	}
	
	/**
	 * Sends a database query to SQLite database.
	 *
	 * @param	string		$query 		a database query
	 * @param	integer		$limit
	 * @param 	integer		$offset
	 * @return 	integer				id of the query result
	 */
	public function sendQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);
		
		$this->queryCount++;
		$this->queryID = @sqlite_query($this->linkID, $query);
		if ($this->queryID === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		return $this->queryID;
	}
	
	/**
	 * @see Database::sendUnbufferedQuery()
	 */
	public function sendUnbufferedQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);
		
		$this->queryCount++;
		$this->queryID = @sqlite_unbuffered_query($this->linkID, $query);
		if ($this->queryID === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		return $this->queryID;
	}

	/**
	 * Gets a row from SQLite database query result.
	 *
	 * @param	integer		$queryID	
	 * @param	integer		$type 		fetch type
	 * @return 	array				a row from result
	 */
	public function fetchArray($queryID = null, $type = null) {
		if ($queryID !== null) $this->queryID = $queryID;

		if ($type === null) {
			$type = Database::SQL_ASSOC;
		}

		if (($row = @sqlite_fetch_array($this->queryID, $type))) {
			foreach ($row as $key => $val) {
				if (is_string($key) && ($pos = strpos($key, '.')) !== false) {
					unset($row[$key]);
					$row[substr($key, $pos + 1)] = $val;	
				}	
			}
		}

		if ($row === null) {
			throw new DatabaseException("Cannot fetch result", $this);
		}
		
		return $row;
	}
	
	/**
	 * Counts number of rows in a result returned by a SELECT query.
	 *
	 * @param	integer		$queryID	
	 * @return 	integer				number of rows in a result
	 */
	public function countRows($queryID = null) {
		if ($queryID !== null) $this->queryID = $queryID;
		
		$rowCount = @sqlite_num_rows($this->queryID);
		if ($rowCount === false) {
			throw new DatabaseException("Cannot count rows", $this);
		}
		
		return $rowCount;
	}
	
	/**
	 * Counts number of affected rows by the last sql statement (INSERT, UPDATE or DELETE).
	 *
	 * @return 	integer				number of affected rows
	 */
	public function getAffectedRows() {
		return @sqlite_changes($this->linkID);
	}

	/**
	 * Returns ID from last insert.
	 *
	 * @param 	string		$table
	 * @param	string		$field
	 * @return 	int		last insert ID
	 */
	public function getInsertID($table = '', $field = '') {
		return @sqlite_last_insert_rowid($this->linkID);
	}

	/**
	 * Moves the internal row pointer of the result associated with the specified result identifier to point to the specified row number.
	 *
	 * @param	integer		$queryID	
	 * @param	integer		$offset		specified row number
	 */
	public function seekResult($queryID = null, $offset) {
		if ($queryID !== null) $this->queryID = $queryID;

		if (@sqlite_seek($this->queryID, $offset) === false) {
			throw new DatabaseException("Cannot seek result to offset " . $offset, $this);
		}
	}
	
	/**
	 * Handles the limit and offset parameter in select queries.
	 * 
	 * @param	string		$query
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @return 	string		$query
	 */
	public function handleLimitParameter($query, $limit = 0, $offset = 0) {
		if ($limit != 0) {
			$query .= " LIMIT " . $limit . " OFFSET " . $offset;
		}

		return $query;
	}

	/**
	 * @see Database::escapeString()
	 */
	public function escapeString($string) {
		return @sqlite_escape_string($string);
	}
	
	/**
	 * Does nothing.
	 */
	public function getTableNames($database = '') {}
	
	/**
	 * Does nothing.
	 */
	public function getTableStatus() {}
	
	/**
	 * Returns true, if this database type is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported() {
		return function_exists('sqlite_open');
	}
}
?>