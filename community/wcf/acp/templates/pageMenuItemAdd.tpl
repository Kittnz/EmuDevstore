{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/pageMenuItem{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.pageMenuItem.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.pageMenuItem.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=PageMenuItemList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/pageMenuItemM.png" alt="" title="{lang}wcf.acp.menu.link.pageMenuItem.view{/lang}" /> <span>{lang}wcf.acp.menu.link.pageMenuItem.view{/lang}</span></a></li></ul>
	</div>
</div>

<form method="post" action="index.php?form=PageMenuItem{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.pageMenuItem.data{/lang}</legend>
				
				{if $action == 'edit'}
					<div class="formElement" id="languageIDDiv">
						<div class="formFieldLabel">
							<label for="languageID">{lang}wcf.acp.pageMenuItem.language{/lang}</label>
						</div>
						<div class="formField">
							<select name="languageID" id="languageID" onchange="location.href='index.php?form=PageMenuItemEdit&amp;pageMenuItemID={@$pageMenuItemID}&amp;languageID=' + this.value + '&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}'">
								{foreach from=$languages key=availableLanguageID item=languageCode}
									<option value="{@$availableLanguageID}"{if $availableLanguageID == $languageID} selected="selected"{/if}>{lang}wcf.global.language.{@$languageCode}{/lang}</option>
								{/foreach}
							</select>
						</div>
						<div class="formFieldDesc hidden" id="languageIDHelpMessage">
							{lang}wcf.acp.pageMenuItem.language.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('languageID');
					//]]></script>
				{/if}
				
				<div class="formElement{if $errorField == 'name'} formError{/if}" id="nameDiv">
					<div class="formFieldLabel">
						<label for="name">{lang}wcf.acp.pageMenuItem.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="name" name="name" value="{$name}" />
						{if $errorField == 'name'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="nameHelpMessage">
						{lang}wcf.acp.pageMenuItem.name.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('name');
				//]]></script>
				
				<div class="formElement{if $errorField == 'link'} formError{/if}" id="linkDiv">
					<div class="formFieldLabel">
						<label for="link">{lang}wcf.acp.pageMenuItem.link{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="link" name="link" value="{$link}" />
						{if $errorField == 'link'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="linkHelpMessage">
						{lang}wcf.acp.pageMenuItem.link.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('link');
				//]]></script>
			</fieldset>
							
			<fieldset>
				<legend>{lang}wcf.acp.pageMenuItem.display{/lang}</legend>
					
				<div class="formElement" id="positionDiv">
					<div class="formFieldLabel">
						<label for="position">{lang}wcf.acp.pageMenuItem.position{/lang}</label>
					</div>
					<div class="formField">
						<select name="position" id="position">
							<option value="header"{if $position == "header"} selected="selected"{/if}>{lang}wcf.acp.pageMenuItem.position.header{/lang}</option>
							<option value="footer"{if $position == "footer"} selected="selected"{/if}>{lang}wcf.acp.pageMenuItem.position.footer{/lang}</option>
						</select>
					</div>
					<div class="formFieldDesc hidden" id="positionHelpMessage">
						{lang}wcf.acp.pageMenuItem.position.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('position');
				//]]></script>	
				
				<div class="formElement{if $errorField == 'iconS'} formError{/if}" id="iconSDiv">
					<div class="formFieldLabel">
						<label for="iconS">{lang}wcf.acp.pageMenuItem.iconS{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="iconS" name="iconS" value="{$iconS}" />
						{if $errorField == 'iconS'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="iconSHelpMessage">
						{lang}wcf.acp.pageMenuItem.iconS.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('iconS');
				//]]></script>
				
				<div class="formElement{if $errorField == 'iconM'} formError{/if}" id="iconMDiv">
					<div class="formFieldLabel">
						<label for="iconM">{lang}wcf.acp.pageMenuItem.iconM{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="iconM" name="iconM" value="{$iconM}" />
						{if $errorField == 'iconM'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="iconMHelpMessage">
						{lang}wcf.acp.pageMenuItem.iconM.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('iconM');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.pageMenuItem.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="showOrder" name="showOrder" value="{@$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.pageMenuItem.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>	
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{if $pageMenuItemID|isset}<input type="hidden" name="pageMenuItemID" value="{@$pageMenuItemID}" />{/if}
 		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}