<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * UserOptionOutputNewlineToBreak is an implementation of UserOptionOutput for an image.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputImage implements UserOptionOutput {
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getOutput($user, $optionData, $value);
	}
	
	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		if (empty($value)) return '';
		
		return '<img src="'.StringUtil::encodeHTML($value).'" alt="" style="max-width: 50px; max-height: 50px" />';
	}
	
	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (empty($value)) return '';
		
		return '<img src="'.StringUtil::encodeHTML($value).'" alt="" />';
	}
}
?>