{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleExportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.export{/lang}</h2>
		<p>{$style->styleName}</p>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=StyleList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=StyleExport">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.style.export.files{/lang}</legend>
				
				{if $canExportTemplates}
					<div class="formElement">
						<label><input type="checkbox" name="exportTemplates" value="1" {if $exportTemplates == 1}checked="checked" {/if}/> {lang}wcf.acp.style.export.files.templates{/lang} ({$templatePackName})</label>
					</div>
				{/if}
				<div class="formElement">
					<label><input type="checkbox" name="exportImages" value="1" {if $exportImages == 1}checked="checked" {/if}/> {lang}wcf.acp.style.export.files.images{/lang} ({$imagesLocation})</label>
				</div>
				{if $canExportIcons}
					<div class="formElement">
						<label><input type="checkbox" name="exportIcons" value="1" {if $exportIcons == 1}checked="checked" {/if}/> {lang}wcf.acp.style.export.files.icons{/lang} ({$iconsLocation})</label>
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
 		<input type="hidden" name="styleID" value="{@$styleID}" />
 	</div>
</form>

{include file='footer'}