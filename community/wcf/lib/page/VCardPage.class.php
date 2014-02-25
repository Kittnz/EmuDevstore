<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/VCard.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

/**
 * Shows the vcard of a user.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	page
 * @category 	Community Framework
 */
class VCardPage extends AbstractPage {
	/**
	 * user id
	 *
	 * @var integer
	 */
	public $userID = 0;
	
	/**
	 * user information
	 *
	 * @var UserProfile
	 */
	public $user = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check permission
		WCF::getUser()->checkPermission('user.profile.canView');
		
		// get user
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new UserProfile($this->userID);
		if (!$this->user->userID || !$this->user->canViewProfile()) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();
		
		$vCard = new VCard($this->user);
		header('Content-Type:text/x-vcard; charset='.CHARSET);
		$filename = (preg_match('/^[a-z0-9_]+$/i', $this->user->username)) ? $this->user->username : $this->userID;
		header('Content-Disposition: attachment; filename="'.$filename.'.vcf"');
		header('Content-Length: '.strlen($vCard->getContent()));
		echo $vCard->getContent();
		exit;
	}
}
?>