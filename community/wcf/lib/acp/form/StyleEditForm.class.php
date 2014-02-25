<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/StyleAddForm.class.php');

/**
 * Shows the style edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleEditForm extends StyleAddForm {
	public $activeMenuItem = 'wcf.acp.menu.link.style';
	public $neededPermissions = 'admin.style.canEditStyle';
	
	public $styleID = 0;
	public $xmlUpload;
	public $importedVariables = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['styleID'])) $this->styleID = intval($_REQUEST['styleID']);
		$this->style = new StyleEditor($this->styleID);
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		if (isset($_FILES['xmlUpload'])) $this->xmlUpload = $_FILES['xmlUpload'];
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'styleID' => $this->styleID,
			'style' => $this->style
		));
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// upload xml file
		if ($this->xmlUpload && $this->xmlUpload['error'] != 4) {
			if ($this->xmlUpload['error'] != 0) {
				throw new UserInputException('xmlUpload', 'uploadFailed');
			}
		
			try {
				$this->importedVariables = StyleEditor::readVariablesData(file_get_contents($this->xmlUpload['tmp_name']));
			}
			catch (SystemException $e) {
				throw new UserInputException('xmlUpload', 'importFailed');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// update style
		if ($this->style->isDefault) $this->enableStyle = 1;
		$finalVariables = ($this->importedVariables !== null ? $this->importedVariables : $this->getFinalVariables());
		$this->style->update($this->styleName, $finalVariables, $this->templatePackID, $this->styleDescription, $this->styleVersion, $this->styleDate, $this->image, $this->copyright, $this->license, $this->authorName, $this->authorURL, intval(!$this->enableStyle));
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.style.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
		$this->variables = $this->getFormVariables();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->styleName = $this->style->styleName;
			$this->templatePackID = $this->style->templatePackID;
			$this->variables = $this->getFormVariables();
			$this->authorName = $this->style->authorName;
			$this->copyright = $this->style->copyright;
			$this->styleVersion = $this->style->styleVersion;
			$this->styleDate = $this->style->styleDate;
			$this->license = $this->style->license;
			$this->styleDescription = $this->style->styleDescription;
			$this->authorURL = $this->style->authorURL;
			$this->image = $this->style->image;
			$this->enableStyle = intval(!$this->style->disabled);
		}
	}
	
	protected function getFormVariables() {
		$formVariables = $this->defaultVariables;
		$styleVariables = $this->style->getVariables();
		
		foreach ($styleVariables as $name => $value) {
			$formVariables[$name] = $value;
		}
		
		// split unit from size
		foreach ($this->sizeVariables as $name) {
			if (!empty($styleVariables[$name]) && preg_match('/(%|em|pt|px)$/', $styleVariables[$name], $match)) {
				$formVariables[$name.'.unit'] = $match[1];
				$formVariables[$name] = StringUtil::substring($styleVariables[$name], 0, strlen($match[1]) * -1);
			}
		}
		
		// split/create values
		// page.header.background.image.alignment
		if ($styleVariables['page.header.background.image.alignment']) {
			$split = explode(' ', $styleVariables['page.header.background.image.alignment']);
			if (count($split) == 2) {
				$formVariables['page.header.background.image.alignment.horizontal'] = $split[0];
				$formVariables['page.header.background.image.alignment.vertical'] = $split[1];
			}
		}
		
		// page.header.background.image.repeat
		if ($styleVariables['page.header.background.image.repeat'] == 'repeat' || $styleVariables['page.header.background.image.repeat'] == 'repeat-x') $formVariables['page.header.background.image.repeat.horizontal'] = 1;
		if ($styleVariables['page.header.background.image.repeat'] == 'repeat' || $styleVariables['page.header.background.image.repeat'] == 'repeat-y') $formVariables['page.header.background.image.repeat.vertical'] = 1;
		
		// page.background.image.attachment
		if ($styleVariables['page.background.image.attachment'] == 'fixed') $formVariables['page.background.image.fixed'] = 1;
		
		// page.background.image.alignment
		if ($styleVariables['page.background.image.alignment']) {
			$split = explode(' ', $styleVariables['page.background.image.alignment']);
			if (count($split) == 2) {
				$formVariables['page.background.image.alignment.horizontal'] = $split[0];
				$formVariables['page.background.image.alignment.vertical'] = $split[1];
			}
		}
		
		// page.background.image.repeat
		if ($styleVariables['page.background.image.repeat'] == 'repeat' || $styleVariables['page.background.image.repeat'] == 'repeat-x') $formVariables['page.background.image.repeat.horizontal'] = 1;
		if ($styleVariables['page.background.image.repeat'] == 'repeat' || $styleVariables['page.background.image.repeat'] == 'repeat-y') $formVariables['page.background.image.repeat.vertical'] = 1;
		
		// page.width*
		if ($styleVariables['page.width.min'] || $styleVariables['page.width.max']) $formVariables['page.width.mode'] = 'dynamic';
		else $formVariables['page.width.mode'] = 'static';
		
		// global.title.hide / buttons.small.caption.hide / buttons.large.caption.hide / menu.main.caption.hide
		$formVariables['global.title.show'] = empty($styleVariables['global.title.hide']);
		$formVariables['buttons.small.caption.show'] = empty($styleVariables['buttons.small.caption.hide']);
		$formVariables['buttons.large.caption.show'] = empty($styleVariables['buttons.large.caption.hide']);
		$formVariables['menu.main.caption.show'] = empty($styleVariables['menu.main.caption.hide']);
		
		// additional css
		if (!empty($styleVariables['user.additional.style.input1.use'])) {
			$formVariables['user.additional.style.input1'] = $styleVariables['user.additional.style.input1.use'];
			$formVariables['user.additional.style.input1.use'] = 1;
			
		}
		else $formVariables['user.additional.style.input1.use'] = 0;
		if (!empty($styleVariables['user.additional.style.input2.use'])) {
			$formVariables['user.additional.style.input2'] = $styleVariables['user.additional.style.input2.use'];
			$formVariables['user.additional.style.input2.use'] = 1;
		}
		else $formVariables['user.additional.style.input2.use'] = 0;
		
		// IE fixes
		if (!empty($styleVariables['user.MSIEFixes.IE6.use'])) {
			$formVariables['user.MSIEFixes.IE6'] = $styleVariables['user.MSIEFixes.IE6.use'];
			$formVariables['user.MSIEFixes.IE6.use'] = 1;
			
		}
		else $formVariables['user.MSIEFixes.IE6.use'] = 0;
		if (!empty($styleVariables['user.MSIEFixes.IE7.use'])) {
			$formVariables['user.MSIEFixes.IE7'] = $styleVariables['user.MSIEFixes.IE7.use'];
			$formVariables['user.MSIEFixes.IE7.use'] = 1;
		}
		else $formVariables['user.MSIEFixes.IE7.use'] = 0;
		if (!empty($styleVariables['user.MSIEFixes.IE8.use'])) {
			$formVariables['user.MSIEFixes.IE8'] = $styleVariables['user.MSIEFixes.IE8.use'];
			$formVariables['user.MSIEFixes.IE8.use'] = 1;
		}
		else $formVariables['user.MSIEFixes.IE8.use'] = 0;
		
		// font styles
		// global.title.font.style
		if ($styleVariables['global.title.font.weight'] == 'bold' && $styleVariables['global.title.font.style'] == 'italic') {
			$formVariables['global.title.font.style'] = 'bold italic';
		}
		else if ($styleVariables['global.title.font.weight'] == 'bold') {
			$formVariables['global.title.font.style'] = 'bold';
		}
		
		// page.title.font.style
		if ($styleVariables['page.title.font.weight'] == 'bold' && $styleVariables['page.title.font.style'] == 'italic') {
			$formVariables['page.title.font.style'] = 'bold italic';
		}
		else if ($styleVariables['page.title.font.weight'] == 'bold') {
			$formVariables['page.title.font.style'] = 'bold';
		}

		// menu.main.bar.show
		if (!isset($styleVariables['menu.main.bar.hide']) || $styleVariables['menu.main.bar.hide'] != 'transparent') $formVariables['menu.main.bar.hide'] = 0;
		else $formVariables['menu.main.bar.hide'] = 1;
		
		if (!isset($styleVariables['menu.main.bar.divider.show']) || $styleVariables['menu.main.bar.divider.show'] != '0') $formVariables['menu.main.bar.divider.show'] = 1;
		else $formVariables['menu.main.bar.divider.show'] = 0;
		
		// main menu alignment
		switch ($styleVariables['menu.main.position']) {
			case 'text-align:center;margin:0 auto':
				$formVariables['menu.main.position'] = 'center';
				break;
			case 'text-align:right;margin:0 0 0 auto':
				$formVariables['menu.main.position'] = 'right';
				break;
			default:
				$formVariables['menu.main.position'] = 'left';
		}
		
		// use variables
		foreach ($this->useVariables as $name => $variables) {
			foreach ($variables as $variable) {
				if ($formVariables[$variable]) {
					$formVariables[$name] = 1;
					break;
				}
			}
		}
		
		// format images
		foreach ($this->images as $image) {
			if (!empty($formVariables[$image])) {
				$isBackgroundImage = (strstr($image, 'background') ? true : false);
				
				// remove url()
				if ($isBackgroundImage) {
					$formVariables[$image] = preg_replace('/^url\("?(.*?)"?\)$/', '\\1', $formVariables[$image]);
				}
				
				// format path
				if (StringUtil::indexOf($formVariables[$image], ($isBackgroundImage ? '../' : '') . $formVariables['global.images.location']) === 0) {
					$formVariables[$image] = StringUtil::substring($formVariables[$image], StringUtil::length(($isBackgroundImage ? '../' : '') . $formVariables['global.images.location']));
				}
			}
		}
		
		return $formVariables;
	}
}
?>