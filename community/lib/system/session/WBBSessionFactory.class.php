<?php
// wbb imports
require_once(WBB_DIR.'lib/system/session/WBBSession.class.php');
require_once(WBB_DIR.'lib/data/user/WBBUserSession.class.php');
require_once(WBB_DIR.'lib/data/user/WBBGuestSession.class.php');

// wcf imports
require_once(WCF_DIR.'lib/system/session/CookieSessionFactory.class.php');

/**
 * WBBSessionFactory extends the CookieSessionFactory class with forum specific functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.session
 * @category 	Burning Board
 */
class WBBSessionFactory extends CookieSessionFactory {
	protected $guestClassName = 'WBBGuestSession';
	protected $userClassName = 'WBBUserSession';
	protected $sessionClassName = 'WBBSession';
}
?>