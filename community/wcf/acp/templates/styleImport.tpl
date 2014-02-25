{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleImportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.import{/lang}</h2>
		<p>{lang}wcf.acp.style.import.description{/lang}</p>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.style.import.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=StyleList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.view{/lang}</span></a></li></ul>
	</div>
</div>
<form enctype="multipart/form-data" method="post" action="index.php?form=StyleImport">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.style.import.source{/lang}</legend>
				
				<div class="formElement{if $errorField == 'styleUpload'} formError{/if}">
					<div class="formFieldLabel">
						<label for="styleUpload">{lang}wcf.acp.style.import.styleUpload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" name="styleUpload" id="styleUpload" />
						{if $errorField == 'styleUpload'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalid'}{lang}wcf.acp.style.import.error.invalid{/lang}{/if}
								{if $errorType == 'uploadFailed'}{lang}wcf.acp.style.import.upload.error.failed{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'styleURL'} formError{/if}">
					<div class="formFieldLabel">
						<label for="styleURL">{lang}wcf.acp.style.import.styleURL{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="styleURL" id="styleURL" value="{$styleURL}" />
						{if $errorField == 'styleURL'}
							<p class="innerError">
								{if $errorType == 'invalid'}{lang}wcf.acp.style.import.error.invalid{/lang}{/if}
								{if $errorType == 'downloadFailed'}{lang}wcf.acp.style.import.download.error.failed{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
			
			{if $availableStyles|count > 0}
				<fieldset>
					<legend>{lang}wcf.acp.style.import.destination{/lang}</legend>
					
					<div class="formElement{if $errorField == 'destinationStyleID'} formError{/if}" id="destinationStyleIDDiv">
						<div class="formFieldLabel">
							<label for="destinationStyleID">{lang}wcf.acp.style.import.destinationStyle{/lang}</label>
						</div>
						<div class="formField">
							<select name="destinationStyleID" id="destinationStyleID">
								<option></option>
								{htmlOptions options=$availableStyles selected=$destinationStyleID}
							</select>
							{if $errorField == 'destinationStyleID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="destinationStyleIDHelpMessage">
							{lang}wcf.acp.style.import.destinationStyle.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('destinationStyleID');
					//]]></script>
				</fieldset>
			{/if}
			
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