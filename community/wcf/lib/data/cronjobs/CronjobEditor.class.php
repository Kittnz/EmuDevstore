<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Contains business logic related to handling of cron jobs.
 *
 * @author	Siegfried Schweizer, Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	data.cronjobs
 * @category 	Community Framework
 */
class CronjobEditor extends DatabaseObject {
	/**
	 * Creates a new CronjobEditor object.
	 */
	public function __construct($cronjobID, $row = null) {
		if ($cronjobID !== null) {
			$sql = "SELECT		cronjob.*, package.packageDir
				FROM		wcf".WCF_N."_cronjobs cronjob
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = cronjob.packageID)
				WHERE		cronjob.cronjobID = ".$cronjobID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}

	/**
	 * Updates a cronjob.
	 * 
	 * @param	string		$classPath
	 * @param	integer		$packageID
	 * @param	string		$description
	 * @param	integer		$execMultiple
	 * @param	string		$startMinute
	 * @param	string		$startHour
	 * @param	string		$startDom
	 * @param	string		$startMonth
	 * @param	string		$startDow
	 */
	public function update($classPath, $packageID = PACKAGE_ID, $description = '', $execMultiple = 0, $startMinute = '*', $startHour = '*', $startDom = '*', $startMonth = '*', $startDow = '*') {
		$sql = "UPDATE		wcf".WCF_N."_cronjobs 
			SET		classPath = '".escapeString($classPath)."',
					packageID = ".$packageID.",
					description = '".escapeString($description)."', 
					startMinute = '".escapeString($startMinute)."', 
					startHour = '".escapeString($startHour)."', 
					startDom = '".escapeString($startDom)."', 
					startMonth = '".escapeString($startMonth)."', 
 					startDow = '".escapeString($startDow)."', 
					execMultiple = ".$execMultiple.", 
					lastExec = 0, nextExec = 1
			WHERE		cronjobID = ".$this->cronjobID;
		WCF::getDB()->sendQuery($sql);
		
		// clear cronjob cache
		self::clearCache();
	}
	
	/**
	 * Deletes this cronjob.
	 */
	public function delete() {
		// delete cronjob
		$sql = "DELETE FROM	wcf".WCF_N."_cronjobs 
			WHERE		cronjobID = ".$this->cronjobID;
		WCF::getDB()->sendQuery($sql);
		
		// clear cronjob cache
		self::clearCache();
	}
	
	/**
	 * Enables a cronjob.
	 */
	public function enable($enable = true) {
		$sql = "UPDATE	wcf".WCF_N."_cronjobs 
			SET	active = ".($enable ? 1 : 0).",
				lastExec = 0, nextExec = 1 
			WHERE	cronjobID = ".$this->cronjobID;
		WCF::getDB()->sendQuery($sql);
		
		// clear cronjob cache
		self::clearCache();
	}
	
	/**
	 * Updates a cronjob with new lastExec and nextExec timestamps.
	 * 
	 * @param	integer		$nextExec
	 */
	public function setNextExec($nextExec = 0) {
		$sql = "UPDATE		wcf".WCF_N."_cronjobs 
			SET		lastExec = ".TIME_NOW.", nextExec = ".$nextExec." 
			WHERE		cronjobID = ".$this->cronjobID;
		WCF::getDB()->sendQuery($sql);
	}
	
 	/**
	 * Logs cronjob execs.
	 * 
	 * @param	integer		$execTime
	 * @return	integer		new log id
	 */
	public function logExec($execTime = TIME_NOW) {
		$sql = "INSERT INTO	wcf".WCF_N."_cronjobs_log 
					(cronjobID, execTime) 
			VALUES 		(".$this->cronjobID.", ".$execTime.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
	
 	/**
	 * Logs cronjob exec success or failure.
	 * 
	 * @param	integer		$logID
	 * @param	boolean		$success
	 * @param	Exception	$exception
	 */
	public static function logSuccess($logID = 0, $success = false, $exception = NULL) {
		if ($success === false && $exception) {
			$message = $exception->getMessage();
			$code = $exception->getCode();
			$file = $exception->getFile();
			$line = $exception->getLine();
			$stacktrace = $exception->getTraceAsString();
			$errString = $message."\n".$code."\n".$file."\n".$line."\n".$stacktrace;
			
			$sql = "UPDATE	wcf".WCF_N."_cronjobs_log 
				SET	success = 0, error = '".escapeString($errString)."' 
				WHERE	cronjobsLogID = ".$logID;
		} 
		else if ($success === true) {
			$sql = "UPDATE	wcf".WCF_N."_cronjobs_log 
				SET	success = 1 
				WHERE	cronjobsLogID = ".$logID;
		}
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Deletes the cronjob log.
	 */
	public static function clearLog($packageID = PACKAGE_ID) {
		$sql = "DELETE FROM	wcf".WCF_N."_cronjobs_log
			WHERE		cronjobID IN (
						SELECT	cronjobID
						FROM	wcf".WCF_N."_cronjobs cronjobs,
							wcf".WCF_N."_package_dependency package_dependency
						WHERE 	cronjobs.packageID = package_dependency.dependency
							AND package_dependency.packageID = ".$packageID."
					)";
		WCF::getDB()->sendQuery($sql);
		
		// clear cronjob cache
		self::clearCache();
	}
	
	/**
	 * Clears the cronjob cache.
	 */
	public static function clearCache() {
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.cronjobs-*');
	}
	
	/**
	 * Creates a new cronjob.
	 * 
	 * @param	string		$classPath
	 * @param	integer		$packageID
	 * @param	string		$description
	 * @param	integer		$execMultiple
	 * @param	string		$startMinute
	 * @param	string		$startHour
	 * @param	string		$startDom
	 * @param	string		$startMonth
	 * @param	string		$startDow
	 * @return	integer
	 */
	public static function create($classPath, $packageID = PACKAGE_ID, $description = '', $execMultiple = 0, $startMinute = '*', $startHour = '*', $startDom = '*', $startMonth = '*', $startDow = '*') {
		$sql = "INSERT INTO	wcf".WCF_N."_cronjobs 
					(classPath, description, packageID,
					startMinute, startHour, startDom, 
					startMonth, startDow, execMultiple) 
			VALUES 		('".escapeString($classPath)."', '".escapeString($description)."', ".$packageID.", 
					'".escapeString($startMinute)."', 
					'".escapeString($startHour)."', 
					'".escapeString($startDom)."', 
					'".escapeString($startMonth)."',
					'".escapeString($startDow)."', 
					".$execMultiple.")";
		WCF::getDB()->sendQuery($sql);
		$cronjobID = WCF::getDB()->getInsertID();
		
		// clear cronjob cache
		self::clearCache();
	}
	
	/**
	 * Validates all cronjob attributes.
	 * 
	 * @param	string		$startMinute
	 * @param	string		$startHour
	 * @param	string		$startDom
	 * @param	string		$startMonth
	 * @param	string		$startDow
	 */
	public static function validate($startMinute, $startHour, $startDom, $startMonth, $startDow) {
		self::validateAttribute('startMinute', $startMinute);
		self::validateAttribute('startHour', $startHour);
		self::validateAttribute('startDom', $startDom);
		self::validateAttribute('startMonth', $startMonth);
		self::validateAttribute('startDow', $startDow);
	}
	
	/**
	 * Validates a cronjob attribute.
	 * 
	 * @param	string		$name
	 * @param	string		$value
	 */
	public static function validateAttribute($name, $value) {
		if ($value === '') {
			throw new SystemException("invalid value '".$value."' given for cronjob attribute '".$name."'");
		}
		
		$pattern = '';
		$step = '[1-9]?[0-9]';
		$months = 'jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec';
		$days = 'mon|tue|wed|thu|fri|sat|sun';
		$namesArr = array();
		
		switch ($name) {
			// check if startMinute is a valid minute or a list of valid minutes.
			case 'startMinute':
				$pattern = '[ ]*(\b[0-5]?[0-9]\b)[ ]*';
				break;
				
			// check if startHour is a valid hour or a list of valid hours.
			case 'startHour':
				$pattern = '[ ]*(\b[01]?[0-9]\b|\b2[0-3]\b)[ ]*';
				break;
				
			// check if startDom is a valid day of month or a list of valid days of month.
			case 'startDom':
				$pattern = '[ ]*(\b[01]?[1-9]\b|\b2[0-9]\b|\b3[01]\b)[ ]*';
				break;
				
			// check if startMonth is a valid month or a list of valid months.
			case 'startMonth':
				$digits = '[ ]*(\b[0-1]?[0-9]\b)[ ]*';
				$namesArr = explode('|', $months);
				$pattern = '('.$digits.')|([ ]*('.$months.')[ ]*)';
				break;
				
			// check if startDow is a valid day of week or a list of valid days of week.
			case 'startDow':
				$digits = '[ ]*(\b[0]?[0-7]\b)[ ]*';
				$namesArr = explode('|', $days);
				$pattern = '('.$digits.')|([ ]*('.$days.')[ ]*)';
				break;
		}
		
		// perform the actual regex pattern matching.
		$range = '((('.$pattern.')|(\*\/'.$step.')?)|((('.$pattern.')-('.$pattern.'))(\/'.$step.')?))';
		
		// $longPattern prototype: ^\d+(,\d)*$
		// $longPattern = '/^(?<!,)'.$range.'+(,'.$range.')*$/i'; // with assertions?
		// $longPattern = '/^'.$range.'+(,'.$range.')*$/i'; / does not work on some php installations
		$longPattern = '/^'.$range.'(,'.$range.')*$/i';
		
		preg_match($longPattern, $value);
		if ($value != '*' && !preg_match($longPattern, $value)) {
			throw new SystemException("invalid value '".$value."' given for cronjob attribute '".$name."'");
		}
		// test whether the user provided a meaningful order inside a range.
		else {
			$testArr = explode(',', $value);
			foreach ($testArr as $testField) {
				if ($pattern && preg_match('/^((('.$pattern.')-('.$pattern.'))(\/'.$step.')?)+$/', $testField)) {
					$compare = explode('-', $testField);
					$compareSlash = explode('/', $compare['1']);
					if (count($compareSlash) == 2) $compare['1'] = $compareSlash['0'];
					
					// see if digits or names are being given.
					$left = array_search(StringUtil::toLowerCase($compare['0']), $namesArr);
					$right = array_search(StringUtil::toLowerCase($compare['1']), $namesArr);
					if (!$left) $left = $compare['0'];
					if (!$right) $right = $compare['1'];
					// now check the values.
					if (intval($left) > intval($right)) {
						throw new SystemException("invalid value '".$value."' given for cronjob attribute '".$name."'");
					}
				}
			}
		}
	}
	
	/**
	 * Executes this cronjob.
	 */
	public function execute() {
		// create log entry
		$logID = CronjobEditor::logExec();

		// include class file
		$classPath = FileUtil::getRealPath(WCF_DIR.$this->packageDir.$this->classPath);
		if (!file_exists($classPath)) {
			throw new SystemException("unable to find class file '".$classPath."'", 11000);
		}
		require_once($classPath);
		
		// create instance.
		$className = StringUtil::getClassName($this->classPath);
		if (!class_exists($className)) {
			throw new SystemException("unable to find class '".$className."'", 11001);
		}
			
		// execute cronjob.
		$cronjobExec = new $className();
		if (method_exists($cronjobExec, 'execute')) {
			$cronjobExec->execute($this->data);
		}
			
		// log success.
		CronjobEditor::logSuccess($logID, true);
	}
}
?>