<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * UserOptionOutputSelectOptions is an implementation of UserOptionOutput for the output of a date input.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputSelectOptions implements UserOptionOutput {
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getOutput($user, $optionData, $value);
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
		$result = self::getResult($optionData, $value);
		if ($result === null) {
			return '';
		}
		else if (is_array($result)) {
			$output = '';
			foreach ($result as $resultValue) {
				if (!empty($output)) $output .= "<br />\n";
				$output .= WCF::getLanguage()->get($resultValue);
			}
			
			return $output;
		}
		else {
			return WCF::getLanguage()->get($result);
		}
	}
	
	protected static function getResult($optionData, $value) {
		$options = OptionUtil::parseSelectOptions($optionData['selectOptions']);
		
		// multiselect
		if (StringUtil::indexOf($value, "\n") !== false) {
			$values = explode("\n", $value);
			$result = array();
			foreach ($values as $value) {
				if (isset($options[$value])) {
					$result[] = $options[$value];
				}
			}
			
			return $result;
		}
		else {
			if (!empty($value) && isset($options[$value])) return $options[$value];
			return null;
		}
	}
}
?>