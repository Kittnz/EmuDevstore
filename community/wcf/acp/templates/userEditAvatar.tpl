<div class="border tabMenuContent hidden" id="avatar-content">
	<div class="container-1">
		<h3 class="subHeadline">{lang}wcf.user.avatar{/lang}</h3>
		
		<div class="formElement">
			<div class="formField">
				<label><input type="radio" name="useAvatar" value="0"{if $useAvatar == 0} checked="checked"{/if} /> {lang}wcf.user.avatar.disable{/lang}</label>
			</div>
		</div>
		
		<fieldset>
			<legend>{lang}wcf.user.avatar.currentAvatar{/lang}</legend>
			{if $currentAvatar}
				{@$currentAvatar}
			{else}
				<img src="{@RELATIVE_WCF_DIR}/images/avatars/avatar-default.png" alt="" />
			{/if}
		</fieldset>
		
		<fieldset>
			<legend>
				<label><input type="radio" name="useAvatar" value="1"{if $useAvatar == 1} checked="checked"{/if} /> {lang}wcf.user.avatar.ownAvatar{/lang}</label>
			</legend>
			
			<div class="formElement{if $errorType.avatarUpload|isset} formError{/if}">
				<div class="formFieldLabel">
					<label for="avatarUpload">{lang}wcf.user.avatar.avatarUpload{/lang}</label>
				</div>
				<div class="formField">
					<input type="file" name="avatarUpload" value="" id="avatarUpload" size="50" />
					{if $errorType.avatarUpload|isset}
						<p class="innerError">
							{if $errorType.avatarUpload == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType.avatarUpload == 'uploadFailed'}{lang}wcf.user.avatar.error.avatarUpload.uploadFailed{/lang}{/if}
							{if $errorType.avatarUpload == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
							{if $errorType.avatarUpload == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
							{if $errorType.avatarUpload == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
							{if $errorType.avatarUpload == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			<div class="formElement{if $errorType.avatarURL|isset} formError{/if}">
				<div class="formFieldLabel">
					<label for="avatarURL">{lang}wcf.user.avatar.avatarURL{/lang}</label>
				</div>
				<div class="formField">
					<input type="text" class="inputText" name="avatarURL" value="{$avatarURL}" id="avatarURL" />
					{if $errorType.avatarURL|isset}
						<p class="innerError">
							{if $errorType.avatarURL == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType.avatarURL == 'downloadFailed'}{lang}wcf.user.avatar.error.avatarURL.downloadFailed{/lang}{/if}
							{if $errorType.avatarURL == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
							{if $errorType.avatarURL == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
							{if $errorType.avatarURL == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
							{if $errorType.avatarURL == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			{if MODULE_GRAVATAR == 1}
				<div id="gravatarDiv" class="formElement{if $errorType.gravatar|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="gravatar">{lang}wcf.user.avatar.gravatar{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="gravatar" value="{$gravatar}" id="gravatar" />
						{if $errorType.gravatar|isset}
							<p class="innerError">
								{if $errorType.gravatar == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<p class="formFieldDesc">{lang}wcf.user.avatar.gravatar.description{/lang}</p>
				</div>
			{/if}
		</fieldset>
		
		{if $avatarCategories|count > 0}
			<div class="formElement{if $errorType.availableAvatars|isset} formError{/if}">
				<fieldset>
					<legend><label><input type="radio" name="useAvatar" value="2"{if $useAvatar == 2} checked="checked"{/if} /> {lang}wcf.user.avatar.availableAvatars{/lang}</label></legend>
					
					<div class="formElement{if $errorType.availableAvatars|isset} formError{/if}">
						<div class="formFieldLabel">
							<label for="availableAvatars"><img id="avatarPreview" src="{@RELATIVE_WCF_DIR}/images/avatars/avatar-default.png" alt="" /></label>
						</div>
						<div class="formField">
							<select name="avatarID" id="availableAvatars" onchange="showAvatarPreview(this)">
								<option value="0"></option>
								{foreach from=$avatarCategories item=avatarCategory}
									{if $avatarCategory.avatars|count > 0}
										<optgroup label="{if $avatarCategory.category}{$avatarCategory.category}{else}{lang}wcf.user.avatar.category.default{/lang}{/if}">
										{foreach from=$avatarCategory.avatars item=avatar}
											<option value="{@$avatar->avatarID}"{if $avatar->avatarID == $avatarID} selected="selected"{/if}>{$avatar->avatarName}</option>
										{/foreach}
										</optgroup>
									{/if}
								{/foreach}
							</select>
							
							{if $errorType.availableAvatars|isset}
								<p class="innerError">
									{if $errorType.availableAvatars == 'invalid'}{lang}wcf.user.avatar.error.availableAvatars.invalid{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>
			</div>
			
			<script type="text/javascript">
				//<![CDATA[
				function showAvatarPreview(select) {
					if (select.options[select.selectedIndex].value == 0) {
						document.getElementById('avatarPreview').src = '{@RELATIVE_WCF_DIR}/images/avatars/avatar-default.png';
					}
					else {
						var temp = select.options[select.selectedIndex].text.split(".");
						document.getElementById('avatarPreview').src = '{@RELATIVE_WCF_DIR}/images/avatars/avatar-'+ select.options[select.selectedIndex].value +'.'+ temp[temp.length - 1];
					}
				}
				//]]>
			</script>
		{/if}
		
		<fieldset>
			<legend>
				<label><input onclick="if (this.checked) enableOptions('disableAvatarReason'); else disableOptions('disableAvatarReason');" type="checkbox" name="disableAvatar" value="1"{if $disableAvatar == 1} checked="checked"{/if} /> {lang}wcf.acp.avatar.disable{/lang}</label>
			</legend>
		
			<div class="formElement" id="disableAvatarReasonDiv">
				<div class="formFieldLabel">
					<label for="disableAvatarReason">{lang}wcf.acp.avatar.disableReason{/lang}</label>
				</div>
				<div class="formField">
					<textarea cols="40" rows="15" name="disableAvatarReason" id="disableAvatarReason">{$disableAvatarReason}</textarea>
				</div>
			</div>
		</fieldset>
		
		<script type="text/javascript">
			//<![CDATA[
			{if $disableAvatar == 1}enableOptions('disableAvatarReason');{else}disableOptions('disableAvatarReason');{/if}
			//]]>
		</script>
	</div>
</div>

<script type="text/javascript">
	//<![CDATA[
	onloadEvents.push(function() { 
		document.forms[0].enctype = 'multipart/form-data';
		document.forms[0].encoding = 'multipart/form-data'; // ie
		{if $avatarCategories|count > 0}showAvatarPreview(document.getElementById('availableAvatars'));{/if}
	});
	//]]>
</script>