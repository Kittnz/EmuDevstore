<?php
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnline.class.php');
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineLocation.class.php');

/**
 * Gets a sorted list of users online.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	data.user.usersOnline
 * @category 	Community Framework (commercial)
 */
class UsersOnlineSortedList extends UsersOnline {
	public $spiderList = null;
	public $users = array();
	public $guests = array();
	public $spiders = array();
	public $enableOwnView = true;
	public $detailedSpiderList = 0;
	public $enableUserAgentIcons = false;
	
	/**
	 * @see UsersOnline::getUsersOnline()
	 */
	public function getUsersOnline() {
		$this->enableUserAgentIcons = WCF::getUser()->getPermission('admin.general.canViewIpAddress');
		
		parent::getUsersOnline();
		
		$location = new UsersOnlineLocation();
		
		// cache location data
		$location->cacheLocations($this->users);
		if (USERS_ONLINE_SHOW_GUESTS) $location->cacheLocations($this->guests);
		if (USERS_ONLINE_SHOW_ROBOTS) $location->cacheLocations($this->spiders);
		
		// get location data
		$location->getLocations($this->users);
		if (USERS_ONLINE_SHOW_GUESTS) $location->getLocations($this->guests);
		if (USERS_ONLINE_SHOW_ROBOTS) $location->getLocations($this->spiders);
	}
	
	/**
	 * @see UsersOnline::handleRow()
	 */
	protected function handleRow($row, User $user) {
		if ($row['userID']) {
			if ($this->isVisible($row, $user)) {
				// username
				$row['username'] = $this->getFormattedUsername($row, $user);
				
				// get icon
				if ($this->enableUserAgentIcons) {
					$row['userAgentIcon'] = UsersOnlineUtil::getUserAgentIcon($row['userAgent']);
				}
				
				// add user
				$this->users[] = $row;
			}
		}
		else if ($row['spiderID']) {
			if (USERS_ONLINE_SHOW_ROBOTS) {
				// search engine robot
				if ($this->spiderList == null) {
					// get spider cache
					$this->spiderList = WCF::getCache()->get('spiders');
				}
				
				// get icon
				if ($this->enableUserAgentIcons) {
					$row['userAgentIcon'] = 'browserRobot';
				}
				
				if (isset($this->spiderList[$row['spiderID']])) {
					if ($this->detailedSpiderList) {
						$row['count'] = 1;
						$row['spiderName'] = $this->spiderList[$row['spiderID']]['spiderName'];
						$row['spiderURL'] = $this->spiderList[$row['spiderID']]['spiderURL'];
						$this->spiders[] = $row;
					}
					else {
						$identifier = $this->spiderList[$row['spiderID']]['spiderIdentifier'];
						
						if (!isset($this->spiders[$identifier])) {
							$row['count'] = 1;
							$row['spiderName'] = $this->spiderList[$row['spiderID']]['spiderName'];
							$row['spiderURL'] = $this->spiderList[$row['spiderID']]['spiderURL'];
							$this->spiders[$identifier] = $row;
						}
						else {
							$this->spiders[$identifier]['count']++;
							if ($row['lastActivityTime'] > $this->spiders[$identifier]['lastActivityTime']) {
								$this->spiders[$identifier]['lastActivityTime'] = $row['lastActivityTime'];
								$this->spiders[$identifier]['requestURI'] = $row['requestURI'];
								$this->spiders[$identifier]['requestMethod'] = $row['requestMethod'];
							}
						}
					}
				}
			}
		}
		else {
			// guest
			if (USERS_ONLINE_SHOW_GUESTS) {
				if (!isset($this->guestIpAddresses[$row['ipAddress']])) {
					// get icon
					if ($this->enableUserAgentIcons) {
						$row['userAgentIcon'] = UsersOnlineUtil::getUserAgentIcon($row['userAgent']);
					}
					
					// add guest
					$this->guests[] = $row;
					$this->guestIpAddresses[$row['ipAddress']] = true;
				}
			}
		}
	}
}
?>