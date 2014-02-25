<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserRankAddForm.class.php');
require_once(WCF_DIR.'lib/data/user/rank/UserRankEditor.class.php');

/**
 * Shows the user rank edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.rank
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserRankEditForm extends UserRankAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank';
	public $neededPermissions = 'admin.user.rank.canEditRank';
	
	public $rank;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['rankID'])) $this->rankID = intval($_REQUEST['rankID']);
		$this->rank = new UserRankEditor($this->rankID);
		if (!$this->rank->rankID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		AbstractForm::save();

		// update
		$this->rank->update($this->title, $this->image, $this->groupID, $this->neededPoints, $this->gender, $this->repeatImage);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'rankID' => $this->rankID,
			'rank' => $this->rank,
			'action' => 'edit'
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default value
			$this->groupID = $this->rank->groupID;
			$this->neededPoints = $this->rank->neededPoints;
			$this->gender = $this->rank->gender;
			$this->title = $this->rank->rankTitle;
			$this->image = $this->rank->rankImage;
			$this->repeatImage = $this->rank->repeatImage;
		}
	}
}
?>