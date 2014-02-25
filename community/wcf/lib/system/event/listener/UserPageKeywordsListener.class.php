<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Finds search keywords in user profile fields.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserPageKeywordsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		foreach ($eventObj->categories as $categoryKey => $category) {
			foreach ($category['options'] as $optionKey => $option) {
				if ($option['optionType'] == 'text' && $option['outputClass'] == '' && $option['searchable'] == 1) {
					$values = preg_split('/\s*(?:,|;|&)\s*/', StringUtil::decodeHTML($option['optionValue']));
					$newValue = '';
					foreach ($values as $value) {
						if (!empty($newValue)) $newValue .= ', ';
						$newValue .= '<a href="index.php?form=MembersSearch&amp;values['.$option['optionName'].']='.StringUtil::encodeHTML(rawurlencode($value)).SID_ARG_2ND.'">'.StringUtil::encodeHTML($value).'</a>';
					}
					
					$eventObj->categories[$categoryKey]['options'][$optionKey]['optionValue'] = $newValue;
				}
			}
		}
	}
}
?>