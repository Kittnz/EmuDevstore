<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
require_once(WCF_DIR.'lib/data/template/TemplatePackEditor.class.php');

/**
 * Shows the style add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class StyleAddForm extends ACPForm {
	public $templateName = 'styleAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.style.add';
	public $neededPermissions = 'admin.style.canAddStyle';
	
	public $activeTabMenuItem = 'overview';
	public $activeSubTabMenuItem = '';
	public $units = array('%', 'em', 'pt', 'px');
	public $borderStyles = array('dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset');
	public $favicons = array('lightBlue', 'blue', 'darkBlue', 'blueExtra', 'lightGreen', 'green', 'darkGreen', 'greenExtra', 'yellow', 'ochre', 'brown', 'brownExtra', 'orange', 'red', 'darkRed', 'redExtra', 'pink', 'violet', 'darkViolet', 'violetExtra', 'lightGrey', 'grey', 'black', 'greyExtra');
	public $fonts = array(
		"Arial, Helvetica, sans-serif" => 'Arial',
		"Chicago, Impact, Compacta, sans-serif" => 'Chicago',
		"'Comic Sans MS', sans-serif" => 'Comic Sans',
		"'Courier New', Courier, monospace" => 'Courier New',
		"Geneva, Arial, Helvetica, sans-serif" => 'Geneva',
		"Georgia, 'Times New Roman', Times, serif" => 'Georgia',
		"Helvetica, Verdana, sans-serif" => 'Helvetica',
		"Impact, Compacta, Chicago, sans-serif" => 'Impact',
		"'Lucida Sans', 'Lucida Grande', Monaco, Geneva, sans-serif" => 'Lucida',
		"Tahoma, Arial, Helvetica, sans-serif" => 'Tahoma',
		"'Times New Roman', Times, Georgia, serif" => 'Times New Roman',
		"'Trebuchet MS', Arial, sans-serif" => 'Trebuchet MS',
		"Verdana, Helvetica, sans-serif" => 'Verdana'
	);
	public $fontStyles = array();
	
	public $styleName = '';
	public $templatePackID = 0;
	public $variables = array();
	public $templatePacks = array();
	public $style;
	public $authorName = '';
	public $copyright = '';
	public $styleVersion = '';
	public $styleDate = '';
	public $license = '';
	public $styleDescription = '';
	public $authorURL = '';
	public $image = '';
	public $enableStyle = 1;
	public $imageUpload;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->enableStyle = 0;
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
		if (isset($_POST['activeSubTabMenuItem'])) $this->activeSubTabMenuItem = $_POST['activeSubTabMenuItem'];
		if (isset($_POST['variables']) && is_array($_POST['variables'])) $this->variables = $_POST['variables'];
		if (isset($_POST['styleName'])) $this->styleName = StringUtil::trim($_POST['styleName']);
		if (isset($_POST['templatePackID'])) $this->templatePackID = intval($_POST['templatePackID']);
		if (isset($_POST['authorName'])) $this->authorName = StringUtil::trim($_POST['authorName']);
		if (isset($_POST['copyright'])) $this->copyright = StringUtil::trim($_POST['copyright']);
		if (isset($_POST['styleVersion'])) $this->styleVersion = StringUtil::trim($_POST['styleVersion']);
		if (isset($_POST['styleDate'])) $this->styleDate = StringUtil::trim($_POST['styleDate']);
		if (isset($_POST['license'])) $this->license = StringUtil::trim($_POST['license']);
		if (isset($_POST['styleDescription'])) $this->styleDescription = StringUtil::trim($_POST['styleDescription']);
		if (isset($_POST['authorURL'])) $this->authorURL = StringUtil::trim($_POST['authorURL']);
		if (isset($_POST['image'])) $this->image = StringUtil::trim($_POST['image']);
		if (isset($_FILES['imageUpload'])) $this->imageUpload = $_FILES['imageUpload'];
		if (isset($_POST['enableStyle'])) $this->enableStyle = intval($_POST['enableStyle']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// use default value for unset variables
		foreach ($this->defaultVariables as $name => $value) {
			if (!isset($this->variables[$name])) {
				$this->variables[$name] = $value;
			}
		}
		
		// unset unnecessary variables
		foreach ($this->variables as $name => $value) {
			if (!isset($this->defaultVariables[$name])) {
				unset($this->variables[$name]);
			}
		}
		
		if (empty($this->styleName)) {
			throw new UserInputException('styleName');
		}
		
		// upload image
		if ($this->imageUpload && $this->imageUpload['error'] != 4) {
			if ($this->imageUpload['error'] != 0) {
				throw new UserInputException('imageUpload', 'uploadFailed');
			}
		
			$newImage = WCF_DIR.'images/'.basename($this->imageUpload['name']);
			if (@getImageSize($this->imageUpload['tmp_name']) !== false && @move_uploaded_file($this->imageUpload['tmp_name'], $newImage)) {
				$this->image = 'images/'.basename($this->imageUpload['name']);
				@chmod($newImage, 0777);
			}
		}
		else if (!empty($this->image) && FileUtil::isURL($this->image)) {
			$newImage = WCF_DIR.'images/'.basename($this->image);
			$tmpFile = FileUtil::downloadFileFromHttp($this->image, 'image');
			if (@getImageSize($tmpFile) !== false && @copy($tmpFile, $newImage)) {
				$this->image = 'images/'.basename($this->image);
				@chmod($newImage, 0777);
			}
		}
		
		// template pack
		if ($this->templatePackID) {
			$templatePack = new TemplatePackEditor($this->templatePackID);
			if (!$templatePack->templatePackID) {
				throw new UserInputException('templatePackID');
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save style
		$finalVariables = $this->getFinalVariables();
		$this->style = StyleEditor::create($this->styleName, $finalVariables, $this->templatePackID, $this->styleDescription, $this->styleVersion, $this->styleDate, $this->image, $this->copyright, $this->license, $this->authorName, $this->authorURL, intval(!$this->enableStyle));
		
		// reset values
		$this->styleName = $this->styleDescription = $this->styleVersion = $this->styleDate = $this->image = $this->copyright = $this->license = $this->authorName = $this->authorURL = '';
		$this->variables = $this->defaultVariables;
		
		// reset cache
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.style.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->variables = $this->defaultVariables;
			
			// enable some functions by default
			$this->variables['container1.link.color.use'] = $this->variables['container1.background.color.use'] = 1;
			$this->variables['container2.link.color.use'] = $this->variables['container2.background.color.use'] = 1;
			$this->variables['container3.link.color.use'] = $this->variables['container3.background.color.use'] = 1;
			$this->variables['container4.link.color.use'] = $this->variables['container4.background.color.use'] = 1;
			$this->variables['global.favicon.use'] = $this->variables['page.background.image.use'] = 1;
			$this->variables['page.logo.image.use'] = $this->variables['page.title.font.color.use'] = 1;
			$this->variables['page.link.external.use'] = $this->variables['page.link.active.use'] = 1;
			$this->variables['buttons.small.caption.show'] = $this->variables['buttons.large.caption.show'] = 1;
			$this->variables['menu.main.caption.show'] = 1;
			$this->variables['menu.dropdown.link.color.use'] = $this->variables['menu.dropdown.background.color.use'] = 1;
			$this->variables['messages.sidebar.divider.use'] = $this->variables['messages.color.cycle'] = 1;
		}
		
		$this->fontStyles = array(
			'normal' => WCF::getLanguage()->get('wcf.acp.style.editor.font.style.normal'),
			'bold' => WCF::getLanguage()->get('wcf.acp.style.editor.font.style.bold'),
			'italic' => WCF::getLanguage()->get('wcf.acp.style.editor.font.style.italic'),
			'bold italic' => WCF::getLanguage()->get('wcf.acp.style.editor.font.style.boldAndItalic')
		);
		
		$this->templatePacks = TemplatePackEditor::getTemplatePacks();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'styleName' => $this->styleName,
			'templatePackID' => $this->templatePackID,
			'variables' => $this->variables,
			'templatePacks' => $this->templatePacks,
			'units' => $this->units,
			'borderStyles' => $this->borderStyles,
			'favicons' => $this->favicons,
			'fonts' => $this->fonts,
			'fontStyles' => $this->fontStyles,
			'action' => 'add',
			'activeTabMenuItem' => $this->activeTabMenuItem,
			'activeSubTabMenuItem' => $this->activeSubTabMenuItem,
			'authorName' => $this->authorName,
			'copyright' => $this->copyright,
			'styleVersion' => $this->styleVersion,
			'styleDate' => $this->styleDate,
			'license' => $this->license,
			'styleDescription' => $this->styleDescription,
			'authorURL' => $this->authorURL,
			'image' => $this->image,
			'enableStyle' => $this->enableStyle
		));
	}
	
	/**
	 * Formats the form variables to a version for database save.
	 * 
	 * @return	array
	 */
	protected function getFinalVariables() {
		$fv = $this->variables;
		
		// append unit to size
		foreach ($this->sizeVariables as $name) {
			if ($fv[$name] !== '') {
				$fv[$name] = str_replace(',', '.', $fv[$name]) . $fv[$name.'.unit'];
			}
			unset($fv[$name.'.unit']);
		}
		
		// merge/create values
		// page.header.background.image.alignment
		$fv['page.header.background.image.alignment'] = $fv['page.header.background.image.alignment.horizontal'].' '.$fv['page.header.background.image.alignment.vertical'];
		unset($fv['page.header.background.image.alignment.horizontal'], $fv['page.header.background.image.alignment.vertical']);
		
		// page.header.background.image.repeat
		if ($fv['page.header.background.image.repeat.horizontal'] && $fv['page.header.background.image.repeat.vertical']) $fv['page.header.background.image.repeat'] = 'repeat';
		else if ($fv['page.header.background.image.repeat.horizontal']) $fv['page.header.background.image.repeat'] = 'repeat-x';
		else if ($fv['page.header.background.image.repeat.vertical']) $fv['page.header.background.image.repeat'] = 'repeat-y';
		else $fv['page.header.background.image.repeat'] = 'no-repeat';
		unset($fv['page.header.background.image.repeat.horizontal'], $fv['page.header.background.image.repeat.vertical']);
		
		// page.background.image.attachment
		$fv['page.background.image.attachment'] = ($fv['page.background.image.fixed'] ? 'fixed' : 'scroll');
		unset($fv['page.background.image.fixed']);
		
		// page.background.image.alignment
		$fv['page.background.image.alignment'] = $fv['page.background.image.alignment.horizontal'].' '.$fv['page.background.image.alignment.vertical'];
		unset($fv['page.background.image.alignment.horizontal'], $fv['page.background.image.alignment.vertical']);
		
		// page.background.image.repeat
		if ($fv['page.background.image.repeat.horizontal'] && $fv['page.background.image.repeat.vertical']) $fv['page.background.image.repeat'] = 'repeat';
		else if ($fv['page.background.image.repeat.horizontal']) $fv['page.background.image.repeat'] = 'repeat-x';
		else if ($fv['page.background.image.repeat.vertical']) $fv['page.background.image.repeat'] = 'repeat-y';
		else $fv['page.background.image.repeat'] = 'no-repeat';
		unset($fv['page.background.image.repeat.horizontal'], $fv['page.background.image.repeat.vertical']);
		
		// page.width*
		if ($fv['page.width.mode'] == 'static') $fv['page.width.min'] = $fv['page.width.max'] = '';
		else $fv['page.width'] = '';
		unset($fv['page.width.mode']);
		
		// page.alignment.margin
		$fv['page.alignment.margin'] = 'margin-left:'.($fv['page.alignment'] == 'left' ? '0' : 'auto').';margin-right:'.($fv['page.alignment'] == 'right' ? '0' : 'auto').';';
		
		// global.title.hide / buttons.small.caption.hide / buttons.large.caption.hide / menu.main.caption.hide
		$fv['global.title.hide'] = (!$fv['global.title.show'] ? 'position: absolute; top: -9000px; left: -9000px;' : '');
		$fv['buttons.small.caption.hide'] = (!$fv['buttons.small.caption.show'] ? 'position: absolute; top: -9000px; left: -9000px;' : '');
		$fv['buttons.large.caption.hide'] = (!$fv['buttons.large.caption.show'] ? 'position: absolute; top: -9000px; left: -9000px;' : '');
		$fv['menu.main.caption.hide'] = (!$fv['menu.main.caption.show'] ? 'position: absolute; top: -9000px; left: -9000px;' : '');
		
		// main menu alignment
		$fv['menu.main.position'] = 'text-align:'.$fv['menu.main.position'].';margin:'.($fv['menu.main.position'] == 'left' ? '0 auto 0 0' : ($fv['menu.main.position'] == 'center' ? '0 auto' : '0 0 0 auto'));
		
		// additional css
		if ($fv['user.additional.style.input1.use']) {
			$fv['user.additional.style.input1.use'] = $fv['user.additional.style.input1'];
			$fv['user.additional.style.input1'] = '';
		}
		if ($fv['user.additional.style.input2.use']) {
			$fv['user.additional.style.input2.use'] = $fv['user.additional.style.input2'];
			$fv['user.additional.style.input2'] = '';
		}
		
		// IE fixes
		if ($fv['user.MSIEFixes.IE6.use']) {
			$fv['user.MSIEFixes.IE6.use'] = $fv['user.MSIEFixes.IE6'];
			$fv['user.MSIEFixes.IE6'] = '';
		}
		if ($fv['user.MSIEFixes.IE7.use']) {
			$fv['user.MSIEFixes.IE7.use'] = $fv['user.MSIEFixes.IE7'];
			$fv['user.MSIEFixes.IE7'] = '';
		}
		if ($fv['user.MSIEFixes.IE8.use']) {
			$fv['user.MSIEFixes.IE8.use'] = $fv['user.MSIEFixes.IE8'];
			$fv['user.MSIEFixes.IE8'] = '';
		}
		
		// font styles
		// global.title.font.style
		$fv['global.title.font.weight'] = 'normal';
		if ($fv['global.title.font.style'] == 'bold') {
			$fv['global.title.font.weight'] = 'bold';
			$fv['global.title.font.style'] = '';
		}
		else if ($fv['global.title.font.style'] == 'bold italic') {
			$fv['global.title.font.weight'] = 'bold';
			$fv['global.title.font.style'] = 'italic';
		}
		
		// page.title.font.style
		$fv['page.title.font.weight'] = 'normal';
		if ($fv['page.title.font.style'] == 'bold') {
			$fv['page.title.font.weight'] = 'bold';
			$fv['page.title.font.style'] = '';
		}
		else if ($fv['page.title.font.style'] == 'bold italic') {
			$fv['page.title.font.weight'] = 'bold';
			$fv['page.title.font.style'] = 'italic';
		}
		
		// menu.main.bar.hide
		if ($fv['menu.main.bar.hide'] == 1) $fv['menu.main.bar.hide'] = 'transparent';
		else {
			$fv['menu.main.bar.hide'] = $fv['container1.background.color'];
			$fv['menu.main.bar.divider.show'] = 1;
		}
		
		if ($fv['menu.main.bar.divider.show'] == 1) $fv['menu.main.bar.divider.show'] = '1px';
		else $fv['menu.main.bar.divider.show'] = '0';
		
		// sidebar alignment top
		if ($fv['messages.sidebar.alignment'] == 'top') {
			// sidebar top does not support dividers
			$fv['messages.sidebar.divider.use'] = 0;
		}

		// logo
		if ($fv['page.logo.image.use'] && !$fv['page.logo.image.global.use']) {
			$fv['page.logo.image.application.use'] = 1;
		}
		
		// page frame
		if ($fv['page.frame.use'] == 1) {
			$fv['page.frame.general'] = "#headerContainer,#mainContainer,#footerContainer{".$fv['page.alignment.margin'].($fv['page.width'] !== '' ? "width:".$fv['page.width'] : "max-width:".$fv['page.width.max'].";min-width:".$fv['page.width.min'].";")."}\n";
			$fv['page.frame.general'] .= "#userPanel,#header,#mainMenu,#main,#footer{max-width:100%!important;min-width:0!important;width:auto!important;}";
		}
		
		// unset variables
		foreach ($this->useVariables as $name => $variables) {
			if (!$fv[$name]) {
				foreach ($variables as $variable) {
					$fv[$variable] = '';
				}
			}
			unset($fv[$name]);
		}

		// format images
		// add trailing to images path
		$fv['global.images.location'] = FileUtil::addTrailingSlash($fv['global.images.location']);
		foreach ($this->images as $image) {
			if (!empty($fv[$image])) {
				$isBackgroundImage = (strstr($image, 'background') ? true : false);
				
				// format path
				if (!FileUtil::isURL($fv[$image]) && StringUtil::substring($fv[$image], 0, 1) !== '/') {
					$fv[$image] = ($isBackgroundImage ? '../' : '') . $fv['global.images.location'] . $fv[$image];
				}
				
				// add url()
				if ($isBackgroundImage) {
					$fv[$image] = 'url("'.addcslashes($fv[$image], '"').'")';
				}
			}
		}
		
		// add trailing to icons path
		$fv['global.icons.location'] = FileUtil::addTrailingSlash($fv['global.icons.location']);
		
		return $fv;
	}
	
	protected $sizeVariables = array(
		'page.width', 'page.width.min', 'page.width.max', 'page.logo.image.padding.top', 'page.logo.image.padding.right',
		'page.logo.image.padding.left', 'container.border.outer.width', 'divider.width',
		'input.font.size', 'input.border.width', 'page.font.size', 'page.font.2nd.size', 'global.title.font.size',
		'page.title.font.size', 'buttons.small.border.outer.width', 'buttons.small.border.inner.width',
		'buttons.large.border.outer.width', 'buttons.large.border.inner.width', 'selection.border.width',
		'page.header.height', 'global.title.font.padding.top', 'global.title.font.padding.right',
		'global.title.font.padding.left', 'page.frame.border.width', 'page.frame.margin', 'page.frame.padding.horizontal', 'page.frame.padding.vertical'
	);
	protected $useVariables = array(
		'global.favicon.use' => array('global.favicon'),
		'page.header.background.image.use' => array('page.header.background.image', 'page.header.background.image.alignment', 'page.header.background.image.repeat'),
		'page.logo.image.use' => array('page.logo.image', 'page.logo.image.alignment', 'page.logo.image.padding.top', 'page.logo.image.padding.right', 'page.logo.image.padding.left'),
		'page.logo.image.global.use' => array('page.logo.image'),
		'page.background.image.use' => array('page.background.image.attachment', 'page.background.image', 'page.background.image.alignment', 'page.background.image.repeat'),
		'container1.font.color.use' => array('container1.font.color', 'container1.font.2nd.color'),
		'container1.link.color.use' => array('container1.link.color', 'container1.link.color.hover'),
		'container2.font.color.use' => array('container2.font.color', 'container2.font.2nd.color'),
		'container2.link.color.use' => array('container2.link.color', 'container2.link.color.hover'),
		'container3.font.color.use' => array('container3.font.color', 'container3.font.2nd.color'),
		'container3.link.color.use' => array('container3.link.color', 'container3.link.color.hover'),
		'container4.font.color.use' => array('container4.font.color', 'container4.font.2nd.color'),
		'container4.link.color.use' => array('container4.link.color', 'container4.link.color.hover'),
		'container.head.background.image.use' => array('container.head.background.image'),
		'global.title.show' => array('global.title.font', 'global.title.font.style', 'global.title.font.size', 'global.title.font.color', 'global.title.font.color.use', 'global.title.font.padding.top', 'global.title.font.padding.right', 'global.title.font.padding.left', 'global.title.font.alignment', 'global.title.font.weight'),
		'global.title.font.color.use' => array('global.title.font.color'),
		'page.title.font.color.use' => array('page.title.font.color'),
		'page.link.external.use' => array('page.link.external.color', 'page.link.external.color.hover'),
		'page.link.active.use' => array('page.link.color.active'),
		'buttons.small.caption.show' => array('buttons.small.caption.color', 'buttons.small.caption.color.hover'),
		'buttons.small.background.image.use' => array('buttons.small.background.image', 'buttons.small.background.image.hover'),
		'buttons.large.caption.show' => array('buttons.large.caption.color', 'buttons.large.caption.color.hover'),
		'buttons.large.background.image.use' => array('buttons.large.background.image', 'buttons.large.background.image.hover'),
		'menu.main.caption.show' => array('menu.main.caption.color', 'menu.main.caption.color.hover', 'menu.main.active.caption.color', 'menu.main.active.caption.color.hover'),
		'menu.main.background.image.use' => array('menu.main.background.image', 'menu.main.background.image.hover'),
		'menu.tab.background.image.use' => array('menu.tab.background.image', 'menu.tab.background.image.hover'),
		'table.head.background.image.use' => array('table.head.background.image', 'table.head.background.image.hover'),
		'menu.dropdown.link.color.use' => array('menu.dropdown.link.color', 'menu.dropdown.link.color.hover'),
		'menu.dropdown.background.color.use' => array('menu.dropdown.background.color', 'menu.dropdown.background.color.hover'),
		'menu.dropdown.background.image.use' => array('menu.dropdown.background.image', 'menu.dropdown.background.image.hover'),
		'selection.background.image.use' => array('selection.background.image'),
		'page.frame.use' => array('page.frame.background.color', 'page.frame.border.width', 'page.frame.border.style', 'page.frame.border.color', 'page.frame.margin', 'page.frame.padding.horizontal', 'page.frame.padding.vertical')
	);
	protected $images = array(
		'page.header.background.image', 'page.background.image', 'container.head.background.image', 'buttons.small.background.image',
		'buttons.large.background.image', 'menu.main.background.image', 'menu.tab.background.image', 'table.head.background.image',
		'menu.dropdown.background.image', 'selection.background.image', 'buttons.small.background.image.hover', 'buttons.large.background.image.hover',
		'menu.main.background.image.hover', 'menu.tab.background.image.hover', 'table.head.background.image.hover', 'menu.dropdown.background.image.hover',
		'page.logo.image'
	);
	protected $defaultVariables = array(
		'page.alignment' => 'center',
		'page.width.mode' => 'dynamic',
		'page.width' => '70',
		'page.width.unit' => '%',
		'page.width.min' => '760',
		'page.width.min.unit' => 'px',
		'page.width.max' => '80',
		'page.width.max.unit' => '%',
		'global.icons.location' => 'icon/',
		'global.images.location' => 'images/',
		'global.favicon.use' => 0,
		'global.favicon' => 'grey',
		'page.header.background.color' => '#777',
		'page.header.background.image.use' => 0,
		'page.header.background.image' => '',
		'page.header.background.image.alignment.horizontal' => 'center',
		'page.header.background.image.alignment.vertical' => 'top',
		'page.header.background.image.repeat.horizontal' => 0,
		'page.header.background.image.repeat.vertical' => 0,
		'page.logo.image.use' => 0,
		'page.logo.image.global.use' => 0,
		'page.logo.image' => '',
		'page.logo.image.alignment' => 'left',
		'page.logo.image.padding.top' => '5',
		'page.logo.image.padding.top.unit' => 'px',
		'page.logo.image.padding.right' => '0',
		'page.logo.image.padding.right.unit' => 'px',
		'page.logo.image.padding.left' => '13',
		'page.logo.image.padding.left.unit' => 'px',
		'page.background.color' => '#fff',
		'page.background.image.use' => 0,
		'page.background.image.fixed' => 0,
		'page.background.image' => '',
		'page.background.image.alignment.horizontal' => 'center',
		'page.background.image.alignment.vertical' => 'top',
		'page.background.image.repeat.horizontal' => 0,
		'page.background.image.repeat.vertical' => 0,
		'container1.background.color' => '#f7f7f7',
		'container1.font.color.use' => 0,
		'container1.font.color' => '#666',
		'container1.font.2nd.color' => '#888',
		'container1.link.color.use' => 0,
		'container1.link.color' => '#666',
		'container1.link.color.hover' => '#333',
		'container2.background.color' => '#efefef',
		'container2.font.color.use' => 0,
		'container2.font.color' => '#666',
		'container2.font.2nd.color' => '#888',
		'container2.link.color.use' => 0,
		'container2.link.color' => '#666',
		'container2.link.color.hover' => '#333',
		'container3.background.color' => '#e0e0e0',
		'container3.font.color.use' => 0,
		'container3.font.color' => '#333',
		'container3.font.2nd.color' => '#777',
		'container3.link.color.use' => 0,
		'container3.link.color' => '#666',
		'container3.link.color.hover' => '#333',
		'container4.background.color' => '#fff',
		'container4.font.color.use' => 0,
		'container4.font.color' => '#666',
		'container4.font.2nd.color' => '#888',
		'container4.link.color.use' => 0,
		'container4.link.color' => '#666',
		'container4.link.color.hover' => '#333',
		'container.head.font.color' => '#fff',
		'container.head.font.2nd.color' => '#fff',
		'container.head.link.color' => '#fff',
		'container.head.link.color.hover' => '#fff',
		'container.head.background.color' => '#777',
		'container.head.background.image.use' => 0,
		'container.head.background.image' => '',
		'container.border.outer.width' => 1,
		'container.border.outer.width.unit' => 'px',
		'container.border.outer.style' => 'solid',
		'container.border.outer.color' => '#999',
		'container.border.inner.color' => '#fff',
		'divider.width' => 1,
		'divider.width.unit' => 'px',
		'divider.style' => 'solid',
		'divider.color' => '#bbb',
		'input.font' => "'Trebuchet MS', Arial, sans-serif",
		'input.font.size' => '.85',
		'input.font.size.unit' => 'em',
		'input.font.color' => '#333',
		'input.font.color.focus' => '#000',
		'input.background.color' => '#fff',
		'input.background.color.focus' => '#ffd',
		'input.border.width' => 1,
		'input.border.width.unit' => 'px',
		'input.border.style' => 'solid',
		'input.border.color' => '#999',
		'input.border.color.focus' => '#08f',
		'page.font' => "'Trebuchet MS', Arial, sans-serif",
		'page.font.size' => '.8',
		'page.font.size.unit' => 'em',
		'page.font.2nd.size' => '.85',
		'page.font.2nd.size.unit' => 'em',
		'page.font.color' => '#333',
		'page.font.2nd.color' => '#888',
		'global.title.show' => 0,
		'global.title.font' => "'Trebuchet MS', Arial, sans-serif",
		'global.title.font.style' => 'bold',
		'global.title.font.size' => '1.4',
		'global.title.font.size.unit' => 'em',
		'global.title.font.color.use' => 0,
		'global.title.font.color' => '#777',
		'global.title.font.padding.top' => '10',
		'global.title.font.padding.top.unit' => 'px',
		'global.title.font.padding.right' => '10',
		'global.title.font.padding.right.unit' => 'px',
		'global.title.font.padding.left' => '10',
		'global.title.font.padding.left.unit' => 'px',
		'page.title.font' => "'Trebuchet MS', Arial, sans-serif",
		'page.title.font.style' => 'bold',
		'page.title.font.size' => '1.3',
		'page.title.font.size.unit' => 'em',
		'page.title.font.color.use' => 0,
		'page.title.font.color' => '#333',
		'page.link.color' => '#666',
		'page.link.color.hover' => '#333',
		'page.link.external.use' => 0,
		'page.link.external.color' => '#333',
		'page.link.external.color.hover' => '#08f',
		'page.link.active.use' => 0,
		'page.link.color.active' => '#08f',
		'buttons.small.caption.show' => 0,
		'buttons.small.caption.color' => '#666',
		'buttons.small.caption.color.hover' => '#333',
		'buttons.small.border.outer.width' => 1,
		'buttons.small.border.outer.width.unit' => 'px',
		'buttons.small.border.outer.style' => 'solid',
		'buttons.small.border.outer.color' => '#999',
		'buttons.small.border.outer.color.hover' => '#999',
		'buttons.small.border.inner.width' => 1,
		'buttons.small.border.inner.width.unit' => 'px',
		'buttons.small.border.inner.style' => 'solid',
		'buttons.small.border.inner.color' => '#fff',
		'buttons.small.border.inner.color.hover' => '#fff',
		'buttons.small.background.color' => '#e8e8e8',
		'buttons.small.background.color.hover' => '#fff',
		'buttons.small.background.image.use' => 0,
		'buttons.small.background.image' => '',
		'buttons.small.background.image.hover' => '',
		'buttons.large.caption.show' => 0,
		'buttons.large.caption.color' => '#fff',
		'buttons.large.caption.color.hover' => '#333',
		'buttons.large.border.outer.width' => 1,
		'buttons.large.border.outer.width.unit' => 'px',
		'buttons.large.border.outer.style' => 'solid',
		'buttons.large.border.outer.color' => '#999',
		'buttons.large.border.outer.color.hover' => '#999',
		'buttons.large.border.inner.width' => 1,
		'buttons.large.border.inner.width.unit' => 'px',
		'buttons.large.border.inner.style' => 'solid',
		'buttons.large.border.inner.color' => '#fff',
		'buttons.large.border.inner.color.hover' => '#fff',
		'buttons.large.background.color' => '#777',
		'buttons.large.background.color.hover' => '#cecece',
		'buttons.large.background.image.use' => 0,
		'buttons.large.background.image' => '',
		'buttons.large.background.image.hover' => '',
		'menu.main.position' => 'left',
		'menu.main.caption.show' => 0,
		'menu.main.caption.color' => '#666',
		'menu.main.caption.color.hover' => '#333',
		'menu.main.active.caption.color' => '#fff',
		'menu.main.active.caption.color.hover' => '#000',
		'menu.main.background.color' => '#efefef',
		'menu.main.background.color.hover' => '#fff',
		'menu.main.active.background.color' => '#777',
		'menu.main.active.background.color.hover' => '#cecece',
		'menu.main.background.image.use' => 0,
		'menu.main.background.image' => '',
		'menu.main.background.image.hover' => '',
		'menu.tab.caption.color' => '#666',
		'menu.tab.caption.color.hover' => '#333',
		'menu.tab.active.caption.color' => '',
		'menu.tab.active.caption.color.hover' => '',
		'menu.tab.background.color' => '#e8e8e8',
		'menu.tab.background.color.hover' => '#fff',
		'menu.tab.active.background.color' => '',
		'menu.tab.active.background.color.hover' => '',
		'menu.tab.background.image.use' => 0,
		'menu.tab.background.image' => '',
		'menu.tab.background.image.hover' => '',
		'menu.tab.button.caption.color' => '#ddd',
		'menu.tab.button.caption.color.hover' => '#fff',
		'menu.tab.button.active.caption.color' => '#fff',
		'menu.tab.button.active.caption.color.hover' => '#fff',
		'menu.tab.button.background.color' => '',
		'menu.tab.button.background.color.hover' => '#666',
		'menu.tab.button.active.background.color' => '#444',
		'menu.tab.button.active.background.color.hover' => '#666',
		'menu.tab.button.border.style' => 'solid',
		'menu.tab.button.border.color' => '#aaa',
		'menu.tab.button.border.color.hover' => '#bbb',
		'table.head.caption.color' => '#666',
		'table.head.caption.color.hover' => '#333',
		'table.head.active.caption.color' => '#333',
		'table.head.active.caption.color.hover' => '#333',
		'table.head.border.bottom.style' => 'solid',
		'table.head.border.bottom.color' => '#999',
		'table.head.border.bottom.color.hover' => '#999',
		'table.head.active.border.bottom.color' => '#08f',
		'table.head.active.border.bottom.color.hover' => '#08f',
		'table.head.background.color' => '#cecece',
		'table.head.background.color.hover' => '#e8e8e8',
		'table.head.active.background.color' => '#e8e8e8',
		'table.head.active.background.color.hover' => '#efefef',
		'table.head.background.image.use' => 0,
		'table.head.background.image' => '',
		'table.head.background.image.hover' => '',
		'menu.dropdown.link.color.use' => 0,
		'menu.dropdown.link.color' => '#555',
		'menu.dropdown.link.color.hover' => '#000',
		'menu.dropdown.background.color.use' => 0,
		'menu.dropdown.background.color' => '#f7f7f7',
		'menu.dropdown.background.color.hover' => '#e0e0e0',
		'menu.dropdown.background.image.use' => 0,
		'menu.dropdown.background.image' => '',
		'menu.dropdown.background.image.hover' => '',
		'selection.link.color' => '#666',
		'selection.background.color' => '#def',
		'selection.border.width' => 1,
		'selection.border.width.unit' => 'px',
		'selection.border.style' => 'solid',
		'selection.border.color' => '#08f',
		'selection.background.image.use' => 0,
		'selection.background.image' => '',
		'user.additional.style.input1.use' => 0,
		'user.additional.style.input1' => '',
		'user.additional.style.input2.use' => 0,
		'user.additional.style.input2' => '',
		'user.MSIEFixes.IE6.use' => 0,
		'user.MSIEFixes.IE6' => '',
		'user.MSIEFixes.IE7.use' => 0,
		'user.MSIEFixes.IE7' => '',
		'user.MSIEFixes.IE8.use' => 0,
		'user.MSIEFixes.IE8' => '',
		'user.comment' => '',
		'page.header.height' => 90,
		'page.header.height.unit' => 'px',
		'global.title.font.alignment' => 'left',
		'selection.font.color' => '#333',
		'selection.font.2nd.color' => '#333',
		'page.font.line.height' => '1.5',
		'menu.main.bar.hide' => 0,
		'menu.main.bar.divider.show' => 0,
		'messages.framed' => 0,
		'messages.sidebar.alignment' => 'left',
		'messages.sidebar.text.alignment' => 'center',
		'messages.sidebar.avatar.framed' => 0,
		'messages.sidebar.color.cycle' => 0,
		'messages.sidebar.divider.use' => 0,
		'messages.color.cycle' => 0,
		'messages.footer.alignment' => 'right',
		'page.frame.use' => 0, 
		'page.frame.background.color' => '',
		'page.frame.border.width' => 0,
		'page.frame.border.width.unit' => '',
		'page.frame.border.style' => '',
		'page.frame.border.color' => '',
		'page.frame.margin' => 0,
		'page.frame.margin.unit' => '',
		'page.frame.padding.vertical' => 0,
		'page.frame.padding.vertical.unit' => '',
		'page.frame.padding.horizontal' => 0,
		'page.frame.padding.horizontal.unit' => ''
	);
}
?>