{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/avatarEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.avatar.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.avatar.edit.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=AvatarList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.avatar.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarM.png" alt="" /> <span>{lang}wcf.acp.menu.link.avatar.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=AvatarEdit">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.avatar{/lang}</legend>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.avatar.name{/lang}</label>
					</div>
					<div class="formField">
						<span>{$avatar->avatarName}</span>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="upload">{lang}wcf.acp.avatar.image{/lang}</label>
					</div>
					<div class="formField">
						<span>{@$avatar}</span>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.avatar.data{/lang}</legend>
				
				{if $availableAvatarCategories|count}
					<div class="formElement{if $errorField == 'avatarCategoryID'} formError{/if}" id="avatarCategoryIDDiv">
						<div class="formFieldLabel">
							<label for="avatarCategoryID">{lang}wcf.acp.avatar.category{/lang}</label>
						</div>
						<div class="formField">
							<select name="avatarCategoryID" id="avatarCategoryID">
								<option value="0"></option>
								{htmlOptions options=$availableAvatarCategories selected=$avatarCategoryID}
							</select>
							{if $errorField == 'avatarCategoryID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="avatarCategoryIDHelpMessage">
							{lang}wcf.acp.avatar.category.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('avatarCategoryID');
					//]]></script>
				{/if}
				
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
						{lang}wcf.acp.avatar.group.description{/lang}
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
						{lang}wcf.acp.avatar.neededPoints.description{/lang}
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
 		<input type="hidden" name="avatarID" value="{@$avatarID}" />
 	</div>
</form>

{include file='footer'}