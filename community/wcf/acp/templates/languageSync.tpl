{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.sync{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $languages|count < 2}
	<p class="warning">{lang}wcf.acp.language.sync.warning.tooFewLanguages{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.sync.success{/lang}</p>	
{/if}

<form method="post" action="index.php?form=LanguageSync">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.language.sync.source{/lang}</legend>
				
				<div class="formElement{if $errorField == 'sourceLanguageID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="sourceLanguageID">{lang}wcf.acp.language.sync.source{/lang}</label>
					</div>
					<div class="formField">
						<select name="sourceLanguageID" id="sourceLanguageID">
						{foreach from=$languages key=languageID item=language}
							<option value="{@$languageID}"{if $languageID == $sourceLanguageID} selected="selected"{/if}>{@$language} ({$languageCodes[$languageID]})</option>
						{/foreach}
						</select>
						{if $errorField == 'sourceLanguageID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<p class="formFieldDesc">{lang}wcf.acp.language.sync.source.description{/lang}</p>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" name="send"accesskey="s" value="{lang}wcf.global.button.submit{/lang}" {if $languages|count < 2}disabled="disabled" {/if}/>
		<input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.preview{/lang}" {if $languages|count < 2}disabled="disabled" {/if}/>
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
	</div>
	
	{if $preview}
	<div class="border content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.global.button.preview{/lang}</h3>
			{if $languageDiff|count}
			<div class="border">
				<table class="tableList">
					<thead>
						<tr class="tableHead">
							<th><span class="emptyHead">{lang}wcf.acp.language.variable{/lang}</span></th>
							{foreach from=$languages key=languageID item=language}
								{if $sourceLanguageID != $languageID}<th class="columnIcon"><span class="emptyHead">{$languageCodes[$languageID]}</span></th>{/if}
							{/foreach}
						</tr>
					</thead>
					<tbody>
						
						{foreach from=$languageDiff key=$languageItem item=itemDiff}
						<tr class="{cycle values='container-1, container-2'}">
							<td>
							{if $languageItemIDs[$languageItem]|isset}
								<a href="index.php?form=LanguageEdit&amp;languageID={$sourceLanguageID}&amp;languageItemID={@$languageItemIDs[$languageItem]}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}#languageItem{@$languageItemIDs[$languageItem]}">{@$languageItem}</a>
							{else}
								{@$languageItem}
							{/if}
							</td>
							{foreach from=$languages key=languageID item=language}
								{if $sourceLanguageID != $languageID}{if $itemDiff[$languageID]|isset}{if $itemDiff[$languageID] === true}<td style="background-color: #efe; color: #090; text-align: center;">+</td>{else}<td style="background-color: #fee; color: #c00; text-align: center;">-</td>{/if}{else}<td>&nbsp;</td>{/if}{/if}
							{/foreach}
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<p>--</p>
			{/if}
		</div>
	</div>
	{/if}
	
</form>

{include file='footer'}