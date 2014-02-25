{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/template{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.template.{@$action}.success{/lang}</p>	
{/if}

{if $templateID|isset && !$template->templatePackID}
	<p class="warning">{lang}wcf.acp.template.edit.warning.canNotEditDefaultTemplates{/lang}</p>
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=TemplateList{if $templateID|isset}&amp;templatePackID={@$template->templatePackID}{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templateM.png" alt="" title="{lang}wcf.acp.menu.link.template.view{/lang}" /> <span>{lang}wcf.acp.menu.link.template.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=Template{@$action|ucfirst}">
	
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.template.pack{/lang}</legend>
				
				{if $templatePacks|count > 0}
					<div class="formElement{if $errorField == 'templatePackID'} formError{/if}">
						<div class="formFieldLabel">
							<label for="templatePackID">{lang}wcf.acp.template.pack{/lang}</label>
						</div>
						<div class="formField">
							<select name="templatePackID" id="templatePackID">
								{htmlOptions options=$templatePacks selected=$templatePackID}
							</select>
							{if $errorField == 'templatePackID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				{else}
					<div class="formElement{if $errorField == 'templatePackName'} formError{/if}">
						<div class="formFieldLabel">
							<label for="templatePackName">{lang}wcf.acp.template.pack.name{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="templatePackName" id="templatePackName" value="{$templatePackName}" />
							{if $errorField == 'templatePackName'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'templatePackFolderName'} formError{/if}">
						<div class="formFieldLabel">
							<label for="templatePackFolderName">{lang}wcf.acp.template.pack.folderName{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="templatePackFolderName" id="templatePackFolderName" value="{$templatePackFolderName}" />
							{if $errorField == 'templatePackFolderName'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				{/if}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.template{/lang}</legend>
				<div class="formElement{if $errorField == 'templateName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="templateName">{lang}wcf.acp.template.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="templateName" id="templateName" value="{$templateName}" />
						{if $errorField == 'templateName'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.template.name.error.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement">
					<div>
						<label for="source">{lang}wcf.acp.template.source{/lang}</label>
					</div>
					<div>
						<textarea name="source" id="source" rows="20" cols="40" wrap="off">{$source}</textarea>
					</div>
				</div>
				
				{if $templateID|isset}
					<div class="formElement">
						<div>
							<label><input type="checkbox" name="copy" value="1" {if !$template->templatePackID || $copy == 1}checked="checked" {/if}{if !$template->templatePackID}disabled="disabled" {/if}/> {lang}wcf.acp.template.copy{/lang}</label>
							{if !$template->templatePackID}<input type="hidden" name="copy" value="1" />{/if}
						</div>
					</div>
				{/if}
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $templateID|isset}<input type="hidden" name="templateID" value="{@$templateID}" />{/if}
 	</div>
</form>

{include file='footer'}