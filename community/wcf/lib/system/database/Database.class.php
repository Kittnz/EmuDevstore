<?php
/**
 * This is an abstract implementation of a database access class.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
abstract class Database {
	protected	$queryID;
	protected	$linkID;
	protected	$host;
	protected	$user;
	protected	$password;
	protected	$database;
	protected	$usePConnect;
	protected 	$shutdownQueries 		= array();
	protected 	$useShutdownQueries 		= true;
	protected 	$charset 			= '';
	protected	$queryCount			= 0;
	
	const 		SQL_ASSOC			= 1;
	const 		SQL_NUM				= 2;
	const 		SQL_BOTH			= 3;
	public static	$dbCharsets = array(
		'UTF-8' => 'utf8',
		'ISO-8859-1' => 'latin1',
		'ISO-8859-2' => 'latin2',
		'ISO-8859-7' => 'greek',
		'ISO-8859-8' => 'hebrew',
		'ISO-8859-9' => 'latin5',
		'SJIS' => 'sjis',
		'EUC-JP' => 'ujis',
		'BIG-5' => 'big5',
		'EUC-CN' => 'gb2312',
		'CP936' => 'gbk',
		'KOI8-R' => 'koi8r',
		'Windows-1251' => 'cp1251',
		'EUC-KR' => 'euckr'
	);
	
	protected abstract function connect();
	protected abstract function selectDatabase();
	public abstract function sendQuery($query, $limit = 0, $offset = 0);
	public abstract function fetchArray($queryID = null, $type = null);
	public abstract function countRows($queryID = null);
	public abstract function getAffectedRows();
	public abstract function getInsertID($table = '', $field = '');
	public abstract function seekResult($queryID = null, $offset);
	public abstract function createDatabase();
	public abstract function getTableNames($database = '');
	public abstract function getTableStatus();
	public abstract function handleLimitParameter($query, $limit = 0, $offset = 0);
	//public abstract static function isSupported();
	
	/**
	 * Creates a Dabatase Object.
	 *  
	 * @param	string		$host 		SQL database server host address
	 * @param	string		$user 		SQL database server username
	 * @param	string		$password 	SQL database server password
	 * @param	string		$database 	SQL database server database name
	 * @param	string		$charset	charset for the db connection
	 * @param	boolean		$usePConnect 	indicates whether we use persistent connections to database
	 * @param	boolean		$autoSelect 	indicates whether we select the given database automatically
	 */
	public function __construct($host, $user, $password, $database, $charset = 'utf8', $usePConnect = false, $autoSelect = true) {
		$this->host 		= $host;
		$this->user 		= $user;
		$this->password 	= $password;
		$this->database		= $database;
		$this->usePConnect	= $usePConnect;
		$this->charset 		= $charset;
		
		$this->connect();

		if ($autoSelect === true) {
			$this->selectDatabase();
		}
		else {
			$this->createDatabase();
			$this->selectDatabase();
		}
	}

	/**
	 * Returns the number of the last error.
	 * 
	 * @return integer
	 */
	public function getErrorNumber() {
		return 0;
	}

	/**
	 * Returns the description of the last error.
	 * 
	 * @return string
	 */
	public function getErrorDesc() {
		return '';
	}

	/**
	 * Sends a database query to SQL server and returns first row from result.
	 *
	 * @param	string		$query 		a database query
	 * @param	integer		$type 		fetch type
	 * @param	integer		$limit
 	 * @param 	integer		$offset
	 * @return 	array				first row from result
	 */
	public function getFirstRow($query, $type = null, $limit = 1, $offset = 0) {
		$limit = (preg_match('/LIMIT\s+\d/i', $query) ? 0 : $limit);
		$this->sendQuery($query, $limit, $offset);

		$row = $this->fetchArray($this->queryID, $type);

		return $row;
	}

	/**
	 * Registers an UPDATE SQL Statement for executing in shutdown function.
	 *
	 * @param	string		$query 		an UPDATE SQL Statement
	 * @param	integer		$key	 	query key in stack
	 */
	public function registerShutdownUpdate($query, $key = null) {
		if ($this->useShutdownQueries) {
			if ($key === null) {
				$this->shutdownQueries[] = $query;
			}
			else {
				$this->shutdownQueries[$key] = $query;
			}
		}
		else {
			$this->sendQuery($query);
		}
	}
	
	/**
	 * Executes the registered shutdown queries.
	 */
	public function sendShutdownUpdates() {
		foreach ($this->shutdownQueries as $update) {
			$this->sendUnbufferedQuery($update);
		}
	}
	
	/**
	 * Gets the current database type.
	 *
	 * @return 	string
	 */
	public function getDBType() {
		return get_class($this);
	}

	/**
	 * Escapes a string for use in sql query.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function escapeString($string) {
		return addslashes($string);
	}
	
	/**
	 * Gets the sql version.
	 *
	 * @return 	string
	 */
	public function getVersion() {
		return 'unknown';
	}
	
	/**
	 * Executes the given sql query and returns the result rows as a multi-dimensional array.
	 * 
	 * @param	string		$sql		sql query
	 * @return	array				result
	 */
	public function getResultList($sql) {
		$resultList = array();
		$result = $this->sendQuery($sql);
		while ($row = $this->fetchArray($result)) {
			$resultList[] = $row;
		}
		
		return $resultList;
	}
	
	/**
	 * Gets the database name.
	 *
	 * @return 	string
	 */
	public function getDatabaseName() {
		return $this->database;
	}
	
	/**
	 * Send an sql query, without fetching and buffering the result rows.
	 * 
	 * @param 	integer		$limit
	 * @param	integer		$offset
	 * @return 	integer		id of the query result
	 */
	public function sendUnbufferedQuery($query, $limit = 0, $offset = 0) {
		return $this->sendQuery($query);
	}
	
	/**
	 * Gets the charset of the database connection.
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * Returns the amount of executed sql queries.
	 *
	 * @return	integer
	 */
	public function getQueryCount() {
		return $this->queryCount;
	}
}
?>