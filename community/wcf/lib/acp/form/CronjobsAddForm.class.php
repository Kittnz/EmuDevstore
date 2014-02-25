<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/cronjobs/CronjobEditor.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');

/**
 * Shows the cronjobs add form.
 *
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class CronjobsAddForm extends ACPForm {
	// system
	public $templateName = 'cronjobsAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.cronjobs.add';
	public $neededPermissions = 'admin.system.cronjobs.canAddCronjob';
	
	// parameters
	public $classPath = '';
	public $packageID = PACKAGE_ID;
	public $description = '';
	public $execMultiple = 0;
	public $startMinute = '*';
	public $startHour = '*';
	public $startDom = '*';
	public $startMonth = '*';
	public $startDow = '*';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['classPath'])) $this->classPath = StringUtil::trim($_POST['classPath']);
		if (isset($_POST['description'])) $this->description = StringUtil::trim($_POST['description']);
		if (isset($_POST['execMultiple'])) $this->execMultiple = intval($_POST['execMultiple']);
		if (isset($_POST['startMinute'])) $this->startMinute = StringUtil::replace(' ', '', $_POST['startMinute']);
		if (isset($_POST['startHour'])) $this->startHour = StringUtil::replace(' ', '', $_POST['startHour']);
		if (isset($_POST['startDom'])) $this->startDom = StringUtil::replace(' ', '', $_POST['startDom']);
		if (isset($_POST['startMonth'])) $this->startMonth = StringUtil::replace(' ', '', $_POST['startMonth']);
		if (isset($_POST['startDow'])) $this->startDow = StringUtil::replace(' ', '', $_POST['startDow']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate class path
		if (empty($this->classPath)) {
			throw new UserInputException('classPath');
		}
		try {
			$package = new Package($this->packageID);
			if (!@file_exists(FileUtil::getRealPath(WCF_DIR.$package->getDir().$this->classPath))) {
				throw new UserInputException('classPath', 'doesNotExist');
			}
		}
		catch (SystemException $e) {
			throw new UserInputException('classPath', 'doesNotExist');
		}
		
		try {
			CronjobEditor::validate($this->startMinute, $this->startHour, $this->startDom, $this->startMonth, $this->startDow);
		} 
		catch (SystemException $e) {
			// extract field name
			$fieldName = '';
			if (preg_match("/cronjob attribute '(.*)'/", $e->getMessage(), $match)) {
				$fieldName = $match[1];
			}
			
			throw new UserInputException($fieldName, 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save cronjob
		CronjobEditor::create($this->classPath, $this->packageID, $this->description, $this->execMultiple, $this->startMinute, $this->startHour, $this->startDom, $this->startMonth, $this->startDow);
		$this->saved();
		
		// reset values
		$this->classPath = $this->description = '';
		$this->execMultiple = 0;
		$this->startMinute = $this->startHour = $this->startDom = $this->startMonth = $this->startDow = '*';
		
		// show success.
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'classPath' => $this->classPath,
			'description' => $this->description,
			'execMultiple' => $this->execMultiple,
			'startMinute' => $this->startMinute,
			'startHour' => $this->startHour,
			'startDom' => $this->startDom,
			'startMonth' => $this->startMonth,
			'startDow' => $this->startDow,
			'action' => 'add'
		));
	}
}
?>