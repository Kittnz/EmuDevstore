{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/templatePack{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.pack.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.template.pack.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=TemplatePackList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templatePackM.png" alt="" title="{lang}wcf.acp.menu.link.templatepack.view{/lang}" /> <span>{lang}wcf.acp.menu.link.templatepack.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=TemplatePack{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.template.pack{/lang}</legend>
				
				{if $availableTemplatePacks|count > 0}
					<div class="formElement{if $errorField == 'parentTemplatePackID'} formError{/if}">
						<div class="formFieldLabel">
							<label for="parentTemplatePackID">{lang}wcf.acp.template.pack.parent{/lang}</label>
						</div>
						<div class="formField">
							<select name="parentTemplatePackID" id="parentTemplatePackID">
								<option></option>
								{htmlOptions options=$availableTemplatePacks selected=$parentTemplatePackID disableEncoding=true}
							</select>
							{if $errorField == 'parentTemplatePackID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				{/if}
				
				<div class="formElement{if $errorField == 'templatePackName'} formError{/if}">
					<div class="formFieldLabel">
						<label for="templatePackName">{lang}wcf.acp.template.pack.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="templatePackName" id="templatePackName" value="{$templatePackName}" />
						{if $errorField == 'templatePackName'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.template.pack.name.error.notUnique{/lang}{/if}
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
								{if $errorType == 'notUnique'}{lang}wcf.acp.template.pack.folderName.error.notUnique{/lang}{/if}
							</p>
						{/if}
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
 		{if $templatePackID|isset}<input type="hidden" name="templatePackID" value="{@$templatePackID}" />{/if}
 	</div>
</form>

{include file='footer'}