<?php
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineSortedList.class.php');

/**
 * Shows the users online page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	page
 * @category 	Community Framework (commercial)
 */
class UsersOnlinePage extends SortablePage {
	public $defaultSortField = 'lastActivityTime';
	public $defaultSortOrder = 'DESC';
	public $templateName = 'usersOnline';
	public $usersOnlineSortedList;
	
	/**
	 * Creates a new UsersOnlinePage object.
	 */
	public function __construct() {
		$this->usersOnlineSortedList = new UsersOnlineSortedList();
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['detailedSpiderList'])) {
			$this->usersOnlineSortedList->detailedSpiderList = intval($_REQUEST['detailedSpiderList']);
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'username':
			case 'lastActivityTime':
			case 'requestURI':
				break;
				
			case 'ipAddress':
			case 'userAgent':
				if (WCF::getUser()->getPermission('admin.general.canViewIpAddress')) break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->usersOnlineSortedList->sqlOrderBy = 'session.'.$this->sortField.' '.$this->sortOrder;
		$this->usersOnlineSortedList->getUsersOnline();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->usersOnlineSortedList->users,
			'guests' => $this->usersOnlineSortedList->guests,
			'spiders' => (WCF::getUser()->getPermission('user.usersOnline.canViewRobots') ? $this->usersOnlineSortedList->spiders : array()),
			'canViewIpAddress' => WCF::getUser()->getPermission('admin.general.canViewIpAddress'),
			'detailedSpiderList' => $this->usersOnlineSortedList->detailedSpiderList,
			'usersOnlineMarkings' => $this->usersOnlineSortedList->getUsersOnlineMarkings()
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		WCF::getUser()->checkPermission('user.usersOnline.canView');
		
		if (MODULE_USERS_ONLINE != 1) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
}
?>