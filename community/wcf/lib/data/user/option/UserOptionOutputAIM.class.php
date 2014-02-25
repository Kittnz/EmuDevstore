<?php
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputICQ.class.php');

/**
 * UserOptionOutputAIM is an implementation of UserOptionOutput for the output of an AOL instant messenger name.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.user.messenger
 * @subpackage	data.user.option
 * @category 	Community Framework (commercial)
 */
class UserOptionOutputAIM extends UserOptionOutputICQ {
	protected $type = 'aim';
}
?>