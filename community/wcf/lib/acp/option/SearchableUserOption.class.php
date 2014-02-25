<?php
/**
 * Any searchable option type should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
interface SearchableUserOption {
	/**
	 * Returns the html code for the search form element of this option.
	 * 
	 * @param	array		$optionData
	 * @return	string		html
	 */
	public function getSearchFormElement(&$optionData);
	
	/**
	 * Returns a condition for search sql query.
	 * 
	 * @param	array		$optionData
	 * @param	string		$value
	 * @param	boolean		$matchesExactly
	 * @return	mixed
	 */
	public function getCondition($optionData, $value, $matchesExactly = true);
}
?>