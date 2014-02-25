<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a predefined warning.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.warning
 * @category 	Community Framework (commercial)
 */
class Warning extends DatabaseObject {
	/**
	 * list of available warning object types
	 * 
	 * @var	array<WarningObjectType>
	 */
	public static $availableWarningObjectTypes = null;

	/**
	 * Creates a new Warning object.
	 *
	 * @param	integer		$warningID
	 * @param	array<mixed>	$row
	 */
	public function __construct($warningID, $row = null) {
		if ($warningID !== null) {
			$sql = "SELECT	warning.*,
					(SELECT COUNT(*) FROM wcf".WCF_N."_user_infraction_warning_to_user WHERE warningID = warning.warningID) AS warnings
				FROM	wcf".WCF_N."_user_infraction_warning warning
				WHERE	warning.warningID = ".$warningID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns the title of this warning.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->title;
	}
	
	/**
	 * Returns a list of warnings.
	 *
	 * @return 	array<Warning>
	 */
	public static function getWarnings() {
		$warnings = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_infraction_warning
			ORDER BY	title";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$warnings[$row['warningID']] = new Warning(null, $row);
		}
		
		return $warnings;
	}
	
	/**
	 * Gets warning objects by their ids.
	 * 
	 * @param	string		$objectType
	 * @param	mixed		$objectID
	 * @return	mixed
	 */
	public static function getWarningObjectByID($objectType, $objectID) {
		// get warning object type object
		$typeObject = null;
		try {
			$typeObject = self::getWarningObjectTypeObject($objectType);
		}
		catch (SystemException $e) {
			return null;
		}
		
		// get warning objects
		return $typeObject->getObjectByID($objectID);
	}
	
	/**
	 * Returns the object of a warning object type.
	 * 
	 * @param	string		$objectType
	 * @return	WarningObjectType
	 */
	public static function getWarningObjectTypeObject($objectType) {
		$types = self::getAvailableWarningObjectTypes();
		if (!isset($types[$objectType])) {
			throw new SystemException("Unknown warning object type '".$objectType."'", 11000);
		}
		
		return $types[$objectType];
	}
	
	/**
	 * Returns a list of available warning object types.
	 * 
	 * @return	array<WarningObjectType>
	 */
	public static function getAvailableWarningObjectTypes() {
		if (self::$availableWarningObjectTypes === null) {
			WCF::getCache()->addResource('warningObjectTypes-'.PACKAGE_ID, WCF_DIR.'cache/cache.warningObjectTypes-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderWarningObjectTypes.class.php');
			$types = WCF::getCache()->get('warningObjectTypes-'.PACKAGE_ID);
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
				self::$availableWarningObjectTypes[$type['objectType']] = new $type['className'];
			}
		}
		
		return self::$availableWarningObjectTypes;
	}
}
?>