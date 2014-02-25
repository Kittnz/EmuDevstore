{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userOptionSetDefaultsL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.option.setDefaults{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.user.option.setDefaults.success{/lang}</p>	
{/if}

<form method="post" action="index.php?form=UserOptionSetDefaults">
	<div class="border content">
		<div class="container-1">
			<div class="formElement" id="applyChangesToExistingUsersDiv">
				<div class="formField">
					<label><input type="checkbox" name="applyChangesToExistingUsers" id="applyChangesToExistingUsers" value="1" {if $applyChangesToExistingUsers == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers{/lang}</label>
				</div>
				<div class="formFieldDesc hidden" id="applyChangesToExistingUsersHelpMessage">
					{lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers.description{/lang}
				</div>
			</div>
			<script type="text/javascript">//<![CDATA[
				inlineHelp.register('applyChangesToExistingUsers');
			//]]></script>
			
			{foreach from=$options item=category}
				<fieldset>
					<legend>{lang}wcf.user.option.category.{@$category.categoryName}{/lang}</legend>
					
					{include file='optionFieldList' options=$category.options langPrefix='wcf.user.option.'}
				</fieldset>
			{/foreach}
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