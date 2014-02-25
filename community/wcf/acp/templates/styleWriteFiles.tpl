{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleRefreshL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.writeFiles{/lang}</h2>
		<p>{lang}wcf.acp.style.writeFiles.description{/lang}</p>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.style.writeFiles.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=StyleList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/styleM.png" alt="" title="{lang}wcf.acp.menu.link.style.view{/lang}" /> <span>{lang}wcf.acp.menu.link.style.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=StyleWriteFiles">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.style.writeFiles.styles{/lang}</legend>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.style.writeFiles.styles{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.style.writeFiles.styles{/lang}</legend>
							
							<div class="formField">
								{if $styles|count > 1}<label><input onclick="checkUncheckAll(document.getElementById('styles'))" type="checkbox" name="all" value="1" checked="checked" /> {lang}wcf.acp.style.writeFiles.styles.all{/lang}</label>{/if}
								<div id="styles">
									{foreach from=$styles item=style}
										<label><input type="checkbox" name="styleIDArray[]" value="{@$style->styleID}" /> {$style->styleName} (/style/style-{@$style->styleID}.css)</label>
									{/foreach}
								</div>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<script type="text/javascript">
			//<![CDATA[
				onloadEvents.push(function() {
					checkUncheckAll(document.getElementById('styles'));
				});
			//]]>
			</script>
			
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