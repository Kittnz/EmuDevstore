<?php
/**
 * EventHandler executes all registered actions for a specific event.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event
 * @category 	Community Framework
 */
class EventHandler {
	/**
	 * Registerd actions.
	 * 
	 * @var array
	 */
	protected static $actions = null;
	
	/**
	 * Registerd inherit actions.
	 * 
	 * @var array
	 */
	protected static $inheritedActions = null;
	
	/**
	 * Instances of registerd actions.
	 * 
	 * @var array
	 */
	protected static $actionsObjects = array();
	
	/**
	 * Instances of registerd inherit actions.
	 * 
	 * @var array
	 */
	protected static $inheritedActionsObjects = array();
	
	/**
	 * Instances of action objects.
	 * 
	 * @var array
	 */
	protected static $objects = array();
	
	/**
	 * Loads all registered actions of the active package.
	 */
	protected static function loadActions() {
		$environment = (class_exists('WCFACP') ? 'admin' : 'user');
		WCF::getCache()->addResource('eventListener-'.PACKAGE_ID, WCF_DIR.'cache/cache.eventListener-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderEventListener.class.php');
		$cache = WCF::getCache()->get('eventListener-'.PACKAGE_ID);
		if (isset($cache['actions'][$environment])) self::$actions = $cache['actions'][$environment];
		if (isset($cache['inheritedActions'][$environment])) self::$inheritedActions = $cache['inheritedActions'][$environment];
		unset($cache);
		if (!is_array(self::$actions)) self::$actions = array();
		if (!is_array(self::$inheritedActions)) self::$inheritedActions = array();
	}
	
	/**
	 * Executes all inherited listeners for the given event.
	 * 
	 * @param	mixed		$eventObj
	 * @param	string		$eventName
	 * @param	string		$className
	 * @param	string		$name
	 */	
	protected static function executeInheritedActions($eventObj, $eventName, $className, $name) {
		// create objects of the actions 
		if (!isset(self::$inheritedActionsObjects[$name]) || !is_array(self::$inheritedActionsObjects[$name])) {
			self::$inheritedActionsObjects[$name] = array();

			// get parent classes
			$familyTree = array();
			$member = (is_object($eventObj) ? get_class($eventObj) : $eventObj);
			while ($member != false) {
				$familyTree[] = $member;
				$member = get_parent_class($member);
			}

			foreach ($familyTree as $member) {
				if (isset(self::$inheritedActions[$member])) {
					$actions = self::$inheritedActions[$member];
					if (isset($actions[$eventName]) && count($actions[$eventName]) > 0) {                        
						foreach ($actions[$eventName] as $action) {
							if (isset(self::$inheritedActionsObjects[$name][$action['listenerClassName']])) continue;

							// get path to class file
							if (empty($action['packageDir'])) {
								$path = WCF_DIR;
							}
							else {						
								$path = FileUtil::getRealPath(WCF_DIR.$action['packageDir']);
							}
							$path .= $action['listenerClassFile'];
							
							// get class object
							if (isset(self::$objects[$path])) {
								$object = self::$objects[$path];
							}
							else {
								$object = null;
								// include class file of the action
								if (!class_exists($action['listenerClassName'])) {
									if (!file_exists($path)) {
										throw new SystemException("Unable to find class file '".$path."'", 11000);
									}
									require_once($path);
								}
								
								// instance action object
								if (!class_exists($action['listenerClassName'])) {
									throw new SystemException("Unable to find class '".$action['listenerClassName']."'", 11001);
								}
	
								$object = new $action['listenerClassName'];
								self::$objects[$path] = $object;	
							}
							
							if ($object !== null) self::$inheritedActionsObjects[$name][$action['listenerClassName']] = $object;
						}
					}
				}
			}
		}
		
		// execute actions
		foreach (self::$inheritedActionsObjects[$name] as $actionObj) {
			$actionObj->execute($eventObj, $className, $eventName);
		}
	}
	
	/**
	 * Executes all registered listeners for the given event.
	 * 
	 * @param	mixed		$eventObj
	 * @param	string		$eventName
	 */	
	public static function fireAction($eventObj, $eventName) {
		// get class name
		if (is_object($eventObj)) $className = get_class($eventObj);
		else $className = $eventObj;
		
		// load actions from cache if necessary
		if (self::$actions === null && self::$inheritedActions === null) {
			self::loadActions();	
		}
		
		// generate action name
		$name = self::generateKey($className, $eventName);

		// execute inherited actions first
		if (count(self::$inheritedActions) > 0) {
			self::executeInheritedActions($eventObj, $eventName, $className, $name);	
		}
		
		// create objects of the actions 
		if (!isset(self::$actionsObjects[$name]) || !is_array(self::$actionsObjects[$name])) {
			if (!isset(self::$actions[$name]) || !is_array(self::$actions[$name])) {
				// no action registered
				return false;
			}
		
			self::$actionsObjects[$name] = array();
			foreach (self::$actions[$name] as $action) {
				if (isset(self::$actionsObjects[$name][$action['listenerClassName']])) continue;

				// get path to class file
				if (empty($action['packageDir'])) {
					$path = WCF_DIR;
				}
				else {						
					$path = FileUtil::getRealPath(WCF_DIR.$action['packageDir']);
				}
				$path .= $action['listenerClassFile'];
				
				// get class object
				if (isset(self::$objects[$path])) {
					$object = self::$objects[$path];
				}
				else {
					// include class file of the action
					if (!class_exists($action['listenerClassName'])) {
						if (!file_exists($path)) {
							throw new SystemException("Unable to find class file '".$path."'", 11000);
						}
						require_once($path);
					}
					
					// instance action object
					if (!class_exists($action['listenerClassName'])) {
						throw new SystemException("Unable to find class '".$action['listenerClassName']."'", 11001);
					}
				
					$object = new $action['listenerClassName'];
					self::$objects[$path] = $object;	
				}
				
				self::$actionsObjects[$name][$action['listenerClassName']] = $object;
			}
		}
		
		// execute actions
		foreach (self::$actionsObjects[$name] as $actionObj) {
			$actionObj->execute($eventObj, $className, $eventName);
		}
	}
	
	/**
	 * Generates an unique name for an action.
	 * 
	 * @param	string		$className
	 * @param	string		$eventName
	 */
	public static function generateKey($className, $eventName) {
		return $eventName.'@'.$className;	
	}
}
?>