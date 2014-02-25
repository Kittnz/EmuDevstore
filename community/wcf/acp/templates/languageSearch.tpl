{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	{if $replace == 0}
		onloadEvents.push(function() { hideOptions('replaceByDiv'); });
	{/if}
	{if $searchVariableName == 1}
		onloadEvents.push(function() { hideOptions('replace'); });
	{/if}
	//]]>
</script>


<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.search{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $noMatches|isset}
	<p class="error">{lang}wcf.acp.language.search.error.noMatches{/lang}</p>
{/if}

<form method="post" action="index.php?form=LanguageSearch">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.language.search.language{/lang}</legend>
				
				<div class="formElement{if $errorField == 'languageID'} formError{/if}" id="templateIDDiv">
					<div class="formFieldLabel">
						<label for="languageID">{lang}wcf.acp.language.search.language{/lang}</label>
					</div>
					<div class="formField">
						<select name="languageID" id="languageID">
							<option value="0">{lang}wcf.acp.language.search.language.all{/lang}</option>
							{htmlOptions options=$languages selected=$languageID disableEncoding=true}
						</select>
						{if $errorField == 'languageID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.language.search.search{/lang}</legend>
				<div class="formElement{if $errorField == 'query'} formError{/if}">
					<div class="formFieldLabel">
						<label for="query">{lang}wcf.acp.language.search.query{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="query" id="query" rows="10" cols="40">{$query}</textarea>
						{if $errorField == 'query'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalidRegex'}{lang}wcf.acp.language.search.query.error.invalidRegex{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="useRegex" value="1" {if $useRegex == 1}checked="checked" {/if}/> {lang}wcf.acp.language.search.useRegex{/lang}</label>
					</div>
				</div>
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="caseSensitive" value="1" {if $caseSensitive == 1}checked="checked" {/if}/> {lang}wcf.acp.language.search.caseSensitive{/lang}</label>
					</div>
				</div>
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" onclick="if (this.checked) hideOptions('replace'); else showOptions('replace');" name="searchVariableName" value="1" {if $searchVariableName == 1}checked="checked" {/if}/> {lang}wcf.acp.language.search.searchVariableName{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset id="replace">
				<legend><label><input type="checkbox" onclick="if (this.checked) showOptions('replaceByDiv'); else hideOptions('replaceByDiv');" name="replace" value="1" {if $replace == 1}checked="checked" {/if}/> {lang}wcf.acp.language.search.replace{/lang}</label></legend>
				<div id="replaceByDiv" class="formElement">
					<div class="formFieldLabel">
						<label for="replaceBy">{lang}wcf.acp.language.search.replaceBy{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="replaceBy" id="replaceBy" rows="10" cols="40">{$replaceBy}</textarea>
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