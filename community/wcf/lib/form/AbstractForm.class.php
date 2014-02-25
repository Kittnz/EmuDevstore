<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/form/Form.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * This class provides default implementations for the Form interface.
 * This includes the default event listener for a form: readFormParameters, validate, save.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category 	Community Framework
 */
abstract class AbstractForm extends AbstractPage implements Form {
	/**
	 * Name of error field.
	 *
	 * @var string
	 */
	public $errorField = '';
	
	/**
	 * Name of error type.
	 *
	 * @var string
	 */
	public $errorType = '';
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		try {
			$this->validate();
			// no errors
			$this->save();
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		// call readFormParameters event
		EventHandler::fireAction($this, 'readFormParameters');
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// call validate event
		EventHandler::fireAction($this, 'validate');
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// call save event
		EventHandler::fireAction($this, 'save');
	}
	
	/**
	 * Calls the 'saved' event after the successful call of the save method.
	 * This functions won't called automatically. You must do this manually, if you inherit AbstractForm.
	 */
	protected function saved() {
		EventHandler::fireAction($this, 'saved');
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		if (count($_POST) || count($_FILES)) {
			$this->submit();
		}
		
		parent::readData();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign default variables
		WCF::getTPL()->assign(array(
			'errorField' => $this->errorField,
			'errorType' => $this->errorType
		));
	}
}
?>