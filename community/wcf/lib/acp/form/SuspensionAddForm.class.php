<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/SuspensionEditor.class.php');

/**
 * Shows the suspension add form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class SuspensionAddForm extends ACPForm {
	// system
	public $templateName = 'suspensionAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.user.infraction.suspension.add';
	public $neededPermissions = 'admin.user.infraction.canAddSuspension';
	
	// parameters
	public $title = '';
	public $points = 0;
	public $expiresWeek = 0;
	public $expiresDay = 0;
	public $expiresHour = 0;
	public $suspensionType = '';
	public $send = false;
	
	/**
	 * list of available suspension types
	 *
	 * @var array<SuspensionType>
	 */
	public $availableSuspensionTypes = array();
	
	/**
	 * suspension type object
	 * 
	 * @var	SuspensionType
	 */
	public $suspensionTypeObject = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get available suspension types
		$this->availableSuspensionTypes = Suspension::getAvailableSuspensionTypes();
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['points'])) $this->points = abs(intval($_POST['points']));
		if (isset($_POST['expiresWeek'])) $this->expiresWeek = intval($_POST['expiresWeek']);
		if (isset($_POST['expiresDay'])) $this->expiresDay = intval($_POST['expiresDay']);
		if (isset($_POST['expiresHour'])) $this->expiresHour = intval($_POST['expiresHour']);
		if (isset($_POST['suspensionType'])) $this->suspensionType = $_POST['suspensionType'];
		if (isset($_POST['send'])) $this->send = (boolean)$_POST['send'];
		
		// get type object
		if ($this->suspensionType && isset($this->availableSuspensionTypes[$this->suspensionType])) {
			$this->suspensionTypeObject = $this->availableSuspensionTypes[$this->suspensionType];
		}
		if ($this->suspensionTypeObject !== null && $this->send) {
			$this->suspensionTypeObject->readFormParameters();
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		
		// points
		if (empty($this->points)) {
			throw new UserInputException('points');
		}
		
		// type
		if (!isset($this->availableSuspensionTypes[$this->suspensionType])) {
			throw new UserInputException('suspensionType');
		}
		$this->suspensionTypeObject->validate();
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		try {
			// send message or save as draft
			if ($this->send) {
				$this->validate();
				// no errors
				$this->save();
			}
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		SuspensionEditor::create($this->title, $this->points, $this->suspensionType, $this->suspensionTypeObject->getData(), ($this->expiresHour * 3600 + $this->expiresDay * 86400 + $this->expiresWeek * 86400 * 7));
		$this->saved();
		
		// reset values
		$this->title = $this->suspensionType = '';
		$this->points = $this->expiresWeek = $this->expiresDay = $this->expiresHour = 0;
		$this->suspensionTypeObject = null;
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		if ($this->suspensionTypeObject !== null) {
			$this->suspensionTypeObject->assignVariables();
		}
		WCF::getTPL()->assign(array(
			'title' => $this->title,
			'points' => $this->points,
			'expiresWeek' => $this->expiresWeek,
			'expiresDay' => $this->expiresDay,
			'expiresHour' => $this->expiresHour,
			'suspensionType' => $this->suspensionType,
			'availableSuspensionTypes' => $this->availableSuspensionTypes,
			'suspensionTypeObject' => $this->suspensionTypeObject,
			'action' => 'add'
		));
	}
}
?>