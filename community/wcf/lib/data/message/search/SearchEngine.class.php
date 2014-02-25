<?php
/**
 * SearchEngine searches for given query in the selected message types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	data.message.search
 * @category 	Community Framework
 */
class SearchEngine {
	/**
	 * search type data
	 * 
	 * @var	array
	 */
	public static $searchTypeData = array();
	
	/**
	 * list of search type objects
	 * 
	 * @var	array<SearchableMessageType>
	 */
	public static $searchTypeObjects = null;
	
	/**
	 * Returns the names of all search types.
	 * 
	 * @return	array
	 */
	public static function getSearchTypes() {
		self::loadSearchTypeObjects();
		
		$result = array();
		foreach (self::$searchTypeData as $type) {
			$messageSearch = self::$searchTypeObjects[$type['typeName']];
			if ($messageSearch->isAccessible()) {
				$result[] = $type['typeName'];
			}
		}
		
		return $result;
	}
	
	/**
	 * Loads the search type objects.
	 */
	public static function loadSearchTypeObjects() {
		if (self::$searchTypeObjects !== null) return;
		
		// get cache
		WCF::getCache()->addResource('searchableMessageTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.searchableMessageTypes-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderSearchableMessageType.class.php');
		self::$searchTypeData = WCF::getCache()->get('searchableMessageTypes-'.PACKAGE_ID);
		
		// get objects
		self::$searchTypeObjects = array();
		
		foreach (self::$searchTypeData as $type) {
			// calculate class path
			$path = '';
			if (empty($type['packageDir'])) {
				$path = WCF_DIR;
			}
			else {						
				$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
			}
			
			// include class file
			if (!file_exists($path.$type['classPath'])) {
				throw new SystemException("unable to find class file '".$path.$type['classPath']."'", 11000);
			}
			require_once($path.$type['classPath']);
			
			// create instance
			$className = StringUtil::getClassName($type['classPath']);
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			self::$searchTypeObjects[$type['typeName']] = new $className();
		}
	}
	
	/**
	 * Returns a cached search message type object.
	 * 
	 * @param	string			$typeName
	 * @return	SearchableMessageType
	 */
	public static function getSearchTypeObject($typeName) {
		if (self::$searchTypeObjects == null) {
			self::loadSearchTypeObjects();
		}
		
		if (isset(self::$searchTypeObjects[$typeName])) {
			return self::$searchTypeObjects[$typeName];
		}
		
		return null;
	}
	
	/**
	 * Searches for given query in the selected message types.
	 * 
	 * @param	string		$q					search query
	 * @param	array		$types					list of searchable message types
	 * @param	array		$conditions				additional conditions
	 * @param	string		$orderBy
	 * @param	integer		$limit
	 * @param	boolean		$parseQuery
	 * @param	string		$additionalRelevanceCalculations
	 * @return	array							search results
	 */
	public function search($q, $types = array(), $conditions = array(), $orderBy = 'relevance DESC', $limit = 1000, $parseQuery = true, $additionalRelevanceCalculations = '') {
		// parse query
		if (!empty($q) && $parseQuery) {
			$queryArray = explode(' ', $q);
			if (count($queryArray) > 1) {
				foreach ($queryArray as $key => $element) {
					$queryArray[$key] = '+'.$element;
				}
				$q = implode(' ', $queryArray);
			}
		}
		$q = escapeString($q);
		
		// no message types given. take all types.
		$this->loadSearchTypeObjects();
		if (count($types) == 0) $types = $this->getSearchTypes();
		
		// sort by
		$sortByRelevance = $sortByTime = $sortByUsername = $sortBySubject = false;
		if ($orderBy == 'relevance ASC' || $orderBy == 'relevance DESC') {
			if (!empty($q)) {
				$sortByRelevance = true;
			}
			else {
				$orderBy == 'time DESC';
			}
		}
		if ($orderBy == 'time ASC' || $orderBy == 'time DESC') $sortByTime = true;
		if ($orderBy == 'username ASC' || $orderBy == 'username DESC') $sortByUsername = true;
		if ($orderBy == 'subject ASC' || $orderBy == 'subject DESC') $sortBySubject = true;
		
		
		// build search query
		$sql = '';
		foreach ($types as $type) {
			if (!isset(self::$searchTypeObjects[$type])) {
				throw new SystemException('unknown search type '.$type, 101001);
			}
		
			// get search type object
			$messageSearch = self::$searchTypeObjects[$type];
			if (!$messageSearch->isAccessible()) continue;
			if (!empty($sql)) $sql .= "\nUNION\n";
			
			// get field names
			$messageIDFieldName = $messageSearch->getIDFieldName();
			$subjectFieldNames = $messageSearch->getSubjectFieldNames();
			$messageFieldNames = $messageSearch->getMessageFieldNames();
			
			$sql .= "(	SELECT		".(strpos($messageIDFieldName, '.') !== false ? $messageIDFieldName : "messageTable.".$messageIDFieldName)." AS messageID,
							'".$type."' AS messageType
							".($sortByTime ? ", ".$messageSearch->getTimeFieldName()." AS time" : "")."
							".($sortByUsername ? ", CAST(".$messageSearch->getUsernameFieldName()." AS CHAR CHARACTER SET ".WCF::getDB()->getCharset().") AS username" : "")."
							".($sortBySubject ? ", CAST(messageTable.".reset($subjectFieldNames)." AS CHAR CHARACTER SET ".WCF::getDB()->getCharset().") AS subject" : "")."
							".($sortByRelevance ? ", MATCH (messageTable.".implode(', messageTable.', $subjectFieldNames).", messageTable.".implode(', messageTable.', $messageFieldNames).") AGAINST ('".escapeString($q)."') + (5 / (1 + POW(LN(1 + (".TIME_NOW." - ".$messageSearch->getTimeFieldName().") / 2592000), 2)))".(!empty($additionalRelevanceCalculations) ? ' + '.$additionalRelevanceCalculations : '')." AS relevance" : ", 0 AS relevance")."
					FROM 		".$messageSearch->getTableName()." messageTable
							".$messageSearch->getJoins()."
					WHERE		".(!empty($q) ? "MATCH (messageTable.".implode(', messageTable.', $subjectFieldNames).", messageTable.".implode(', messageTable.', $messageFieldNames).") AGAINST ('".escapeString($q)."' IN BOOLEAN MODE)" : "")."
							".(!empty($conditions[$type]) ? " ".(!empty($q) ? "AND" : "")." (".$conditions[$type].")" : "")."
					GROUP BY	messageID)";
		}
		if (empty($sql)) {
			throw new SystemException('no message types given', 101002);
		}
		
		if (!empty($orderBy)) {
			$sql .= " ORDER BY " . $orderBy;
		}
		
		// send search query
		$messages = array();
		$result = WCF::getDB()->sendQuery($sql, $limit);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$messages[] = array('messageID' => $row['messageID'], 'messageType' => $row['messageType']);
		}
		
		return $messages;
	}
}
?>