<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/WarningEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/warning/UserWarningEditor.class.php');

/**
 * Shows the user warning form.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class UserWarnForm extends AbstractForm {
	// system
	public $templateName = 'userWarn';
	
	/**
	 * user id
	 * 
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user object
	 * 
	 * @var	User
	 */
	public $user;
	
	/**
	 * object id
	 * 
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * object type
	 * 
	 * @var	string
	 */
	public $objectType = '';
	
	/**
	 * warning object
	 * 
	 * @var	WarningObject
	 */
	public $object = null;
	
	/**
	 * list of predefined warnings
	 * 
	 * @var	array<Warning>
	 */
	public $warnings = array();
	
	// form parameters
	public $warningID = 0;
	public $title = '';
	public $points = 0;
	public $expiresWeek = 0;
	public $expiresDay = 0;
	public $expiresHour = 0;
	public $reason = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['objectID'])) $this->objectID = intval($_REQUEST['objectID']);
		if (isset($_REQUEST['objectType'])) $this->objectType = $_REQUEST['objectType'];
		if ($this->objectID != 0 && $this->objectType != '') {
			$this->object = Warning::getWarningObjectByID($this->objectType, $this->objectID);
			if ($this->object === null) {
				throw new IllegalLinkException();
			}
		}
		
		// get user
		$this->user = new UserSession($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		if ($this->user->getPermission('admin.user.infraction.canNotBeWarned')) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.infraction.error.canNotBeWarned', array('user' => $this->user)));
		}
		if ($this->object !== null) {
			// search existing warning
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_infraction_warning_to_user
				WHERE	packageID = ".PACKAGE_ID."
					AND objectID = ".$this->objectID."
					AND objectType = '".escapeString($this->objectType)."'
					AND userID = ".$this->userID;
			$warning = new UserWarning(null, WCF::getDB()->getFirstRow($sql));
			if ($warning->warningID) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.user.infraction.error.alreadyReported', array('warning' => $warning)));
			}
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['warningID'])) $this->warningID = intval($_POST['warningID']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['points'])) $this->points = abs(intval($_POST['points']));
		if (isset($_POST['expiresWeek'])) $this->expiresWeek = intval($_POST['expiresWeek']);
		if (isset($_POST['expiresDay'])) $this->expiresDay = intval($_POST['expiresDay']);
		if (isset($_POST['expiresHour'])) $this->expiresHour = intval($_POST['expiresHour']);
		if (isset($_POST['reason'])) $this->reason = StringUtil::trim($_POST['reason']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->warningID) {
			$warning = new Warning($this->warningID);
			if (!$warning->warningID) {
				$this->warningID = 0;
			} else {
				$this->title = $warning->title;
			}
		}
		
		if (!$this->warningID) {
			if (!WCF::getUser()->getPermission('admin.user.infraction.canWarnUserIndividual')) {
				throw new UserInputException('warningID');
			}
			
			if (empty($this->title)) {
				throw new UserInputException('title');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// create user warning
		$expires = ($this->expiresHour * 3600 + $this->expiresDay * 86400 + $this->expiresWeek * 86400 * 7);
		$userWarning = UserWarningEditor::create($this->userID, WCF::getUser()->userID, $this->warningID, $this->objectID, $this->objectType, $this->title, $this->points, ($expires ? TIME_NOW + $expires : 0), $this->reason);
		
		// check suspensions
		UserWarningEditor::checkWarnings($this->userID);
		
		// get language
		$languages = array(
			WCF::getLanguage()->getLanguageID() => WCF::getLanguage(),
			0 => WCF::getLanguage()
		);
		if (!isset($languages[$this->user->languageID])) {
			$languages[$this->user->languageID] = new Language($this->user->languageID);	
		}
		$languages[$this->user->languageID]->setLocale();
		
		// send pm
		require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
		PMEditor::create(0, array($this->user->userID => array('userID' => $this->user->userID, 'username' => $this->user->username)), array(), $languages[$this->user->languageID]->get('wcf.user.infraction.userWarning.message.subject'), $languages[$this->user->languageID]->getDynamicVariable('wcf.user.infraction.userWarning.message.text', array('title' => $this->title, 'reason' => $this->reason)), WCF::getUser()->userID, WCF::getUser()->username);
		$this->saved();
		
		// reset language
		WCF::getLanguage()->setLocale();
		
		// show success message and forward user
		$url = ($this->object !== null ? $this->object->getURL() : 'index.php?page=User&userID='.$this->userID) . SID_ARG_2ND_NOT_ENCODED;
		WCF::getTPL()->assign(array(
			'url' => $url,
			'message' => WCF::getLanguage()->get('wcf.user.infraction.userWarning.add.success'),
			'wait' => 5
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get predefined warnings
		$this->warnings = Warning::getWarnings();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userID' => $this->userID,
			'user' => $this->user,
			'objectID' => $this->objectID,
			'objectType' => $this->objectType,
			'warningID' => $this->warningID,
			'title' => $this->title,
			'points' => $this->points,
			'expiresWeek' => $this->expiresWeek,
			'expiresDay' => $this->expiresDay,
			'expiresHour' => $this->expiresHour,
			'reason' => $this->reason,
			'warnings' => $this->warnings,
			'object' => $this->object
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check permission
		WCF::getUser()->checkPermission('admin.user.infraction.canWarnUser');
		
		if (MODULE_USER_INFRACTION != 1) {
			throw new IllegalLinkException();
		}
		
		// show form
		parent::show();
	}
}
?>