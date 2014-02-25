<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/group/GroupOptionType.class.php');

/**
 * GroupOptionTypeGroups generates a select-list of all available user groups.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.group
 * @category 	Community Framework
 */
class GroupOptionTypeGroups implements GroupOptionType {
	/**
	 * @see OptionType::getFormElement()
	 */
	public function getFormElement(&$optionData) {
		$optionData['divClass'] = 'formRadio'; 
		$optionData['isOptionGroup'] = true; 
		if (!isset($optionData['optionValue'])) {
			if (isset($optionData['defaultValue'])) $optionData['optionValue'] = $optionData['defaultValue'];
			else $optionData['optionValue'] = '';
		}
		
		// get selected group
		$selectedGroups = explode(',', $optionData['optionValue']);
		
		// get all groups
		$groups = Group::getAllGroups();
		
		// generate html
		$html = '';
		foreach ($groups as $groupID => $group) {
			$html .= '<label><input type="checkbox" name="values['.StringUtil::encodeHTML($optionData['optionName']).'][]" value="'.$groupID.'" '.(in_array($groupID, $selectedGroups) ? 'checked="checked" ' : '').'/> '.StringUtil::encodeHTML($group).'</label>';
		}
		
		return $html;
	}
	
	/**
	 * @see OptionType::validate()
	 */
	public function validate($optionData, $newValue) {
		// get all groups
		$groups = Group::getAllGroups();
		
		// get new value
		if (!is_array($newValue)) $newValue = array();
		$selectedGroups = ArrayUtil::toIntegerArray($newValue);
		
		// check groups
		foreach ($selectedGroups as $groupID) {
			if (!isset($groups[$groupID])) {
				throw new UserInputException($optionData['optionName'], 'validationFailed');
			}
		}
	}
	
	/**
	 * @see OptionType::getData()
	 */
	public function getData($optionData, $newValue) {
		if (!is_array($newValue)) $newValue = array();
		$newValue = ArrayUtil::toIntegerArray($newValue);
		sort($newValue, SORT_NUMERIC);
		return implode(',', $newValue);
	}
	
	/**
	 * @see GroupOptionType::merge()
	 */
	public function merge($values) {
		$result = array();
		foreach ($values as $value) {
			$value = explode(',', $value);
			$result = array_merge($result, $value);
		}
		
		$result = array_unique($result);

		return implode(',', $result);
	}
}
?>