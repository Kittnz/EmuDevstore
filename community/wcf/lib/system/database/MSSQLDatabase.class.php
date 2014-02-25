<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
}

/**
 * This is the database implementation for MSSQL.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class MSSQLDatabase extends Database {
	protected $insertID = null;
	protected $affectedRows = null;

	/**
	 * Connects to MSSQL Server
	 */
	protected function connect() {
		if ($this->usePConnect) {
			$this->linkID = @mssql_pconnect($this->host, $this->user, $this->password);
		}
		else {
			$this->linkID = @mssql_connect($this->host, $this->user, $this->password);
		}

		if ($this->linkID === false) {
			throw new DatabaseException("Connecting to MSSQL server '".$this->host."' failed.", $this);
		}
	}

	/**
	 * Selects a MSSQL database.
	 */
	protected function selectDatabase() {
		if (@mssql_select_db($this->database, $this->linkID) === false) {
			throw new DatabaseException("Cannot use database ".$this->database, $this);
		}
	}

	/**
	 * Does nothing.
	 */
	public function createDatabase() {}

	/**
	 * Sends a database query to MSSQL server.
	 *
	 * @param	string		$query 		a database query
	 * @param	integer		$limit
	 * @param 	integer		$offset
	 * @return 	integer				id of the query result
	 */
	public function sendQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);
		
		$this->queryCount++;
		$result = @mssql_query($query, $this->linkID);
		if ($result === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		// get insert id and affected rows
		if (preg_match('/^INSERT /i', $query)) {
			$row = $this->getFirstRow("SELECT @@IDENTITY AS id, @@ROWCOUNT as affected");
			$this->insertID = $row['id'];
			$this->affectedRows = $row['affected'];
		}
		else if (preg_match('/^(UPDATE|DELETE) /i', $query)) {
			$row = $this->getFirstRow("SELECT @@ROWCOUNT as affected");
			$this->affectedRows = $row['affected'];
		}
		
		$this->queryID = $result;
		if ($offset != 0) {
			$this->seekResult($this->queryID, $offset);		
		}
		return $this->queryID;
	}

	/**
	 * Gets a row from MSSQL database query result.
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

		$row = @mssql_fetch_array($this->queryID, $type);
		
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
		
		$rowCount = @mssql_num_rows($this->queryID);
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
		return $this->affectedRows;
	}

	/**
	 * Returns ID from last insert.
	 *
	 * @param 	string		$table
	 * @param	string		$field
	 * @return 	int		last insert ID
	 */
	public function getInsertID($table = '', $field = '') {
		return $this->insertID;
	}

	/**
	 * Moves the internal row pointer of the result associated with the specified result identifier to point to the specified row number.
	 *
	 * @param	integer		$queryID	
	 * @param	integer		$offset		specified row number
	 */
	public function seekResult($queryID = null, $offset) {
		if ($queryID !== null) $this->queryID = $queryID;

		if (@mssql_data_seek($this->queryID, $offset) === false) {
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
			$limit += $offset;
			$query = preg_replace("/^SELECT\s(.*)$/is", "SELECT TOP " . $limit . " \\1", $query);
		}

		return $query;
	}

	/**
	 * Returns MSSQL error description for last error.
	 *
	 * @return 	string		MSSQL error description
	 */
	public function getErrorDesc() {
		return mssql_get_last_message();
	}
	
	/**
	 * @see Database::escapeString()
	 */
	public function escapeString($string) {
		return str_replace("'", "''", $string);
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
		return function_exists('mssql_connect');
	}
}
?>