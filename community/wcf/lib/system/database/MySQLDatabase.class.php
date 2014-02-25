<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
}

/**
 * This is the database implementation for MySQL4.1 or higher.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class MySQLDatabase extends Database {
	/**
	 * Connects to MySQL Server
	 */
	protected function connect() {
		if ($this->usePConnect) {
			$this->linkID = @mysql_pconnect($this->host, $this->user, $this->password);
		}
		else {
			$this->linkID = @mysql_connect($this->host, $this->user, $this->password);
		}

		if ($this->linkID === false) {
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
		try {
			$this->sendQuery("SET NAMES '".$this->escapeString($charset)."'");
		}
		catch (DatabaseException $e) {
			// ignore
		}
	}

	/**
	 * Selects a MySQL database.
	 */
	protected function selectDatabase() {
		if (@mysql_select_db($this->database, $this->linkID) === false) {
			throw new DatabaseException("Cannot use database ".$this->database, $this);
		}
	}

	/**
	 * Creates a new MySQL database.
	 */
	public function createDatabase() {
		try {
			$this->selectDatabase();
		}
		catch (DatabaseException $e) {
			try {
				$this->sendQuery("CREATE DATABASE IF NOT EXISTS `".$this->database."`");
			}
			catch (DatabaseException $e2) {
				throw new DatabaseException("Cannot create database ".$this->database, $this);
			}
		}
	}

	/**
	 * Returns MySQL error number for last error.
	 *
	 * @return 	integer		MySQL error number
	 */
	public function getErrorNumber() {
		if (!($errorNumber = @mysql_errno($this->linkID))) {
			$errorNumber = @mysql_errno();
		}
		return $errorNumber;
	}

	/**
	 * Returns MySQL error description for last error.
	 *
	 * @return 	string		MySQL error description
	 */
	public function getErrorDesc() {
		if (!($errorDesc = @mysql_error($this->linkID))) {
			$errorDesc = @mysql_error();
		}
		return $errorDesc;
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
		$this->queryID = @mysql_query($query, $this->linkID);
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
		$this->queryID = @mysql_unbuffered_query($query, $this->linkID);
		if ($this->queryID === false) {
			throw new DatabaseException("Invalid SQL: " . $query, $this);
		}
		
		return $this->queryID;
	}

	/**
	 * Gets a row from MySQL database query result.
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

		$row = @mysql_fetch_array($this->queryID, $type);
		
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
		
		$rowCount = @mysql_num_rows($this->queryID);
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
		$rowCount = @mysql_affected_rows($this->linkID);
		if ($rowCount === -1) {
			throw new DatabaseException("Cannot count affected rows", $this);
		}
		return $rowCount;
	}

	/**
	 * Returns ID from last insert.
	 *
	 * @param 	string		$table
	 * @param	string		$field
	 * @return 	int		last insert ID
	 */
	public function getInsertID($table = '', $field = '') {
		return @mysql_insert_id($this->linkID);
	}

	/**
	 * Moves the internal row pointer of the result associated with the specified result identifier to point to the specified row number.
	 *
	 * @param	integer		$queryID	
	 * @param	integer		$offset		specified row number
	 */
	public function seekResult($queryID = null, $offset) {
		if ($queryID !== null) $this->queryID = $queryID;

		if (@mysql_data_seek($this->queryID, $offset) === false) {
			throw new DatabaseException("Cannot seek result to offset " . $offset, $this);
		}
	}

	/**
	 * Returns the mysql version.
	 * 
	 * @return 	string
	 */
	public function getVersion() {
		$result = $this->getFirstRow('SELECT VERSION() AS version');
		if (isset($result['version'])) {
			return $result['version'];
		}
		
		return parent::getVersion();
	}
	
	/**
	 * @see Database::escapeString()
	 */
	public function escapeString($string) {
		return @mysql_real_escape_string($string, $this->linkID);
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
			if ($offset > 0) $query .= " LIMIT " . $offset . ", " . $limit;
			else $query .= " LIMIT " . $limit;
		}

		return $query;
	}
	
	/**
	 * Returns all existing tablenames.  
	 * 
	 * @return 	array 		$existingTables
	 */
	public function getTableNames($database = '') {
		if (empty($database)) $database = $this->database;
		$existingTables = array();
		$sql = "SHOW TABLES FROM `".$database."`";
		$result = $this->sendQuery($sql);
		while ($row = $this->fetchArray($result, self::SQL_NUM)) {
			$existingTables[] = $row[0];
		}
		return $existingTables;
	}
	
	/**
	 * Returns the columns of a table.
	 * 
	 * @param	string	$tableName
	 * @return	array	$columns
	 */
	public function getColumns($tableName) {
		$columns = array();
		$sql = "SHOW COLUMNS FROM `".$tableName."`";
		$result = $this->sendQuery($sql);
		while ($row = $this->fetchArray($result)) {
      	 		$columns[] = $row['Field'];
   		}
   		return $columns;
	}
	
	/**
	 * Returns the indices of a table.
	 * 
	 * @param	string	$tableName
	 * @return	array	$indices
	 */
	public function getIndices($tableName, $namesOnly = false) {
		$indices = array();
		$sql = "SHOW INDEX FROM `".$tableName."`";
		$result = $this->sendQuery($sql);
		while ($row = $this->fetchArray($result)) {
      	 		$indices[] = $row;
   		}
   		
   		if ($namesOnly) {
   			foreach ($indices as $key => $index) {
				// store only index names
				$indices[$key] = $index['Key_name'];
			}
   		}
   		
   		return $indices;
	}
	
	/**
	 * Returns table informations.  
	 * 
	 * @return 	array 		$table
	 */
	public function getTableStatus() {
		$tables = array();
		$sql = "SHOW TABLE STATUS FROM `".$this->database."`";
		$result = $this->sendQuery($sql);
		while ($row = $this->fetchArray($result)) {
      	 		$tables[] = $row;
   		}
		return $tables;
	}
	
	/**
	 * Returns true, if this database type is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported() {
		return function_exists('mysql_connect');
	}
}
?>