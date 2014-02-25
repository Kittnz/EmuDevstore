{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	onloadEvents.push(function() { {if $mode == 'import'}hideOptions('copyDiv');{else}hideOptions('importDiv');{/if} });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageAddL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.add{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.add.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=LanguageList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/languageM.png" alt="" title="{lang}wcf.acp.menu.link.language.view{/lang}" /> <span>{lang}wcf.acp.menu.link.language.view{/lang}</span></a></li></ul>
	</div>
</div>
<form enctype="multipart/form-data" method="post" action="index.php?form=LanguageAdd">
	<div class="border content">
		<div class="container-1">
			<div class="formElement">
				<p class="formFieldLabel">
					{lang}wcf.acp.language.add.mode{/lang}
				</p>
				<div class="formField">
					<label><input onclick="if (IS_SAFARI) showOptions('importDiv') + hideOptions('copyDiv');" onfocus="showOptions('importDiv') + hideOptions('copyDiv');" type="radio" name="mode" value="import" {if $mode == 'import'}checked="checked" {/if}/> {lang}wcf.acp.language.add.mode.import{/lang}</label>
					<label><input onclick="if (IS_SAFARI) showOptions('copyDiv') + hideOptions('importDiv');" onfocus="showOptions('copyDiv') + hideOptions('importDiv');" type="radio" name="mode" value="copy" {if $mode == 'copy'}checked="checked" {/if}/> {lang}wcf.acp.language.add.mode.copy{/lang}</label>
				</div>
			</div>
			
			<fieldset id="importDiv">
				<legend>{lang}wcf.acp.language.import.source{/lang}</legend>
				
				<div class="formElement{if $errorField == 'languageFile'} formError{/if}">
					<div class="formFieldLabel">
						<label for="languageFile">{lang}wcf.acp.language.import.source.file{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="languageFile" id="languageFile" value="{$languageFile}" />
						{if $errorField == 'languageFile'}
							<p class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.error.empty{/lang}
								{else}
									{lang}wcf.acp.language.import.error{/lang} {$errorType}
								{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.language.import.source.file.description{/lang}</p>
					</div>
				</div>
				<div class="formElement{if $errorField == 'languageUpload'} formError{/if}">
					<div class="formFieldLabel">
						<label for="languageUpload">{lang}wcf.acp.language.import.source.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" name="languageUpload" id="languageUpload" />
						{if $errorField == 'languageUpload'}
							<p class="innerError">
								{lang}wcf.acp.language.import.error{/lang} {$errorType}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
		
			<fieldset id="copyDiv">
				<legend>{lang}wcf.acp.language.add.new{/lang}</legend>
				
				<div class="formElement{if $errorField == 'languageCode'} formError{/if}">
					<div class="formFieldLabel">
						<label for="languageCode">{lang}wcf.acp.language.code{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="languageCode" id="languageCode" value="{$languageCode}" />
						{if $errorField == 'languageCode'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.language.add.languageCode.error.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.language.code.description{/lang}</p>
					</div>
				</div>
				<div class="formElement{if $errorField == 'sourceLanguageID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="sourceLanguageID">{lang}wcf.acp.language.add.source{/lang}</label>
					</div>
					<div class="formField">
						{htmlOptions options=$languages selected=$sourceLanguageID name=sourceLanguageID id=sourceLanguageID disableEncoding=true}
						{if $errorField == 'sourceLanguageID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.acp.language.add.source.description{/lang}</p>
					</div>
				</div>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}