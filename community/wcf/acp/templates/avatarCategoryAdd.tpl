{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	document.observe('dom:loaded', function() {
		$('title').focus();
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/avatarCategory{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.avatar.category.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.avatar.category.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=AvatarCategoryList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.avatar.category.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarCategoryM.png" alt="" /> <span>{lang}wcf.acp.menu.link.avatar.category.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=AvatarCategory{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.avatar.category.general{/lang}</legend>
				
				<div class="formElement{if $errorField == 'title'} formError{/if}" id="titleDiv">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.acp.avatar.category.title{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="title" id="title" value="{$title}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="titleHelpMessage">
						{lang}wcf.acp.avatar.category.title.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.avatar.category.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="showOrder" id="showOrder" value="{@$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.avatar.category.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.avatar.category.permissions{/lang}</legend>
				
				<div class="formElement{if $errorField == 'groupID'} formError{/if}" id="groupIDDiv">
					<div class="formFieldLabel">
						<label for="groupID">{lang}wcf.acp.avatar.group{/lang}</label>
					</div>
					<div class="formField">
						<select name="groupID" id="groupID">
							{foreach from=$groups key=groupKey item=groupName}
								<option value="{@$groupKey}"{if $groupID == $groupKey} selected="selected"{/if}>{lang}{$groupName}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'groupID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="groupIDHelpMessage">
						{lang}wcf.acp.avatar.group.category.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('groupID');
				//]]></script>
				
				<div class="formElement" id="neededPointsDiv">
					<div class="formFieldLabel">
						<label for="neededPoints">{lang}wcf.acp.avatar.neededPoints{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" name="neededPoints" id="neededPoints" value="{@$neededPoints}" />
					</div>
					<div class="formFieldDesc hidden" id="neededPointsHelpMessage">
						{lang}wcf.acp.avatar.neededPoints.category.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('neededPoints');
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
 		{if $avatarCategoryID|isset}<input type="hidden" name="avatarCategoryID" value="{@$avatarCategoryID}" />{/if}
 	</div>
</form>

{include file='footer'}