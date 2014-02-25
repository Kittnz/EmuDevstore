{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu({
		{if PAGE_DIRECTION == 'ltr'}
			imgPrevious: 		'{@RELATIVE_WCF_DIR}icon/previousS.png',
			imgPreviousDisabled: 	'{@RELATIVE_WCF_DIR}icon/previousDisabledS.png',
			imgNext: 		'{@RELATIVE_WCF_DIR}icon/nextS.png',
			imgNextDisabled: 	'{@RELATIVE_WCF_DIR}icon/nextDisabledS.png'
		{else}
			imgPrevious: 		'{@RELATIVE_WCF_DIR}icon/nextS.png',
			imgPreviousDisabled: 	'{@RELATIVE_WCF_DIR}icon/nextDisabledS.png',
			imgNext: 		'{@RELATIVE_WCF_DIR}icon/previousS.png',
			imgNextDisabled: 	'{@RELATIVE_WCF_DIR}icon/previousDisabledS.png'
		{/if}	
	});
	onloadEvents.push(function() { tabMenu.showSubTabMenu("{$activeTabMenuItem}"); });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/optionL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.option.category.{$category.categoryName}{/lang}</h2>
		<p>{lang}wcf.acp.option.category.{$category.categoryName}.description{/lang}</p>
	</div>
</div>

{if $success|isset}
	<p class="success">{lang}wcf.acp.option.success{/lang}</p>
{/if}

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=Option&amp;categoryID={@$category.categoryID}">
	<div class="tabMenu">
		<ul>
			{foreach from=$options item=categoryLevel1}
				<li id="{@$categoryLevel1.categoryName}"><a onclick="tabMenu.showSubTabMenu('{@$categoryLevel1.categoryName}');"><span>{lang}wcf.acp.option.category.{@$categoryLevel1.categoryName}{/lang}</span></a></li>
			{/foreach}
		</ul>
	</div>
	<div class="subTabMenu">
		<div class="containerHead"><div> </div></div>
	</div>
	
	{foreach from=$options item=categoryLevel1}
		<div class="border tabMenuContent hidden" id="{@$categoryLevel1.categoryName}-content">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.acp.option.category.{@$categoryLevel1.categoryName}{/lang}</h3>
				<p class="description">{lang}wcf.acp.option.category.{$categoryLevel1.categoryName}.description{/lang}</p>
				
				{if $categoryLevel1.options|isset && $categoryLevel1.options|count}
					<fieldset>
						<legend>{lang}wcf.acp.option.category.{$categoryLevel1.categoryName}{/lang}</legend>
						{include file='optionFieldList' options=$categoryLevel1.options langPrefix='wcf.acp.option.'}
					</fieldset>
				{/if}
				
				{if $categoryLevel1.categories|isset}
					{foreach from=$categoryLevel1.categories item=categoryLevel2}
						<fieldset>
							<legend>{lang}wcf.acp.option.category.{@$categoryLevel2.categoryName}{/lang}</legend>
							<p class="description">{lang}wcf.acp.option.category.{$categoryLevel2.categoryName}.description{/lang}</p>
							
							{include file='optionFieldList' options=$categoryLevel2.options langPrefix='wcf.acp.option.'}
						</fieldset>
					{/foreach}
				{/if}
			</div>
		</div>
	{/foreach}
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
	</div>
</form>

{include file='footer'}