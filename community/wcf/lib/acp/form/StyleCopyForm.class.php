<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/StyleEditForm.class.php');

/**
 * Shows the style copy form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleCopyForm extends StyleEditForm {
	public $copy = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['copy'])) $this->copy = intval($_REQUEST['copy']);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'copy'
		));
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save style
		$finalVariables = $this->getFinalVariables();
		$this->style = StyleEditor::create($this->styleName, $finalVariables, $this->templatePackID, $this->styleDescription, $this->styleVersion, $this->styleDate, $this->image, $this->copyright, $this->license, $this->authorName, $this->authorURL, intval(!$this->enableStyle));
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.style.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
?>