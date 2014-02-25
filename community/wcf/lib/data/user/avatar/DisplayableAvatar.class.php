<?php
/**
 * Any displayable avatar type should implement this function.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.avatar
 * @category 	Community Framework
 */
interface DisplayableAvatar {
	/**
	 * Returns the url to this avatar.
	 * 
	 * @return	string
	 */
	public function getURL();
	
	/**
	 * Returns the html code to display this avatar.
	 * 
	 * @return	string
	 */
	public function __toString();
	
	/**
	 * Scales the avatar to a specific maximum height.
	 * 
	 * @param	integer		$maxHeight
	 */
	public function setMaxHeight($maxHeight);
	
	/**
	 * Scales the avatar to a specific maximum size.
	 * 
	 * @param	integer		$maxWidth
	 * @param	integer		$maxHeight
	 */
	public function setMaxSize($maxWidth, $maxHeight);
	
	/**
	 * Returns the width of this avatar.
	 *
	 * @return	integer
	 */
	public function getWidth();
	
	/**
	 * Returns the height of this avatar.
	 *
	 * @return	integer
	 */
	public function getHeight();
}
?>