<?php
/**
 * Abstract class for a list of database objects.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
abstract class DatabaseObjectList {
	/**
	 * sql offset
	 *
	 * @var integer
	 */
	public $sqlOffset = 0;
	
	/**
	 * sql limit
	 *
	 * @var integer
	 */
	public $sqlLimit = 20;
	
	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = '';
	
	/**
	 * sql conditions
	 *
	 * @var string
	 */
	public $sqlConditions = '';
	
	/**
	 * sql select parameters
	 *
	 * @var string
	 */
	public $sqlSelects = '';
	
	/**
	 * sql select joins
	 *
	 * @var string
	 */
	public $sqlJoins = '';
	
	/**
	 * Counts the number of objects.
	 * 
	 * @return	integer
	 */
	public abstract function countObjects();
	
	/**
	 * Reads the objects from database.
	 */
	public abstract function readObjects();
	
	/**
	 * Returns the objects of the list.
	 * 
	 * @return	array<DatabaseObject>
	 */
	public abstract function getObjects();
}
?>