{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userOptionCategory{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.option.category.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.user.option.category.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UserOptionCategoryList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionCategoryM.png" alt="" title="{lang}wcf.acp.menu.link.user.option.category.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.option.category.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UserOptionCategory{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.option.category.general{/lang}</legend>
				
				<div class="formElement{if $errorField == 'categoryName'} formError{/if}" id="categoryNameDiv">
					<div class="formFieldLabel">
						<label for="categoryName">{lang}wcf.acp.user.option.category.name{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="categoryName" id="categoryName" value="{$categoryName}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="categoryNameHelpMessage">
						{lang}wcf.acp.user.option.category.name.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('categoryName');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.user.option.category.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="showOrder" id="showOrder" value="{@$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.user.option.category.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $categoryID|isset}<input type="hidden" name="categoryID" value="{@$categoryID}" />{/if}
 	</div>
</form>

{include file='footer'}