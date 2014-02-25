<?php
/**
 * Determines the location of a user. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	data.user.usersOnline
 * @category 	Community Framework (commercial)
 */
class UsersOnlineLocation {
	protected static $locations = null;
	protected $locationObjects = array();
	
	/**
	 * Returns a object of a location class.
	 * 
	 * @param	array		$location
	 * @param	Location
	 */
	protected function getLocationObject($location) {
		if (!isset($this->locationObjects[$location['locationName']])) {
			// get path
			$path = '';
			if (empty($location['packageDir'])) {
				$path = WCF_DIR;
			}
			else {						
				$path = FileUtil::getRealPath(WCF_DIR.$location['packageDir']);
			}
			
			require_once($path.$location['classPath']);
			$this->locationObjects[$location['locationName']] = new $location['className']();
		}
		
		return $this->locationObjects[$location['locationName']];
	}
	
	/**
	 * Gets current location, if given user is the active user.
	 * 
	 * @param	array		$user
	 */
	protected function getCurrentLocation(&$user) {
		if ($user['userID']) {
			if ($user['userID'] == WCF::getUser()->userID) {
				$user['requestURI'] = WCF::getSession()->requestURI;
				$user['requestMethod'] = WCF::getSession()->requestMethod;
			}
		}
		else if (isset($user['sessionID']) && $user['sessionID'] == WCF::getSession()->sessionID) {
			$user['requestURI'] = WCF::getSession()->requestURI;
			$user['requestMethod'] = WCF::getSession()->requestMethod;
		}
		
		return $user;
	}
	
	/**
	 * Caches location information.
	 * 
	 * @param	array		$user
	 */
	public function cacheLocation($user) {
		// get cache
		if (self::$locations == null) {
			self::$locations = WCF::getCache()->get('pageLocations-'.PACKAGE_ID);
		}
		
		$this->getCurrentLocation($user);
		
		foreach (self::$locations as $location) {
			if (!empty($location['classPath'])) {
				if (preg_match('~'.$location['locationPattern'].'~i', $user['requestURI'], $match)) {
					$locationObj = $this->getLocationObject($location);
					$locationObj->cache($location, $user['requestURI'], $user['requestMethod'], $match);
					break;
				}
			}
		}
		
		return $user;
	}
	
	/**
	 * Caches location information.
	 * 
	 * @param	array		$users
	 */
	public function cacheLocations(&$users) {
		foreach ($users as $key => $user) {
			$users[$key] = $this->cacheLocation($user);
		}
	}
	
	/**
	 * Gets location information.
	 * 
	 * @param	array		$users
	 */
	public function getLocations(&$users) {
		foreach ($users as $key => $user) {
			$users[$key]['location'] = $this->getLocation($user);
		}
	}
	
	/**
	 * Gets location information.
	 * 
	 * @param	array		$user
	 * @return	string		location
	 */
	public function getLocation($user) {
		$userLocation = '';
		
		$this->getCurrentLocation($user);
		
		foreach (self::$locations as $location) {
			if (preg_match('~'.$location['locationPattern'].'~i', $user['requestURI'], $match)) {
				if (!empty($location['classPath'])) {
					$locationObj = $this->getLocationObject($location);
					$userLocation = $locationObj->get($location, $user['requestURI'], $user['requestMethod'], $match);
				}
				else {
					$userLocation = WCF::getLanguage()->get($location['locationName'], array('SID_ARG_1ST' => SID_ARG_1ST, 'SID_ARG_2ND' => SID_ARG_2ND));
				}
				
				break;
			}
		}
		
		return $userLocation;
	}
}
?>