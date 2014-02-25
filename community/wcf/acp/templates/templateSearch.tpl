{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	{if $allTemplates == 1}
		onloadEvents.push(function() { hideOptions('templateIDDiv', 'invertTemplatesDiv'); });
	{/if}
	{if $replace == 0}
		onloadEvents.push(function() { hideOptions('replaceByDiv'); });
	{/if}
	{if $invertSearch == 1}
		onloadEvents.push(function() { hideOptions('replace'); });
	{/if}
	//]]>
</script>


<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/templateSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.search{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $noMatches|isset}
	<p class="error">{lang}wcf.acp.template.search.error.noMatches{/lang}</p>
{/if}

<form method="post" action="index.php?form=TemplateSearch">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.template.search.templates{/lang}</legend>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" onclick="if (this.checked) hideOptions('templateIDDiv', 'invertTemplatesDiv'); else showOptions('templateIDDiv', 'invertTemplatesDiv');" name="allTemplates" value="1" {if $allTemplates == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.templates.all{/lang}</label>
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'templateID'} formError{/if}" id="templateIDDiv">
					<div class="formFieldLabel">
						<label for="templateID">{lang}wcf.acp.template.search.templates.names{/lang}</label>
					</div>
					<div class="formField">
						<select size="10" style="width: 99%" name="templateID[]" id="templateID" multiple="multiple">
							{htmlOptions options=$templates selected=$templateID}
						</select>
						{if $errorField == 'templateID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement" id="invertTemplatesDiv">
					<div class="formField">
						<label><input type="checkbox" name="invertTemplates" value="1" {if $invertTemplates == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.invertTemplates{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.template.search.search{/lang}</legend>
				<div class="formElement{if $errorField == 'query'} formError{/if}">
					<div class="formFieldLabel">
						<label for="query">{lang}wcf.acp.template.search.query{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="query" id="query" rows="10" cols="40">{$query}</textarea>
						{if $errorField == 'query'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalidRegex'}{lang}wcf.acp.template.search.query.error.invalidRegex{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="useRegex" value="1" {if $useRegex == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.useRegex{/lang}</label>
					</div>
				</div>
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="caseSensitive" value="1" {if $caseSensitive == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.caseSensitive{/lang}</label>
					</div>
				</div>
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" onclick="if (this.checked) hideOptions('replace'); else showOptions('replace');" name="invertSearch" value="1" {if $invertSearch == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.invertSearch{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset id="replace">
				<legend><label><input type="checkbox" onclick="if (this.checked) showOptions('replaceByDiv'); else hideOptions('replaceByDiv');" name="replace" value="1" {if $replace == 1}checked="checked" {/if}/> {lang}wcf.acp.template.search.replace{/lang}</label></legend>
				<div id="replaceByDiv" class="formElement">
					<div class="formFieldLabel">
						<label for="replaceBy">{lang}wcf.acp.template.search.replaceBy{/lang}</label>
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