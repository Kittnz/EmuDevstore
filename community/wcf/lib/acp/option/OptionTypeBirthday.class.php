<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeDate.class.php');

/**
 * OptionTypeBirthday is an implementation of OptionType for birthday inputs.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeBirthday extends OptionTypeDate {
	protected $yearRequired = false;
	
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		if (!empty($optionData['required'])) $this->yearRequired = true;
		return parent::getFormElement($optionData);
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if (!empty($optionData['required'])) $this->yearRequired = true;
		return parent::getData($optionData, $newValue);
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		if (!empty($optionData['required'])) $this->yearRequired = true;
		parent::validate($optionData, $newValue);
		
		$this->getValue($newValue);
		
		if ($newValue['year'] || $newValue['month'] || $newValue['day']) {
			if (strlen($newValue['year']) == 2) {
				$newValue['year'] = '19'.$newValue['year'];
			}
			
			if ($newValue['year'] && ($newValue['year'] > gmdate('Y') || @gmmktime(0, 0, 0, $newValue['month'], $newValue['day'], $newValue['year']) > TIME_NOW)) {
				throw new UserInputException($optionData['optionName'], 'validationFailed');
			}
		}
	}
}
?>