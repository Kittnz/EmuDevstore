{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/avatarAddL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.avatar.add{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $savedAvatars|isset && $savedAvatars > 0}
	<p class="success">{lang}wcf.acp.avatar.add.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=AvatarList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.avatar.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarM.png" alt="" /> <span>{lang}wcf.acp.menu.link.avatar.view{/lang}</span></a></li></ul>
	</div>
</div>
<form enctype="multipart/form-data" method="post" action="index.php?form=AvatarAdd">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.avatar.add.source{/lang}</legend>
				<div class="formElement{if $errorField == 'upload'} formError{/if}" id="uploadDiv">
					<div class="formFieldLabel">
						<label for="upload">{lang}wcf.acp.avatar.add.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" name="upload" id="upload" />
						{if $errorField == 'upload'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{$error.filename}:
											{if $error.errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
											{if $error.errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
											{if $error.errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
											{if $error.errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
										</p>
									{/foreach}
								{else}
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'uploadFailed'}{lang}wcf.user.avatar.error.avatarUpload.uploadFailed{/lang}{/if}
									{if $errorType == 'emptyArchive'}{lang}wcf.acp.avatar.error.emptyArchive{/lang}{/if}
									{if $errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
									{if $errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
									{if $errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
									{if $errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
								{/if}
							</div>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="uploadHelpMessage">
						{lang}wcf.acp.avatar.add.upload.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('upload');
				//]]></script>
				
				<div class="formElement{if $errorField == 'filename'} formError{/if}" id="filenameDiv">
					<div class="formFieldLabel">
						<label for="filename">{lang}wcf.acp.avatar.add.filename{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="filename" id="filename" value="{$filename}" />
						{if $errorField == 'filename'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{$error.filename}:
											{if $error.errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
											{if $error.errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
											{if $error.errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
											{if $error.errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
										</p>
									{/foreach}
								{else}
									{if $errorType == 'notFound'}{lang}wcf.global.error.file.notFound{/lang}{/if}
									{if $errorType == 'emptyFolder'}{lang}wcf.acp.avatar.error.emptyFolder{/lang}{/if}
									{if $errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
									{if $errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
									{if $errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
									{if $errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
								{/if}
							</div>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="filenameHelpMessage">
						{lang}wcf.acp.avatar.add.filename.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('filename');
				//]]></script>
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
 	</div>
</form>

{include file='footer'}