{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleSetDefaultL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.toPackage{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.style.toPackage.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=StyleList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=StyleToPackage">
	<div class="border content">
		<div class="container-1">
			{foreach from=$packages item=package}
				<fieldset>
					<legend>{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</legend>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="defaultStyle-{@$package.packageID}">{lang}wcf.acp.style.toPackage.default{/lang}</label>
						</div>
						<div class="formField">
							<select name="defaultStyleIDArray[{@$package.packageID}]" id="defaultStyle-{@$package.packageID}">
								<option value="0"></option>
								{if $defaultStyleIDArray[$package.packageID]|isset}
									{htmlOptions options=$styles selected=$defaultStyleIDArray[$package.packageID]}
								{else}
									{htmlOptions options=$styles}
								{/if}
							</select>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.style.toPackage.disabled{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.style.toPackage.disabled{/lang}</legend>
								
								<div class="formField">
									{if $styles|count > 1}<label><input onclick="checkUncheckAll(document.getElementById('styles-{@$package.packageID}'))" type="checkbox" name="all" value="1" /> {lang}wcf.acp.style.toPackage.disabled.all{/lang}</label>{/if}
									<div id="styles-{@$package.packageID}">
										{foreach from=$styles item=style}
											<label><input type="checkbox" name="disabledStyleIDArray[{@$package.packageID}][]" value="{@$style->styleID}" {if $disabledStyleIDArray[$package.packageID]|isset && $style->styleID|in_array:$disabledStyleIDArray[$package.packageID]} checked="checked"{/if}/> {$style->styleName}</label>
										{/foreach}
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</fieldset>
			{/foreach}
			
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