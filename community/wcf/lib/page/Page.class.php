<?php
/**
 * All page classes should implement this interface. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category 	Community Framework
 */
interface Page {
	/**
	 * Reads the given parameters.
	 */
	public function readParameters();
	
	/**
	 * Reads/Gets the data to be displayed on this page.
	 */
	public function readData();
	
	/**
	 * Assigns variables to the template engine.
	 */
	public function assignVariables();
	
	/**
	 * Shows the requested page.
	 */
	public function show();
}
?>