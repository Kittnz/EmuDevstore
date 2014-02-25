<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/database/Database.class.php');
	require_once(WCF_DIR.'lib/system/database/DatabaseException.class.php');
}

/**
 * This is the database implementation for PostgreSQL.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class PostgreSQLDatabase extends Database {
	/**
	 * Connects to PostgreSQL Server
	 */
	protected function connect() {
		// get port
		$port = 5432; // default PostgreSQL port
		$host = $this->host;
		if (preg_match('/^(.+?):(\d+)$/', $host, $match)) {
			$host = $match[1];
			$port = $match[2];
		}
		
		// connect
		$connectionString = "host=".$host." port=".$port." dbname=".$this->database." user=".$this->user." password=".$this->password;
		if ($this->usePConnect) {
			$this->linkID = @pg_pconnect($connectionString);
		}
		else {
			$this->linkID = @pg_connect($connectionString);
		}
		
		if ($this->linkID === false) {
			throw new DatabaseException("Connecting to PostgreSQL server '".$this->host."' failed.", $this);
		}
	}

	/**
	 * Does nothing.
	 */
	protected function selectDatabase() {}

	/**
	 * Does nothing.
	 */
	public function createDatabase() {}

	/**
	 * Returns PostgreSQL error description for last error.
	 *
	 * @return 	string		PostgreSQL error description
	 */
	public function getErrorDesc() {
		return @pg_last_error($this->linkID);
	}

	/**
	 * Sends a database query to PostgreSQL server.
	 *
	 * @param	string		$query 		a database query
	 * @param	integer		$limit 		limit
	 * @param	integer		$offset	 	offset
	 * @return 	integer				id of the query result
	 */
	public function sendQuery($query, $limit = 0, $offset = 0) {
		$query = $this->handleLimitParameter($query, $limit, $offset);

		$this->queryCount++;
		$this->queryID = @pg_query($this->linkID, $query);
		if ($this->queryID === false) {
			throw new DatabaseException("Invalid SQL:" . $query, $this);
		}
		
		return $this->queryID;
	}

	/**
	 * Gets a row from PostgreSQL database query result.
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

		$row = @pg_fetch_array($this->queryID, null, $type);
		
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
		
		$rowCount = @pg_num_rows($this->queryID);
		if ($rowCount === -1) {
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
		$rowCount = @pg_affected_rows($this->queryID);
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
		$tempQueryID = $this->queryID;
		$row = $this->getFirstRow("SELECT currval('" . $table . "_" . $field . "_seq') AS insert_id");
		$this->queryID = $tempQueryID;

		return ($row) ? $row['insert_id'] : false;
	}

	/**
	 * Moves the internal row pointer of the result associated with the specified result identifier to point to the specified row number.
	 *
	 * @param	integer		$queryID	
	 * @param	integer		$offset		specified row number
	 */
	public function seekResult($queryID = null, $offset) {
		if ($queryID !== null) $this->queryID = $queryID;

		if (@pg_result_seek($this->queryID, $offset) === false) {
			throw new DatabaseException("Cannot seek result to offset " . $offset, $this);
		}
	}

	/**
	 * Returns the PostgreSQL version.
	 * 
	 * @return 	string
	 */
	public function getVersion() {
		$version = pg_parameter_status($this->linkID, 'server_version');
		if (!empty($version)) return $version;
		
		return parent::getVersion();
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
		return function_exists('pg_connect');
	}
}
?>