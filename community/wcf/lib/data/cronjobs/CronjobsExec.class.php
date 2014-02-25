<?php
// wcf imports
require_once(WCF_DIR.'lib/data/cronjobs/CronjobEditor.class.php');

/**
 * Handles execution of cronjobs and calculates future dates for cronjob execution.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	data.cronjobs
 * @category 	Community Framework
 */
class CronjobsExec {
	protected $now = 0;
	protected $cronjobsCache = array();
	protected $cacheName = '';
	protected $nextExec = 0;
	protected $cronjobsData = array();
	protected $cronjobsDataRaw = array();
	protected $cronjobsDataComparator = array();
	protected $cronjobsDataResultDate = array();
	protected $startDom = array();
	protected $isleapYear = false;
	protected $timebase = 0;
	protected $countMultipleExecs = 1;
	protected $plannedExecs = array();
	protected $execMultiple = 0;
	
	/**
	 * Constructs an instance of this class.
	 */
	public function __construct() {
		// get current server system time for use with date() in order to ensure valid
		// and consistent date and time values all over one instance of this class 
		// in case the timebase is "now" which will be the case if no multipleExec action
		// is requested by configuration.
		$this->now = TIME_NOW;
		// set the timezone which is either the one set by the WBB admin, or if it isn't set, it's GMT.
		DateUtil::formatDate();
		
		// set cache name.
		$this->cacheName = 'cronjobs-'.PACKAGE_ID;
		// read cache.
		$this->getCronjobsCache();
	}
	
	/**
	 * Gets cronjobs information from cache.
	 */
	protected function getCronjobsCache() {
		WCF::getCache()->addResource($this->cacheName, WCF_DIR.'cache/cache.'.$this->cacheName.'.php', WCF_DIR.'lib/system/cache/CacheBuilderCronjobs.class.php');
		$result = WCF::getCache()->get($this->cacheName);
		if (count($result)) {
			$this->cronjobsCache = $result['cronjobs'];
			// sort cronjobs cache
			uasort($this->cronjobsCache, array('CronjobsExec', 'compareCronjobs'));
			// find and execute cronjobs.
			$this->findPendingCronjobs();
		}
	}
	
	/**
	 * Compares to cronjobs by their next execution time.
	 * 
	 * @param	array		$cronjobA
	 * @param	array		$cronjobB
	 * @return	integer
	 */
	private static function compareCronjobs($cronjobA, $cronjobB) {
		if ($cronjobA['nextExec'] < $cronjobB['nextExec']) return -1;
		else if ($cronjobA['nextExec'] > $cronjobB['nextExec']) return 1;
		return 0;
	}
	
	/**
	 * Looks if an execution of a given cronjob is pending.
	 * 
	 * The basic principle of the wcf cronjobs is that a call to this method is being triggered 
	 * via ajax with every Burning Board page view. This is being done in order to ensure a sufficient 
	 * accuracy of execution time, despite the lack of a "real" crond.
	 * Every time this method is being called, configured execution times for a given cronjob 
	 * may most likely either lie ahead, or be a thing of the past. In the latter case this cronjob
	 * is being estimated as "pending" and thus be executed.
	 */
	protected function findPendingCronjobs() {
		// there might be ranges of date/time values specified. Ranges can be expressed 
		// either with a comma-separated list, or with a dash, or with a combination of both.
		// also there are intervals possible, expressed with an asterisk followed by a slash 
		// and a number (e.g. '*/2' meaning 'every two somethings'). days of week and months
		// may also be noted as abbreviations of plaintext names.
		foreach ($this->cronjobsCache as $key => $cronjobsCache) {
			$this->plannedExecs = array();
			if (intval($cronjobsCache['execMultiple']) == 1) {
				// set identificator for an eventual execMultiple action.
				$this->execMultiple = 1;
				// reset counter needed for an eventual execMultiple action.
				$this->countMultipleExecs = 0;
				// initiate array where "planned" dates of inbetween execs are being stored if execMultiple is set.
				if (intval($cronjobsCache['nextExec']) > 1) $this->plannedExecs[] = intval($cronjobsCache['nextExec']);
			}
			else {
				$this->execMultiple = 0;
				$this->countMultipleExecs = 1;
			}
			
			// don't execute this cronjob in case nextExec is in the future.
			if ($cronjobsCache['nextExec'] > $this->now) {
				unset($this->cronjobsCache[$key]);
				continue;
			}
			
			// determine time base.
			if ($this->execMultiple == 0) {
				$this->timebase = $this->now;
			}
			else if ($this->execMultiple == 1) {
				if (intval($cronjobsCache['nextExec'] <= 1)) {
					$this->timebase = $this->now;
					$this->countMultipleExecs = 1;
				}
				else {
					$this->timebase = $cronjobsCache['nextExec']; // the nextExec determined during last execution.
				}
			}
			
			// get the actual nextExec time.
			if ($this->findCronjobNextExec($cronjobsCache) === false) {
				unset($this->cronjobsCache[$key]);
				continue;
			}
			
			// write new lastExec and nextExec timestamps to database and add log entry.
			$cronjobObj = new CronjobEditor(null, $cronjobsCache);
			$cronjobObj->setNextExec($this->nextExec);
			$this->cronjobsCache[$key]['newLogID'] = $cronjobObj->logExec();
		}
		
		// now clear the cache.
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.cronjobs-*.php');
		// memcache workaround to be sure that at least the cache of the active application is cleared
		WCF::getCache()->clearResource($this->cacheName);
		
		foreach ($this->cronjobsCache as $cronjobsCache) {
			try {
				// now really execute the cronjob.
				$this->execPendingCronjobs($cronjobsCache);
			}
			catch (SystemException $e) {
				CronjobEditor::logSuccess($cronjobsCache['newLogID'], false, $e);
				continue;
			}
		}
	}
	
	/**
	 * Gets the actual date of nextExec for a given cronjob.
	 * 
	 * @param	array		$cronjobsCache
	 * @return	boolean		success
	 */
	protected function findCronjobNextExec($cronjobsCache = array()) {
		// there must be a reasonable time base.
		if ($this->timebase == 0) {
			return false;
		}
		
		// purge arrays.
		$this->cronjobsDataRaw = array();
		$this->cronjobsDataComparator = array();
		$this->cronjobsDataResultDate = array();
		
		// process configured dates and fill the cronjobsDataRaw array.
		$this->processConfiguredDates($cronjobsCache);
		
		// clean and further process resulting array.
		foreach ($this->cronjobsDataRaw as $key => $processedDate) {
			array_unique($processedDate);
			sort($processedDate);
			if ($processedDate['0'] !== '*') {
				$processedDate = ArrayUtil::toIntegerArray($processedDate);
			}
			$this->cronjobsDataRaw[$key] = $processedDate;
		}
		
		// fill the array we'll be using for comparison with the current date and time values.
		$this->cronjobsDataComparator['startMinute'] = date('i', $this->timebase);
		$this->cronjobsDataComparator['startHour'] = date('H', $this->timebase);
		$this->cronjobsDataComparator['startDom'] = date('d', $this->timebase);
		$this->cronjobsDataComparator['startMonth'] = date('m', $this->timebase);
		$this->cronjobsDataComparator['startDow'] = date('D', $this->timebase);
		
		// merge startDom and startDow.
		$this->getDaysFromDow(0, 0);
		
		// fill up eventual unconfigured fields with default values.
		$this->getDefaultDateValues(0, 0);
		
		// investigate configured dates and assemble date of the next pending execution of this cronjob
		// -- this is the core of the whole algorithm.
		$this->getCronjobsDataMonth();
		
		// process nextExec timestamp from the found date.
		$this->buildNextExec();
		
		// if execMultiple is set, we must do a little recursion in order to find eventual other execs
		// between the old nextExec and now; then we will execute this cronjob as many times as we found 
		// inbetween execs.
		if (($this->execMultiple == 1) && ($this->nextExec <= $this->now)) {
			if (intval($this->nextExec) > 1) $this->plannedExecs[] = $this->nextExec;
			$this->timebase = $this->nextExec;
			$this->findCronjobNextExec($cronjobsCache);
		}
		
		return true;
	}
	
	/**
	 * Actually execute the cronjob.
	 * 
	 * @param	array		$cronjobsCache
	 * @return	boolean		success
	 */
	protected function execPendingCronjobs($cronjobsCache = array()) {
		// include class file
		if (!file_exists(WCF_DIR.$cronjobsCache['classPath'])) {
			throw new SystemException("unable to find class file '".WCF_DIR.$cronjobsCache['classPath']."'", 11000);
		}
		require_once(WCF_DIR.$cronjobsCache['classPath']);
		$i = 0;
		while ($i < $this->countMultipleExecs) {
			// omit 1970-01-01T01:00:00.
			if ($this->execMultiple == 1) {
				$cronjobsCache['nextExec'] = $this->timebase;
			}
			if (isset($this->plannedExecs) && count($this->plannedExecs)) {
				$cronjobsCache['nextExec'] = $this->plannedExecs[$i];
			}
			
			// create instance.
			$className = StringUtil::getClassName($cronjobsCache['classPath']);
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11001);
			}
			
			// execute cronjob.
			$cronjobExec = new $className();
			if (method_exists($cronjobExec, 'execute')) {
				$cronjobExec->execute($cronjobsCache);
			}
			
			// log success.
			CronjobEditor::logSuccess($cronjobsCache['newLogID'], true);
			$i++;
		}
	}
	
	/**
	 * Investigates in which month this cronjob is to be executed.
	 */
	protected function getCronjobsDataMonth() {
		/*
		the cronjobsDataComparator array is our frame of reference. there are three main possibilities what a found value
		of cronjobsDataRaw[startMonth] can be:
		--	first we look for the occurrence of a value that is equal to the current month (cronjobsDataComparator[startMonth]).
			then, we have to investigate the cronjobsDataRaw[startDom]. there we first have to -- analogically -- look for the 
			same day as today and, if found, go ahead with cronjobsDataRaw[startHour]; otherwise look for a bigger value, and 
			if not found, look for a smaller value and so on.
			cronjobsDataRaw[startHour] and cronjobsDataRaw[startMinute] must be investigated analogically.
			this has priority 1 because this date might be in the nearer future as the second alternative can be.
		--	if we do not find this, we look for the occurrence of a bigger value than cronjobsDataComparator[startMonth].
			if found, then it is this year, and all we have to do further is look for the respective "youngest" values 
			in the cronjobsDataRaw[startDom], cronjobsDataRaw[startHour] and cronjobsDataRaw[startMinute] fields. these put
			together then make up the nextExec date.
			this has priority 2 because this date must be in the nearer future as the third alternative can be.
			we can skip this step if the current month (cronjobsDataComparator[startMonth]) is december. 
		--	if we also do not find a bigger value, we look for a smaller value of cronjobsDataRaw[startMonth]. if found,
			this needs to be next year, and all we have to do further is, like before, look for the respective other
			"youngest" values, and also take the "youngest" value for the month because we know it's got to be next year.
			this has priority 3.
			we can skip this step if the current month (cronjobsDataComparator[startMonth]) is january.
		The algorithm is always halted as soon as a suitable date has been completely assembled.
		*/
		
		// start investigating the cronjobsDataRaw[startMonth] field.
		foreach ($this->cronjobsDataRaw['startMonth'] as $monthToCompare) {
			if ($monthToCompare == $this->cronjobsDataComparator['startMonth']) {
				$this->cronjobsDataResultDate['startYear'] = date('Y', $this->timebase); // this year
				$this->cronjobsDataResultDate['startMonth'] = $monthToCompare;
				$this->getCronjobsDataDay();
				break;
			}
		}
		
		// search for an occurrence of a bigger value than cronjobsDataComparator[startMonth].
		if (!isset($this->cronjobsDataResultDate['startMonth'])) {
			foreach ($this->cronjobsDataRaw['startMonth'] as $monthToCompare) {
				if ($monthToCompare > $this->cronjobsDataComparator['startMonth']) {
					$this->cronjobsDataResultDate['startYear'] = date('Y', $this->timebase); // this year
					$this->cronjobsDataResultDate['startMonth'] = $monthToCompare;
					$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom']['0'];
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
		
		// search for an occurrence of a lower value than cronjobsDataComparator[startMonth].
		if (!isset($this->cronjobsDataResultDate['startMonth'])) {
			foreach ($this->cronjobsDataRaw['startMonth'] as $monthToCompare) {
				if ($monthToCompare < $this->cronjobsDataComparator['startMonth']) {
					$this->cronjobsDataResultDate['startYear'] = date('Y', $this->timebase) + 1; // next year.
					
					$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth']['0'];
					$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom']['0'];
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					
					// before we proceed, we have to check if the found startDom is the 29th and the found startMonth
					// is February, in which case we have to check if this really is a leap year.
					if (intval($this->cronjobsDataResultDate['startDom']) === 29 && intval($this->cronjobsDataResultDate['startMonth']) === 2 && intval(date('L', $this->timebase)) === 0) {
						// this is February, the 29th, but this is not a leap year; so search the next leap year.
						$leapYear = 0;
						$this->isleapYear = true;
						while (intval($leapYear) === 0) {
							$leapYear = date('Y', mktime(0, 0, 0, date('n', $this->timebase), $this->cronjobsDataResultDate['startDom'], $this->cronjobsDataResultDate['startYear']));
							$this->cronjobsDataResultDate['startYear']++;
						}
					}
					
					// the next block is important in the case that we have to increment the month.
					$nextMonth = $this->cronjobsDataRaw['startMonth']['0'];
					if ($this->isleapYear === false) {
						if (isset($this->startDom) && count($this->startDom)) {
							unset($this->cronjobsDataRaw['startDom']);
							$this->cronjobsDataRaw['startDom'] = $this->startDom;
						}
						$this->getDaysFromDow($nextMonth, date('Y', $this->timebase) + 1);
						$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom']['0'];
					}
					
					break;
				}
			}
		}
	}
	
	/**
	 * Investigates on which day this cronjob is to be executed.
	 */
	protected function getCronjobsDataDay() {
		// start investigating the cronjobsDataRaw[startDom] field.
		foreach ($this->cronjobsDataRaw['startDom'] as $dayToCompare) {
			if ($dayToCompare == $this->cronjobsDataComparator['startDom']) {
				$this->cronjobsDataResultDate['startDom'] = $dayToCompare;
				$this->getCronjobsDataHour();
				break;
			}
		}
		
		// search for an occurrence of a bigger value than cronjobsDataComparator[startDom].
		if (!isset($this->cronjobsDataResultDate['startDom'])) {
			foreach ($this->cronjobsDataRaw['startDom'] as $dayToCompare) {
				if ($dayToCompare > $this->cronjobsDataComparator['startDom']) {
					$this->cronjobsDataResultDate['startDom'] = $dayToCompare;
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
		
		// search for an occurrence of a lower value than cronjobsDataComparator[startDom].
		if (!isset($this->cronjobsDataResultDate['startDom'])) {
			foreach ($this->cronjobsDataRaw['startDom'] as $dayToCompare) {
				if ($dayToCompare < $this->cronjobsDataComparator['startDom']) {
					// if this is a day which is in this month's past, we have to set the month to the next month.
					$key = array_search($this->cronjobsDataResultDate['startMonth'], $this->cronjobsDataRaw['startMonth']);
					if ($key === count($this->cronjobsDataRaw['startMonth']) - 1) {
						$key = 0;
						// this must be next year.
						$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
					}
					else $key = $key + 1;
					$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$key];
					$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom']['0'];
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
	}
	
	/**
	 * Investigates in which hour this cronjob is to be executed.
	 */
	protected function getCronjobsDataHour() {
		// start investigating the cronjobsDataRaw[startHour] field.
		foreach ($this->cronjobsDataRaw['startHour'] as $hourToCompare) {
			if ($hourToCompare == $this->cronjobsDataComparator['startHour']) {
				$this->cronjobsDataResultDate['startHour'] = $hourToCompare;
				$this->getCronjobsDataMinute();
				break;
			}
		}
		
		// search for an occurrence of a bigger value than cronjobsDataComparator[startHour].
		if (!isset($this->cronjobsDataResultDate['startHour'])) {
			foreach ($this->cronjobsDataRaw['startHour'] as $hourToCompare) {
				if ($hourToCompare > $this->cronjobsDataComparator['startHour']) {
					$this->cronjobsDataResultDate['startHour'] = $hourToCompare;
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
		
		// search for an occurrence of a lower value than cronjobsDataComparator[startHour].
		if (!isset($this->cronjobsDataResultDate['startHour'])) {
			foreach ($this->cronjobsDataRaw['startHour'] as $hourToCompare) {
				if ($hourToCompare < $this->cronjobsDataComparator['startHour']) {
					// if this is an hour which is in today's past, so we have to set the day to the next day,
					// and if the day exceeds the range of the possible days, set the month to the next one etc.
					$key = array_search($this->cronjobsDataResultDate['startDom'], $this->cronjobsDataRaw['startDom']);
					if ($key === count($this->cronjobsDataRaw['startDom']) - 1) {
						$key = 0;
						
						// innermost swap section
						$keyMonth = array_search($this->cronjobsDataComparator['startMonth'], $this->cronjobsDataRaw['startMonth']);
						if ($keyMonth === count($this->cronjobsDataRaw['startMonth']) - 1) {
							$keyMonth = 0;
							// this must be next year.
							$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
						}
						else {
							$keyMonth = $keyMonth + 1;
						}
						$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$keyMonth];
						// BEWARE! there could still be an issue with years and leap years in this spot!
						// end innermost swap section
						
					} 
					else {
						$key = $key + 1;
					}
					
					$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$key];
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
	}
	
	/**
	 * Investigates in which minute this cronjob is to be executed.
	 */
	protected function getCronjobsDataMinute() {
		// start investigating the cronjobsDataRaw[startMinute] field.
		foreach ($this->cronjobsDataRaw['startMinute'] as $minuteToCompare) {
			if ($minuteToCompare == $this->cronjobsDataComparator['startMinute']) {
				// this would be the same timestamp as the current nextExec, so we must treat this case in a special way.
				if (count($this->cronjobsDataRaw['startMinute']) > 1) {
					$key = array_search($this->cronjobsDataComparator['startMinute'], $this->cronjobsDataRaw['startMinute']);
					if ($key === count($this->cronjobsDataRaw['startMinute']) - 1) {
						$key = 0;
						
						// ----------------
						// new experimental
						// furthermost swap section
						$keyHour = array_search($this->cronjobsDataComparator['startHour'], $this->cronjobsDataRaw['startHour']);
						if ($keyHour === count($this->cronjobsDataRaw['startHour']) - 1) {
							$keyHour = 0;
							
							// middle swap section
							// ... and so on:
							$keyDom = array_search($this->cronjobsDataComparator['startDom'], $this->cronjobsDataRaw['startDom']);
							if ($keyDom === count($this->cronjobsDataRaw['startDom']) - 1) {
								$keyDom = 0;
								
								// innermost swap section
								$keyMonth = array_search($this->cronjobsDataComparator['startMonth'], $this->cronjobsDataRaw['startMonth']);
								if ($keyMonth === count($this->cronjobsDataRaw['startMonth']) - 1) {
									$keyMonth = 0;
									// this must be next year.
									$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
								}
								else {
									$keyMonth = $keyMonth + 1;
								}
								$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$keyMonth];
								// BEWARE! there could still be an issue with years and leap years in this spot!
								// end innermost swap section
							
							}
							else {
								$keyDom = $keyDom + 1;
							}
							$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$keyDom];
							// end middle swap section
						
						}
						else {
							$keyHour = $keyHour + 1;
						}
						$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour'][$keyHour];
						// end furthermost swap section
						// end new experimental
						// --------------------
						
					}
					else {
						$key = $key + 1;
					}
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute'][$key];					
				}
				else {
					// if more than one hours are possible exec dates, we must seek the right one.
					if (count($this->cronjobsDataRaw['startHour']) > 1) {
						
						$key = array_search($this->cronjobsDataComparator['startHour'], $this->cronjobsDataRaw['startHour']);
						if ($key === count($this->cronjobsDataRaw['startHour']) - 1) {
							$key = 0;
							
							// newly added 2006-10-27
							// middle swap section
							// ... and so on:
							$keyDom = array_search($this->cronjobsDataComparator['startDom'], $this->cronjobsDataRaw['startDom']);
							if ($keyDom === count($this->cronjobsDataRaw['startDom']) - 1) {
								$keyDom = 0;
								
								// innermost swap section
								$keyMonth = array_search($this->cronjobsDataComparator['startMonth'], $this->cronjobsDataRaw['startMonth']);
								if ($keyMonth === count($this->cronjobsDataRaw['startMonth']) - 1) {
									$keyMonth = 0;
									// this must be next year.
									$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
								}
								else {
									$keyMonth = $keyMonth + 1;
								}
								$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$keyMonth];
								// BEWARE! there could still be an issue with years and leap years in this spot!
								// end innermost swap section
							
							}
							else {
								$keyDom = $keyDom + 1;
							}
							$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$keyDom];
							// end middle swap section
							// end newly added
							
						}
						else {
							$key = $key + 1;
						}
						$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour'][$key];
						$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					}
					else {
						// if there's only one configured startHour and no startMinute, the latter must always be 0.
						if (!isset($this->cronjobsDataResultDate['startMinute'])) {
							$this->cronjobsDataResultDate['startMinute'] = 0;
						}
						
						if (count($this->cronjobsDataRaw['startDom']) > 1) {
							$key = array_search($this->cronjobsDataComparator['startDom'], $this->cronjobsDataRaw['startDom']);
							if ($key === count($this->cronjobsDataRaw['startDom']) - 1) {
								$key = 0;
							}
							else {
								$key = $key + 1;
							}
							$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$key];
							$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour']['0'];
						}
						else {
							if (count($this->cronjobsDataRaw['startMonth']) > 1) {
								$key = array_search($this->cronjobsDataComparator['startMonth'], $this->cronjobsDataRaw['startMonth']);
								if ($key === count($this->cronjobsDataRaw['startMonth']) - 1) {
									$key = 0;
									// this must be next year.
									$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
								}
								else {
									$key = $key + 1;
								}
								$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$key];
								// BEWARE! there could still be an issue with years and leap years in this spot!
							}
						}
					}
				}
				if ($this->execMultiple == 1) $this->getNextExecMultiple();
				break;
			}
		}
		
		// search for an occurrence of a bigger value than cronjobsDataComparator[startMinute].
		if (!isset($this->cronjobsDataResultDate['startMinute'])) {
			foreach ($this->cronjobsDataRaw['startMinute'] as $minuteToCompare) {
				if ($minuteToCompare > $this->cronjobsDataComparator['startMinute']) {
					$this->cronjobsDataResultDate['startMinute'] = $minuteToCompare;
					break;
				}
			}
		}
		
		// search for an occurrence of a lower value than cronjobsDataComparator[startMinute].
		if (!isset($this->cronjobsDataResultDate['startMinute'])) {
			foreach ($this->cronjobsDataRaw['startMinute'] as $minuteToCompare) {
				if ($minuteToCompare < $this->cronjobsDataComparator['startMinute']) {
					
					// if this is a minute which is in this hour's past, we have to set the hour to the next hour.
					$key = array_search($this->cronjobsDataResultDate['startHour'], $this->cronjobsDataRaw['startHour']);
					if ($key === count($this->cronjobsDataRaw['startHour']) - 1) {
						$key = 0;
						
						// this must be the next day
						// middle swap section
						// ... and so on:
						$keyDom = array_search($this->cronjobsDataComparator['startDom'], $this->cronjobsDataRaw['startDom']);
						if ($keyDom === count($this->cronjobsDataRaw['startDom']) - 1) {
							$keyDom = 0;
							
							// innermost swap section
							$keyMonth = array_search($this->cronjobsDataComparator['startMonth'], $this->cronjobsDataRaw['startMonth']);
							if ($keyMonth === count($this->cronjobsDataRaw['startMonth']) - 1) {
								$keyMonth = 0;
								// this must be next year.
									$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
							}
							else {
								$keyMonth = $keyMonth + 1;
							}
							$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$keyMonth];
							// BEWARE! there could still be an issue with years and leap years in this spot!
							// end innermost swap section
						
						}
						else {
							$keyDom = $keyDom + 1;
						}
						$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$keyDom];
						// end middle swap section
					}
					else {
						$key = $key + 1;
					}
					$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour'][$key];
					$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute']['0'];
					break;
				}
			}
		}
	}
	
	/**
	 * Process configured dates with plaintext names, dashed ranges and slashed intervals.
	 * 
	 * @param	array		$cronjobsCache
	 */
	protected function processConfiguredDates($cronjobsCache = array()) {
		// get arrays containing the configured dates from our database (or, respectively, the cache).
		$this->cronjobsDataRaw['startMinute'] = explode(',', $cronjobsCache['startMinute']);
		$this->cronjobsDataRaw['startHour'] = explode(',', $cronjobsCache['startHour']);
		$this->cronjobsDataRaw['startDom'] = explode(',', $cronjobsCache['startDom']);
		$this->cronjobsDataRaw['startMonth'] = explode(',', $cronjobsCache['startMonth']);
		$this->cronjobsDataRaw['startDow'] = explode(',', $cronjobsCache['startDow']);
		
		// process plaintext month and day of week values.
		foreach ($this->cronjobsDataRaw as $element => $datesRaw) {
			foreach ($datesRaw as $position => $dateRaw) {
				switch ($element) {
					// months.
					case 'startMonth':
						$datesPlain = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
						break;
					// days of week.
					case 'startDow':
						// for us, the week begins on sunday because date() wants us to think that way.
						$datesPlain = array('sun','mon','tue','wed','thu','fri','sat');
						break;
					// nothing to do for the others.
					default:
						break;
				}
				
				// if $dateRaw is a time range expressed with a dash, a special handling is needed.
				if (StringUtil::indexOf($dateRaw, '-')) {
					// dismantle the range into an array.
					$range = explode('-', $dateRaw);
					
					// investigate the new array.
					foreach ($range as $key => $value) {
						$slashPos = StringUtil::indexOf($value, '/');
						
						if ((StringUtil::length($value) == 3) && ($slashPos === false)) {
							// this is a plaintext name, so convert this into a number.
							$datePlain = StringUtil::toLowerCase($value);
							$dateNum = array_search($datePlain, $datesPlain);
							// put the converted value back to the array.
							if ($dateNum !== false) $range[$key] = $dateNum;
						}
						else if ($slashPos !== false) {
							// this value additionally contains a slashed interval,
							// so once again part this value.
							$interval = explode('/', $value);
							if (StringUtil::length($interval['0']) == 3) {
								// this is a plaintext name, so convert this into a number.
								$datePlain = StringUtil::toLowerCase($interval['0']);
								$dateNum = array_search($datePlain, $datesPlain);
								if ($dateNum !== false) $range[$key] = $dateNum;
							}
							else {
								$range[$key] = $interval['0'];
							}
							$range[$key + 1] = $interval['1'];
						}
					}
					
					// reassemble array.
					foreach ($range as $key => $digit) {
						$range[$key] = intval($digit);
					}
					$newPos = array_search($dateRaw, $this->cronjobsDataRaw[$element]);
					$this->cronjobsDataRaw[$element][$newPos] = $range;
					$this->getDashedRange($element, $newPos);
					
				}
				else {
					// this is no range.
					$slashPos = StringUtil::indexOf($dateRaw, '/');
					if ((StringUtil::length($dateRaw) == 3) && ($slashPos === false)) {
						// this is a plaintext name, so convert this into a number.
						$datePlain = StringUtil::toLowerCase($dateRaw);
						$dateNum = array_search($datePlain, $datesPlain);
						// put the converted value back to the original array.
						if ($dateNum !== false) $this->cronjobsDataRaw[$element][$position] = $dateNum;
					}
					else if ($slashPos !== false) {
						// this value additionally contains a slashed interval,
						// so once again part this value.
						$interval = explode('/', $dateRaw);
						
						// put parted value back to array.
						unset($this->cronjobsDataRaw[$element][$position]);
						$this->cronjobsDataRaw[$element][$position][] = $interval['0'];
						$this->cronjobsDataRaw[$element][$position][] = $interval['1'];
						
						// break down slashed interval.
						$this->getSlashedInterval($element, $position);
					}
				}
			}
		}
	}
	
	/**
	 * Searches and processes time ranges expressed with a dash between two numbers (dashed range).
	 * 
	 * @param	string		$element -- these are the entries representing months, hours etc.
	 * @param	integer		$position -- there might be multiple entries for each month, hour etc.
	 */
	protected function getDashedRange($element = '', $position = 0) {
		$i = 1;
		$insert = array();
		$data = array();
		
		// get the numbers between the dashes.
		if (is_array($this->cronjobsDataRaw[$element][$position])) {
			foreach ($this->cronjobsDataRaw[$element] as $key => $data) {
				if (count($data) > 1) {
					$count = ($data['1'] - $data['0']);
					if ($count) {
						$insert[] = intval($data['0']);
						while ($i < $count) {
							$insert[] = $data['0'] + $i;
							$i++;
						}
						$insert[] = intval($data['1']);
					}
					switch (count($data)) {
						case '2':
							// this is a normal dashed range.
							unset($this->cronjobsDataRaw[$element][$key]);
							$this->cronjobsDataRaw[$element] = array_merge($this->cronjobsDataRaw[$element], $insert);
							break;
						case '3':
							// this is a dashed range additionally containing a slashed interval.
							unset($this->cronjobsDataRaw[$element][$key]);
							$this->cronjobsDataRaw[$element][$key][] = $insert;
							$this->cronjobsDataRaw[$element][$key][] = $data['2'];
							// break down slashed interval.
							$this->getSlashedInterval($element, $key);
							break;
					}
				}
			}
		}
	}
	
	/**
	 * Searches and processes time intervals expressed with a dashed range or an asterisk followed by a slash followed by a number.
	 * 
	 * @param	array		$element
	 */
	protected function getSlashedInterval($element = array()) {
		if (count($element)) {
			$scope = 0;
			$resultArr = array();
			switch ($element) {
				case 'startMinute':
					$scope = 59; // the scope within which the interval must be.
					$result = 0; // where the scope starts.
					break;
				case 'startHour':
					$scope = 23;
					$result = 0;
					break;
				case 'startDom':
					$scope = date('t', $this->timebase);
					$result = 1;
					break;
				case 'startMonth':
					$scope = 12;
					$result = 1;
					break;
				case 'startDow':
					$scope = 7;
					$result = 1;
					break;
				default:
					exit;
			}
			foreach ($this->cronjobsDataRaw[$element] as $key => $value) {
				if (is_array($value)) {
					$iterator = $value['1'];
					// the variant with the asterisk.			
					if ($value['0'] == '*') {
						while ($result <= $scope) {
							$resultArr[] = $result;
							$result = $result + $iterator;
						}
					}
					// the variant with a dashed range.
					else if (is_array($value['0']) && count($value['0'])) {
						$result = $value['0']['0'];
						$upper = count($value['0']) - 1;
						$scope = $value['0'][$upper];
						while ($result <= $value['0'][$upper]) {
							$resultArr[] = $result;
							$result = $result + $iterator;
						}
					}
					$getrid = array_search($value, $this->cronjobsDataRaw[$element]);
					if (($getrid !== false) && is_array($this->cronjobsDataRaw[$element][$getrid])) unset($this->cronjobsDataRaw[$element][$getrid]);
					$this->cronjobsDataRaw[$element] = array_merge($this->cronjobsDataRaw[$element], $resultArr);
				}
			}
		}
	}
	
	/**
	 * Fills up unconfigured dates (values just containing the asterisk) with default (all possible) values.
	 * 
	 * @param	integer		$month
	 * @param	integer		$year
	 */
	protected function getDefaultDateValues($month = 0, $year = 0) {
		$i = $max = 0;
		if ($month == 0) $month = date('n', $this->timebase);
		if ($year == 0) $year = date('Y', $this->timebase);
		
		foreach ($this->cronjobsDataRaw as $key => $asteriskedDate) {
			if ($asteriskedDate['0'] === '*') {
				$result = array();
				switch ($key) {
					case 'startMinute':	
						$i = 0;
						$max = 59;
						break;
					case 'startHour':
						$i = 0;
						$max = 23;
						break;
					case 'startDom':
						$i = 1;
						$max = intval(date('t', mktime(0, 0, 0, $month, 1, $year)));
						break;
					case 'startMonth':
						$i = 1;
						$max = 12;
						break;
					case 'startDow':
						$i = 0;
						$max = 6;
						break;
				}
				
				// set the default values.
				while ($i <= $max) {
					$result[] = $i;
					$i++;
				}
				
				$this->cronjobsDataRaw[$key] = $result;
			}
		}
	}
	
	/**
	 * Takes an array containing days of week and converts these days into days of month.
	 * 
	 * @param	integer		$month
	 * @param	integer		$year
	 */
	protected function getDaysFromDow($month = 0, $year = 0) {
		// if both startDom and startDow or only startDow contain only the asterisk, there's nothing to do here.
		if (isset($this->cronjobsDataRaw['startDom']['0']) && ($this->cronjobsDataRaw['startDom']['0'] === '*') && isset($this->cronjobsDataRaw['startDow']['0']) && ($this->cronjobsDataRaw['startDow']['0'] == '*')) {
			return;
		}
		// this may look dumb, but happens to be necessary.
		if (isset($this->cronjobsDataRaw['startDow']['0']) && ($this->cronjobsDataRaw['startDow']['0'] === '*')) {
			return;
		}
		
		if ($month == 0) $month = date('n', $this->timebase);
		if ($year == 0) $year = date('Y', $this->timebase);
		
		$configuredDows = array();
		
		// build an array containing all days of the given month.
		$i = 1;
		while ($i <= date('t', mktime(0, 0, 0, $month, 1, $year))) {
			$daysOfMonth[] = $i;
			$i++;
		}
		
		// for every day of the given month, we look up what day of week this will be.
		foreach ($daysOfMonth as $dayOfMonth) {
			// get the day of week as integer (0 = Sunday, 6 = Saturday).
			$dayOfWeek = date('w', mktime(0, 0, 0, $month, intval($dayOfMonth), $year));
			// add it to the target array if it is contained in the configured days of week array.
			$dowNum = array_search($dayOfWeek, $this->cronjobsDataRaw['startDow']);
			if ($dowNum !== false && $dayOfMonth <= count($daysOfMonth)) $configuredDows[] = $dayOfMonth;
		}
		$configuredDows = ArrayUtil::toIntegerArray($configuredDows);
		
		// considering the case that startDom is unconfigured, then only the dates found out above are significant;
		// else we must build the set union of both day configurations.
		if (isset($this->cronjobsDataRaw['startDom']['0']) && $this->cronjobsDataRaw['startDom']['0'] !== '*') {
			$configuredDows = array_merge($this->cronjobsDataRaw['startDom'], $configuredDows);
			$configuredDows = array_unique($configuredDows);
			sort($configuredDows);
		}
		
		// remember unmerged startDom in case this method is called once more from this CronjobsExec instance 
		// because days of week most likely will have changed then.
		if (count($this->cronjobsDataRaw['startDom'])) $this->startDom = $this->cronjobsDataRaw['startDom'];
		unset($this->cronjobsDataRaw['startDom']);
		$this->cronjobsDataRaw['startDom'] = $configuredDows;
	}
	
	/**
	 * Contains the custom algorithm needed for multiple execs.
	 */
	protected function getNextExecMultiple() {
		// process nextExec timestamp from the found date.
		$this->buildNextExec();
		// special treatment for execMultiple.
		//if (($this->nextExec == $this->timebase) && ()) {
		if ($this->nextExec == $this->timebase) {
			// get next minute, if available, else get next hour.
			$keyMinute = array_search($this->cronjobsDataResultDate['startMinute'], $this->cronjobsDataRaw['startMinute']);
			if ($keyMinute === count($this->cronjobsDataRaw['startMinute']) - 1) {
				$keyMinute = 0;
				// get next hour, if available, else get next day.
				$keyHour = array_search($this->cronjobsDataResultDate['startHour'], $this->cronjobsDataRaw['startHour']);
				if ($keyHour === count($this->cronjobsDataRaw['startHour']) - 1) {
					$keyHour = 0;
					// get next day, if available, else get next month.
					$keyDom = array_search($this->cronjobsDataResultDate['startDom'], $this->cronjobsDataRaw['startDom']);
					if ($keyDom === count($this->cronjobsDataRaw['startDom']) - 1) {
						$keyDom = 0;
						// get next month, if available, else get next year.
						$keyMonth = array_search($this->cronjobsDataResultDate['startMonth'], $this->cronjobsDataRaw['startMonth']);
						if ($keyMonth === count($this->cronjobsDataRaw['startMonth']) - 1) {
							$keyMonth = 0;
							
							$this->cronjobsDataResultDate['startYear'] = $this->cronjobsDataResultDate['startYear'] + 1;
							// BEWARE! the line above most likely bears an issue with leap years, this has still to be fixed!
						}
						else {
							$keyMonth = $keyMonth + 1;
						}
						$this->cronjobsDataResultDate['startMonth'] = $this->cronjobsDataRaw['startMonth'][$keyMonth];
					}
					else {
						$keyDom = $keyDom + 1;
					}
					$this->cronjobsDataResultDate['startDom'] = $this->cronjobsDataRaw['startDom'][$keyDom];
				}
				else {
					$keyHour = $keyHour + 1;
				}
				$this->cronjobsDataResultDate['startHour'] = $this->cronjobsDataRaw['startHour'][$keyHour];
			}
			else {
				$keyMinute = $keyMinute + 1;
			}
			$this->cronjobsDataResultDate['startMinute'] = $this->cronjobsDataRaw['startMinute'][$keyMinute];
		}
		// increment repetition counter.
		$this->countMultipleExecs++;
	}
	
	/**
	 * Builds the nextExec timestamp from the cronjobsDataResultDate array.
	 */
	protected function buildNextExec() {
		$this->nextExec = mktime($this->cronjobsDataResultDate['startHour'],
					$this->cronjobsDataResultDate['startMinute'],
					0, // seconds are not being considered.
					$this->cronjobsDataResultDate['startMonth'],
					$this->cronjobsDataResultDate['startDom'],
					$this->cronjobsDataResultDate['startYear']);
	}
}
?>