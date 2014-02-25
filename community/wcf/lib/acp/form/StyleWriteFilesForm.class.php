<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');

/**
 * Shows the style refresh form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleWriteFilesForm extends ACPForm {
	// system
	public $templateName = 'styleWriteFiles';
	public $activeMenuItem = 'wcf.acp.menu.link.style.writeFiles';
	public $neededPermissions = 'admin.style.canEditStyle';
	
	/**
	 * list of styles
	 *
	 * @var	array<Style>
	 */
	public $styles = array();
	
	/**
	 * list of style ids
	 *
	 * @var	array<integer>
	 */
	public $styleIDArray = array();
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['styleIDArray']) && is_array($_POST['styleIDArray'])) $this->styleIDArray = ArrayUtil::toIntegerArray($_POST['styleIDArray']);
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		foreach ($this->styleIDArray as $styleID) {
			$style = new StyleEditor($styleID);
			if ($style->styleID) {
				$style->writeStyleFile();
			}
		}
		$this->saved();
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get available styles
		$this->styles = Style::getStyles();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'styles' => $this->styles
		));
	}
}
?>