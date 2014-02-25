<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * UserOptionOutputDate is an implementation of UserOptionOutput for the output of a date input.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputDate implements UserOptionOutput {
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		if ($optionData['optionType'] == 'birthday') {
			// show cake icon
			if (empty($value) || $value == '0000-00-00') return '';
			
			$age = 0;
			$date = self::splitDate($value);
			if ($date['year']) $age = self::calcAge($date['year'], $date['month'], $date['day']);
			
			if ($date['month'] == intval(DateUtil::formatDate('%m', null, false, true)) && $date['day'] == DateUtil::formatDate('%e', null, false, true)) {
				WCF::getTPL()->assign(array(
					'age' => $age,
					'username' => $user->username
				));
				return '<img src="'.StyleManager::getStyle()->getIconPath('birthdayS.png').'" alt="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.birthday').'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.user.profile.birthday').'" />';
			}
		}
		else {
			return $this->getOutput($user, $optionData, $value);
		}
	}
	
	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		return $this->getOutput($user, $optionData, $value);
	}

	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (empty($value) || $value == '0000-00-00') return '';
		
		$age = 0;
		$date = self::splitDate($value);
		
		// format date
		try {
			$dateString = DateUtil::formatDate(null, gmmktime(12, 1, 1, $date['month'], $date['day'], ($date['year'] ? $date['year'] : 2028)));
			if (!$date['year']) $dateString = StringUtil::replace('2028', '', $dateString);
		}
		catch (Exception $e) {
			// fallback for negative timestamps under windows before php 5.1.0
			$dateString = $value;
		}
		
		// calc age
		if ($date['year'] && $optionData['optionType'] == 'birthday') {
			$age = self::calcAge($date['year'], $date['month'], $date['day']);
		}
		return $dateString . ($age ? ' ('.$age.')' : '');
	}
	
	protected static function splitDate($value) {
		$year = $month = $day = 0;
		$optionValue = explode('-', $value);
		if (isset($optionValue[0])) $year = intval($optionValue[0]);
		if (isset($optionValue[1])) $month = intval($optionValue[1]);
		if (isset($optionValue[2])) $day = intval($optionValue[2]);
		
		return array('year' => $year, 'month' => $month, 'day' => $day);
	}
	
	protected static function calcAge($year, $month, $day) {
		$age = DateUtil::formatDate('%Y', null, false, true) - $year;
		if (intval(DateUtil::formatDate('%m', null, false, true)) < intval($month)) $age--;
		else if (intval(DateUtil::formatDate('%m', null, false, true)) == intval($month) && DateUtil::formatDate('%e', null, false, true) < intval($day)) $age--;
		
		return $age;
	}
}
?>