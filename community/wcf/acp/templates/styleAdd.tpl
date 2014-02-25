{capture append='specialStyles'}
<style type="text/css">
	@import url("{@RELATIVE_WCF_DIR}acp/style/extra/styleEditor{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
</style>
{/capture}{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/ColorChooser.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/AlignmentPreview.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	onloadEvents.push(function() { 
		tabMenu.showSubTabMenu("{$activeTabMenuItem}", "{$activeSubTabMenuItem}");

		{if $variables['global.favicon.use'] != 1}hideOptions('faviconDiv');{/if}
		{if $variables['page.header.background.image.use'] != 1}hideOptions('headerBackgroundDiv');{/if}
		{if $variables['page.logo.image.use'] != 1}hideOptions('logoDiv');{/if}
		{if $variables['page.logo.image.global.use'] != 1}hideOptions('logoImageDiv');{/if}
		{if $variables['page.background.image.use'] != 1}hideOptions('pageBackgroundDiv');{/if}
		{if $variables['page.frame.use'] != 1}hideOptions('pageFrameDiv');{/if}
		{if $variables['container1.font.color.use'] != 1}hideOptions('container1FontColorDiv');{/if}
		{if $variables['container1.link.color.use'] != 1}hideOptions('container1LinkColorDiv');{/if}
		{if $variables['container2.font.color.use'] != 1}hideOptions('container2FontColorDiv');{/if}
		{if $variables['container2.link.color.use'] != 1}hideOptions('container2LinkColorDiv');{/if}
		{if $variables['container3.font.color.use'] != 1}hideOptions('container3FontColorDiv');{/if}
		{if $variables['container3.link.color.use'] != 1}hideOptions('container3LinkColorDiv');{/if}
		{if $variables['container4.font.color.use'] != 1}hideOptions('container4FontColorDiv');{/if}
		{if $variables['container4.link.color.use'] != 1}hideOptions('container4LinkColorDiv');{/if}
		{if $variables['container.head.background.image.use'] != 1}hideOptions('containerHeadBackgroundDiv');{/if}
		{if $variables['global.title.show'] != 1}hideOptions('globalTitleDiv');{/if}
		{if $variables['global.title.font.color.use'] != 1}hideOptions('globalTitleFontColorInnerDiv');{/if}
		{if $variables['page.title.font.color.use'] != 1}hideOptions('pageTitleFontColorInnerDiv');{/if}
		{if $variables['page.link.external.use'] != 1}hideOptions('pageLinkExternalColorDiv');{/if}
		{if $variables['page.link.active.use'] != 1}hideOptions('pageLinkColorActiveDiv');{/if}
		{if $variables['buttons.small.caption.show'] != 1}hideOptions('buttonsSmallCaptionColorDiv');{/if}
		{if $variables['buttons.small.background.image.use'] != 1}hideOptions('buttonsSmallBackgroundImageDiv');{/if}
		{if $variables['buttons.large.caption.show'] != 1}hideOptions('buttonsLargeCaptionColorDiv');{/if}
		{if $variables['buttons.large.background.image.use'] != 1}hideOptions('buttonsLargeBackgroundImageDiv');{/if}
		{if $variables['menu.main.caption.show'] != 1}hideOptions('menuMainCaptionColorDiv');{/if}
		{if $variables['menu.main.background.image.use'] != 1}hideOptions('menuMainBackgroundImageDiv');{/if}
		{if $variables['menu.tab.background.image.use'] != 1}hideOptions('menuTabBackgroundImageDiv');{/if}
		{if $variables['table.head.background.image.use'] != 1}hideOptions('tableHeadBackgroundImageDiv');{/if}
		{if $variables['menu.dropdown.link.color.use'] != 1}hideOptions('menuDropDownLinkColorDiv');{/if}
		{if $variables['menu.dropdown.background.color.use'] != 1}hideOptions('menuDropDownBackgroundColorDiv');{/if}
		{if $variables['menu.dropdown.background.image.use'] != 1}hideOptions('menuDropDownBackgroundImageDiv');{/if}
		{if $variables['selection.background.image.use'] != 1}hideOptions('selectionBackgroundImageDiv');{/if}
		{if $variables['user.additional.style.input1.use'] != 1}hideOptions('userAdditionalStyle1Div');{/if}
		{if $variables['user.additional.style.input2.use'] != 1}hideOptions('userAdditionalStyle2Div');{/if}
		{if $variables['page.width.mode'] == 'static'}hideOptions('pageWidthMinDiv', 'pageWidthMaxDiv');{else}hideOptions('pageWidthDiv');{/if}
		{if $variables['menu.main.bar.hide'] != 1}hideOptions('menuMainBarDividerDiv');{/if}
		{if $variables['messages.sidebar.alignment'] == 'top'}hideOptions('messagesSidebarTextAlignmentDiv', 'messagesSidebarDividerDiv');{/if}
		{if $variables['user.MSIEFixes.IE6.use'] != 1}hideOptions('userMSIEFixesIE6Div');{/if}
		{if $variables['user.MSIEFixes.IE7.use'] != 1}hideOptions('userMSIEFixesIE7Div');{/if}
		{if $variables['user.MSIEFixes.IE8.use'] != 1}hideOptions('userMSIEFixesIE8Div');{/if}
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/style{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.{@$action}{/lang}</h2>
		{if $style|isset}
			<p>{$style->styleName} {$style->styleVersion}</p>
			<p>/style/style-{$style->styleID}.css</p>
		{/if}
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.style.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=StyleList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.view{/lang}</span></a></li></ul>
	</div>
</div>
<form enctype="multipart/form-data" method="post" action="index.php?form=Style{@$action|ucfirst}{if $styleID|isset}&amp;styleID={@$styleID}{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
	<div class="tabMenu">
		<ul>
			<li id="overview"><a onclick="tabMenu.showSubTabMenu('overview');"><span>{lang}wcf.acp.style.editor.overview{/lang}</span></a></li>
			<li id="global"><a onclick="tabMenu.showSubTabMenu('global');"><span>{lang}wcf.acp.style.editor.global{/lang}</span></a></li>
			<li id="text"><a onclick="tabMenu.showSubTabMenu('text');"><span>{lang}wcf.acp.style.editor.text{/lang}</span></a></li>
			<li id="button"><a onclick="tabMenu.showSubTabMenu('button');"><span>{lang}wcf.acp.style.editor.button{/lang}</span></a></li>
			<li id="menuTab"><a onclick="tabMenu.showSubTabMenu('menuTab');"><span>{lang}wcf.acp.style.editor.menu{/lang}</span></a></li>
			
			{if $additionalTabs|isset}{@$additionalTabs}{/if}
			
			<li id="misc"><a onclick="tabMenu.showSubTabMenu('misc');"><span>{lang}wcf.acp.style.editor.misc{/lang}</span></a></li>
		</ul>
	</div>
	<div class="subTabMenu">
		<div class="containerHead">
			<ul class="hidden" id="global-categories">
				<li id="global-general"><a onclick="tabMenu.showTabMenuContent('global-general');"><span>{lang}wcf.acp.style.editor.global.general{/lang}</span></a></li>
				<li id="global-page"><a onclick="tabMenu.showTabMenuContent('global-page');"><span>{lang}wcf.acp.style.editor.global.page{/lang}</span></a></li>
				<li id="global-container"><a onclick="tabMenu.showTabMenuContent('global-container');"><span>{lang}wcf.acp.style.editor.global.container{/lang}</span></a></li>
				<li id="global-border"><a onclick="tabMenu.showTabMenuContent('global-border');"><span>{lang}wcf.acp.style.editor.global.border{/lang}</span></a></li>
				<li id="global-form"><a onclick="tabMenu.showTabMenuContent('global-form');"><span>{lang}wcf.acp.style.editor.global.form{/lang}</span></a></li>
				
				{if $additionalGlobalSubTabs|isset}{@$additionalGlobalSubTabs}{/if}
			</ul>
		
			<ul class="hidden" id="text-categories">
				<li id="text-general"><a onclick="tabMenu.showTabMenuContent('text-general');"><span>{lang}wcf.acp.style.editor.text.general{/lang}</span></a></li>
				<li id="text-link"><a onclick="tabMenu.showTabMenuContent('text-link');"><span>{lang}wcf.acp.style.editor.text.link{/lang}</span></a></li>
				
				{if $additionalTextSubTabs|isset}{@$additionalTextSubTabs}{/if}
			</ul>
			
			<ul class="hidden" id="button-categories">
				<li id="button-small"><a onclick="tabMenu.showTabMenuContent('button-small');"><span>{lang}wcf.acp.style.editor.button.small{/lang}</span></a></li>
				<li id="button-large"><a onclick="tabMenu.showTabMenuContent('button-large');"><span>{lang}wcf.acp.style.editor.button.large{/lang}</span></a></li>
				
				{if $additionalButtonSubTabs|isset}{@$additionalButtonSubTabs}{/if}
			</ul>
			
			<ul class="hidden" id="menuTab-categories">
				<li id="menuTab-main"><a onclick="tabMenu.showTabMenuContent('menuTab-main');"><span>{lang}wcf.acp.style.editor.menu.main{/lang}</span></a></li>
				<li id="menuTab-tab"><a onclick="tabMenu.showTabMenuContent('menuTab-tab');"><span>{lang}wcf.acp.style.editor.menu.tab{/lang}</span></a></li>
				<li id="menuTab-tabbutton"><a onclick="tabMenu.showTabMenuContent('menuTab-tabbutton');"><span>{lang}wcf.acp.style.editor.menu.tab.button{/lang}</span></a></li>
				<li id="menuTab-tableheader"><a onclick="tabMenu.showTabMenuContent('menuTab-tableheader');"><span>{lang}wcf.acp.style.editor.table.head{/lang}</span></a></li>
				<li id="menuTab-misc"><a onclick="tabMenu.showTabMenuContent('menuTab-misc');"><span>{lang}wcf.acp.style.editor.menu.misc{/lang}</span></a></li>
				
				{if $additionalMenuTabSubTabs|isset}{@$additionalMenuTabSubTabs}{/if}
			</ul>
			
			{if $additionalSubTabs|isset}{@$additionalSubTabs}{/if}
			
			<ul class="hidden" id="misc-categories">
				<li id="misc-messages"><a onclick="tabMenu.showTabMenuContent('misc-messages');"><span>{lang}wcf.acp.style.editor.misc.messages{/lang}</span></a></li>
				<li id="misc-additionalCSS"><a onclick="tabMenu.showTabMenuContent('misc-additionalCSS');"><span>{lang}wcf.acp.style.editor.misc.additionalCSS{/lang}</span></a></li>
				<li id="misc-MSIEFixes"><a onclick="tabMenu.showTabMenuContent('misc-MSIEFixes');"><span>{lang}wcf.acp.style.editor.misc.MSIEFixes{/lang}</span></a></li>
				<li id="misc-comment"><a onclick="tabMenu.showTabMenuContent('misc-comment');"><span>{lang}wcf.acp.style.editor.misc.comment{/lang}</span></a></li>
				
				{if $additionalMiscSubTabs|isset}{@$additionalMiscSubTabs}{/if}
			</ul>
		</div>
	</div>
	
	<!-- overview -->
	<div class="border tabMenuContent hidden" id="overview-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.overview{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.overview.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.overview.information{/lang}</legend>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="styleName">{lang}wcf.acp.style.image{/lang}</label>
						<img src="{@RELATIVE_WCF_DIR}{if $style|isset && $style->image}{@$style->image}{else}images/styleNoPreview.jpg{/if}" alt="" />
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.overview.information{/lang}</legend>
			
							<div class="formElement{if $errorField == 'styleName'} formError{/if}">
								<div class="formFieldLabel">
									<label for="styleName">{lang}wcf.acp.style.name{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" id="styleName" name="styleName" value="{$styleName}" />
									{if $errorField == 'styleName'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							
							<div class="formElement{if $errorField == 'authorName'} formError{/if}">
								<div class="formFieldLabel">
									<label for="authorName">{lang}wcf.acp.style.authorName{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="authorName" id="authorName" value="{$authorName}" />
									{if $errorField == 'authorName'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'copyright'} formError{/if}">
								<div class="formFieldLabel">
									<label for="copyright">{lang}wcf.acp.style.copyright{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="copyright" id="copyright" value="{$copyright}" />
									{if $errorField == 'copyright'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'styleVersion'} formError{/if}">
								<div class="formFieldLabel">
									<label for="styleVersion">{lang}wcf.acp.style.version{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="styleVersion" id="styleVersion" value="{$styleVersion}" />
									{if $errorField == 'styleVersion'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'styleDate'} formError{/if}">
								<div class="formFieldLabel">
									<label for="styleDate">{lang}wcf.acp.style.date{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="styleDate" id="styleDate" value="{$styleDate}" maxlength="10" />
									{if $errorField == 'styleDate'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'license'} formError{/if}">
								<div class="formFieldLabel">
									<label for="license">{lang}wcf.acp.style.license{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="license" id="license" value="{$license}" />
									{if $errorField == 'license'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'authorURL'} formError{/if}">
								<div class="formFieldLabel">
									<label for="authorURL">{lang}wcf.acp.style.authorURL{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="authorURL" id="authorURL" value="{$authorURL}" />
									{if $errorField == 'authorURL'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
							<div class="formElement{if $errorField == 'styleDescription'} formError{/if}">
								<div class="formFieldLabel">
									<label for="styleDescription">{lang}wcf.acp.style.description{/lang}</label>
								</div>
								<div class="formField">
									<textarea cols="40" rows="5" name="styleDescription" id="styleDescription">{$styleDescription}</textarea>
									{if $errorField == 'styleDescription'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				{if $action != 'edit' || !$style->isDefault}
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="enableStyle" value="1" {if $enableStyle == 1}checked="checked" {/if}/> {lang}wcf.acp.style.enable{/lang}</label>
						</div>
					</div>
				{/if}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.image{/lang}</legend>
				
				<div class="formElement{if $errorField == 'image'} formError{/if}">
					<div class="formFieldLabel">
						<label for="image">{lang}wcf.acp.style.image{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="image" id="image" value="{$image}" />
						{if $errorField == 'image'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'imageUpload'} formError{/if}">
					<div class="formFieldLabel">
						<label for="imageUpload">{lang}wcf.acp.style.image.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" name="imageUpload" id="imageUpload" />
						{if $errorField == 'imageUpload'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
			
			{if $action == 'edit'}
				<fieldset>
					<legend>{lang}wcf.acp.style.importXML{/lang}</legend>
					
					<div class="formElement{if $errorField == 'xmlUpload'} formError{/if}">
						<div class="formFieldLabel">
							<label for="xmlUpload">{lang}wcf.acp.style.importXML.file{/lang}</label>
						</div>
						<div class="formField">
							<input type="file" name="xmlUpload" id="xmlUpload" />
							{if $errorField == 'xmlUpload'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'importFailed'}{lang}wcf.acp.style.importXML.file.error.importFailed{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.acp.style.importXML.file.description{/lang}</p>
						</div>
					</div>
				</fieldset>
			{/if}
		</div>
	</div>
	
	<!-- global -->
	<div class="border tabMenuContent hidden" id="global-general-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.global.general{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.global.general.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.general.appearance{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.general.appearance.description{/lang}</p>
				</div>
			
				<div class="formElement alignmentPreview">
					<div class="formFieldLabel">
						<label for="page-alignment">{lang}wcf.acp.style.editor.alignment{/lang}</label>
					</div>
					<div class="formField">
						<select name="variables[page.alignment]" id="page-alignment">
							<option value="left"{if $variables['page.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
							<option value="center"{if $variables['page.alignment'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
							<option value="right"{if $variables['page.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
						</select>
						<script type="text/javascript">
							//<![CDATA[
							alignmentPreview.init('page-alignment');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="page-width-mode">{lang}wcf.acp.style.editor.width{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.width{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-width-mode">{lang}wcf.acp.style.editor.properties{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[page.width.mode]" id="page-width-mode" onchange="if (this.options[this.selectedIndex].value == 'static') showOptions('pageWidthDiv') + hideOptions('pageWidthMinDiv', 'pageWidthMaxDiv'); else hideOptions('pageWidthDiv') + showOptions('pageWidthMinDiv', 'pageWidthMaxDiv');">
										<option value="static"{if $variables['page.width.mode'] == 'static'} selected="selected"{/if}>{lang}wcf.acp.style.editor.width.static{/lang}</option>
										<option value="dynamic"{if $variables['page.width.mode'] == 'dynamic'} selected="selected"{/if}>{lang}wcf.acp.style.editor.width.dynamic{/lang}</option>
									</select>
								</div>
							</div>
							
							<div class="formElement" id="pageWidthDiv">
								<div class="formFieldLabel">
									<label for="page-width">{lang}wcf.acp.style.editor.width.general{/lang}</label>
								</div>
								<div class="formField">										
									<input type="text" class="inputText" id="page-width" name="variables[page.width]" value="{$variables['page.width']}" /> 				
									<select name="variables[page.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.width.unit']}
									</select>								
								</div>
							</div>
							
							<div class="formElement" id="pageWidthMinDiv">
								<div class="formFieldLabel">
									<label for="page-width-min">{lang}wcf.acp.style.editor.width.min{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-width-min" name="variables[page.width.min]" value="{$variables['page.width.min']}" /> 
									<select name="variables[page.width.min.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.width.min.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement" id="pageWidthMaxDiv">
								<div class="formFieldLabel">
									<label for="page-width-max">{lang}wcf.acp.style.editor.width.max{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-width-max" name="variables[page.width.max]" value="{$variables['page.width.max']}" /> 
									<select name="variables[page.width.max.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.width.max.unit']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.templates{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.templates.description{/lang}</p>
				</div>
				<div class="formElement{if $errorField == 'templatePackID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="templatePackID">{lang}wcf.acp.style.template.pack{/lang}</label>
					</div>
					<div class="formField">
						<select name="templatePackID" id="templatePackID">
							<option value="0">{lang}wcf.acp.style.template.pack.default{/lang}</option>
							{htmlOptions options=$templatePacks selected=$templatePackID}
						</select>
						{if $errorField == 'templatePackID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.general.icons{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.general.icons.description{/lang}</p>
				</div>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="global-icons-location">{lang}wcf.acp.style.editor.folder{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="global-icons-location" name="variables[global.icons.location]" value="{$variables['global.icons.location']}" /> 
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.general.images{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.general.images.description{/lang}</p>
				</div>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="global-images-location">{lang}wcf.acp.style.editor.folder{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="global-images-location" name="variables[global.images.location]" value="{$variables['global.images.location']}" /> 
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.general.favicon{/lang}</legend>

				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.general.favicon.description{/lang}</p>
				</div>
				
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('faviconDiv'); else hideOptions('faviconDiv');" name="variables[global.favicon.use]" value="1" {if $variables['global.favicon.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.global.general.favicon.use{/lang}</label>
				</div>
				
				<div class="formGroup" id="faviconDiv">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.global.general.favicon.select{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.global.general.favicon.select{/lang}</legend>
							<ul class="formOptions smallFont">
								{foreach from=$favicons item=favicon}
									<li><label><img src="{@RELATIVE_WCF_DIR}icon/favicon{@$favicon|ucfirst}S.png" alt="" /> <input type="radio" name="variables[global.favicon]" value="{@$favicon}" {if $variables['global.favicon'] == $favicon}checked="checked" {/if}/> {lang}wcf.acp.style.editor.global.general.favicon.{@$favicon}{/lang}</label></li>
								{/foreach}
							</ul>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="global-page-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.global.page{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.global.page.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.page.header{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.page.header.description{/lang}</p>
				</div>

				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="page-header-background-color">{lang}wcf.acp.style.editor.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="page-header-background-color" name="variables[page.header.background.color]" value="{$variables['page.header.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('page-header-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="page-header-height">{lang}wcf.acp.style.editor.height{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.height{/lang}</legend>
				
							<div class="formElement">
								<div class="formField">
									<input type="text" class="inputText" id="page-header-height" name="variables[page.header.height]" value="{$variables['page.header.height']}" />
									<select name="variables[page.header.height.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.header.height.unit']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('headerBackgroundDiv'); else hideOptions('headerBackgroundDiv');" name="variables[page.header.background.image.use]" value="1" {if $variables['page.header.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				
				<div id="headerBackgroundDiv">
					<div class="formGroup">
						<div class="formGroupLabel">
							<label for="page-header-background-image">{lang}wcf.acp.style.editor.image{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.image{/lang}</legend>
								<div><input type="text" class="inputText" id="page-header-background-image" name="variables[page.header.background.image]" value="{$variables['page.header.background.image']}" /></div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.alignment{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.alignment{/lang}</legend>
					
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="page-header-background-image-alignment-horizontal">{lang}wcf.acp.style.editor.alignment.horizontal{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.header.background.image.alignment.horizontal]" id="page-header-background-image-alignment-horizontal">
											<option value="left"{if $variables['page.header.background.image.alignment.horizontal'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
											<option value="center"{if $variables['page.header.background.image.alignment.horizontal'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="right"{if $variables['page.header.background.image.alignment.horizontal'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('page-header-background-image-alignment-horizontal');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="page-header-background-image-alignment-vertical">{lang}wcf.acp.style.editor.alignment.vertical{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.header.background.image.alignment.vertical]" id="page-header-background-image-alignment-vertical">
											<option value="top"{if $variables['page.header.background.image.alignment.vertical'] == 'top'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.top{/lang}</option>
											<option value="center"{if $variables['page.header.background.image.alignment.vertical'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="bottom"{if $variables['page.header.background.image.alignment.vertical'] == 'bottom'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.bottom{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('page-header-background-image-alignment-vertical');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.repeat{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.repeat{/lang}</legend>
								<ul class="formOptions">
									<li><label><input type="checkbox" name="variables[page.header.background.image.repeat.horizontal]" value="1" {if $variables['page.header.background.image.repeat.horizontal'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.repeat.horizontal{/lang}</label></li>
									<li><label><input type="checkbox" name="variables[page.header.background.image.repeat.vertical]" value="1" {if $variables['page.header.background.image.repeat.vertical'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.repeat.vertical{/lang}</label></li>
								</ul>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.page.logo{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.page.logo.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('logoDiv'); else hideOptions('logoDiv');" name="variables[page.logo.image.use]" value="1" {if $variables['page.logo.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.global.page.logo.use{/lang}</label>
				</div>
				
				<div id="logoDiv">
					<div class="formGroup">
						<div class="formGroupLabel ">
							<label for="page-logo-image">{lang}wcf.acp.style.editor.image{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.image{/lang}</legend>
								
								<div>
									<label><input type="checkbox" onclick="if (this.checked) showOptions('logoImageDiv'); else hideOptions('logoImageDiv');" name="variables[page.logo.image.global.use]" value="1" {if $variables['page.logo.image.global.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.global.page.logo.global.use{/lang}</label>
									<p class="smallFont light">{lang}wcf.acp.style.editor.global.page.logo.global.use.description{/lang}</p>
								</div>
								
								<div id="logoImageDiv"><input type="text" class="inputText" id="page-logo-image" name="variables[page.logo.image]" value="{$variables['page.logo.image']}" /></div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.alignment{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.alignment{/lang}</legend>
					
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="page-logo-image-alignment">{lang}wcf.acp.style.editor.alignment.horizontal{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.logo.image.alignment]" id="page-logo-image-alignment">
											<option value="left"{if $variables['page.logo.image.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
											<option value="center"{if $variables['page.logo.image.alignment'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="right"{if $variables['page.logo.image.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('page-logo-image-alignment');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.padding{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.padding{/lang}</legend>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-logo-image-padding-top">{lang}wcf.acp.style.editor.padding.top{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-logo-image-padding-top" name="variables[page.logo.image.padding.top]" value="{$variables['page.logo.image.padding.top']}" />
										<select name="variables[page.logo.image.padding.top.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.logo.image.padding.top.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-logo-image-padding-right">{lang}wcf.acp.style.editor.padding.right{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-logo-image-padding-right" name="variables[page.logo.image.padding.right]" value="{$variables['page.logo.image.padding.right']}" />
										<select name="variables[page.logo.image.padding.right.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.logo.image.padding.right.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-logo-image-padding-left">{lang}wcf.acp.style.editor.padding.left{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-logo-image-padding-left" name="variables[page.logo.image.padding.left]" value="{$variables['page.logo.image.padding.left']}" />
										<select name="variables[page.logo.image.padding.left.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.logo.image.padding.left.unit']}
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.page.h1{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.page.h1.description{/lang}</p>
				</div>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('globalTitleDiv'); else hideOptions('globalTitleDiv');" name="variables[global.title.show]" value="1" {if $variables['global.title.show'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.global.page.h1.show{/lang}</label>
				</div>
			
				<div id="globalTitleDiv">
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.font{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.font{/lang}</legend>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font">{lang}wcf.acp.style.editor.font.face{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[global.title.font]" id="global-title-font">
											{foreach from=$fonts key=fontValue item=fontName}
												<option style="font-family: {@$fontValue}" value="{@$fontValue}"{if $variables['global.title.font'] == $fontValue} selected="selected"{/if}>{@$fontName}</option>
											{/foreach}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font-style">{lang}wcf.acp.style.editor.font.style{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[global.title.font.style]" id="global-title-font-style">
											{htmlOptions options=$fontStyles selected=$variables['global.title.font.style']}
										</select>
									</div>
								</div>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font-size">{lang}wcf.acp.style.editor.font.size{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="global-title-font-size" name="variables[global.title.font.size]" value="{$variables['global.title.font.size']}" /> 
										<select name="variables[global.title.font.size.unit]">
											{htmlOptions values=$units output=$units selected=$variables['global.title.font.size.unit']}
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.color{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.color{/lang}</legend>
					
								<div class="formCheckBox formElement">
									<div>	
										<label><input type="checkbox" onclick="if (this.checked) showOptions('globalTitleFontColorInnerDiv'); else hideOptions('globalTitleFontColorInnerDiv');" name="variables[global.title.font.color.use]" value="1" {if $variables['global.title.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.color.use{/lang}</label>
									</div>
								</div>
					
								<div class="formElement colorPicker" id="globalTitleFontColorInnerDiv">
									<div class="formFieldLabel">
										<label for="global-title-font-color">{lang}wcf.acp.style.editor.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="global-title-font-color" name="variables[global.title.font.color]" value="{$variables['global.title.font.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('global-title-font-color');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.alignment{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.alignment{/lang}</legend>
					
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="global-title-font-alignment">{lang}wcf.acp.style.editor.alignment.horizontal{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[global.title.font.alignment]" id="global-title-font-alignment">
											<option value="left"{if $variables['global.title.font.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
											<option value="center"{if $variables['global.title.font.alignment'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="right"{if $variables['global.title.font.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('global-title-font-alignment');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.padding{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.padding{/lang}</legend>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font-padding-top">{lang}wcf.acp.style.editor.padding.top{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="global-title-font-padding-top" name="variables[global.title.font.padding.top]" value="{$variables['global.title.font.padding.top']}" />
										<select name="variables[global.title.font.padding.top.unit]">
											{htmlOptions values=$units output=$units selected=$variables['global.title.font.padding.top.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font-padding-right">{lang}wcf.acp.style.editor.padding.right{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="global-title-font-padding-right" name="variables[global.title.font.padding.right]" value="{$variables['global.title.font.padding.right']}" />
										<select name="variables[global.title.font.padding.right.unit]">
											{htmlOptions values=$units output=$units selected=$variables['global.title.font.padding.right.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="global-title-font-padding-left">{lang}wcf.acp.style.editor.padding.left{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="global-title-font-padding-left" name="variables[global.title.font.padding.left]" value="{$variables['global.title.font.padding.left']}" />
										<select name="variables[global.title.font.padding.left.unit]">
											{htmlOptions values=$units output=$units selected=$variables['global.title.font.padding.left.unit']}
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.page.background{/lang}</legend>

				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.page.background.description{/lang}</p>
				</div>
			
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="page-background-color">{lang}wcf.acp.style.editor.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="page-background-color" name="variables[page.background.color]" value="{$variables['page.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('page-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('pageBackgroundDiv'); else hideOptions('pageBackgroundDiv');" name="variables[page.background.image.use]" value="1" {if $variables['page.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				
				<div id="pageBackgroundDiv">
					<div class="formGroup">
						<div class="formGroupLabel">
							<label for="page-background-image">{lang}wcf.acp.style.editor.image{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.image{/lang}</legend>
								<div><input type="text" class="inputText" id="page-background-image" name="variables[page.background.image]" value="{$variables['page.background.image']}" /></div>
							</fieldset>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="variables[page.background.image.fixed]" value="1" {if $variables['page.background.image.fixed'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.fixed{/lang}</label>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.alignment{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.alignment{/lang}</legend>
					
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="page-background-image-alignment-horizontal">{lang}wcf.acp.style.editor.alignment.horizontal{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.background.image.alignment.horizontal]" id="page-background-image-alignment-horizontal">
											<option value="left"{if $variables['page.background.image.alignment.horizontal'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
											<option value="center"{if $variables['page.background.image.alignment.horizontal'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="right"{if $variables['page.background.image.alignment.horizontal'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('page-background-image-alignment-horizontal');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement alignmentPreview">
									<div class="formFieldLabel">
										<label for="page-background-image-alignment-vertical">{lang}wcf.acp.style.editor.alignment.vertical{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.background.image.alignment.vertical]" id="page-background-image-alignment-vertical">
											<option value="top"{if $variables['page.background.image.alignment.vertical'] == 'top'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.top{/lang}</option>
											<option value="center"{if $variables['page.background.image.alignment.vertical'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
											<option value="bottom"{if $variables['page.background.image.alignment.vertical'] == 'bottom'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.bottom{/lang}</option>
										</select>
										<script type="text/javascript">
											//<![CDATA[
											alignmentPreview.init('page-background-image-alignment-vertical');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.repeat{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.repeat{/lang}</legend>
								<ul class="formOptions">
									<li><label><input type="checkbox" name="variables[page.background.image.repeat.horizontal]" value="1" {if $variables['page.background.image.repeat.horizontal'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.repeat.horizontal{/lang}</label></li>
									<li><label><input type="checkbox" name="variables[page.background.image.repeat.vertical]" value="1" {if $variables['page.background.image.repeat.vertical'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.repeat.vertical{/lang}</label></li>
								</ul>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.page.frame{/lang}</legend>

				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.page.frame.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('pageFrameDiv'); else hideOptions('pageFrameDiv');" name="variables[page.frame.use]" value="1" {if $variables['page.frame.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.page.frame.use{/lang}</label>
				</div>
				
				<div id="pageFrameDiv">
					<div class="formElement colorPicker">
						<div class="formFieldLabel">
							<label for="page-frame-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="page-frame-background-color" name="variables[page.frame.background.color]" value="{$variables['page.frame.background.color']}" />
							<script type="text/javascript">
								//<![CDATA[
								colorChooser.init('page-frame-background-color');
								//]]>
							</script>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.border{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.border{/lang}</legend>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-frame-border-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-frame-border-width" name="variables[page.frame.border.width]" value="{$variables['page.frame.border.width']}" /> 								
										<select name="variables[page.frame.border.width.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.frame.border.width.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-frame-border-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[page.frame.border.style]" id="page-frame-border-style">
											{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['page.frame.border.style']}
										</select>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="page-frame-border-color">{lang}wcf.acp.style.editor.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-frame-border-color" name="variables[page.frame.border.color]" value="{$variables['page.frame.border.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('page-frame-border-color');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.padding{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.padding{/lang}</legend>
					
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-frame-margin">{lang}wcf.acp.style.editor.padding.outer.vertical{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-frame-margin" name="variables[page.frame.margin]" value="{$variables['page.frame.margin']}" />
										<select name="variables[page.frame.margin.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.frame.margin.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-frame-padding-horizontal">{lang}wcf.acp.style.editor.padding.inner.horizontal{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-frame-padding-horizontal" name="variables[page.frame.padding.horizontal]" value="{$variables['page.frame.padding.horizontal']}" />
										<select name="variables[page.frame.padding.horizontal.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.frame.padding.horizontal.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="page-frame-padding-vertical">{lang}wcf.acp.style.editor.padding.inner.vertical{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-frame-padding-vertical" name="variables[page.frame.padding.vertical]" value="{$variables['page.frame.padding.vertical']}" />
										<select name="variables[page.frame.padding.vertical.unit]">
											{htmlOptions values=$units output=$units selected=$variables['page.frame.padding.vertical.unit']}
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="global-container-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.global.container{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.global.container.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.container.1{/lang}</legend>
				
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.container.1.description{/lang}</p>
				</div>
			
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="container1-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="container1-background-color" name="variables[container1.background.color]" value="{$variables['container1.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('container1-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
				
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('container1FontColorDiv'); else hideOptions('container1FontColorDiv');" name="variables[container1.font.color.use]" value="1" {if $variables['container1.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.font.color.use{/lang}</label>
							</div>
							
							<div id="container1FontColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container1-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container1-font-color" name="variables[container1.font.color]" value="{$variables['container1.font.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container1-font-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container1-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container1-font-2nd-color" name="variables[container1.font.2nd.color]" value="{$variables['container1.font.2nd.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container1-font-2nd-color');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
				
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('container1LinkColorDiv'); else hideOptions('container1LinkColorDiv');" name="variables[container1.link.color.use]" value="1" {if $variables['container1.link.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
							</div>
							
							<div id="container1LinkColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container1-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container1-link-color" name="variables[container1.link.color]" value="{$variables['container1.link.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container1-link-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container1-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container1-link-color-hover" name="variables[container1.link.color.hover]" value="{$variables['container1.link.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container1-link-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.container.2{/lang}</legend>
				
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.container.2.description{/lang}</p>
				</div>
			
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="container2-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="container2-background-color" name="variables[container2.background.color]" value="{$variables['container2.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('container2-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
				
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('container2FontColorDiv'); else hideOptions('container2FontColorDiv');" name="variables[container2.font.color.use]" value="1" {if $variables['container2.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.font.color.use{/lang}</label>
							</div>
							
							<div id="container2FontColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container2-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container2-font-color" name="variables[container2.font.color]" value="{$variables['container2.font.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container2-font-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container2-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container2-font-2nd-color" name="variables[container2.font.2nd.color]" value="{$variables['container2.font.2nd.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container2-font-2nd-color');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
				
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('container2LinkColorDiv'); else hideOptions('container2LinkColorDiv');" name="variables[container2.link.color.use]" value="1" {if $variables['container2.link.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
							</div>
							
							<div id="container2LinkColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container2-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container2-link-color" name="variables[container2.link.color]" value="{$variables['container2.link.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container2-link-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="container2-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="container2-link-color-hover" name="variables[container2.link.color.hover]" value="{$variables['container2.link.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('container2-link-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.container.3{/lang}</legend>
				
				<div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.style.editor.global.container.3.description{/lang}</p>
					</div>
				
					<div class="formElement colorPicker">
						<div class="formFieldLabel">
							<label for="container3-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="container3-background-color" name="variables[container3.background.color]" value="{$variables['container3.background.color']}" />
							<script type="text/javascript">
								//<![CDATA[
								colorChooser.init('container3-background-color');
								//]]>
							</script>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
					
								<div class="formCheckBox formElement">
									<label><input type="checkbox" onclick="if (this.checked) showOptions('container3FontColorDiv'); else hideOptions('container3FontColorDiv');" name="variables[container3.font.color.use]" value="1" {if $variables['container3.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.font.color.use{/lang}</label>
								</div>
								
								<div id="container3FontColorDiv">
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container3-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container3-font-color" name="variables[container3.font.color]" value="{$variables['container3.font.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container3-font-color');
												//]]>
											</script>
										</div>
									</div>
									
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container3-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container3-font-2nd-color" name="variables[container3.font.2nd.color]" value="{$variables['container3.font.2nd.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container3-font-2nd-color');
												//]]>
											</script>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
					
								<div class="formCheckBox formElement">
									<label><input type="checkbox" onclick="if (this.checked) showOptions('container3LinkColorDiv'); else hideOptions('container3LinkColorDiv');" name="variables[container3.link.color.use]" value="1" {if $variables['container3.link.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
								</div>
								
								<div id="container3LinkColorDiv">
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container3-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container3-link-color" name="variables[container3.link.color]" value="{$variables['container3.link.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container3-link-color');
												//]]>
											</script>
										</div>
									</div>
									
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container3-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container3-link-color-hover" name="variables[container3.link.color.hover]" value="{$variables['container3.link.color.hover']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container3-link-color-hover');
												//]]>
											</script>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.container.4{/lang}</legend>
				
				<div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.style.editor.global.container.4.description{/lang}</p>
					</div>
				
					<div class="formElement colorPicker">
						<div class="formFieldLabel">
							<label for="container4-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="container4-background-color" name="variables[container4.background.color]" value="{$variables['container4.background.color']}" />
							<script type="text/javascript">
								//<![CDATA[
								colorChooser.init('container4-background-color');
								//]]>
							</script>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
					
								<div class="formCheckBox formElement">
									<label><input type="checkbox" onclick="if (this.checked) showOptions('container4FontColorDiv'); else hideOptions('container4FontColorDiv');" name="variables[container4.font.color.use]" value="1" {if $variables['container4.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.font.color.use{/lang}</label>
								</div>
								
								<div id="container4FontColorDiv">
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container4-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container4-font-color" name="variables[container4.font.color]" value="{$variables['container4.font.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container4-font-color');
												//]]>
											</script>
										</div>
									</div>
									
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container4-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container4-font-2nd-color" name="variables[container4.font.2nd.color]" value="{$variables['container4.font.2nd.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container4-font-2nd-color');
												//]]>
											</script>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
					
								<div class="formCheckBox formElement">
									<label><input type="checkbox" onclick="if (this.checked) showOptions('container4LinkColorDiv'); else hideOptions('container4LinkColorDiv');" name="variables[container4.link.color.use]" value="1" {if $variables['container4.link.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
								</div>
								
								<div id="container4LinkColorDiv">
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container4-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container4-link-color" name="variables[container4.link.color]" value="{$variables['container4.link.color']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container4-link-color');
												//]]>
											</script>
										</div>
									</div>
									
									<div class="formElement colorPicker">
										<div class="formFieldLabel">
											<label for="container4-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
										</div>
										<div class="formField">	
											<input type="text" class="inputText" id="container4-link-color-hover" name="variables[container4.link.color.hover]" value="{$variables['container4.link.color.hover']}" />
											<script type="text/javascript">
												//<![CDATA[
												colorChooser.init('container4-link-color-hover');
												//]]>
											</script>
										</div>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="global-border-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.global.border{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.global.border.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.border.head{/lang}</legend>
				
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.border.head.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-head-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-head-font-color" name="variables[container.head.font.color]" value="{$variables['container.head.font.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-head-font-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-head-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-head-font-2nd-color" name="variables[container.head.font.2nd.color]" value="{$variables['container.head.font.2nd.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-head-font-2nd-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-head-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-head-link-color" name="variables[container.head.link.color]" value="{$variables['container.head.link.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-head-link-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-head-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-head-link-color-hover" name="variables[container.head.link.color.hover]" value="{$variables['container.head.link.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-head-link-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="container-head-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="container-head-background-color" name="variables[container.head.background.color]" value="{$variables['container.head.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('container-head-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('containerHeadBackgroundDiv'); else hideOptions('containerHeadBackgroundDiv');" name="variables[container.head.background.image.use]" value="1" {if $variables['container.head.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				
				<div class="formElement" id="containerHeadBackgroundDiv">
					<div class="formFieldLabel">
						<label for="container-head-background-image">{lang}wcf.acp.style.editor.background.image{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="container-head-background-image" name="variables[container.head.background.image]" value="{$variables['container.head.background.image']}" /> 
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.border.general{/lang}</legend>
				
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.border.general.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.border.outer{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.border.outer{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="container-border-outer-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-border-outer-width" name="variables[container.border.outer.width]" value="{$variables['container.border.outer.width']}" /> 								
									<select name="variables[container.border.outer.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['container.border.outer.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="container-border-outer-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[container.border.outer.style]" id="container-border-outer-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['container.border.outer.style']}
									</select>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-border-outer-color">{lang}wcf.acp.style.editor.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-border-outer-color" name="variables[container.border.outer.color]" value="{$variables['container.border.outer.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-border-outer-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.border.inner{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.border.inner{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="container-border-inner-color">{lang}wcf.acp.style.editor.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="container-border-inner-color" name="variables[container.border.inner.color]" value="{$variables['container.border.inner.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('container-border-inner-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.divider{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.divider{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="divider-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="divider-width" name="variables[divider.width]" value="{$variables['divider.width']}" /> 
									<select name="variables[divider.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['divider.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="divider-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[divider.style]" id="divider-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['divider.style']}
									</select>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="divider-color">{lang}wcf.acp.style.editor.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="divider-color" name="variables[divider.color]" value="{$variables['divider.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('divider-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="global-form-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.global.form{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.global.form.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.form.text{/lang}</legend>
				
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.form.text.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="input-font">{lang}wcf.acp.style.editor.font.face{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[input.font]" id="input-font">
										{foreach from=$fonts key=fontValue item=fontName}
											<option style="font-family: {@$fontValue}" value="{@$fontValue}"{if $variables['input.font'] == $fontValue} selected="selected"{/if}>{@$fontName}</option>
										{/foreach}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="input-font-size">{lang}wcf.acp.style.editor.font.size{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-font-size" name="variables[input.font.size]" value="{$variables['input.font.size']}" /> 
									<select name="variables[input.font.size.unit]">
										{htmlOptions values=$units output=$units selected=$variables['input.font.size.unit']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-font-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-font-color" name="variables[input.font.color]" value="{$variables['input.font.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-font-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-font-color-focus">{lang}wcf.acp.style.editor.color.focus{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-font-color-focus" name="variables[input.font.color.focus]" value="{$variables['input.font.color.focus']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-font-color-focus');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.form.background{/lang}</legend>
					
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.form.background.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-background-color" name="variables[input.background.color]" value="{$variables['input.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-background-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-background-color-focus">{lang}wcf.acp.style.editor.color.focus{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-background-color-focus" name="variables[input.background.color.focus]" value="{$variables['input.background.color.focus']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-background-color-focus');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.global.form.border{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.global.form.border.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.properties{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.properties{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="input-border-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-border-width" name="variables[input.border.width]" value="{$variables['input.border.width']}" /> 
									<select name="variables[input.border.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['input.border.width.unit']}
									</select>
								</div>
							</div>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="input-border-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[input.border.style]" id="input-border-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['input.border.style']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-border-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-border-color" name="variables[input.border.color]" value="{$variables['input.border.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-border-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="input-border-color-focus">{lang}wcf.acp.style.editor.color.focus{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="input-border-color-focus" name="variables[input.border.color.focus]" value="{$variables['input.border.color.focus']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('input-border-color-focus');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<!-- text -->
	<div class="border tabMenuContent hidden" id="text-general-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.text.general{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.text.general.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.text.general.text{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.text.general.text.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-font">{lang}wcf.acp.style.editor.font.face{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[page.font]" id="page-font">
										{foreach from=$fonts key=fontValue item=fontName}
											<option style="font-family: {@$fontValue}" value="{@$fontValue}"{if $variables['page.font'] == $fontValue} selected="selected"{/if}>{@$fontName}</option>
										{/foreach}
									</select>
								</div>
							</div>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-font-size">{lang}wcf.acp.style.editor.font.size.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-font-size" name="variables[page.font.size]" value="{$variables['page.font.size']}" /> 
									<select name="variables[page.font.size.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.font.size.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-font-2nd-size">{lang}wcf.acp.style.editor.font.size.small{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-font-2nd-size" name="variables[page.font.2nd.size]" value="{$variables['page.font.2nd.size']}" /> 
									<select name="variables[page.font.2nd.size.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.font.2nd.size.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-font-line-height">{lang}wcf.acp.style.editor.line.height{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-font-line-height" name="variables[page.font.line.height]" value="{$variables['page.font.line.height']}" /> 
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="page-font-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-font-color" name="variables[page.font.color]" value="{$variables['page.font.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('page-font-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="page-font-2nd-color">{lang}wcf.acp.style.editor.2nd.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-font-2nd-color" name="variables[page.font.2nd.color]" value="{$variables['page.font.2nd.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('page-font-2nd-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.text.general.h2{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.text.general.h2.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-title-font">{lang}wcf.acp.style.editor.font.face{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[page.title.font]" id="page-title-font">
										{foreach from=$fonts key=fontValue item=fontName}
											<option style="font-family: {@$fontValue}" value="{@$fontValue}"{if $variables['page.title.font'] == $fontValue} selected="selected"{/if}>{@$fontName}</option>
										{/foreach}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-title-font-style">{lang}wcf.acp.style.editor.font.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[page.title.font.style]" id="page-title-font-style">
										{htmlOptions options=$fontStyles selected=$variables['page.title.font.style']}
									</select>
								</div>
							</div>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="page-title-font-size">{lang}wcf.acp.style.editor.font.size{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-title-font-size" name="variables[page.title.font.size]" value="{$variables['page.title.font.size']}" /> 
									<select name="variables[page.title.font.size.unit]">
										{htmlOptions values=$units output=$units selected=$variables['page.title.font.size.unit']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.color{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.color{/lang}</legend>
				
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('pageTitleFontColorInnerDiv'); else hideOptions('pageTitleFontColorInnerDiv');" name="variables[page.title.font.color.use]" value="1" {if $variables['page.title.font.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.color.use{/lang}</label>
							</div>
				
							<div class="formElement colorPicker" id="pageTitleFontColorInnerDiv">
								<div class="formFieldLabel">
									<label for="page-title-font-color">{lang}wcf.acp.style.editor.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-title-font-color" name="variables[page.title.font.color]" value="{$variables['page.title.font.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('page-title-font-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="text-link-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.text.link{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.text.link.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.text.link.general{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.text.link.general.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="page-link-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-link-color" name="variables[page.link.color]" value="{$variables['page.link.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('page-link-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="page-link-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="page-link-color-hover" name="variables[page.link.color.hover]" value="{$variables['page.link.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('page-link-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.text.link.external{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.text.link.external.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formCheckBox">
									<label><input type="checkbox" onclick="if (this.checked) showOptions('pageLinkExternalColorDiv'); else hideOptions('pageLinkExternalColorDiv');" name="variables[page.link.external.use]" value="1" {if $variables['page.link.external.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
							</div>
				
							<div id="pageLinkExternalColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="page-link-external-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-link-external-color" name="variables[page.link.external.color]" value="{$variables['page.link.external.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('page-link-external-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="page-link-external-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="page-link-external-color-hover" name="variables[page.link.external.color.hover]" value="{$variables['page.link.external.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('page-link-external-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.text.link.active{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.text.link.active.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('pageLinkColorActiveDiv'); else hideOptions('pageLinkColorActiveDiv');" name="variables[page.link.active.use]" value="1" {if $variables['page.link.active.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.text.link.active.use{/lang}</label>
				</div>
			
				<div class="formElement colorPicker" id="pageLinkColorActiveDiv">
					<div class="formFieldLabel">
						<label for="page-link-color-active">{lang}wcf.acp.style.editor.color{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="page-link-color-active" name="variables[page.link.color.active]" value="{$variables['page.link.color.active']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('page-link-color-active');
							//]]>
						</script>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<!-- button -->
	<div class="border tabMenuContent hidden" id="button-small-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.button.small{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.button.small.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.caption.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('buttonsSmallCaptionColorDiv'); else hideOptions('buttonsSmallCaptionColorDiv');" name="variables[buttons.small.caption.show]" value="1" {if $variables['buttons.small.caption.show'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.button.caption.show{/lang}</label>
				</div>
			
				<div class="formGroup" id="buttonsSmallCaptionColorDiv">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-caption-color" name="variables[buttons.small.caption.color]" value="{$variables['buttons.small.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-caption-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-caption-color-hover" name="variables[buttons.small.caption.color.hover]" value="{$variables['buttons.small.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.border.outer{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.border.outer.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.properties{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.properties{/lang}</legend>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-small-border-outer-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-outer-width" name="variables[buttons.small.border.outer.width]" value="{$variables['buttons.small.border.outer.width']}" /> 
									<select name="variables[buttons.small.border.outer.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['buttons.small.border.outer.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-small-border-outer-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[buttons.small.border.outer.style]" id="buttons-small-border-outer-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['buttons.small.border.outer.style']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-border-outer-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-outer-color" name="variables[buttons.small.border.outer.color]" value="{$variables['buttons.small.border.outer.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-border-outer-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-border-outer-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-outer-color-hover" name="variables[buttons.small.border.outer.color.hover]" value="{$variables['buttons.small.border.outer.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-border-outer-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.border.inner{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.border.inner.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.properties{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.properties{/lang}</legend>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-small-border-inner-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-inner-width" name="variables[buttons.small.border.inner.width]" value="{$variables['buttons.small.border.inner.width']}" /> 
									<select name="variables[buttons.small.border.inner.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['buttons.small.border.inner.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-small-border-inner-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[buttons.small.border.inner.style]" id="buttons-small-border-inner-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['buttons.small.border.inner.style']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-border-inner-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-inner-color" name="variables[buttons.small.border.inner.color]" value="{$variables['buttons.small.border.inner.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-border-inner-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-border-inner-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-border-inner-color-hover" name="variables[buttons.small.border.inner.color.hover]" value="{$variables['buttons.small.border.inner.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-border-inner-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.background.color{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-background-color" name="variables[buttons.small.background.color]" value="{$variables['buttons.small.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-background-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-small-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-small-background-color-hover" name="variables[buttons.small.background.color.hover]" value="{$variables['buttons.small.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-small-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.background.image{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.background.image.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('buttonsSmallBackgroundImageDiv'); else hideOptions('buttonsSmallBackgroundImageDiv');" name="variables[buttons.small.background.image.use]" value="1" {if $variables['buttons.small.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				
				<div id="buttonsSmallBackgroundImageDiv">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="buttons-small-background-image">{lang}wcf.acp.style.editor.background.image.normal{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="buttons-small-background-image" name="variables[buttons.small.background.image]" value="{$variables['buttons.small.background.image']}" /> 
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="buttons-small-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="buttons-small-background-image-hover" name="variables[buttons.small.background.image.hover]" value="{$variables['buttons.small.background.image.hover']}" /> 
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="button-large-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.button.large{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.button.large.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.caption.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('buttonsLargeCaptionColorDiv'); else hideOptions('buttonsLargeCaptionColorDiv');" name="variables[buttons.large.caption.show]" value="1" {if $variables['buttons.large.caption.show'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.button.caption.show{/lang}</label>
				</div>
			
				<div class="formGroup" id="buttonsLargeCaptionColorDiv">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-caption-color" name="variables[buttons.large.caption.color]" value="{$variables['buttons.large.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-caption-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-caption-color-hover" name="variables[buttons.large.caption.color.hover]" value="{$variables['buttons.large.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.border.outer{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.border.outer.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.properties{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.properties{/lang}</legend>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-large-border-outer-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-border-outer-width" name="variables[buttons.large.border.outer.width]" value="{$variables['buttons.large.border.outer.width']}" /> 
									<select name="variables[buttons.large.border.outer.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['buttons.large.border.outer.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="buttons-large-border-outer-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[buttons.large.border.outer.style]" id="buttons-large-border-outer-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['buttons.large.border.outer.style']}
									</select>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-border-outer-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-border-outer-color" name="variables[buttons.large.border.outer.color]" value="{$variables['buttons.large.border.outer.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-border-outer-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-border-outer-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-border-outer-color-hover" name="variables[buttons.large.border.outer.color.hover]" value="{$variables['buttons.large.border.outer.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-border-outer-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.border.inner{/lang}</legend>
				
				<div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.style.editor.button.border.inner.description{/lang}</p>
					</div>
				
					<div class="formGroup">
						<div class="formGroupLabel">
								<label>{lang}wcf.acp.style.editor.properties{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.properties{/lang}</legend>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="buttons-large-border-inner-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="buttons-large-border-inner-width" name="variables[buttons.large.border.inner.width]" value="{$variables['buttons.large.border.inner.width']}" /> 
										<select name="variables[buttons.large.border.inner.width.unit]">
											{htmlOptions values=$units output=$units selected=$variables['buttons.large.border.inner.width.unit']}
										</select>
									</div>
								</div>
								
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="buttons-large-border-inner-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
									</div>
									<div class="formField">	
										<select name="variables[buttons.large.border.inner.style]" id="buttons-large-border-inner-style">
											{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['buttons.large.border.inner.style']}
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
					
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="buttons-large-border-inner-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="buttons-large-border-inner-color" name="variables[buttons.large.border.inner.color]" value="{$variables['buttons.large.border.inner.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('buttons-large-border-inner-color');
											//]]>
										</script>
									</div>
								</div>
								
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="buttons-large-border-inner-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="buttons-large-border-inner-color-hover" name="variables[buttons.large.border.inner.color.hover]" value="{$variables['buttons.large.border.inner.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('buttons-large-border-inner-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.background.color{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-background-color" name="variables[buttons.large.background.color]" value="{$variables['buttons.large.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-background-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="buttons-large-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="buttons-large-background-color-hover" name="variables[buttons.large.background.color.hover]" value="{$variables['buttons.large.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('buttons-large-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.button.background.image{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.button.background.image.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('buttonsLargeBackgroundImageDiv'); else hideOptions('buttonsLargeBackgroundImageDiv');" name="variables[buttons.large.background.image.use]" value="1" {if $variables['buttons.large.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				
				<div id="buttonsLargeBackgroundImageDiv">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="buttons-large-background-image">{lang}wcf.acp.style.editor.background.image.normal{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="buttons-large-background-image" name="variables[buttons.large.background.image]" value="{$variables['buttons.large.background.image']}" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="buttons-large-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="buttons-large-background-image-hover" name="variables[buttons.large.background.image.hover]" value="{$variables['buttons.large.background.image.hover']}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<!-- menu -->
	<div class="border tabMenuContent hidden" id="menuTab-main-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.menu.main{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.menu.main.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.main.buttons{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.main.buttons.description{/lang}</p>
				</div>
			
				<div class="formElement alignmentPreview">
					<div class="formFieldLabel">
						<label for="menu-main-position">{lang}wcf.acp.style.editor.alignment{/lang}</label>
					</div>
					<div class="formField">	
						<select name="variables[menu.main.position]" id="menu-main-position">
							<option value="left"{if $variables['menu.main.position'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
							<option value="center"{if $variables['menu.main.position'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
							<option value="right"{if $variables['menu.main.position'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
						</select>
						<script type="text/javascript">
							//<![CDATA[
							alignmentPreview.init('menu-main-position');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">	
						<label><input type="checkbox" onclick="if (this.checked) showOptions('menuMainBarDividerDiv'); else hideOptions('menuMainBarDividerDiv');" name="variables[menu.main.bar.hide]" value="1" {if $variables['menu.main.bar.hide'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.menu.main.bar.hide{/lang}</label>
					</div>
				</div>
				<div class="formElement" id="menuMainBarDividerDiv">
					<div class="formField">	
						<label><input type="checkbox" name="variables[menu.main.bar.divider.show]" value="1" {if $variables['menu.main.bar.divider.show'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.menu.main.bar.divider.show{/lang}</label>
					</div>
				</div>
				<input type="hidden" name="variables[menu.main.bar.show]" value="1" />
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.main.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.main.caption.description{/lang}</p>
				</div>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('menuMainCaptionColorDiv'); else hideOptions('menuMainCaptionColorDiv');" name="variables[menu.main.caption.show]" value="1" {if $variables['menu.main.caption.show'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.menu.main.caption.use{/lang}</label>
				</div>
				<div class="formGroup" id="menuMainCaptionColorDiv">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-caption-color" name="variables[menu.main.caption.color]" value="{$variables['menu.main.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-caption-color-hover" name="variables[menu.main.caption.color.hover]" value="{$variables['menu.main.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-active-caption-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-active-caption-color" name="variables[menu.main.active.caption.color]" value="{$variables['menu.main.active.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-active-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-active-caption-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-active-caption-color-hover" name="variables[menu.main.active.caption.color.hover]" value="{$variables['menu.main.active.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-active-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.main.background.color{/lang}</legend>
					<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.main.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-background-color" name="variables[menu.main.background.color]" value="{$variables['menu.main.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-background-color-hover" name="variables[menu.main.background.color.hover]" value="{$variables['menu.main.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-active-background-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-active-background-color" name="variables[menu.main.active.background.color]" value="{$variables['menu.main.active.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-active-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-main-active-background-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-main-active-background-color-hover" name="variables[menu.main.active.background.color.hover]" value="{$variables['menu.main.active.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-main-active-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.main.background.image{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.main.background.image.description{/lang}</p>
				</div>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('menuMainBackgroundImageDiv'); else hideOptions('menuMainBackgroundImageDiv');" name="variables[menu.main.background.image.use]" value="1" {if $variables['menu.main.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				<div id="menuMainBackgroundImageDiv">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="menu-main-background-image">{lang}wcf.acp.style.editor.background.image.normal{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="menu-main-background-image" name="variables[menu.main.background.image]" value="{$variables['menu.main.background.image']}" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="menu-main-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="menu-main-background-image-hover" name="variables[menu.main.background.image.hover]" value="{$variables['menu.main.background.image.hover']}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="menuTab-tab-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.menu.tab{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.menu.tab.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.caption.description{/lang}</p>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-caption-color" name="variables[menu.tab.caption.color]" value="{$variables['menu.tab.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-caption-color-hover" name="variables[menu.tab.caption.color.hover]" value="{$variables['menu.tab.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-active-caption-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-active-caption-color" name="variables[menu.tab.active.caption.color]" value="{$variables['menu.tab.active.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-active-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-active-caption-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-active-caption-color-hover" name="variables[menu.tab.active.caption.color.hover]" value="{$variables['menu.tab.active.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-active-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.background.color{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-background-color" name="variables[menu.tab.background.color]" value="{$variables['menu.tab.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-background-color-hover" name="variables[menu.tab.background.color.hover]" value="{$variables['menu.tab.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-active-background-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-active-background-color" name="variables[menu.tab.active.background.color]" value="{$variables['menu.tab.active.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-active-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-active-background-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-active-background-color-hover" name="variables[menu.tab.active.background.color.hover]" value="{$variables['menu.tab.active.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-active-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.background.image{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.background.image.description{/lang}</p>
				</div>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('menuTabBackgroundImageDiv'); else hideOptions('menuTabBackgroundImageDiv');" name="variables[menu.tab.background.image.use]" value="1" {if $variables['menu.tab.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				<div id="menuTabBackgroundImageDiv">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="menu-tab-background-image">{lang}wcf.acp.style.editor.background.image.normal{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="menu-tab-background-image" name="variables[menu.tab.background.image]" value="{$variables['menu.tab.background.image']}" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="menu-tab-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="menu-tab-background-image-hover" name="variables[menu.tab.background.image.hover]" value="{$variables['menu.tab.background.image.hover']}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="menuTab-tabbutton-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.menu.tab.button{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.menu.tab.button.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.button.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.button.caption.description{/lang}</p>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-caption-color" name="variables[menu.tab.button.caption.color]" value="{$variables['menu.tab.button.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-caption-color-hover" name="variables[menu.tab.button.caption.color.hover]" value="{$variables['menu.tab.button.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-active-caption-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-active-caption-color" name="variables[menu.tab.button.active.caption.color]" value="{$variables['menu.tab.button.active.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-active-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-active-caption-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-active-caption-color-hover" name="variables[menu.tab.button.active.caption.color.hover]" value="{$variables['menu.tab.button.active.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-active-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.button.background.color{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.button.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-background-color" name="variables[menu.tab.button.background.color]" value="{$variables['menu.tab.button.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-background-color-hover" name="variables[menu.tab.button.background.color.hover]" value="{$variables['menu.tab.button.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-active-background-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-active-background-color" name="variables[menu.tab.button.active.background.color]" value="{$variables['menu.tab.button.active.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-active-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-active-background-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-active-background-color-hover" name="variables[menu.tab.button.active.background.color.hover]" value="{$variables['menu.tab.button.active.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-active-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.tab.button.border.outer{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.tab.button.border.outer.description{/lang}</p>
				</div>
			
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="menu-tab-button-border-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
					</div>
					<div class="formField">	
						<select name="variables[menu.tab.button.border.style]" id="menu-tab-button-border-style">
							{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['menu.tab.button.border.style']}
						</select>
					</div>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-border-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-border-color" name="variables[menu.tab.button.border.color]" value="{$variables['menu.tab.button.border.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-border-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="menu-tab-button-border-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="menu-tab-button-border-color-hover" name="variables[menu.tab.button.border.color.hover]" value="{$variables['menu.tab.button.border.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('menu-tab-button-border-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="menuTab-tableheader-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.table.head{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.table.head.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.table.head.caption{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.table.head.caption.description{/lang}</p>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-caption-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-caption-color" name="variables[table.head.caption.color]" value="{$variables['table.head.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-caption-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-caption-color-hover" name="variables[table.head.caption.color.hover]" value="{$variables['table.head.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-caption-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-caption-color" name="variables[table.head.active.caption.color]" value="{$variables['table.head.active.caption.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-caption-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-caption-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-caption-color-hover" name="variables[table.head.active.caption.color.hover]" value="{$variables['table.head.active.caption.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-caption-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.table.head.border.bottom{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.table.head.border.bottom.description{/lang}</p>
				</div>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="table-head-border-bottom-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
					</div>
					<div class="formField">	
						<select name="variables[table.head.border.bottom.style]" id="table-head-border-bottom-style">
							{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['table.head.border.bottom.style']}
						</select>
					</div>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-border-bottom-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-border-bottom-color" name="variables[table.head.border.bottom.color]" value="{$variables['table.head.border.bottom.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-border-bottom-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-border-bottom-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-border-bottom-color-hover" name="variables[table.head.border.bottom.color.hover]" value="{$variables['table.head.border.bottom.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-border-bottom-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-border-bottom-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-border-bottom-color" name="variables[table.head.active.border.bottom.color]" value="{$variables['table.head.active.border.bottom.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-border-bottom-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-border-bottom-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-border-bottom-color-hover" name="variables[table.head.active.border.bottom.color.hover]" value="{$variables['table.head.active.border.bottom.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-border-bottom-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.table.head.background.color{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.table.head.background.color.description{/lang}</p>
				</div>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.colors{/lang}</legend>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-background-color">{lang}wcf.acp.style.editor.color.normal{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-background-color" name="variables[table.head.background.color]" value="{$variables['table.head.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-background-color-hover">{lang}wcf.acp.style.editor.color.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-background-color-hover" name="variables[table.head.background.color.hover]" value="{$variables['table.head.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-background-color">{lang}wcf.acp.style.editor.color.active{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-background-color" name="variables[table.head.active.background.color]" value="{$variables['table.head.active.background.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-background-color');
										//]]>
									</script>
								</div>
							</div>
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="table-head-active-background-color-hover">{lang}wcf.acp.style.editor.color.active.hover{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="table-head-active-background-color-hover" name="variables[table.head.active.background.color.hover]" value="{$variables['table.head.active.background.color.hover']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('table-head-active-background-color-hover');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.table.head.background.image{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.table.head.background.image.description{/lang}</p>
				</div>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('tableHeadBackgroundImageDiv'); else hideOptions('tableHeadBackgroundImageDiv');" name="variables[table.head.background.image.use]" value="1" {if $variables['table.head.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
				</div>
				<div id="tableHeadBackgroundImageDiv">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="table-head-background-image">{lang}wcf.acp.style.editor.background.image.normal{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="table-head-background-image" name="variables[table.head.background.image]" value="{$variables['table.head.background.image']}" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="table-head-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="table-head-background-image-hover" name="variables[table.head.background.image.hover]" value="{$variables['table.head.background.image.hover']}" />
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="menuTab-misc-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.menu.misc{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.menu.misc.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.dropdown{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.dropdown.description{/lang}</p>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.link.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.link.colors{/lang}</legend>
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('menuDropDownLinkColorDiv'); else hideOptions('menuDropDownLinkColorDiv');" name="variables[menu.dropdown.link.color.use]" value="1" {if $variables['menu.dropdown.link.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.link.color.use{/lang}</label>
							</div>
							<div id="menuDropDownLinkColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="menu-dropdown-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-link-color" name="variables[menu.dropdown.link.color]" value="{$variables['menu.dropdown.link.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('menu-dropdown-link-color');
											//]]>
										</script>
									</div>
								</div>
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="menu-dropdown-link-color-hover">{lang}wcf.acp.style.editor.link.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-link-color-hover" name="variables[menu.dropdown.link.color.hover]" value="{$variables['menu.dropdown.link.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('menu-dropdown-link-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.background.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.background.colors{/lang}</legend>
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('menuDropDownBackgroundColorDiv'); else hideOptions('menuDropDownBackgroundColorDiv');" name="variables[menu.dropdown.background.color.use]" value="1" {if $variables['menu.dropdown.background.color.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.color.use{/lang}</label>
							</div>
							<div id="menuDropDownBackgroundColorDiv">
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="menu-dropdown-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-background-color" name="variables[menu.dropdown.background.color]" value="{$variables['menu.dropdown.background.color']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('menu-dropdown-background-color');
											//]]>
										</script>
									</div>
								</div>
								<div class="formElement colorPicker">
									<div class="formFieldLabel">
										<label for="menu-dropdown-background-color-hover">{lang}wcf.acp.style.editor.background.color.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-background-color-hover" name="variables[menu.dropdown.background.color.hover]" value="{$variables['menu.dropdown.background.color.hover']}" />
										<script type="text/javascript">
											//<![CDATA[
											colorChooser.init('menu-dropdown-background-color-hover');
											//]]>
										</script>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.background.images{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.background.images{/lang}</legend>
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('menuDropDownBackgroundImageDiv'); else hideOptions('menuDropDownBackgroundImageDiv');" name="variables[menu.dropdown.background.image.use]" value="1" {if $variables['menu.dropdown.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
							</div>
							<div id="menuDropDownBackgroundImageDiv">
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="menu-dropdown-background-image">{lang}wcf.acp.style.editor.background.image{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-background-image" name="variables[menu.dropdown.background.image]" value="{$variables['menu.dropdown.background.image']}" />
									</div>
								</div>
								<div class="formElement">
									<div class="formFieldLabel">
										<label for="menu-dropdown-background-image-hover">{lang}wcf.acp.style.editor.background.image.hover{/lang}</label>
									</div>
									<div class="formField">	
										<input type="text" class="inputText" id="menu-dropdown-background-image-hover" name="variables[menu.dropdown.background.image.hover]" value="{$variables['menu.dropdown.background.image.hover']}" />
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.menu.selection{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.menu.selection.description{/lang}</p>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.font.colors{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.font.colors{/lang}</legend>
				
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="selection-font-color">{lang}wcf.acp.style.editor.font.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="selection-font-color" name="variables[selection.font.color]" value="{$variables['selection.font.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('selection-font-color');
										//]]>
									</script>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="selection-font-2nd-color">{lang}wcf.acp.style.editor.font.2nd.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="selection-font-2nd-color" name="variables[selection.font.2nd.color]" value="{$variables['selection.font.2nd.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('selection-font-2nd-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="selection-link-color">{lang}wcf.acp.style.editor.link.color{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="selection-link-color" name="variables[selection.link.color]" value="{$variables['selection.link.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('selection-link-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement colorPicker">
					<div class="formFieldLabel">
						<label for="selection-background-color">{lang}wcf.acp.style.editor.background.color{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="selection-background-color" name="variables[selection.background.color]" value="{$variables['selection.background.color']}" />
						<script type="text/javascript">
							//<![CDATA[
							colorChooser.init('selection-background-color');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.border.outer{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.border.outer{/lang}</legend>
				
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="selection-border-width">{lang}wcf.acp.style.editor.border.width{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="selection-border-width" name="variables[selection.border.width]" value="{$variables['selection.border.width']}" /> 
									<select name="variables[selection.border.width.unit]">
										{htmlOptions values=$units output=$units selected=$variables['selection.border.width.unit']}
									</select>
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="selection-border-style">{lang}wcf.acp.style.editor.border.style{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[selection.border.style]" id="selection-border-style">
										{htmlOptions values=$borderStyles output=$borderStyles selected=$variables['selection.border.style']}
									</select>
								</div>
							</div>
							
							<div class="formElement colorPicker">
								<div class="formFieldLabel">
									<label for="selection-border-color">{lang}wcf.acp.style.editor.color{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="selection-border-color" name="variables[selection.border.color]" value="{$variables['selection.border.color']}" />
									<script type="text/javascript">
										//<![CDATA[
										colorChooser.init('selection-border-color');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="selection-background-image">{lang}wcf.acp.style.editor.background.image{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.background.image{/lang}</legend>
							<div class="formCheckBox formElement">
								<label><input type="checkbox" onclick="if (this.checked) showOptions('selectionBackgroundImageDiv'); else hideOptions('selectionBackgroundImageDiv');" name="variables[selection.background.image.use]" value="1" {if $variables['selection.background.image.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.background.image.use{/lang}</label>
							</div>
							<div class="formElement" id="selectionBackgroundImageDiv">
								<div class="formFieldLabel">
									<label for="selection-background-image">{lang}wcf.acp.style.editor.background.image{/lang}</label>
								</div>
								<div class="formField">	
									<input type="text" class="inputText" id="selection-background-image" name="variables[selection.background.image]" value="{$variables['selection.background.image']}" />
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<!-- misc -->
	<div class="border tabMenuContent hidden" id="misc-messages-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.misc.messages{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.misc.messages.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.messages.sidebar{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.messages.sidebar.description{/lang}</p>
				</div>
				
				<div class="formElement alignmentPreview">
					<div class="formFieldLabel">
						<label for="messages-sidebar-alignment">{lang}wcf.acp.style.editor.alignment{/lang}</label>
					</div>
					<div class="formField">	
						<select name="variables[messages.sidebar.alignment]" id="messages-sidebar-alignment" onchange="if (this.options[this.selectedIndex].value == 'top') hideOptions('messagesSidebarTextAlignmentDiv', 'messagesSidebarDividerDiv') + alignmentPreview.refresh(this.id); else showOptions('messagesSidebarTextAlignmentDiv', 'messagesSidebarDividerDiv') + alignmentPreview.refresh(this.id);">
							<option value="left"{if $variables['messages.sidebar.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
							<option value="top"{if $variables['messages.sidebar.alignment'] == 'top'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.top{/lang}</option>
							<option value="right"{if $variables['messages.sidebar.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
						</select>
						<script type="text/javascript">
							//<![CDATA[
							alignmentPreview.init('messages-sidebar-alignment', true);
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement alignmentPreview" id="messagesSidebarTextAlignmentDiv">
					<div class="formFieldLabel">
						<label for="messages-sidebar-text-alignment">{lang}wcf.acp.style.editor.text.alignment{/lang}</label>
					</div>
					<div class="formField">	
						<select name="variables[messages.sidebar.text.alignment]" id="messages-sidebar-text-alignment">
							<option value="left"{if $variables['messages.sidebar.text.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
							<option value="center"{if $variables['messages.sidebar.text.alignment'] == 'center'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.center{/lang}</option>
							<option value="right"{if $variables['messages.sidebar.text.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
						</select>
						<script type="text/javascript">
							//<![CDATA[
							alignmentPreview.init('messages-sidebar-text-alignment');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">	
						<label><input type="checkbox" name="variables[messages.sidebar.avatar.framed]" value="1" {if $variables['messages.sidebar.avatar.framed'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.messages.sidebar.avatar.framed{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">	
						<label><input type="checkbox" name="variables[messages.sidebar.color.cycle]" value="1" {if $variables['messages.sidebar.color.cycle'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.messages.sidebar.color.cycle{/lang}</label>
					</div>
				</div>
				<div class="formElement" id="messagesSidebarDividerDiv">
					<div class="formField">	
						<label><input type="checkbox" name="variables[messages.sidebar.divider.use]" value="1" {if $variables['messages.sidebar.divider.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.messages.sidebar.divider.use{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.messages{/lang}</legend>
				<div class="formFieldDesc">
					<p>{lang}wcf.acp.style.editor.messages.description{/lang}</p>
				</div>
				
				<div class="formElement">
					<div class="formField">	
						<label><input type="checkbox" name="variables[messages.framed]" value="1" {if $variables['messages.framed'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.messages.framed{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">	
						<label><input type="checkbox" name="variables[messages.color.cycle]" value="1" {if $variables['messages.color.cycle'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.messages.color.cycle{/lang}</label>
					</div>
				</div>
								
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.editor.messages.footer{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.editor.messages.footer{/lang}</legend>
							
							<div class="formElement alignmentPreview">
								<div class="formFieldLabel">
									<label for="messages-footer-alignment">{lang}wcf.acp.style.editor.alignment{/lang}</label>
								</div>
								<div class="formField">	
									<select name="variables[messages.footer.alignment]" id="messages-footer-alignment">
										<option value="left"{if $variables['messages.footer.alignment'] == 'left'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.left{/lang}</option>
										<option value="right"{if $variables['messages.footer.alignment'] == 'right'} selected="selected"{/if}>{lang}wcf.acp.style.editor.alignment.right{/lang}</option>
									</select>
									<script type="text/javascript">
										//<![CDATA[
										alignmentPreview.init('messages-footer-alignment');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="misc-additionalCSS-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.misc.additionalCSS{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.misc.additionalCSS.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.additionalCSS.1{/lang}</legend>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('userAdditionalStyle1Div'); else hideOptions('userAdditionalStyle1Div');" name="variables[user.additional.style.input1.use]" value="1" {if $variables['user.additional.style.input1.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.additionalCSS.1.use{/lang}</label>
				</div>
				
				<div class="formElement" id="userAdditionalStyle1Div">
					<div>
						<label for="user-additional-style-input1">{lang}wcf.acp.style.editor.additionalCSS.1.input{/lang}</label>
					</div>
					<div>
						<textarea id="user-additional-style-input1" name="variables[user.additional.style.input1]" cols="40" rows="20">{$variables['user.additional.style.input1']}</textarea>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.additionalCSS.2{/lang}</legend>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('userAdditionalStyle2Div'); else hideOptions('userAdditionalStyle2Div');" name="variables[user.additional.style.input2.use]" value="1" {if $variables['user.additional.style.input2.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.additionalCSS.2.use{/lang}</label>
				</div>
				
				<div class="formElement" id="userAdditionalStyle2Div">
					<div>
						<label for="user-additional-style-input2">{lang}wcf.acp.style.editor.additionalCSS.2.input{/lang}</label>
					</div>
					<div>
						<textarea id="user-additional-style-input2" name="variables[user.additional.style.input2]" cols="40" rows="20">{$variables['user.additional.style.input2']}</textarea>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="misc-MSIEFixes-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.misc.MSIEFixes{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.misc.MSIEFixes.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.misc.MSIEFixes.IE8{/lang}</legend>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('userMSIEFixesIE8Div'); else hideOptions('userMSIEFixesIE8Div');" name="variables[user.MSIEFixes.IE8.use]" value="1" {if $variables['user.MSIEFixes.IE8.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.misc.MSIEFixes.IE8.use{/lang}</label>
				</div>
				
				<div class="formElement" id="userMSIEFixesIE8Div">
					<div>
						<label for="user-MSIEFixes-IE8">{lang}wcf.acp.style.editor.misc.MSIEFixes.IE8.input{/lang}</label>
					</div>
					<div>
						<textarea id="user-MSIEFixes-IE8" name="variables[user.MSIEFixes.IE8]" cols="40" rows="20">{$variables['user.MSIEFixes.IE8']}</textarea>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.misc.MSIEFixes.IE7{/lang}</legend>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('userMSIEFixesIE7Div'); else hideOptions('userMSIEFixesIE7Div');" name="variables[user.MSIEFixes.IE7.use]" value="1" {if $variables['user.MSIEFixes.IE7.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.misc.MSIEFixes.IE7.use{/lang}</label>
				</div>
				
				<div class="formElement" id="userMSIEFixesIE7Div">
					<div>
						<label for="user-MSIEFixes-IE7">{lang}wcf.acp.style.editor.misc.MSIEFixes.IE7.input{/lang}</label>
					</div>
					<div>
						<textarea id="user-MSIEFixes-IE7" name="variables[user.MSIEFixes.IE7]" cols="40" rows="20">{$variables['user.MSIEFixes.IE7']}</textarea>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.misc.MSIEFixes.IE6{/lang}</legend>
				<div class="formElement">
					<label><input type="checkbox" onclick="if (this.checked) showOptions('userMSIEFixesIE6Div'); else hideOptions('userMSIEFixesIE6Div');" name="variables[user.MSIEFixes.IE6.use]" value="1" {if $variables['user.MSIEFixes.IE6.use'] == 1}checked="checked" {/if}/> {lang}wcf.acp.style.editor.misc.MSIEFixes.IE6.use{/lang}</label>
				</div>
				
				<div class="formElement" id="userMSIEFixesIE6Div">
					<div>
						<label for="user-MSIEFixes-IE6">{lang}wcf.acp.style.editor.misc.MSIEFixes.IE6.input{/lang}</label>
					</div>
					<div>
						<textarea id="user-MSIEFixes-IE6" name="variables[user.MSIEFixes.IE6]" cols="40" rows="20">{$variables['user.MSIEFixes.IE6']}</textarea>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="border tabMenuContent hidden" id="misc-comment-content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.acp.style.editor.misc.comment{/lang}</h3>
			<p class="description">{lang}wcf.acp.style.editor.misc.comment.description{/lang}</p>
			
			<fieldset>
				<legend>{lang}wcf.acp.style.editor.misc.comment{/lang}</legend>
				<div class="formElement">
					<div>
						<label for="user-comment">{lang}wcf.acp.style.editor.comment.input{/lang}</label>
					</div>
					<div>	
						<textarea id="user-comment" name="variables[user.comment]" cols="40" rows="20">{$variables['user.comment']}</textarea>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
 		<input type="hidden" id="activeSubTabMenuItem" name="activeSubTabMenuItem" value="{$activeSubTabMenuItem}" />
 	</div>
</form>

{include file='footer'}