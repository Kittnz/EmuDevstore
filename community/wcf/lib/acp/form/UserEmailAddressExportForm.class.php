<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Shows the export user mail addresses form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserEmailAddressExportForm extends ACPForm {
	public $templateName = 'userEmailAddressExport';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = 'admin.user.canMailUser';
	
	public $fileType = 'csv';
	public $userIDs = '';
	public $separator = ',';
	public $textSeparator = '"'; 
	public $users = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') $this->fileType = $_POST['fileType'];
		if (isset($_POST['userIDs'])) $this->userIDs = implode(',', ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs'])));
		if (isset($_POST['separator'])) $this->separator = $_POST['separator'];
		if (isset($_POST['textSeparator'])) $this->textSeparator = $_POST['textSeparator'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->userIDs)) throw new IllegalLinkException();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// send content type
		header('Content-Type: text/'.$this->fileType.'; charset='.CHARSET);
		header('Content-Disposition: attachment; filename="export.'.$this->fileType.'"');
		
		if ($this->fileType == 'xml') {
			echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n<addresses>\n";
		}
		
		// get users
		$sql = "SELECT		email
			FROM		wcf".WCF_N."_user
			WHERE		userID IN (".$this->userIDs.")
			ORDER BY	email";
		$result = WCF::getDB()->sendQuery($sql);
		$i = 0; $j = WCF::getDB()->countRows($result) - 1;
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($this->fileType == 'xml') echo "<address><![CDATA[".StringUtil::escapeCDATA($row['email'])."]]></address>\n";
			else echo $this->textSeparator . $row['email'] . $this->textSeparator . ($i < $j ? $this->separator : '');
			$i++;
		}
		
		if ($this->fileType == 'xml') {
			echo "</addresses>";
		}
		
		UserEditor::unmarkAll();
		$this->saved();
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get marked user ids
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if (is_array($markedUsers)) $this->userIDs = implode(',', $markedUsers);
			if (empty($this->userIDs)) throw new IllegalLinkException();
		}
		
		$this->users = User::getUsers($this->userIDs);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'separator' => $this->separator,
			'textSeparator' => $this->textSeparator,
			'fileType' => $this->fileType
		));
	}
}
?>