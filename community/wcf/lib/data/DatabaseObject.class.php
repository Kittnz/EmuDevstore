<?php
/**
 * Abstract class for all data holder classes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
class DatabaseObject {
	private static $sortBy;
	private static $sortOrder;
	protected $data;
	
	/**
	 * Creates a new instance of the DatabaseObject class.
	 * Stores object data.
	 * 
	 * @param	array		$data
	 */
	public function __construct($data) {
		$this->handleData($data);
	}

	/**
	 * Stores the data of a database row.
	 * 
	 * @param	array		$data
	 */
	protected function handleData($data) {
		$this->data = $data;
	}

	/**
	 * Returns the value of a variable in object data.
	 * 
	 * @param	string		$name
	 * @return	mixed		value
	 */
	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		else {
			return null;
		}
	}
	
	/**
	 * Sorts a list of database objects.
	 *
	 * @param	array<DatabaseObject>	$objects
	 * @param	mixed			$sortBy
	 * @param	string			$sortOrder
	 * @return	boolean
	 */
	public static function sort(&$objects, $sortBy, $sortOrder = 'ASC') {
		self::$sortBy = (!is_array($sortBy) ? array($sortBy) : $sortBy);
		self::$sortOrder = (!is_array($sortOrder) ? array($sortOrder) : $sortOrder);
		return uasort($objects, array('DatabaseObject', 'compareObjects'));
	}
	
	/**
	 * Compares to database objects.
	 *
	 * @param	DatabaseObject		$objectA
	 * @param	DatabaseObject		$objectB
	 * @return	float
	 */
	private static function compareObjects($objectA, $objectB) {
		foreach (self::$sortBy as $key => $sortBy) {
			$sortOrder = (isset(self::$sortOrder[$key]) ? self::$sortOrder[$key] : 'ASC');
			if (is_numeric($objectA->$sortBy) && is_numeric($objectB->$sortBy)) {
				if ($objectA->$sortBy > $objectB->$sortBy) {
					return ($sortOrder == 'ASC' ? 1 : 0);
				}
				else if ($objectA->$sortBy < $objectB->$sortBy) {
					return ($sortOrder == 'ASC' ? 0 : 1);
				}
			}
			else {
				if ($sortOrder == 'ASC') {
					$result =  strcoll($objectA->$sortBy, $objectB->$sortBy);
				}
				else {
					$result = strcoll($objectB->$sortBy, $objectA->$sortBy);
				}
				
				if ($result != 0.0) {
					return $result;
				}
			}
		}
		
		return 0;
	}
}
?>