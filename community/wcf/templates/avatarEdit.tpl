{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.avatar{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $disableAvatar}
			<p class="error">{lang}wcf.user.avatar.error.disabled{/lang}</p>
		{/if}
		
		{if $success|isset}
			<p class="success">{lang}wcf.user.avatar.success{/lang}</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	
	<div class="border">
		<div class="layout-3">
			<div class="columnContainer">
				<div class="container-3 column first sidebar avatarSelectSidebar">
					<div class="columnInner">
						<div class="contentBox">
							<h3 class="subHeadline">{lang}wcf.user.avatar.currentAvatar{/lang}</h3>
							<div class="avatarShow">
								{if $currentAvatar}
									<img src="{$currentAvatar->getURL()}" alt="" id="currentAvatar" />
								{else}
									<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" id="currentAvatar" />
								{/if}
								<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" id="defaultAvatar" class="hidden" />
							</div>
							<div class="buttonBar">
								<div class="smallButtons">
									<ul>
										{if $this->user->getPermission('user.profile.avatar.canUploadAvatar')}
											<li>
												<a href="index.php?form=AvatarEdit&userAvatar=1{@SID_ARG_2ND}" title="{lang}wcf.user.avatar.ownAvatar{/lang}"><img src="{icon}addS.png{/icon}" alt="" /> <span>{lang}wcf.user.avatar.ownAvatar{/lang}</span></a>
											</li>
										{/if}
										{if $currentAvatar && !$disableAvatar}
											<li>
												<a href="index.php?action=AvatarDelete&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" id="avatarDeleteButton" title="{lang}wcf.user.avatar.disable{/lang}"><img src="{icon}deleteS.png{/icon}" alt="" /> <span>{lang}wcf.user.avatar.disable{/lang}</span></a>
												<script type="text/javascript">
													//<![CDATA[
													$('avatarDeleteButton').observe('click', function(evt) {
														var a = evt.findElement();
														if (confirm('{lang}wcf.user.avatar.disable.sure{/lang}')) {
															var ajaxRequest = new AjaxRequest();
															ajaxRequest.openPost(a.href.gsub('&nbsp;', '&') + '&ajax=1');
															a.up('li').hide();
															$('currentAvatar').hide();
															$('defaultAvatar').removeClassName('hidden');
														}
														evt.stop();
													});
													//]]>
												</script>
											</li>
										{/if}
									</ul>
								</div>
							</div>
						</div>
						{if $this->user->getPermission('user.profile.avatar.canUseDefaultAvatar') && ($avatarCategories|count || $hasDefaultAvatars)}
							<div class="contentBox">
								<h3 class="subHeadline">{lang}wcf.user.avatar.defaultAvatars{/lang}</h3>
								<ul class="itemList">
									{if $hasDefaultAvatars}
										<li>
											<h4 class="itemListTitle">
												<img src="{icon}categoryS.png{/icon}" />
												{if !$userAvatar && !$avatarCategoryID}
													{lang}wcf.user.avatar.category.default{/lang}
												{else}
													<a href="index.php?form=AvatarEdit&amp;userAvatar=0&amp;avatarCategoryID=0&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">{lang}wcf.user.avatar.category.default{/lang}</a>
												{/if}
											</h4>
										</li>
									{/if}
									{foreach from=$avatarCategories key="categoryID" item="category"}
										<li>
											<h4 class="itemListTitle">
												<img src="{icon}categoryS.png{/icon}" />
												{if !$userAvatar && $avatarCategoryID == $categoryID}
													{lang}{$category}{/lang}
												{else}
													<a href="index.php?form=AvatarEdit&amp;userAvatar=0&amp;avatarCategoryID={@$categoryID}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">{lang}{$category}{/lang}</a>
												{/if}
											</h4>
										</li>
									{/foreach}
								</ul>
							</div>
						{/if}
					</div>
				</div>
				<div class="container-1 column second">
					<div class="columnInner">
						<form method="post" enctype="multipart/form-data" action="index.php?form=AvatarEdit">
							<h3 class="subHeadline">{lang}{if $userAvatar}wcf.user.avatar.ownAvatar{else}{if $avatarCategoryID}{$avatarCategory->title}{else}wcf.user.avatar.category.default{/if}{/if}{/lang}</h3>
							
							{if $this->user->getPermission('user.profile.avatar.canUploadAvatar') && $userAvatar}
							
								<script type="text/javascript">
									//<![CDATA[
									document.observe('dom:loaded', function() {
										$('avatarUpload').observe('keyup', checkInput).observe('change', checkInput);
										$('avatarURL').observe('keyup', checkInput).observe('focus', checkInput).observe('blur', checkInput);
										var urlValue = $('avatarURL').value;
										if ($('gravatar')) {
											$('gravatar').observe('keyup', checkInput).observe('focus', checkInput).observe('blur', checkInput);
											var gravatarValue = $('gravatar').value;
										}
										else {
											var gravatarValue = null;
										}
										{if $errorField == 'avatarURL' || $avatarType == 'gravatar'}checkInput({if $errorField == 'avatarURL'}$('avatarURL'){elseif $avatarType == 'gravatar'}$('gravatar'){/if});{/if}
									
										function checkInput(element) {
											// if it is not a normal element, it must be an event object
											if (!(!!element && element.nodeType === 1)) {
												element = element.findElement();
												var init = false;
											}
											else {
													
												var init = true;
											}
											
											switch (element.identify()) {
												case 'avatarURL':
													if (urlValue != $('avatarURL').value || init) {
														urlValue = $('avatarURL').value;
														if ($('avatarURL').value != '' && $('avatarURL').value != 'http://') {
															disableOptions('avatarUpload', 'gravatar');
														}
														else {
															enableOptions('avatarUpload', 'gravatar');
														}
													}
													break;
												case 'gravatar':
													if (gravatarValue != $('gravatar').value || init) {
														gravatarValue = $('gravatar').value;
														if ($('gravatar').value != '') {
															disableOptions('avatarUpload', 'avatarURL');
														}
														else {
															enableOptions('avatarUpload', 'avatarURL');
														}
													}
													break;
											}
										}
									});
									//]]>
								</script>
								
								<p class="info">{lang}wcf.user.avatar.avatarUpload.description{/lang}</p>		
								<div class="formElement{if $errorField == 'avatarUpload'} formError{/if}" id="avatarUploadDiv">
									<div class="formFieldLabel">
										<label for="avatarUpload">{lang}wcf.user.avatar.avatarUpload{/lang}</label>
									</div>
									<div class="formField">
										<input type="file" name="avatarUpload" value="" id="avatarUpload" />
										{if $errorField == 'avatarUpload'}
											<p class="innerError">
												{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
												{if $errorType == 'uploadFailed'}{lang}wcf.user.avatar.error.avatarUpload.uploadFailed{/lang}{/if}
												{if $errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
												{if $errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
												{if $errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
												{if $errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
											</p>
										{/if}
									</div>
								</div>
								<div class="formElement{if $errorField == 'avatarURL'} formError{/if}" id="avatarURLDiv">
									<div class="formFieldLabel">
										<label for="avatarURL">{lang}wcf.user.avatar.avatarURL{/lang}</label>
									</div>
									<div class="formField">
										<input type="text" class="inputText" name="avatarURL" value="{$avatarURL}" id="avatarURL" />
										{if $errorField == 'avatarURL'}
											<p class="innerError">
												{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
												{if $errorType == 'downloadFailed'}{lang}wcf.user.avatar.error.avatarURL.downloadFailed{/lang}{/if}
												{if $errorType == 'badAvatar'}{lang}wcf.user.avatar.error.badAvatar{/lang}{/if}
												{if $errorType == 'notAllowedExtension'}{lang}wcf.user.avatar.error.notAllowedExtension{/lang}{/if}
												{if $errorType == 'tooLarge'}{lang}wcf.user.avatar.error.tooLarge{/lang}{/if}
												{if $errorType == 'copyFailed'}{lang}wcf.user.avatar.error.copyFailed{/lang}{/if}
											</p>
										{/if}
									</div>
								</div>
								{if MODULE_GRAVATAR == 1}
									<div class="formElement{if $errorField == 'gravatar'} formError{/if}" id="gravatarDiv">
										<div class="formFieldLabel">
											<label for="gravatar">{lang}wcf.user.avatar.gravatar{/lang}</label>
										</div>
										<div class="formField">
											<input type="text" class="inputText" name="gravatar" value="{$gravatar}" id="gravatar" />
											{if $errorField == 'gravatar'}
												<p class="innerError">
													{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
												</p>
											{/if}
										</div>
										<p class="formFieldDesc">{lang}wcf.user.avatar.gravatar.description{/lang}</p>
									</div>
								{/if}
							{/if}
							
							{if !$userAvatar && $this->user->getPermission('user.profile.avatar.canUseDefaultAvatar')}
								<div class="contentHeader">
									{pages print=true assign=pagesOutput link="index.php?form=AvatarEdit&userAvatar=0&avatarCategoryID=$avatarCategoryID&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED:'#availableAvatars'}
								</div>
								<div class="avatarSelect">
									<ul>
									{foreach from=$avatars item=avatar}
										<li class="container-4 {if $avatarID == $avatar->avatarID}selected{/if}">
											<label>
												<input onclick="if (IS_SAFARI) disableOptions('avatarUpload', 'avatarURL')" onfocus="disableOptions('avatarUpload', 'avatarURL')" type="radio" name="avatarID" value="{@$avatar->avatarID}"{if $avatarID == $avatar->avatarID} checked="checked"{/if} />
												<img src="{@$avatar->getURL()}" alt="" style="width: {@$avatar->width}px; height: {@$avatar->height}px; margin-top: -{@$avatar->height/2|intval}px; margin-left: -{@$avatar->width/2|intval}px" />
											</label>
										</li>
									{/foreach}
									</ul>
								</div>
								
								<div class="contentFooter">
									{@$pagesOutput}
								</div>
								
								{if $errorField == 'availableAvatars'}
									<div class="formError">
										<p class="innerError">
											{if $errorType == 'invalid'}{lang}wcf.user.avatar.error.availableAvatars.invalid{/lang}{/if}
										</p>
									</div>
								{/if}
								
								<input type="hidden" name="pageNo" value="{@$pageNo}" />
								<input type="hidden" name="avatarCategoryID" value="{@$avatarCategoryID}" />
							{/if}
							
							{@SID_INPUT_TAG}
							{if !$disableAvatar}
								<div class="formSubmit">
									<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
									<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
								</div>
							{/if}
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>
