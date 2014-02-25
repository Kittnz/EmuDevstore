<?php
/**
 * The query parser has the capability to take many sql queries and to execute them one by one.
 * TODO: test the parse function with utf-8 encoded strings
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class QueryParser {
	
	/**
	 * Splits the queries, sends them one by one and filters the data which should be logged.
	 *
	 * @param 	array 		$queries
	 * @param 	integer		$packageID
	 * @return 	array 		$tableLogData
	 */
	public static function sendQueries($queries, $packageID = 0) {
		$alterStatement = $columnName = $columns = array();
		
		$queries = self::deleteComments($queries);
		// Put all queries in the $queries array. 
		$queryArray = array();
		$char = '';
		$lastChar = '';
		$inString = false;
		$stringOpen = '';
		for ($i = 0; $i < strlen($queries); $i++) {
			$char = $queries[$i];

			// if delimiter found, add the parsed part to the returned array
			if ($char == ';' && !$inString) {
				$queryArray[] = substr($queries, 0, $i);
				$queries = substr($queries, $i + 1);
				$i = 0;
				$lastChar = '';
			}
			
			else if (!$inString) {
				if ($char == "'" || $char == '"') {
					$inString = true;
					$stringOpen = $char;
				}
			}
			else if ($inString) {
				if ($lastChar != '\\' && $char == $stringOpen) {
					$inString = false;	
				} 
			}
			$lastChar = $char;
		}
		if ($queries != '') {
			$queryArray[] = $queries;
		}
		unset($queries);

		// array for setup log data.
		$tableLogData = array();
		
		// loop through the queries and handle them one by one
		$c = count($queryArray);
		for ($i = 0; $i < $c; $i++) {
			$queryArray[$i] = StringUtil::trim($queryArray[$i]);

			// execute query
			if (substr($queryArray[$i], 0, 1) == '@') {
				// ignore errors
				$sql = substr($queryArray[$i], 1);
				try {
					WCF::getDB()->sendQuery($sql);
				}
				catch (DatabaseException $e) {}
			}
			else {
				WCF::getDB()->sendQuery($queryArray[$i]);
			}
			
			// installation/update is running: log now
			if ($packageID > 0) {
				self::checkLogData($queryArray[$i], $packageID);
			}
			// setup is running: log in WCFSetup.class
			else if ($tableName = self::getSetupTablesLog($queryArray[$i])) {
				$tableLogData[] = $tableName;
			}
		}
		return $tableLogData;
	}
	
	/**
	 * Deletes the comments from an sql file. 
	 *
	 * @param 	string 	$queries
	 * @return	string	$queries
	 */
	public static function deleteComments($queries) {
//		$queries = preg_replace("~('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(?:(?:--|#).*|/\*(?:.|[\r\n])*?\*/)~", '$1', $queries);
		$queries = preg_replace("~('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|(?:(?:--|#)[^\n]*|/\*.*?\*/)~s", '$1', $queries);

		// strip whitespace
		return StringUtil::trim($queries);
	}
	
	/**
	 * This function reads the logable data out of the statement and 
	 * calls the log function if so.  
	 * 
	 * @param	string		$sql		one sql statement
	 * @param	integer		$packageID	packageID of installing/updating package
	 */
	protected static function checkLogData($sql, $packageID) {
		// store dropped table
		if (preg_match("%DROP\s+TABLE(?:\s+IF\s+EXISTS)?\s+([^;]+)%i", $sql, $matches)) {
			preg_match_all("%`?(\w+)`?,?%", $matches[1], $matches);
			$tables = array();
			foreach ($matches[1] as $tableName) {
				// store for immediate logging
				$tables[] = $tableName;
			}
			self::updateLog('dropTables', $tables, $packageID);
		}
		// store dropped index
		else if (preg_match("%DROP\s+INDEX\s+`?(\w+)`?\s+ON\s+`?(\w+)`?%i", $sql, $matches)) {
			self::updateLog('dropIndex', array('tableName' => $matches[2], 'indexName' => $matches[1]), $packageID);
		}
		// store alter statement
		else if (preg_match("%ALTER\s+(?:IGNORE\s+)?TABLE\s+`?(\w+)`?\s+(.*)%si", $sql, $alterStatement)) {
			// get alter definitions
			$definitions = self::splitAlterStatement($sql);
			foreach ($definitions as $definition) {
				$matches = array();
				// store dropped columns
				if (preg_match("%DROP(?:\s+COLUMN)?(?!(?:\s+(?:INDEX|KEY)))\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('dropColumn', array('tableName' => $alterStatement[1], 'columnName' => $matches[1]), $packageID);
				}
				// store renamed table
				else if (preg_match("%RENAME(?:\s+TO)?\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('renameTables', array(array('tableName' => $alterStatement[1], 'newTableName' => $matches[1])), $packageID);
				}
				// store added index
				else if (preg_match("%ADD(?:\s+(?:INDEX|KEY))\s+(?:`?\w+`?\s*)?`?\(((\w+(,\s*)?)+)\)`?%i", $definition, $matches)) {
					$columnName = preg_replace("/^(\w+).*$/", "$1", $matches[1]);
					$indexName = $matches[1];
					// find key_name given by mysql
					$result = WCF::getDB()->sendQuery("SHOW INDEX FROM `".$alterStatement[1]."`");
					while ($row = WCF::getDB()->fetchArray($result)) {
						if ($row['Column_name'] == $columnName) {
							$indexName = $row['Key_name'];
						}
					}
					self::updateLog('addIndex', array('tableName' => $alterStatement[1], 'indexName' => $indexName), $packageID);
				}
				// store added primary key
				else if (preg_match("%ADD(?:\s+CONSTRAINT(?:\s+`?\w+`?)?)?\s+PRIMARY\s+KEY%i", $definition)) {
					self::updateLog('addIndex', array('tableName' => $alterStatement[1], 'indexName' => 'PRIMARY'), $packageID);
				}
				// store added unique key
				else if (preg_match("%ADD(?:\s+CONSTRAINT(?:\s+`?\w+`?)?)?UNIQUE(?:\s+INDEX)?\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('addIndex', array('tableName' => $alterStatement[1], 'indexName' => $matches[1]), $packageID);
				}
				// store added fulltext|spatial index
				else if (preg_match("%ADD(?:\s+(?:FULLTEXT|SPATIAL))(?:\s+INDEX)?\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('addIndex', array('tableName' => $alterStatement[1], 'indexName' => $matches[1]), $packageID);
				}
				// store added columns
				else if (preg_match("%ADD(?:\s+COLUMN)?\s+\((.*)%si", $definition, $matches)) {
					// matches: column_name data_type [(length|value)].*,
					// TODO: backticks here
					preg_match_all("%`?(\w+)`?\s+\w+(?:\s*\([\w,]+\))?[^,]+%i", $matches[1], $addColumns);
					$columns = array();
					foreach ($addColumns[1] as $key => $columnName) {
						$columns[] = array('tableName' => $alterStatement[1], 'columnName' => $columnName);
					}
					self::updateLog('addColumns', $columns, $packageID);	
				}
				// store added column
				else if (preg_match("%ADD(?:\s+COLUMN)?\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('addColumns', array(array('tableName' => $alterStatement[1], 'columnName' => $matches[1])), $packageID);
				}
				// store dropped index
				else if (preg_match("%DROP\s+INDEX\s+`?(\w+)`?%i", $definition, $matches)) {
					self::updateLog('dropIndex', array('tableName' => $alterStatement[1], 'indexName' => $matches[1]), $packageID);
				}
				// store changed columns (renamed)
				else if (preg_match("%CHANGE(?:\s+COLUMN)?\s+`?(\w+)`?\s+`?(\w+)`?%i", $definition, $matches)) {
				 	if ($matches[1] != $matches[2]) {
				 		self::updateLog('changedColumn', array('tableName' => $alterStatement[1], 'columnName' => $matches[1], 'newColumnName' => $matches[2]), $packageID);
					}
				}
			}
		}
		// store created table
		else if (preg_match("%CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?(.*)%i", $sql, $matches)) {
			self::updateLog('createTable', $matches[1], $packageID);
		}
		// store created index
		else if (preg_match("%CREATE(?:\s+(?:UNIQUE|FULLTEXT|SPATIAL))?\s+INDEX\s+`?(\w+)`?(?:\s+USING\s+(?:BTREE|HASH))?\s+ON\s+`?(\w+)`?\s+\((.*)\)%i", $sql, $matches)) {
			self::updateLog('addIndex', array('tableName' => $matches[2], 'indexName' => $matches[1]), $packageID);
		}
		// store renamed table
		else if (preg_match("%RENAME\s+TABLE\s+([^;]+)%i", $sql, $matches)) {
			// get multiple rename statements
			preg_match_all("%`?(\w+)`?\s+TO\s+`?(\w+)`?%", $matches[1], $namePairs);
			$tables = array();
			foreach ($namePairs[1] as $key => $tableName) {
				$tables[] = array('tableName' => $tableName, 'newTableName' => $namePairs[2][$key]);
			}
			self::updateLog('renameTables', $tables, $packageID);
		}
	}
	
	/**
	 * This function reads the tablename of a CREATE TABLE statement and returns the name.
	 * 
	 * @param	string 		$sql	one sql statement
	 * @return 	string		table name
	 */
	protected static function getSetupTablesLog($sql) {
		if (preg_match("%CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?(.*)%i", $sql, $matches)) {
			return $matches[1];
		}
		else return null;
	}
	
	/**
	 * Splits an alter statement into it's definitions. Returns just needed definitions.
	 * 
	 * @param	string		$alterStatement		sql alter statement
	 * @param	array		$useableKeywords	words which are used to detect a definition start
	 * @return	array		$definitions
	 */
	public static function splitAlterStatement($alterStatement, $useableKeywords = array()) {
		// TODO: ALTER is also a definition in an ALTER statement and table_options are also definitions.
		//	 check what happens if such a keyword appears in alter statement		
		$defintionStartKeywords = 'ADD|DROP|RENAME|CHANGE|MODIFY|DISABLE|ENABLE|ORDER|CONVERT|CHARACTER|DISCARD|IMPORT';
		
		// set keywords which should be used
		if (empty($useableKeywords)) $useableKeywords = array('ADD', 'DROP', 'RENAME', 'CHANGE');
		
		// split definitions
		$splittedDefinitions = preg_split("%(".$defintionStartKeywords.")\s+%", $alterStatement, -1, PREG_SPLIT_DELIM_CAPTURE);

		// get only those defintions which should be handled
		$definitions = array();
		foreach ($splittedDefinitions as $key => $definition) {
			if (in_array(strtoupper($definition), $useableKeywords)) {
				$definitions[] =  StringUtil::toUpperCase($definition)." ".$splittedDefinitions[$key+1];
			}
		}
		return $definitions;
	}
	
	/**
	 * Update the log-table for INSERT, ALTER or DROP TABLE, CREATE or DROP INDEX, RENAME TABLE.
	 * 
	 * @param	string		$type
	 * @param 	array		$logData	data to log			
	 * @param	integer		$packageID	packageID of the installing/updating package
	 */
	protected static function updateLog($type, $logData, $packageID) {
		switch ($type) {
			// delete dropped tables from log
			case 'dropTables':
				foreach ($logData as $tableName) {
					WCF::getDB()->sendQuery("DELETE	FROM	wcf".WCF_N."_package_installation_sql_log
					 			 WHERE		sqlTable = '".$tableName."'");
				}
				break;
			
			// log tables which where created 
			case 'createTable':
				self::insertLog($packageID, $logData);
				break;
			
			// delete columns from log file which where dropped
			case 'dropColumn':
				WCF::getDB()->sendQuery("DELETE	FROM	wcf".WCF_N."_package_installation_sql_log
						 	WHERE		sqlTable = '".$logData['tableName']."'
						 	AND		sqlColumn = '".$logData['columnName']."'");
				break;
			// log columns which where added
			
			case 'addColumns':
				foreach ($logData as $column) {
					self::insertLog($packageID, $column['tableName'], $column['columnName']);
				}
				break;

			// update log records where indizes where dropped
			case 'dropIndex':
				WCF::getDB()->sendQuery("DELETE FROM	wcf".WCF_N."_package_installation_sql_log
							 WHERE		sqlTable = '".$logData['tableName']."'
							 AND		sqlIndex = '".$logData['indexName']."'");
				break;
				
			// update log records where the table was renamed
			case 'renameTables':
				foreach ($logData as $table) {
					WCF::getDB()->sendQuery("UPDATE	wcf".WCF_N."_package_installation_sql_log
								 SET	sqlTable = '".$table['newTableName']."'
								 WHERE	sqlTable = '".$table['tableName']."'");
				}
				break;
				
			// log added indizes
			case 'addIndex':
				self::insertLog($packageID, $logData['tableName'], '', $logData['indexName']);
				break;
				
			// log changed (renamed) columns	
			case 'changedColumn':
				WCF::getDB()->sendQuery("UPDATE	wcf".WCF_N."_package_installation_sql_log
							 SET	sqlColumn = '".$logData['newColumnName']."'
							 WHERE	sqlColumn = '".$logData['columnName']."'
							 AND	sqlTable = '".$logData['tableName']."'");
				break;
		}	
	}
	
	/**
	 * Logs the installed tables, columns and indices.
	 * 
	 * @param 	integer		$packageID	packageID of the installing/updating package
	 * @param 	string 		$sqlTable	tablename of the table to log
	 * @param 	string 		$sqlColumn	columnname of the added column   
	 * @param 	string 		$sqlIndex	indexname of the added index
	 */
	protected static function insertLog($packageID, $sqlTable, $sqlColumn = '', $sqlIndex = '') {
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_package_installation_sql_log
	 					(packageID, sqlTable, sqlColumn, sqlIndex)
			VALUES			(".$packageID.", '".$sqlTable."', '".$sqlColumn."', '".$sqlIndex."')";
		WCF::getDB()->sendQuery($sql);
	}
}
?>