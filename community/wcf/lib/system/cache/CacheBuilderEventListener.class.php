<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Caches the event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderEventListener implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array(
			'actions' => array('user' => array(), 'admin' => array()),
			'inheritedActions' => array('user' => array(), 'admin' => array())
		);
		
		// get all listeners and filter options with low priority
		$sql = "SELECT		listener.*, package.packageDir
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_event_listener listener
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = listener.packageID)
			WHERE 		listener.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$row['listenerClassName'] = StringUtil::getClassName($row['listenerClassFile']);
			// distinguish between inherited actions and non-inherited actions
			if (!$row['inherit']) {
				$data['actions'][$row['environment']][EventHandler::generateKey($row['eventClassName'], $row['eventName'])][] = $row;
			}
			else {
				if (!isset($data['inheritedActions'][$row['environment']][$row['eventClassName']])) $data['inheritedActions'][$row['eventClassName']] = array();
				$data['inheritedActions'][$row['environment']][$row['eventClassName']][$row['eventName']][] = $row;	
			}
		}
		
		// sort data by class name
		foreach ($data['actions'] as $environment => $listenerMap) {
			foreach ($listenerMap as $key => $listeners) {
				uasort($data['actions'][$environment][$key], array('CacheBuilderEventListener', 'sortListeners'));
			}
		}
		
		foreach ($data['inheritedActions'] as $environment => $listenerMap) {
			foreach ($listenerMap as $class => $listeners) {
				foreach ($listeners as $key => $val) {
					uasort($data['inheritedActions'][$environment][$class][$key], array('CacheBuilderEventListener', 'sortListeners'));
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Sorts the event listeners alphabetically.
	 */
	public static function sortListeners($listenerA, $listenerB) {
		if ($listenerA['niceValue'] < $listenerB['niceValue']) {
			return -1;
		}
		else if ($listenerA['niceValue'] > $listenerB['niceValue']) {
			return 1;
		}
		else {
			return strcmp($listenerA['listenerClassName'], $listenerB['listenerClassName']);
		}	
	}
}
?>