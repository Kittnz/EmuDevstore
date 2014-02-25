<?php
/**
 * Builds a sql query 'where' condition.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category 	Community Framework
 */
class ConditionBuilder {
	protected $conditions;
	protected $addWhereKeyword = true;
	
	/**
	 * Creates a new ConditionBuilder object.
	 *
	 * @param	string		$addWhereCommand
	 */
	public function __construct($addWhereKeyword = true) {
		$this->addWhereKeyword = $addWhereKeyword;
	}
	
	/**
	 * Adds a new condition.
	 * 
	 * @param	string		$condition
	 */
	public function add($condition) {
		if (!empty($this->conditions)) $this->conditions .= " AND ";
		else $this->conditions = ($this->addWhereKeyword ? " WHERE " : '');
		
		$this->conditions .= $condition;
	}
	
	/**
	 * Returns the build condition.
	 * 
	 * @return	string
	 */
	public function get() {
		return $this->conditions;
	}
}
?>