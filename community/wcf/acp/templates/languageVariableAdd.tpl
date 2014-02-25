{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.variable.add{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.language.variable.add.success{/lang}</p>	
{/if}

<form method="post" action="index.php?form=LanguageVariableAdd">
	<div class="border content">
		<div class="container-1">
			<fieldset class="content">
				<legend>{lang}wcf.acp.language.variable{/lang}</legend>
			
				<div class="formElement{if $errorField == 'languageCategoryID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="languageCategoryID">{lang}wcf.acp.language.category{/lang}</label>
					</div>
					<div class="formField">
						<select name="languageCategoryID" id="languageCategoryID">
							{htmlOptions options=$languageCategories selected=$languageCategoryID}
						</select>
						{if $errorField == 'languageCategoryID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				<div class="formElement{if $errorField == 'newLanguageCategory'} formError{/if}">
					<div class="formFieldLabel">
						<label for="newLanguageCategory">{lang}wcf.acp.language.category.new{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="newLanguageCategory" id="newLanguageCategory" value="{$newLanguageCategory}" />
						{if $errorField == 'newLanguageCategory'}
							<p class="innerError">
								{if $errorType == 'invalid'}{lang}wcf.acp.language.category.new.error.invalid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.language.category.new.error.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				<div class="formElement{if $errorField == 'languageItemName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="languageItemName">{lang}wcf.acp.language.variable.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="languageItemName" id="languageItemName" value="{$languageItemName}" />
						{if $errorField == 'languageItemName'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalid'}{lang}wcf.acp.language.variable.name.error.invalid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.language.variable.name.error.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
		
			<fieldset class="content">
				<legend>{lang}wcf.acp.language.variable.value{/lang}</legend>
				
				{foreach from=$languages key=$languageID item=language}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="language-{@$languageID}">
								<span>{lang}wcf.global.language.{@$language.languageCode}{/lang} ({@$language.languageCode})</span>
								<img src="{@RELATIVE_WCF_DIR}icon/language{@$language.languageCode|ucfirst}S.png" alt="" />
							</label>
						</div>
						<div class="formField">
							<textarea rows="5" cols="60" class="textareaSmall" onfocus="this.className=''; this.select();" onblur="this.className='textareaSmall'"name="languageItemValues[{@$languageID}]" id="language-{@$languageID}">{if $languageItemValues.$languageID|isset}{$languageItemValues.$languageID}{/if}</textarea>
						</div>
					</div>
				{/foreach}
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