<?php
// wcf imports
require_once(WCF_DIR.'lib/page/Page.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * This class provides default implementations for the Page interface.
 * This includes the call of the default event listeners for a page: readParameters, readData, assignVariables and show.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category 	Community Framework
 */
abstract class AbstractPage implements Page {
	/**
	 * Name of the template for the called page.
	 * 
	 * @var string
	 */
	public $templateName = '';
	
	/**
	 * Value of the given action parameter.
	 * 
	 * @var string
	 */
	public $action = '';
	
	/**
	 * Needed permissions to view this page.
	 * 
	 * @var string
	 */
	public $neededPermissions = '';
	
	/**
	 * Creates a new AbstractPage object.
	 * Calls the readParameters() and show() methods automatically.
	 */
	public function __construct() {
		// call default methods
		$this->readParameters();
		$this->show();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		// call readParameters event
		EventHandler::fireAction($this, 'readParameters');
		
		// read action parameter
		if (isset($_REQUEST['action'])) $this->action = $_REQUEST['action'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		// call readData event
		EventHandler::fireAction($this, 'readData');
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		// call assignVariables event
		EventHandler::fireAction($this, 'assignVariables');
		
		// assign parameters
		WCF::getTPL()->assign(array(
			'action' => $this->action,
			'templateName' => $this->templateName
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check permission
		if (!empty($this->neededPermissions)) WCF::getUser()->checkPermission($this->neededPermissions);
		
		// read data
		$this->readData();

		// assign variables
		$this->assignVariables();		
		
		// call show event
		EventHandler::fireAction($this, 'show');
		
		// show template
		if (!empty($this->templateName)) {
			WCF::getTPL()->display($this->templateName);
		}
	}
}
?>