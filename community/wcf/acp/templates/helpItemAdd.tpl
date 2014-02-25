{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/helpItem{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.helpItem.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.helpItem.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=HelpItemList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/helpItemM.png" alt="" title="{lang}wcf.acp.menu.link.helpItem.view{/lang}" /> <span>{lang}wcf.acp.menu.link.helpItem.view{/lang}</span></a></li></ul>
	</div>
</div>

<form method="post" action="index.php?form=HelpItem{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.helpItem.classification{/lang}</legend>	
							
				<div class="formElement" id="parentItemDiv">
					<div class="formFieldLabel">
						<label for="parentItem">{lang}wcf.acp.helpItem.parentHelpItem{/lang}</label>
					</div>
					<div class="formField">
						<select name="parentItem" id="parentItem">
							<option value=""></option>
							{htmlOptions options=$items selected=$parentItem disableEncoding=true}
						</select>
					</div>
					<div class="formFieldDesc hidden" id="parentItemHelpMessage">
						{lang}wcf.acp.helpItem.parentHelpItem.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('parentItem');
				//]]></script>
							
				<div class="formElement" id="refererPatternDiv">
					<div class="formFieldLabel">
						<label for="refererPattern">{lang}wcf.acp.helpItem.refererPattern{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="refererPattern" name="refererPattern" value="{$refererPattern}" />
					</div>
					<div class="formFieldDesc hidden" id="refererPatternHelpMessage">
						{lang}wcf.acp.helpItem.refererPattern.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('refererPattern');
				//]]></script>
								
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.helpItem.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="showOrder" name="showOrder" value="{$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.helpItem.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>	
									
				<div class="formElement" id="isDisabledDiv">
					<div class="formField">
						<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1" {if $isDisabled == 1}checked="checked" {/if}/> {lang}wcf.acp.helpItem.isDisabled{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="isDisabledHelpMessage">
						{lang}wcf.acp.helpItem.isDisabled.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('isDisabled');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.helpItem.data{/lang}</legend>
				
				{if $action == 'edit'}
					<div class="formElement" id="languageIDDiv">
						<div class="formFieldLabel">
							<label for="languageID">{lang}wcf.user.language{/lang}</label>
						</div>
						<div class="formField">
							<select name="languageID" id="languageID"  onchange="location.href='index.php?form=HelpItemEdit&amp;helpItemID={@$helpItemID}&amp;languageID=' + this.value + '&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}'">
								{foreach from=$languages key=key item=language}
									<option value="{@$key}"{if $key == $languageID} selected="selected"{/if}>
										{lang}wcf.global.language.{@$language}{/lang}
									</option>
								{/foreach}						
							</select>
						</div>
						<div class="formFieldDesc hidden" id="languageIDHelpMessage">
							{lang}wcf.acp.helpItem.language.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('languageID');
					//]]></script>
				{/if}
				
				<div class="formElement{if $errorField == 'topic'} formError{/if}" id="topicDiv">
					<div class="formFieldLabel">
						<label for="topic">{lang}wcf.acp.helpItem.topic{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="topic" name="topic" value="{$topic}" />
						{if $errorField == 'topic'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="topicHelpMessage">
						{lang}wcf.acp.helpItem.topic.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('topic');
				//]]></script>
							
				<div class="formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
					<div class="formFieldLabel">
						<label for="text">{lang}wcf.acp.helpItem.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="text" id="text" rows="20" cols="40">{$text}</textarea>
						{if $errorField == 'text'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="textHelpMessage">
						{lang}wcf.acp.helpItem.text.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('text');
				//]]></script>
			</fieldset>
						
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{if $helpItemID|isset}<input type="hidden" name="helpItemID" value="{@$helpItemID}" />{/if}
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}