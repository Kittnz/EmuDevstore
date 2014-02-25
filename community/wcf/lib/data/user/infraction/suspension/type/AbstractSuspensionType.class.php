<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/infraction/suspension/type/SuspensionType.class.php');

/**
 * Provides default implementations for suspension types.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	data.user.infraction.suspension.type
 * @category 	Community Framework (commercial)
 */
abstract class AbstractSuspensionType implements SuspensionType {
	/**
	 * suspension type data
	 *
	 * @var	array
	 */
	public $data = array();

	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function readFormParameters() {}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function validate() {}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function getData() {
		return array();
	}
	
	/**
	 * @see SuspensionType::readFormParameters()
	 */
	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	 * @see SuspensionType::assignVariables()
	 */
	public function assignVariables() {}
	
	/**
	 * @see SuspensionType::getTemplateName()
	 */
	public function getTemplateName() {
		return '';
	}
}
?>