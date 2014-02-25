{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/smileyEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.smiley.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.smiley.edit.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=SmileyList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyM.png" alt="" title="{lang}wcf.acp.menu.link.smiley.view{/lang}" /> <span>{lang}wcf.acp.menu.link.smiley.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=SmileyEdit">
	
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.smiley.source{/lang}</legend>
				<div class="formElement{if $errorField == 'path'} formError{/if}" id="pathDiv">
					<div class="formFieldLabel">
						<label for="path">{lang}wcf.acp.smiley.path{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="path" id="path" value="{$path}" />
						{if $errorField == 'path'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notFound'}{lang}wcf.global.error.file.notFound{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="pathHelpMessage">
						{lang}wcf.acp.smiley.path.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('path');
				//]]></script>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.smiley.image{/lang}</label>
					</div>
					<div class="formField">
						<span>{@$smiley}</span>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.smiley.data{/lang}</legend>
				
				{if $availableSmileyCategories|count}
					<div class="formElement{if $errorField == 'smileyCategoryID'} formError{/if}" id="smileyCategoryIDDiv">
						<div class="formFieldLabel">
							<label for="smileyCategoryID">{lang}wcf.acp.smiley.category{/lang}</label>
						</div>
						<div class="formField">
							<select name="smileyCategoryID" id="smileyCategoryID">
								<option value="0"></option>
								{htmlOptions options=$availableSmileyCategories selected=$smileyCategoryID}
							</select>
							{if $errorField == 'smileyCategoryID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="smileyCategoryIDHelpMessage">
							{lang}wcf.acp.smiley.category.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('smileyCategoryID');
					//]]></script>
				{/if}
				
				<div class="formElement{if $errorField == 'title'} formError{/if}" id="titleDiv">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.acp.smiley.title{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="title" id="title" value="{$title}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="titleHelpMessage">
						{lang}wcf.acp.smiley.title.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formElement{if $errorField == 'code'} formError{/if}" id="codeDiv">
					<div class="formFieldLabel">
						<label for="code">{lang}wcf.acp.smiley.code{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="code" id="code" value="{$code}" />
						{if $errorField == 'code'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.smiley.error.code.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="codeHelpMessage">
						{lang}wcf.acp.smiley.code.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('code');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.smiley.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="showOrder" id="showOrder" value="{@$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.smiley.showOrder.description{/lang}
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
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="smileyID" value="{@$smileyID}" />
 	</div>
</form>

{include file='footer'}