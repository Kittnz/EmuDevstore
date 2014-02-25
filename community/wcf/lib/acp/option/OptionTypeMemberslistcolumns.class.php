<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/option/OptionTypeUseroptions.class.php');
require_once(WCF_DIR.'lib/acp/option/OptionTypeMemberslistsortfield.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * OptionTypeMemberslistcolumns lets you configure the displayed columns in the members list.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class OptionTypeMemberslistcolumns extends OptionTypeUseroptions {
	protected static $selectedColumns = array();
	public $staticColumns = array('username' => 'wcf.user.username', 'avatar' => 'wcf.user.avatar', 'registrationDate' => 'wcf.user.registrationDate', 'lastActivity' => 'wcf.user.lastActivity', 'language' => 'wcf.user.language');
	public $templateName = 'optionTypeMemberslistColumns';
	
	/**
	 * Creates a new OptionTypeMemberslistcolumns object.
	 * Calls the construct event.
	 */
	public function __construct() {
		if (MODULE_AVATAR != 1) {
			unset($this->staticColumns['avatar']);
		}

		// call construct event
		EventHandler::fireAction($this, 'construct');
	}
	
	protected function getUserOptions(&$optionData) {
		$this->readCache();
		$options = $this->getCategoryOptions('profile');
		$options = array_merge($this->staticColumns, $options);
		
		// sort options
		self::$selectedColumns = explode(',', $optionData['optionValue']);
		uksort($options, array('self', 'compareOptions'));
		
		// update sort field options
		$selectedOptions = explode(',', $optionData['optionValue']);
		$selectOptions = '';
		foreach ($selectedOptions as $selectedOption) {
			if (isset($options[$selectedOption])) {
				$selectOptions .= $selectedOption . ':' . $options[$selectedOption] . "\n";
			}
		}
		
		OptionTypeMemberslistsortfield::$selectOptions = StringUtil::trim($selectOptions);
		
		return $options;
	}
	
	protected static function compareOptions($optionA, $optionB) {
		$keyA = array_search($optionA, self::$selectedColumns);
		$keyB = array_search($optionB, self::$selectedColumns);
		
		if ($keyA !== false && $keyB !== false) {
			if ($keyA < $keyB) return -1;
			else return 1;
		}
		else if ($keyA !== false) {
			return -1;
		}
		else if ($keyB !== false) {
			return 1;
		}
		else {
			return 0;
		}
	}
}
?>