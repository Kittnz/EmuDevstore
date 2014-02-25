{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	{if $enable == 0}
		onloadEvents.push(function() { hideOptions('languageIDs'); });
	{/if}
	//]]>
</script>


<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.multilingualism{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.multilingualism.success{/lang}</p>	
{/if}

<form method="post" action="index.php?form=LanguageMultilingualism">
	<div class="border content">
		<div class="container-1">
			<div class="formElement" id="enableDiv">
				<div class="formField">
					<label><input type="checkbox" id="enable" onclick="if (this.checked) showOptions('languageIDs'); else hideOptions('languageIDs');" name="enable" value="1" {if $enable == 1}checked="checked" {/if}/> {lang}wcf.acp.language.multilingualism.enable{/lang}</label>
				</div>
				<div class="formFieldDesc hidden" id="enableHelpMessage">
					{lang}wcf.acp.language.multilingualism.enable.description{/lang}
				</div>
			</div>
			<script type="text/javascript">//<![CDATA[
				inlineHelp.register('enable');
			//]]></script>
			
			<div class="formGroup{if $errorField == 'languageIDs'} formError{/if}" id="languageIDs">
				<div class="formGroupLabel">
					<label>{lang}wcf.acp.language.multilingualism.languages{/lang}</label>
				</div>
				<div class="formGroupField">
					<fieldset>
						<legend>{lang}wcf.acp.language.multilingualism.languages{/lang}</legend>
						
						<div class="formField">
							{htmlCheckboxes options=$languages name=languageIDs selected=$languageIDs disableEncoding=true}
						</div>
					</fieldset>
					{if $errorField == 'languageIDs'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.acp.language.multilingualism.languages.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			
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