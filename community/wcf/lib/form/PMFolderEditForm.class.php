<?php
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');

/**
 * Shows the folder edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class PMFolderEditForm extends AbstractForm {
	public $color = '';
	public $colors = array();
	public $folderName = '';
	public $folderNames = array();
	public $rename = false;
	public $add = false;
	public $delete = 0;
	public $templateName = 'pmFolderEdit';
	public $folders = array();
	public static $availableColors = array('yellow', 'red', 'green', 'blue', 'white');
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['delete'])) $this->delete = intval($_GET['delete']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['folderNames']) && is_array($_POST['folderNames'])) $this->folderNames = ArrayUtil::trim($_POST['folderNames'], false);
		if (isset($_POST['folderName'])) $this->folderName = StringUtil::trim($_POST['folderName']);
		if (isset($_POST['rename'])) $this->rename = $_POST['rename'];
		if (isset($_POST['add'])) $this->add = $_POST['add'];
		if (isset($_POST['color'])) $this->color = $_POST['color'];
		if (isset($_POST['colors'])  && is_array($_POST['colors'])) $this->colors = $_POST['colors'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->add) {
			// color
			if (!in_array($this->color, self::$availableColors)) {
				$this->color = reset(self::$availableColors);
			}
			
			if (empty($this->folderName)) {
				throw new UserInputException('folderName');
			}
			if (count(PMFolderList::getUserFolders()) >= WCF::getUser()->getPermission('user.pm.maxFolders')) {
				throw new UserInputException('folderName', 'tooManyFolders');
			}
		}
		else if ($this->rename) {
			foreach ($this->folderNames as $folderID => $folderName) {
				if (empty($folderName)) {
					throw new UserInputException('folderName'.$folderID);
				}
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->delete) {
			// delete folder
			$sql = "DELETE FROM	wcf".WCF_N."_pm_folder
				WHERE		folderID = ".$this->delete."
						AND userID = ".WCF::getUser()->userID;
			WCF::getDB()->sendQuery($sql);
			
			// move messages to inbox
			$sql = "UPDATE	wcf".WCF_N."_pm_to_user
				SET	folderID = 0
				WHERE	recipientID = ".WCF::getUser()->userID."
					AND folderID = ".$this->delete;
			WCF::getDB()->registerShutdownUpdate($sql);			
			
			WCF::getTpl()->assign('success', 'delete');
		}
		else if ($this->add) {
			// add folder
			$sql = "INSERT INTO	wcf".WCF_N."_pm_folder
						(userID, folderName, color)
				VALUES		(".WCF::getUser()->userID.", '".escapeString($this->folderName)."', '".escapeString($this->color)."')";
			WCF::getDB()->sendQuery($sql);
			$this->folderName = '';
			WCF::getTpl()->assign('success', 'add');
		}
		else if ($this->rename) {
			// rename folders
			foreach ($this->folderNames as $folderID => $folderName) {
				if (!isset($this->colors[$folderID]) || !in_array($this->colors[$folderID], self::$availableColors)) {
					$this->colors[$folderID] = reset(self::$availableColors);
				}
				
				$sql = "UPDATE	wcf".WCF_N."_pm_folder
					SET	folderName = '".escapeString($folderName)."',
						color = '".escapeString($this->colors[$folderID])."'
					WHERE	folderID = ".intval($folderID)."
						AND userID = ".WCF::getUser()->userID;
				WCF::getDB()->sendQuery($sql);
			}
			WCF::getTpl()->assign('success', 'rename');
		}
		$this->saved();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->folders = PMFolderList::getUserFolders();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTpl()->assign(array(
			'folderName' => $this->folderName,
			'folderNames' => $this->folderNames,
			'folders' => $this->folders,
			'color' => $this->color,
			'colors' => $this->colors,
			'availableColors' => self::$availableColors
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}

		// check permission
		WCF::getUser()->checkPermission('user.pm.canUsePm');
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (!count($_POST) && $this->delete) {
			$this->submit();
		}
		
		parent::show();
	}
}
?>