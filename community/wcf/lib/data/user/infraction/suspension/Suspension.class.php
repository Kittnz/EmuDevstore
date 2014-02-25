<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a predefined suspension.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension
 * @category 	Community Framework (commercial)
 */
class Suspension extends DatabaseObject {
	/**
	 * list of available suspension types
	 * 
	 * @var	array<SuspensionType>
	 */
	public static $availableSuspensionTypes = null;

	/**
	 * Creates a new Suspension object.
	 *
	 * @param	integer		$suspensionID
	 * @param	array<mixed>	$row
	 */
	public function __construct($suspensionID, $row = null) {
		if ($suspensionID !== null) {
			$sql = "SELECT	suspension.*,
					(SELECT COUNT(*) FROM wcf".WCF_N."_user_infraction_suspension_to_user WHERE suspensionID = suspension.suspensionID) AS suspensions
				FROM	wcf".WCF_N."_user_infraction_suspension suspension
				WHERE	suspension.suspensionID = ".$suspensionID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the title of this suspension.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->title;
	}
	
	/**
	 * Returns a list of available suspension types.
	 * 
	 * @return	array<SuspensionType>
	 */
	public static function getAvailableSuspensionTypes() {
		if (self::$availableSuspensionTypes === null) {
			WCF::getCache()->addResource('suspensionTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.suspensionTypes-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderSuspensionTypes.class.php');
			$types = WCF::getCache()->get('suspensionTypes-'.PACKAGE_ID);
			foreach ($types as $type) {
				// get path to class file
				if (empty($type['packageDir'])) {
					$path = WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
				}
				$path .= $type['classFile'];
				
				// include class file
				if (!class_exists($type['className'])) {
					if (!file_exists($path)) {
						throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					require_once($path);
				}
				
				// instance object
				if (!class_exists($type['className'])) {
					throw new SystemException("Unable to find class '".$type['className']."'", 11001);
				}
				self::$availableSuspensionTypes[$type['suspensionType']] = new $type['className'];
			}
		}
		
		return self::$availableSuspensionTypes;
	}
	
	/**
	 * Returns the object of a suspension type.
	 * 
	 * @param	string		$suspensionType
	 * @return	SuspensionType
	 */
	public static function getSuspensionTypeObject($suspensionType) {
		$types = self::getAvailableSuspensionTypes();
		if (!isset($types[$suspensionType])) {
			throw new SystemException("Unknown suspension type '".$suspensionType."'", 11000);
		}
		
		return $types[$suspensionType];
	}
	
	/**
	 * Returns a list of suspensions.
	 *
	 * @return 	array<Suspension>
	 */
	public static function getSuspensions() {
		$suspensions = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_infraction_suspension
			ORDER BY	title";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$suspensions[$row['suspensionID']] = new Suspension(null, $row);
		}
		
		return $suspensions;
	}
}
?>