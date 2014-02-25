{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageExportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.export{/lang}</h2>
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
<form enctype="multipart/form-data" method="post" action="index.php?form=LanguageExport">
	<div class="border content">
		<div class="container-1">
			<div class="formElement">
				<p class="formFieldLabel">
					{lang}wcf.acp.language.search.language{/lang}
				</p>
				<div class="formField">
					{htmloptions options=$languages selected=$languageID name="languageID" disableEncoding=true}
				</div>
			</div>
			
			<div class="formElement">
				<p class="formFieldLabel">
					<label for="packageSelect">{lang}wcf.acp.language.export.selectPackages{/lang}</label>
				</p>
				<div class="formField">
					<select id="packageSelect" name="selectedPackages[]" multiple="multiple" size="20" style="font-family: Monaco, 'Courier New', Courier, monospace">
						<option value="*"{if $selectAllPackages} selected="selected"{/if}>{lang}wcf.acp.language.export.allPackages{/lang}</option>
						<option value="-">--------------------</option>
						{foreach from=$packages item=package}
						{assign var=loop value=$packageNameLength-$package->packageNameLength}
						<option value="{@$package->packageID}"{if $selectedPackages[$package->packageID]|isset} selected="selected"{/if}>{$package->packageName} {section name=i loop=$loop}&nbsp;{/section}&nbsp;&nbsp;{$package->package}</option>
						{/foreach}
					</select>
				</div>
			</div>
			
			<div class="formElement">
				<div class="formField">
					<label><input type="checkbox" name="exportCustomValues" /> {lang}wcf.acp.language.export.customValues{/lang}</label>
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