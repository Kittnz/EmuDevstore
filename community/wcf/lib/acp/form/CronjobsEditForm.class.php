<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/CronjobsAddForm.class.php');

/**
 * Shows the cronjobs edit form.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class CronjobsEditForm extends CronjobsAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.cronjobs';
	public $neededPermissions = 'admin.system.cronjobs.canEditCronjob';
	
	/**
	 * cronjob id
	 * 
	 * @var	integer
	 */
	public $cronjobID = 0;
	
	/**
	 * cronjob editor object
	 *
	 * @var CronjobEditor
	 */
	public $cronjob;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['cronjobID'])) $this->cronjobID = intval($_REQUEST['cronjobID']);
		$this->cronjob = new CronjobEditor($this->cronjobID);
		if (!$this->cronjob->cronjobID) {
			throw new IllegalLinkException();
		}
		$this->packageID = $this->cronjob->packageID;
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		ACPForm::save();
		
		// update cronjob
		$this->cronjob->update($this->classPath, $this->packageID, $this->description, $this->execMultiple, $this->startMinute, $this->startHour, $this->startDom, $this->startMonth, $this->startDow);
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->classPath = $this->cronjob->classPath;
			$this->description = $this->cronjob->description;
			$this->execMultiple = $this->cronjob->execMultiple;
			$this->startMinute = $this->cronjob->startMinute;
			$this->startHour = $this->cronjob->startHour;
			$this->startDom = $this->cronjob->startDom;
			$this->startMonth = $this->cronjob->startMonth;
			$this->startDow = $this->cronjob->startDow;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'cronjobID' => $this->cronjobID,
			'action' => 'edit'
		));
	}
}
?>