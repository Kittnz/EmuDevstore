<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/language/Language.class.php');
}

/**
 * Contains date-related functions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class DateUtil {
	public static $enableDaylightSavingTime = null;
	public static $timezone = null;
	public static $useStrftime = null;
	public static $osIsWindows = false;
	
	protected static $windowsCodes = array(
		//'%C' => '', not supported by windows
		'%D' => '%m/%d/%y',
		'%e' => '%#d',
		//'%g' => '', not supported
		//'%G' => '', not supported
		'%h' => '%b',
		'%n' => "\n",
		'%r' => '%p', // not supported but '%p' is okay
		'%R' => '%H:%M', // test this
		'%t' => "\t",
		'%T' => '%H:%M:%S',
		//'%u' => '', not supported
		//'%V' => '', not supported
	);
	protected static $timezoneMap = array(
		-12 => 'Pacific/Kwajalein',
		-11 => 'Pacific/Midway',
		-10 => 'Pacific/Honolulu',
		-9.5 => 'Pacific/Gambier',
		-9 => 'America/Anchorage',
		-8 => 'America/Los_Angeles',
		-7 => 'America/Chihuahua',
		-6 => 'America/Mexico_City',
		-5 => 'America/New_York', //America/Lima',
		-4.5 => 'America/Caracas',
		-4 => 'America/New_York',
		-3.5 => 'America/St_Johns',
		-3 => 'America/Argentina/Buenos_Aires',
		-2 => 'Atlantic/Bermuda',
		-1 => 'Atlantic/Azores',
		0 => 'Europe/London',
		1 => 'Europe/Berlin',
		2 => 'Africa/Cairo',
		3 => 'Europe/Moscow',
		3.5 => 'Asia/Tehran',
		4 => 'Asia/Dubai',
		4.5 => 'Asia/Kabul',
		5 => 'Asia/Ashgabat',
		5.5 => 'Asia/Calcutta',
		5.75 => 'Asia/Kathmandu', // unsupported?
		6 => 'Asia/Dhaka',
		6.5 => 'Asia/Rangoon',
		7 => 'Asia/Bangkok',
		8 => 'Asia/Singapore',
		8.75 => 'Australia/West',
		9 => 'Asia/Seoul',
		9.5 => 'Australia/Adelaide',
		10 => 'Australia/Melbourne',
		10.5 => 'Australia/Lord_Howe',
		11 => 'Asia/Magadan',
		11.5 => 'Pacific/Norfolk',
		12 => 'Pacific/Auckland',
		12.75 => 'Pacific/Chatham',
		13 => 'Pacific/Enderbury',
		14 => 'Pacific/Kiritimati'
	);
	
	/**
	 * Loads the timezone und daylight saving time configuration.
	 */
	public static function init() {
		if (self::$timezone !== null) {
			return;
		}
		
		// get time zone
		if (WCF::getUser()->timezone !== null) {
			self::$timezone = WCF::getUser()->timezone;
		}
		else if (defined('TIMEZONE')) {
			self::$timezone = TIMEZONE;
		}
		else {
			// default timezone is utc
			self::$timezone = 0;
		}
		
		// set default time zone; necessary for daylight saving option
		if (function_exists('date_default_timezone_set')) { // since php5.1
			@date_default_timezone_set(self::$timezoneMap[self::$timezone]);
		}
		
		// get daylight saving time config
		if (WCF::getUser()->enableDaylightSavingTime !== null) {
			self::$enableDaylightSavingTime = WCF::getUser()->enableDaylightSavingTime;
		}
		else if (defined('ENABLE_DAYLIGHT_SAVING_TIME')) {
			self::$enableDaylightSavingTime = ENABLE_DAYLIGHT_SAVING_TIME;
		}
		else {
			self::$enableDaylightSavingTime = 0;
		}
		
		// examine use of strftime function
		self::$useStrftime = (WCF::getLanguage()->get('wcf.global.dateMethod') == 'strftime');
		
		// get operation system
		if (preg_match('/^WIN/i', PHP_OS)) {
			self::$osIsWindows = true;
		}
	}
	
	/**
	 * Takes and formats a timestamp.
	 *
	 * @param 	integer 	$timestamp
	 * @param 	string 		$format
	 * @param	boolean		$replaceToday	replace date of today and yesterday 
	 * @param	boolean		$useStrftime
	 * @return	string				formatted date
	 */
	public static function formatDate($format = null, $timestamp = null, $replaceToday = false, $useStrftime = false) {
		self::init();
		
		if ($format === null) {
			$format = WCF::getLanguage()->get('wcf.global.dateFormat'.((($useStrftime || self::$useStrftime) && Language::$dateFormatLocalized) ? 'Localized' : ''));
		}
		if ($timestamp === null) {
			$timestamp = TIME_NOW;	
		}
		
		$daylightSavingTime = $timezone = 0; 
		
		// check summer time
		if (self::$enableDaylightSavingTime == 1 && @date('I', $timestamp) == 1) {
			$daylightSavingTime = 3600;
		}
		
		// check time zone
		$timezone = 3600 * self::$timezone;
		
		// change time stamp
		$timestamp += $daylightSavingTime + $timezone;
		
		// replace date of today and yesterday 
		if ($replaceToday) {
			if (gmdate('Ymd', $timestamp) == gmdate('Ymd', TIME_NOW + $daylightSavingTime + $timezone)) {
				// date of today
				$format = WCF::getLanguage()->get('wcf.global.dateFormatToday');
			}
			else if (gmdate('Ymd', $timestamp) == gmdate('Ymd', TIME_NOW - 86400 + $daylightSavingTime + $timezone)) {
				// date of yesterday
				$format = WCF::getLanguage()->get('wcf.global.dateFormatYesterday');
			}
		}
		
		if ($useStrftime || self::$useStrftime) {
			if (self::$osIsWindows) $format = self::getWindowsCodes($format);
			$result = gmstrftime($format, $timestamp);
		
			// convent result to utf-8 if necessary (windows)
			if (CHARSET == 'UTF-8') {
				if (!StringUtil::isASCII($result) && !StringUtil::isUTF8($result)) {
					$result = StringUtil::convertEncoding('ISO-8859-1', CHARSET, $result);
				}
			}
			// convent result from utf-8 if necessary (unix)
			else if (CHARSET != 'UTF-8') {
				if (!StringUtil::isASCII($result) && StringUtil::isUTF8($result)) {
					$result = StringUtil::convertEncoding('UTF-8', CHARSET, $result);
				}
			}
		}
		else {
			$result = gmdate($format, $timestamp);
		}
		
		return trim($result);
	}
	
	/**
	 * @see DateUtil::formatDate()
	 * Uses 'wcf.global.timeFormat' as default date format.
	 */
	public static function formatTime($format = null, $timestamp = null, $replaceToday = false, $useStrftime = false) {
		self::init();
		
		if ($format === null) {
			$format = WCF::getLanguage()->get('wcf.global.timeFormat'.((($useStrftime || self::$useStrftime) && Language::$dateFormatLocalized) ? 'Localized' : ''));
		}
		
		return self::formatDate($format, $timestamp, $replaceToday, $useStrftime);
	}
	
	/**
	 * @see DateUtil::formatDate()
	 * Uses 'wcf.global.shortTimeFormat' as default date format.
	 */
	public static function formatShortTime($format = null, $timestamp = null, $replaceToday = false, $useStrftime = false) {
		self::init();
		
		if ($format === null) {
			$format = WCF::getLanguage()->get('wcf.global.shortTimeFormat'.((($useStrftime || self::$useStrftime) && Language::$dateFormatLocalized) ? 'Localized' : ''));
		}
		
		return self::formatDate($format, $timestamp, $replaceToday, $useStrftime);
	}
	
	/**
	 * Windows' strftime function doesn't support correctly all available codes.
	 * This function replaces unsupported codes by a windows alternative, if possible.
	 * 
	 * @param 	string		$format
	 * @return 	string
	 */
	public static function getWindowsCodes($format) {
		return str_replace(array_keys(self::$windowsCodes), array_values(self::$windowsCodes), $format);
	}
	
	/**
	 * Converts given timestamp, which matches the active timezone and daylight saving settings, to a UTC timestamp.
	 * 
	 * @param	integer		$timestamp
	 * @return	integer
	 */
	public static function getUTC($timestamp) {
		self::init();
		
		$daylightSavingTime = 0; 
		// check summer time
		if (self::$enableDaylightSavingTime == 1 && @date('I', $timestamp) == 1) {
			$daylightSavingTime = 3600;
		}
		
		return $timestamp - ($daylightSavingTime + 3600 * self::$timezone);
	}
	
	/**
	 * Converts given UTC timestamp into local, which matches the active timezone and daylight saving settings.
	 * 
	 * @param	integer		$timestamp	UTC timestamp
	 * @return	integer				local timestamp
	 */
	public static function getLocalTimestamp($timestamp) {
		self::init();
		
		$daylightSavingTime = 0; 
		// check summer time
		if (self::$enableDaylightSavingTime == 1 && @date('I', $timestamp) == 1) {
			$daylightSavingTime = 3600;
		}
		
		return $timestamp + ($daylightSavingTime + 3600 * self::$timezone);
	}
	
	/**
	 * Returns the active timezone for given specific timestamp.
	 * 
	 * @param	integer		$timestamp
	 * @return	float
	 */
	public static function getTimezone($timestamp = TIME_NOW) {
		self::init();
		
		$timezone = self::$timezone;
		
		if (self::$enableDaylightSavingTime == 1 && @date('I', $timestamp) == 1) {
			$timezone++;
		}
		
		return $timezone;
	}
	
	/**
	 * Check the given timestamp if it is in summer or winter time
	 *
	 * @param 	integer 	$timestamp 	timestamp to check
	 * @return 	boolean 			if timestamp is in daylight saving time
	 */
	public static function isDayLightSavingTime($timestamp = TIME_NOW) {
		self::init();
		
		// check summer time
		if (self::$enableDaylightSavingTime == 1 && @date('I', $timestamp) == 1) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get the current age from a given birthday
	 *
	 * @param	string	$birthday	enter the birthday in the format "YYYY-MM-DD"
	 * @param	integer	$timestamp	the timestamp to compare
	 * @return	integer	$age		the age to get
	 */
	public static function getAge($birthday, $timestamp = null) {
		if ($timestamp === null) $timestamp = DateUtil::getLocalTimestamp(TIME_NOW);
		
		$year = $month = $day = $age = 0;
		$dateValues = explode('-', $birthday);
		if (isset($dateValues[0])) $year = intval($dateValues[0]);
		if (isset($dateValues[1])) $month = intval($dateValues[1]);
		if (isset($dateValues[2])) $day = intval($dateValues[2]);
		
		$yearCompare = intval(gmdate('Y', $timestamp));
		$monthCompare = intval(gmdate('n', $timestamp));
		$dayCompare = intval(gmdate('j', $timestamp));
		
		$age = $yearCompare - $year;
		if ($monthCompare < $month) $age--;
		elseif ($monthCompare == $month && $dayCompare < $day) $age--;
		
		return $age;
	}
	
	/**
	 * Makes a diff between two unix timestamps.
	 * 
	 * @param	integer		$start
	 * @param	integer		$end
	 * @param	string		$output
	 * @return	mixed
	 */
	public static function diff($start, $end, $output = 'string') {
		// get values
		list($startYear, $startMonth, $startDay, $startHour, $startMinute, $startSecond) = explode('-', gmdate('Y-n-j-G-i-s', $start));
		list($endYear, $endMonth, $endDay, $endHour, $endMinute, $endSecond) = explode('-', gmdate('Y-n-j-G-i-s', $end));
		
		// seconds
		$secondDiff = $endSecond - $startSecond;
		if ($startSecond > $endSecond) {
			$secondDiff += 60;
			$startMinute++;
		}
		// minutes
		$minuteDiff = $endMinute - $startMinute;
		if ($startMinute > $endMinute) {
			$minuteDiff += 60;
			$startHour++;
		}
		// hours
		$hourDiff = $endHour - $startHour;
		if ($startHour > $endHour) {
			$hourDiff += 24;
			$startDay++;
		}
		
		// days
		if ($endMonth > $startMonth || $endYear > $startYear) {
			if ($startDay > $endDay) {
				$daysThisMonth = gmdate('t', $start);
				$dayDiff = ($daysThisMonth - $startDay) + $endDay;
				$startMonth++;
			}
			else {
				$dayDiff = $endDay - $startDay;
			}
		}
		else {
			$dayDiff = $endDay - $startDay;
		}

		// months
		$monthDiff = $endMonth - $startMonth;
		if ($startMonth > $endMonth) {
			$monthDiff += 12;
			$startYear++;
		}

		// years
		$yearDiff = $endYear - $startYear;
		
		// result
		if ($output == 'string') {
			$string = '';
			if ($yearDiff > 0) {
				$string .= $yearDiff.' '.WCF::getLanguage()->get('wcf.global.date.year'.($yearDiff > 1 ? 's' : ''));
			}
			if ($monthDiff > 0) {
				if (!empty($string)) $string .= ', ';
				$string .= $monthDiff.' '.WCF::getLanguage()->get('wcf.global.date.month'.($monthDiff > 1 ? 's' : ''));
			}
			if ($dayDiff > 0) {
				if (!empty($string)) $string .= ', ';
				$string .= $dayDiff.' '.WCF::getLanguage()->get('wcf.global.date.day'.($dayDiff > 1 ? 's' : ''));
			}
			if ($hourDiff > 0) {
				if (!empty($string)) $string .= ', ';
				$string .= $hourDiff.' '.WCF::getLanguage()->get('wcf.global.date.hour'.($hourDiff > 1 ? 's' : ''));
			}
			if ($minuteDiff > 0) {
				if (!empty($string)) $string .= ', ';
				$string .= $minuteDiff.' '.WCF::getLanguage()->get('wcf.global.date.minute'.($minuteDiff > 1 ? 's' : ''));
			}
			if ($secondDiff > 0) {
				if (!empty($string)) $string .= ', ';
				$string .= $secondDiff.' '.WCF::getLanguage()->get('wcf.global.date.second'.($secondDiff > 1 ? 's' : ''));
			}
			return $string;
		}
		else {
			return array(
				'years' => $yearDiff,
				'months' => $monthDiff,
				'days' => $dayDiff,
				'hours' => $hourDiff,
				'minutes' => $minuteDiff,
				'seconds' => $secondDiff
			);
		}
	}
}
?>