<?php
// wcf imports
require_once(WCF_DIR.'lib/system/database/QueryParser.class.php');
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');

/**
 * This PIP executes the delivered sql file.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class SqlPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'sql';
	public $tableName = 'package_installation_sql_log';
	
	protected $existingTables = array();
	protected $externalTables = array();
	protected $loggedTables = array();
	protected $mandatoryPackageIDs = array();
	protected $overrideTables = array();
	protected $checkedTables = array();
	protected $keepAll = false;
	protected $sqlStr = '';
	
	/** 
	 * Installs sql tables, columns or indeces. 
	 */
	public function install() {
		parent::install();

		// extract sql file from archive
		if ($this->sqlStr = $this->readSQL($this->installation)) {
			$standalonePackage = $this->installation->getPackage();
			if ($standalonePackage->getParentPackageID()) {
				// package is a plugin; get parent package
				$standalonePackage = $standalonePackage->getParentPackage();
			}
			
			if ($standalonePackage->isStandalone() == 1) {
				// package is standalone
				$packageAbbr = $standalonePackage->getAbbreviation();
				$tablePrefix = WCF_N.'_'.$standalonePackage->getInstanceNo().'_';
				
				// Replace the variable xyz1_1 with $tablePrefix in the table names.  
				$this->sqlStr = str_replace($packageAbbr.'1_1_', $packageAbbr.$tablePrefix, $this->sqlStr);
			}
			
			// replace wcf1_  with the actual WCF_N value 
			$this->sqlStr = str_replace("wcf1_", "wcf".WCF_N."_", $this->sqlStr);
			
			// replace charset configuration
			if (Database::$dbCharsets[CHARSET] != 'utf8') {
				$this->sqlStr = str_replace('DEFAULT CHARSET=utf8', 'DEFAULT CHARSET='.Database::$dbCharsets[CHARSET], $this->sqlStr);
			}
			
			// get dontAskAgain value from session 
			$handleType = WCF::getSession()->getVar('overrideTablesUserDescission');
			$isSetInSession = false;
						
			if (empty($handleType)) $handleType = 'askAgain';
			else $isSetInSession = true;
			
			// check if user decided to not show him again conflicted tables
			if (isset($_POST['dontAskAgainOverride'])) {
				$handleType = $_POST['dontAskAgainOverride'] ? 'dontAskAgainOverride' : 'askAgain';
			}
			elseif (isset($_POST['dontAskAgainKeep'])) {
				$handleType = $_POST['dontAskAgainKeep'] ? 'dontAskAgainKeep' : 'askAgain';
			}
			if ($handleType == 'dontAskAgainKeep')  $this->keepAll = true;

			// store in session
			if (!$isSetInSession && $handleType != 'askAgain') {
				WCF::getSession()->register('overrideTablesUserDescission', $handleType);
				WCF::getSession()->update();		
				Session::resetSessions();
			}
			
			// check and edit (if a table should not be overwritten) sql string 
			$this->checkSQL($this->installation->getPackageID(), $this->installation->getAction());
			
			// display overrides template
			if ($handleType == 'askAgain' && !isset($_POST['overrideTables']) && count($this->overrideTables) > 0) {
				
				// rearrange array. store each table just one time
				foreach ($this->overrideTables as $table) {
					$tmp[$table['tableName']][] = $table['overrideType'];
				}
				$this->overrideTables = array();
				
				// make an indexed array for the javascript funktion selectAll
				foreach ($tmp as $tableName => $table) {
					$this->overrideTables[] = array('tableName' => $tableName, 'overrideTypes' => $table);
				}

				WCF::getTPL()->assign('tables', $this->overrideTables);
				WCF::getTPL()->display('packageInstallationCheckOverrideTables');
				exit;
			}

			// execute queries
			QueryParser::sendQueries($this->sqlStr, $this->installation->getPackageID());
		}		
	}
	
	/** 
	 * Deletes the sql tables or columns which where installed by the package.
	 */
	public function uninstall() {
		// get logged sql tables/columns
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		$entries = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$entries[] = $row;
		}
		
		// get all tablenames from database
		$existingTableNames = WCF::getDB()->getTablenames();
		
		// delete or alter tables
		foreach ($entries as $entry) {
			// don't alter table if it should be dropped 
			if (!empty($entry['sqlColumn']) || !empty($entry['sqlIndex'])) {
				$isDropped = false;
				foreach ($entries as $entry_) {
					if ($entry['sqlTable'] == $entry_['sqlTable'] && empty($entry_['sqlColumn']) && empty($entry_['sqlIndex'])) {
						$isDropped = true;
					}
				}
				if ($isDropped) continue;
			} 
			// drop table
			if (!empty($entry['sqlTable']) && empty($entry['sqlColumn']) && empty($entry['sqlIndex'])) {
				WCF::getDB()->sendQuery("DROP TABLE IF EXISTS ".$entry['sqlTable']);
			}
			// drop column
			elseif (in_array($entry['sqlTable'], $existingTableNames) && !empty($entry['sqlColumn']) && empty($entry['sqlIndex'])) {
				WCF::getDB()->sendQuery("ALTER TABLE 	`".$entry['sqlTable']."` 
							 DROP COLUMN	`".$entry['sqlColumn'])."`";
			}
			// drop index
			elseif (in_array($entry['sqlTable'], $existingTableNames) && empty($entry['sqlColumn']) && !empty($entry['sqlIndex'])) {
				WCF::getDB()->sendQuery("ALTER TABLE 	`".$entry['sqlTable']."` 
							 DROP INDEX	`".$entry['sqlIndex'])."`";
			}
		}
		// delete from log table
		parent::uninstall();
	}
	
	/**
	* Extracts and returns the sql file.
	* If the specified sql file was not found,
	* an error message is thrown.
	*
	* @return 	string 				sql
	*/
	protected function readSQL() {
		$instructions = $this->installation->getInstructions();
		
		if (!isset($instructions['sql'])) {
			return false; 
		}
		
		// search sql files in package archive
		if (($fileindex = $this->installation->getArchive()->getTar()->getIndexByFilename($instructions['sql'])) === false) {
			throw new SystemException("SQL file '".($instructions['sql'])."' not found.", 13016);
		}

		// extract sql file to string
		return $this->installation->getArchive()->getTar()->extractToString($fileindex); 
 	}	
	
	/**
	 * Checks a sql string.
	 * 
	 * @param 	integer 	$packageID
	 * @param 	string 		$action 
	 */
	protected function checkSQL($packageID, $action) {

		// checked tables should be overwritten 
		$this->checkedTables = isset($_POST['checkedTables']) ? ArrayUtil::trim($_POST['checkedTables']) : array();
		
		// delete comments
		$this->sqlStr = QueryParser::deleteComments($this->sqlStr);
				
		/* 
		Before installing,updating or alter any table, it will be checked if any statement 
		from the sql file is illegal. 
		Not the  MySQL Syntax will be checked but the woltlab package philosophy.
		No table will be installed,  altered or dropped until all statements are proved.
		*/
		
		// get existing tables from database
		$this->existingTables = WCF::getDB()->getTableNames();
		
		// get logged and external tables 
		$this->getTableConditions($packageID);
				
		// get IDs from packages which got logged tables and are in the same package environment 		
		$this->getMandatoryPackageIDs($packageID);
				
		/* 
		 The following "preg_match-parts" are checking if the actual package got the rights 
		 to install or update the tables which are in the sql file (i.e. install.sql).
		 An exception will be thrown if illegal operations appear. 
		 If overwriting is allowed (external tables) the "overwrite-template" will be shown 
		 and the user can decide which tables should be overwritten or not.
		*/
		
		// ckeck "DATABASE"-statements. don't allow any DATABASE manipulation
		$matches = array();
		if (preg_match_all("%(DROP|ALTER|CREATE)\s+DATABASE%i", $this->sqlStr, $matches)) {
			throw new SystemException("Illegal statement '".$matches[1]." DATABASE ...'.", 13017);
		}
		
		// check other statements
		$this->checkDropTables($packageID, $action);
		$this->checkAlterTables($packageID, $action);
		$this->checkCreateTables($packageID, $action);
		$this->checkRenameTables($packageID, $action);
		$this->checkCreateIndeces($packageID, $action);
		$this->checkDropIndeces($packageID, $action);
		
	}
	
	/**
	 * Get each table from wcf system and check which of the exisiting tables 
	 * is external and which is logged.
	 * 
	 * @param	integer		$packageID
	 */
	protected function getTableConditions($packageID) {
		// get logged tables 
		$query = "SELECT	packageID, sqlTable
			  FROM		wcf".WCF_N."_package_installation_sql_log
			  WHERE		sqlTable IN ('".implode("', '", $this->existingTables)."') 
			  AND		sqlColumn = '' 
			  AND		sqlIndex = ''"; 
		$result = WCF::getDB()->sendQuery($query);
		$loggedTables  = array();
		$tmpLogged = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->loggedTables[$row['sqlTable']] = $row['packageID'];
			$tmpLogged[] = $row['sqlTable'];
		}
		
		// get external tables 
		$this->externalTables = array_diff($this->existingTables, $tmpLogged);
	}
	
	/**
	 * Get those packageIDs from mandatory packages which got logged tables.   
	 * 
	 * @param	integer		$packageID
	 */
	protected function getMandatorypackageIDs($packageID) {
		// get mandatory packageIDs from packages with logged tables
		$query = "SELECT DISTINCT	packageID
			  FROM			wcf".WCF_N."_package_installation_sql_log
			  WHERE			sqlColumn = ''	
			  AND			packageID IN (
				  		SELECT	dependency
				  		FROM	wcf".WCF_N."_package_dependency
				  		WHERE	packageID = ".$packageID.")";
		$result = WCF::getDB()->sendQuery($query);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->mandatoryPackageIDs[] = $row['packageID']; 
		} 
	}
	
	/**
	 * Check the sql string for illegal drop table statements. i.e. an installation
	 * can't drop existing tables.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	protected function checkDropTables($packageID, $action) {
		if (preg_match_all("%DROP\s+TABLE(?:\s+IF\s+EXISTS)?\s+`?(\w+)`?(?(?=;)|([^;]+))%i", $this->sqlStr, $dropTableStatements)) {
			foreach ($dropTableStatements[1] as $key => $requiredTableName) {
				// detect other tablenames
				preg_match_all("%(?:,\s*)`?(\w+)`?%", $dropTableStatements[2][$key], $dropTableNames);
				
				// add the required tablename
				$dropTableNames[1][] = $requiredTableName;
				
				// check each tablename 
				foreach ($dropTableNames[1] as $key_ => $tableName) {
					// default values
					$isExternalTable = $isLoggedTable = false;
					$existingTablePackageID = 0;
						
					// get values
					$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);

					// table doesn't exist
					if ($action != 'update' && !in_array($tableName, $this->existingTables)) {
						if (!preg_match("%IF\s+EXISTS%", $dropTableStatements[0][$key])) {
							throw new SystemException("Illegal statement '".$dropTableStatements[0][$key]."'. '".$tableName."' doesn't exists.", 13020);
						}
					}
					// external table. show override template. 
					elseif ($isExternalTable && empty($this->checkedTables) && !$this->keepAll) {
						$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'dropTable');
					}
					// logged table. an installation can't drop logged tables
					elseif($action == 'install' && $isLoggedTable) {
						throw new SystemException("Illegal statement '".$dropTableStatements[0][$key]."'. An installation can't drop logged tables.", 13020);
					}
					// logged table. an update can't drop tables from other package than the updating one.
					elseif ($action == 'update' && $isLoggedTable && $existingTablePackageID != $packageID) {
						throw new SystemException("Illegal statement '".$dropTableStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected ID: '".$existingTablePackageID."'", 13020);
					}
					// handle a repeatedly executed update
					// or external table. table is not checked. erase this sql statement from $this->sqlStr
					elseif (($action == 'update' && !in_array($tableName, $this->existingTables)) || (!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables)) || $this->keepAll) {
						// the DROP statement is used for only one table. erase whole DROP statement
						if (count($dropTableNames[1]) == 1) {
							$this->deleteStatement($dropTableStatements[0][$key]);
						}
						// the DROP statement is used for more than one table. delete this tablename from statement 
						else {
							$replaceStatement = preg_replace("%`?".$tableName."`?(,\s*)?%", "", $dropTableStatements[0][$key]);
							$this->replaceStatement($dropTableStatements[0][$key], $replaceStatement);
						}
					}	
				}
			}
		}
	}
	
	/**
	 * Check the sql string for illegal alter table statements. i.e. an installation
	 * can only add things to tables or an table has to exist before an alter table statement could be executed.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	protected function checkAlterTables($packageID, $action) {
		// following SQL commands are only usable for ALTER statements from updating packages
		$reservedUpdateCommandsRegex = '[,\s]ALTER\s+|[,\s]CHANGE\s+|[,\s]MODIFY\s+|[,\s]DROP\s+|[,\s]RENAME\s+|([,\s]DISABLE\s+KEYS\s+)';
		
		if (preg_match_all("%ALTER(?:\s+IGNORE)?\s+TABLE\s+`?(\w+)`?\s+([^;]+);%i", $this->sqlStr, $alterStatements)) {
			foreach ($alterStatements[1] as $key => $tableName) {
				
				// replace strings. since this is only a check (no sql statments are executed here) 
				// and the original string isn't touched there is no need to reinsert the strings. 
				$alterStatements[2][$key] = preg_replace("~('[^'\\\\]*(?:(?:'(?=')|\\\\).[^'\\\\]*)*')~s", "''", $alterStatements[2][$key]);
				
				// default values
				$isExternalTable = $isLoggedTable = false;
				$existingTablePackageID = 0;
						
				// get values
				$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);
				
				// table doesn't exist
				if (!in_array($tableName, $this->existingTables)) {
					throw new SystemException("Illegal statement '".$alterStatements[0][$key]."'. Table '".$tableName."' doesn't exists in database.", 13019);
				}
				// external table. show overrides template 
				elseif ($isExternalTable && empty($this->checkedTables) && !$this->keepAll) {
					$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'alterTable');
				}
				// statement for "update-routine" found
				elseif ($isLoggedTable && preg_match("%(".$reservedUpdateCommandsRegex.")%i", $alterStatements[2][$key])) {
					// an installation can't use these statements
					if ($action == 'install')	{
						throw new SystemException("Illegal statement '".$alterStatements[0][$key]."'. An installation can only 'ADD' things to tables.", 13019);
					}
					// packageID is not correct
					elseif ($existingTablePackageID != $packageID) { 
						throw new SystemException("Illegal statement '".$alterStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected ID: '".$existingTablePackageID."'.", 13019);
					}
				}
				// statement with ADD. the existing table is not from the same package environment
				elseif ($isLoggedTable && $action == 'install' && !in_array($existingTablePackageID, $this->mandatoryPackageIDs)) {
					throw new SystemException("Illegal statement '".$alterStatements[0][$key]."'. An installion can only 'ADD' things to tables from the same package environment.", 13019);
				}
				// statement with ADD. the update has wrong packageID
				elseif ($action == 'update' && (!in_array($existingTablePackageID, $this->mandatoryPackageIDs) && $existingTablePackageID != $packageID)) {
					throw new SystemException("Illegal statement '".$alterStatements[0][$key]."'. An update can only 'ADD' things to tables from same package environment.", 13019);
				}
				// external table. table is not checked. erase this sql statement from $this->sqlStr
				elseif ((!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables)) || $this->keepAll) {
					$this->replaceStatement($alterStatements[0][$key], '');
				}
				
				// handle a repeatedly executed update 
				if ($action == 'update') {
					$this->handleAlterUpdate($alterStatements[0][$key], $alterStatements[2][$key], $tableName);
				}
			}
		}
	}
	
	/**
	 * Check the sql string for illegal create table statements. i.e. an installation can't create 
	 * existing logged tables.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	protected function checkCreateTables($packageID, $action) {
		if (preg_match_all("%CREATE(?:\s+TEMPORARY)?\s+TABLE(\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?([^;]+);%i", $this->sqlStr, $createStatements)) {
			foreach ($createStatements[2] as $key => $tableName) {
				
				// default values
				$isExternalTable = $isLoggedTable = false;
				$existingTablePackageID = 0;
						
				// get values.
				$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);

				// table exists already
				if (in_array($tableName, $this->existingTables)) {
					
					// no "IF NOT EXISTS" condition in statement and no "DROP TABLE" ahead the CREATE statement 
					if (!empty($createStatements[1][$key]) && !preg_match("%DROP\s+TABLE(\s+IF\s+EXISTS)?\s+`?".$tableName."`?.*CREATE\s+(TEMPORARY )?TABLE\s+`?".$tableName."`?%si", $this->sqlStr)) {
						throw new SystemException("Illegal statement '".$createStatements[0][$key]."'. If creating an existing table make sure you are dropping it before.", 13018);
					}
					// external table. show override template 
					elseif ($isExternalTable && empty($this->checkedTables) && !$this->keepAll) {
						$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'createTable');
					}
					// an installion can't create tables which exists already (excepting externals).
					elseif ($action == 'install' && $isLoggedTable) {
						throw new SystemException("Illegal statement '".$createStatements[0][$key]."'. An installion can't create a table which exists already.", 13018);
					}
					// an updating package can only create an existing table if packageID is correct.
					elseif ($action == 'update' && $isLoggedTable && $existingTablePackageID != $packageID) { 
						throw new SystemException("Illegal statement '".$createStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected package ID: '".$existingTablePackageID.".", 13018);
					}
					// external table. table is not checked. erase this sql statement from $this->sqlStr
					elseif ((!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables)) || $this->keepAll) {
						
						// delete all create statements for the "not-to-be-overwritten" table
						$this->deleteStatement($createStatements[0][$key]);
					}
				}
			}
		}
	}
	
	/**
	 * Check the sql string for illegal create index statements. i.e. an installation can only 
	 * create an index on tables from same package environment.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	protected function checkCreateIndeces($packageID, $action) {
		if (preg_match_all("%CREATE(?:\s+(?:UNIQUE|FULLTEXT|SPATIAL))\s+INDEX\s+`?(\w+)`?(?:\s+(?:USING\s+(?:BTREE|HASH)))?\s+ON\s+`?(\w+)`?[^;]+;%i", $this->sqlStr, $createIndexStatements)) {
			foreach ($createIndexStatements[2] as $key => $tableName) {
				// default values
				$isExternalTable = $isLoggedTable = false;
				$existingTablePackageID = 0;
						
				// get values.
				$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);
				
				// external table. show override template 
				if ($isExternalTable && empty($this->checkedTables) && !$this->keepAll) {
					$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'createIndex');
				}
				// external table. table is not checked. erase this sql statement from $this->sqlStr
				elseif ((!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables)) || $this->keepAll) {
					// delete all CREATE INDEX statements for the "not-to-be-overwritten" table
					$this->deleteStatement($createIndexStatements[0][$key]);
				}
				// an installion can only create indices on logged tables which are from the same package environment
				elseif ($action == 'install' && $isLoggedTable && !in_array($existingTablePackageID, $this->mandatoryPackageIDs)) {
					throw new SystemException("Illegal statement '".$createIndexStatements[0][$key]."'. An installion can't create an index on logged tables.", 13022);
				}
				// an updating package can only create indices if packageID is correct
				// advice: if in sqlStr "CREATE TABLE x" and later "CREATE INDEX ... ON x" appears, follwing check leads to a systemexception
				elseif ($action == 'update' && $isLoggedTable && $existingTablePackageID != $packageID) { 
					throw new SystemException("Illegal statement '".$createIndexStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected package ID: '".$existingTablePackageID.".", 13022);
				}
				
				// handle a repeatedly executed update (possible if an update wasn't finished)
				if ($action == 'update') {
					
					// get indices info
					$indices = WCF::getDB()->getIndices($tableName, true);
					
					// index exist already. delete statement
					if (in_array($createIndexStatements[1][$key], $indices)) {
						$this->deleteCreateIndexStatement($createIndexStatements[1][$key], $createIndexStatements[2][$key], $createIndexStatements[0][$key]);
					}	
				}
			}
		}
	}
	
	/**
	 * Check the sql string for illegal drop index statements. i.e. an installation can't drop 
	 * indices.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	protected function checkDropIndeces($packageID, $action) {
		// check DROP INDEX statement
		if (preg_match_all("%DROP\s+INDEX\s+`?(\w+)`?\s+ON\s+`?(\w+)`?;%i", $this->sqlStr, $dropIndexStatements)) {
			// loop through each "DROP INDEX" statement
			foreach ($dropIndexStatements[2] as $key => $tableName) {
				
				// default values
				$isExternalTable = $isLoggedTable = false;
				$existingTablePackageID = 0;
						
				// get values
				$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);
				
				// table doesn't exist
				if ($existingTablePackageID == 0) {
					$this->deleteStatement($dropIndexStatements[0][$key]);
				}
				// external table. show override template 
				elseif ($isExternalTable && empty($this->checkedTables)) {
					$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'dropIndex');
				}
				// external table. table is not checked. erase this sql statement from $this->sqlStr
				elseif ((!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables)) || $this->keepAll) {
					// delete all DROP INDEX statements for the "not-to-be-overwritten" table
					$this->deleteStatement($dropIndexStatements[0][$key]);
				}
				// an installion can't drop indices on logged tables.
				elseif ($action == 'install' && $isLoggedTable) {
					throw new SystemException("Illegal statement '".$dropIndexStatements[0][$key]."'. An installion can't drop an index from logged tables.", 13027);
				}
				// an updating package can only drop indices if packageID is correct.
				elseif ($action == 'update' && $isLoggedTable && $existingTablePackageID != $packageID) { 
					throw new SystemException("Illegal statement '".$dropIndexStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected package ID: '".$existingTablePackageID.".", 13027);
				}
				// handle a repeatedly executed update (possible if an update wasn't finished)
				elseif ($action == 'update') {
					$indices = WCF::getDB()->getIndices($tableName, true);
					
					// check if index exists
					if (!in_array($dropIndexStatements[1][$key], $indices)) {
						$this->deleteStatement($dropIndexStatements[0][$key]);
					}
				}
			}
		}
	}
	
	/**
	 * Check the sql string for illegal rename table statements. i.e. an installation can't  
	 * rename tables.
	 * 
	 * @param	integer		$packageID
	 * @param	string		$action
	 */
	 protected function checkRenameTables($packageID, $action) {
		if (preg_match_all("%RENAME\s+TABLE\s+([^;]+);%i", $this->sqlStr, $renameTableStatements)) {
			foreach ($renameTableStatements[1] as $key => $renamePairs) {
				
				// get multiple rename statements
				preg_match_all("%`?(\w+)`?\s+TO\s+`?(\w+)`?%i", $renamePairs, $pairs);
				foreach ($pairs[1] as $key_ => $tableName) {
				
					// default values
					$isLoggedTable = $isExternalTable = false;
					$existingTablePackageID = 0;
					
					// get values
					$this->getTableProperties($tableName, $isExternalTable, $isLoggedTable, $existingTablePackageID);
					
					// table doesn't exist
					if ($action != 'update' && !in_array($tableName, $this->existingTables)) {
						throw new SystemException("Illegal statement '".$renameTableStatements[0][$key]."'. '".$tableName."' doesn't exist.", 13021);
					}
					// handle a repeatedly executed update (possible if an update wasn't finished)
					// or external table. table is not checked. erase this sql statement from $this->sqlStr
					elseif (($action == 'update' || (!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables))) || $this->keepAll) {
						// update package failure
						if ($action == 'update' && !in_array($tableName, $this->existingTables) && !in_array($pairs[2][$key_], $this->existingTables)) {
							throw new SystemException("Illegal statement '".$renameTableStatements[0][$key]."'. ", 13021);
						}
						// update renamed table but wasn't finished and is executed again
						// or table shouldn't be renamed. delete this rename part
						if ((in_array($pairs[2][$key_], $this->existingTables) || (!empty($this->checkedTables) && $isExternalTable && !in_array($tableName, $this->checkedTables))) || $this->keepAll) {
							// only one table
							if (count($pairs[1]) == 1) {
								$this->deleteStatement($renameTableStatements[0][$key]);
							}
							// more than one table
							else {
								$cleanedTableNames = preg_replace("%`?".$tableName."`?\s+TO\s+`?\w+`?%i", "", $renameTableStatements[1][$key]);
								$replaceStatement = 'RENAME TABLE '.$cleanedTableNames.';';
								$this->replaceStatement($renameTableStatements[0][$key], $replaceStatement);
							}
						}
					}
					// external table. show override template 
					elseif ($isExternalTable && empty($this->checkedTables)) {
						$this->overrideTables[] = array('tableName' => $tableName, 'overrideType' => 'renameTable');
					}
					// an installion can't rename logged tables.
					elseif ($action == 'install' && $isLoggedTable) {
						throw new SystemException("Illegal statement '".$renameTableStatements[0][$key]."'. An installion can't rename a logged table.", 13021);
					}
					// an updating package can only rename tables if packageID is correct.
					elseif ($action == 'update' && $isLoggedTable && $existingTablePackageID != $packageID) { 
						throw new SystemException("Illegal statement '".$renameTableStatements[0][$key]."'. Wrong package ID '".$packageID."'. Expected package ID: '".$existingTablePackageID.".", 13021);
					}
				}
			}
		}
	}
	
	/**
	 * Set vars for deciding if sql statements are illegal. 
	 * 
	 * @param	string		$tableName
	 * @param	boolean		$isExternalTable
	 * @param	boolean		$isLoggedTable
	 * @param	integer		$existingTablePackageID
	 */
	protected function getTableProperties($tableName, &$isExternalTable, &$isLoggedTable, &$existingTablePackageID) {
		if (in_array($tableName, $this->externalTables)) {$isExternalTable = true;}
		if (array_key_exists($tableName, $this->loggedTables)) $isLoggedTable = true;
		if (isset($this->loggedTables[$tableName])) $existingTablePackageID =  $this->loggedTables[$tableName];
	}
	
	/**
	 * Deletes from alter statements those definitions (columns or indices) which 
	 * could lead to an exception. 
	 * 
	 * @param	string		$alterStatement
	 * @param	string		$alterDefinitions
	 * @param 	string		$tableName
	 */
	protected function handleAlterUpdate($alterStatement, $alterDefinitions, $tableName) {
		$definitions = QueryParser::splitAlterStatement($alterStatement);
		
		// get existing columns & indices
		$columns = WCF::getDB()->getColumns($tableName);
		$indices = WCF::getDB()->getIndices($tableName, true);
		
		// "checksum"		
		$definitionsCount = count($definitions);

		// check for predictable problems 
		foreach ($definitions as $key => $definition) {
			
			// check CHANGE COLUMN
			if (preg_match("%CHANGE(?:\s+COLUMN)?\s+`?(\w+)`?\s+`?(\w+)`?([^,]+),?%i", $definition, $changeColumn)) {
				// column is already renamed. 
				if (!in_array($changeColumn[1], $columns) && in_array($changeColumn[2], $columns)) {
					unset($definitions[$key]);
				}
			}
			// check ADD
			elseif (preg_match("%ADD%i", $definition)) {
				// check ADD COLUMN
				if (preg_match("%(?:\s+COLUMN)\s+\(?`?(\w+)`?,?%i", $definition, $addColumn)) {
					// column exists already
					if (in_array($addColumn[1], $columns)) {
						unset($definitions[$key]);
					}
				}
				// check ADD INDEX
				elseif (preg_match("%(?:INDEX|KEY)\s+`?(\w+)`?%i", $definition, $addIndex)) {
					// index exist already
					if (in_array($addIndex[1], $indices)) {
						unset($definitions[$key]);
					}	
				}
				// check ADD FULLTEXT|SPATIAL
				elseif (preg_match("%(?:FULLTEXT|SPATIAL)(?:\s+INDEX)?\s*`?(\w+)`?%i", $definition, $addFulltext)) {
					// index exist already
					if (in_array($addFulltext[1], $indices)) {
						unset($definitions[$key]);
					}
				}
				// check ADD PRIMARY KEY
				elseif (preg_match("%(?:\s+CONSTRAINT(?:\s+`?\w+`?)?)?\s*PRIMARY\s+KEY%i", $definition, $addPrimaryKey)) {
					// index exist already
					if (in_array('PRIMARY', $indices)) {
						unset($definitions[$key]);
					}
				}
				// add unique index
				elseif (preg_match("%(?:\s+CONSTRAINT(?:\s+`?\w+`?)?)?\s*UNIQUE(?:\s+INDEX)?\s`?(\w+)`?%i", $definition, $addUniqueKey)) {
					// index exist already
					if (in_array($addUniqueKey[1], $indices)) {
						unset($definitions[$key]);
					}
				}
			}
			elseif (preg_match("%RENAME(?:\s+TO)?\s+`?(\w+)`?%si", $definition, $rename)) {
				// update package failure
				if (!in_array($tableName, $this->existingTables) && !in_array($rename[1], $this->existingTables)) {
					throw new SystemException("Illegal statement '".$rename[0]."'. ", 13021);
				}
				// table is already renamed 
				if (in_array($rename[1], $this->existingTables)) {
					unset($definitions[$key]);
				}
			}
		}
		
		// alter statement is empty. delete it
		if (count($definitions) == 0) {
			$this->deleteStatement($alterStatement);
		}
		// definitions were deleted. replace statement
		elseif ($definitionsCount > count($definitions)) {
			preg_match("%ALTER(\s+IGNORE)?\s+TABLE\s+`?\w+`?\s+%i", $alterStatement, $matches);
			$replaceStatement = $matches[0];
			foreach ($definitions as $key => $definition) {
				$definition = preg_replace("%\s*,\s*$%", "", $definition);
				$replaceStatement .= $definition;
				if (isset($definitions[$key+1])) $replaceStatement .= ',';
				else $replaceStatement .= ';';
			}
			$this->replaceStatement($alterStatement, $replaceStatement);
		}
	}
	
	/**
	 * Deletes a statement.
	 * 
	 * @param	string		$statement
	 */
	protected function deleteStatement($statement) {
		$this->sqlStr = preg_replace("%".preg_quote($statement)."\s*(?:;|$)%i", '', $this->sqlStr);
	}
	
	/**
	 * Replaces a statement in $this->sqlStr.
	 * 
	 * @param	string		$statement
	 * @param	string		$replaceStatement
	 */
	protected function replaceStatement($statement, $replaceStatement) {
		$this->sqlStr = preg_replace("%".preg_quote($statement)."%i", $replaceStatement, $this->sqlStr);
	}
	
	/**
	 * Deletes a create index statement and the leading drop index if so.
	 * 
	 * @param	string		$indexName
	 * @param	string		$tableName
	 * @param	string		$statement 
	 */
	protected function deleteCreateIndexStatement($indexName, $tableName, $statement) {
		// delete drop index if it was dropped before
		if (preg_match("%(DROP\s+INDEX\s+`?".$indexName."`?\s+ON\s+`?".$tableName."`?[^;]+).*".$statement."%si", $this->sqlStr, $matches)) {
			$this->deleteStatement($matches[1]);
		}
		// delete create statement
		$this->deleteStatement($statement);
	}
}
?>