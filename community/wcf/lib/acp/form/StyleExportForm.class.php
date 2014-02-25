<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');

/**
 * Shows the style export form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleExportForm extends ACPForm {
	public $templateName = 'styleExport';
	public $activeMenuItem = 'wcf.acp.menu.link.style';
	public $neededPermissions = 'admin.style.canExportStyle';
	
	public $styleID = 0;
	public $style;
	
	public $exportTemplates = 0;
	public $exportImages = 0;
	public $exportIcons = 0;
	
	public $canExportTemplates = false;
	public $canExportIcons = false;
	public $templatePackName = '';
	public $iconsLocation = '';
	public $imagesLocation = '';
	
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
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->exportImages = $this->exportTemplates = $this->exportIcons = 0;
		if (isset($_POST['exportTemplates'])) $this->exportTemplates = intval($_POST['exportTemplates']);
		if (isset($_POST['exportImages'])) $this->exportImages = intval($_POST['exportImages']);
		if (isset($_POST['exportIcons'])) $this->exportIcons = intval($_POST['exportIcons']);
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// get style filename
		$filename = str_replace(' ', '-', preg_replace('/[^a-z0-9 _-]/', '', StringUtil::toLowerCase($this->style->styleName)));
		
		// send headers
		header('Content-Type: application/x-gzip; charset='.CHARSET);
		header('Content-Disposition: attachment; filename="'.$filename.'-style.tgz"');
		
		// export style
		$this->style->export($this->exportTemplates, $this->exportImages, $this->exportIcons);
		$this->saved();
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if ($this->style->templatePackID) {
			require_once(WCF_DIR.'lib/data/template/TemplatePack.class.php');
			$templatePack = new TemplatePack($this->style->templatePackID);
			if ($templatePack->templatePackID) {
				$this->canExportTemplates = true;
			}
		}
		$variables = $this->style->getVariables();
		if (!empty($variables['global.icons.location']) && $variables['global.icons.location'] != 'icons/') $this->canExportIcons = true;
		
		$this->imagesLocation = $variables['global.images.location'];
		$this->iconsLocation = $variables['global.icons.location'];
		require_once(WCF_DIR.'lib/data/template/TemplatePack.class.php');
		$templatePack = new TemplatePack($this->style->templatePackID);
		$this->templatePackName = $templatePack->templatePackName;
	} 
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'styleID' => $this->styleID,
			'style' => $this->style,
			'exportTemplates' => $this->exportTemplates,
			'exportImages' => $this->exportImages,
			'exportIcons' => $this->exportIcons,
			'canExportTemplates' => $this->canExportTemplates,
			'canExportIcons' => $this->canExportIcons,
			'templatePackName' => $this->templatePackName,
			'imagesLocation' => $this->imagesLocation,
			'iconsLocation' => $this->iconsLocation
		));
	}
}
?>