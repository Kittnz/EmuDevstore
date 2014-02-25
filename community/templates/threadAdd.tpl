{include file="documentHeader"}
<head>
	<title>{lang}wbb.threadAdd.title{/lang} - {lang}{$board->title}{/lang} - {lang}{PAGE_TITLE}{/lang}</title>

	{include file='headInclude' sandbox=false}
	
	{include file='imageViewer'}
	
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabbedPane.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{if $canUseBBCodes}{include file="wysiwyg"}{/if}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation" showBoard=true}
	
	<div class="mainHeadline">
		<img src="{icon}threadNewL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wbb.threadAdd.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	{if $preview|isset}
		<div class="border messagePreview">
			<div class="containerHead">
				<h3>{lang}wcf.message.preview{/lang}</h3>
			</div>
			<div class="message content">
				<div class="messageInner container-1">
					{if $subject}
						<h4>{$subject}</h4>
					{/if}
					<div class="messageBody">
						<div>{@$preview}</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
	
	<form enctype="multipart/form-data" method="post" action="index.php?form=ThreadAdd&amp;boardID={@$board->boardID}">
		<div class="border content">
			<div class="container-1">
	
				{if $board->getModeratorPermission('canPinThread') || $board->getModeratorPermission('canStartAnnouncement')}
					<fieldset>
						<legend>{lang}wbb.threadAdd.isImportant{/lang}</legend>
						
						<div class="formGroup">
							<div class="formGroupLabel">
								{lang}wbb.threadAdd.status{/lang}
							</div>
							<div class="formGroupField">
								<fieldset>
									<legend>{lang}wbb.threadAdd.status{/lang}</legend>
									<div class="formField">
										<ul class="formOptions">
											<li><label><input onclick="openList('boards', { setVisible: false })" type="radio" name="isImportant" value="0" {if $isImportant == 0}checked="checked" {/if}tabindex="{counter name='tabindex'}" /> <img src="{icon}threadM.png{/icon}" alt="" /> {lang}wbb.threadAdd.isImportant.0{/lang}</label></li>
											{if $board->getModeratorPermission('canPinThread')}<li><label><input onclick="openList('boards', { setVisible: false })" type="radio" name="isImportant" value="1" {if $isImportant == 1}checked="checked" {/if}tabindex="{counter name='tabindex'}" /> <img src="{icon}threadImportantM.png{/icon}" alt="" /> {lang}wbb.threadAdd.isImportant.1{/lang}</label></li>{/if}
											{if $board->getModeratorPermission('canStartAnnouncement')}<li><label><input onclick="openList('boards', { setVisible: true })" type="radio" name="isImportant" value="2" {if $isImportant == 2}checked="checked" {/if}tabindex="{counter name='tabindex'}" /> <img src="{icon}threadAnnouncementM.png{/icon}" alt="" /> {lang}wbb.threadAdd.isImportant.2{/lang}</label></li>{/if}
										</ul>
									</div>
								</fieldset>
							</div>
						</div>
						
						{if $board->getModeratorPermission('canStartAnnouncement')}
							{if $isImportant != 2}
								<script type="text/javascript">
									//<![CDATA[
									onloadEvents.push(function() { document.getElementById('boards').style.display = 'none'; });
									//]]>
								</script>
							{/if}
							<div id="boards" class="formElement">
								<div class="formFieldLabel">
									<label>{lang}wbb.threadAdd.assignedBoards{/lang}</label>
								</div>
								<div class="formField longSelect">
									<select name="boardIDs[]" id="boardIDs" multiple="multiple" size="10" tabindex="{counter name='tabindex'}">
										{htmloptions options=$boardOptions selected=$boardIDs
										disableEncoding=true}
									</select>
								</div>
								<div class="formFieldDesc">
									<p>{lang}wbb.threadAdd.assignedBoards.description{/lang}</p>
									<p>{lang}wcf.global.multiSelect{/lang}</p>
								</div>
							</div>
						{/if}
					</fieldset>
				{/if}
					
				<fieldset>
					<legend>{lang}wbb.threadAdd.information{/lang}</legend>
					
					{if $availableLanguages|count > 1}
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="languageID">{lang}wbb.threadAdd.language{/lang}</label>
							</div>
							<div class="formField">
								<select name="languageID" id="languageID" tabindex="{counter name='tabindex'}">
									{foreach from=$availableLanguages item=availableLanguage}
									<option value="{@$availableLanguage.languageID}"
										{if $availableLanguage.languageID == $languageID} selected="selected"{/if}>{lang}wcf.global.language.{@$availableLanguage.languageCode}{/lang}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
					
					{if $board->hasPrefixes() && $board->getPermission('canUsePrefix')}
						<div class="formElement{if $errorField == 'prefix'} formError{/if}">
							<div class="formFieldLabel">
								<label for="prefix">{lang}wbb.threadAdd.prefix{/lang}</label>
							</div>
							<div class="formField">
								<select name="prefix" id="prefix" tabindex="{counter name='tabindex'}">
									<option value=""></option>
									{htmlOptions options=$board->getPrefixOptions() selected=$prefix}
								</select>
								{if $errorField == 'prefix'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wbb.threadAdd.error.prefix.empty{/lang}{/if}
										{if $errorType == 'invalid'}{lang}wbb.threadAdd.error.prefix.invalid{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
					{/if}
					
					{if !$this->user->userID}
						<div class="formElement{if $errorField == 'username'} formError{/if}">
							<div class="formFieldLabel">
								<label for="username">{lang}wcf.user.username{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="username" id="username" value="{$username}" tabindex="{counter name='tabindex'}" />
								{if $errorField == 'username'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'notValid'}{lang}wcf.user.error.username.notValid{/lang}{/if}
										{if $errorType == 'notAvailable'}{lang}wcf.user.error.username.notUnique{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
					{/if}
					
					<div class="formElement{if $errorField == 'subject'} formError{/if}">
						<div class="formFieldLabel">
							<label for="subject">{lang}wbb.threadAdd.subject{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="subject" id="subject" value="{$subject}" tabindex="{counter name='tabindex'}" />
							{if $errorField == 'subject'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					{if MODULE_TAGGING && THREAD_ENABLE_TAGS && $board->getPermission('canSetTags')}{include file='tagAddBit'}{/if}
					
					{if $additionalInformationFields|isset}{@$additionalInformationFields}{/if}
				</fieldset>
				
				<fieldset>
					<legend>{lang}wbb.threadAdd.text{/lang}</legend>
					
					<div class="editorFrame formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
	
						<div class="formFieldLabel">
							<label for="text">{lang}wbb.threadAdd.text{/lang}</label>
						</div>
						
						<div class="formField">				
							<textarea name="text" id="text" rows="15" cols="40" tabindex="{counter name='tabindex'}">{$text}</textarea>
							{if $errorField == 'text'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'tooLong'}{lang}wcf.message.error.tooLong{/lang}{/if}
									{if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
									{if $errorType == 'tooShort'}{lang}wbb.threadAdd.text.error.tooShort{/lang}{/if}
								</p>
							{/if}
						</div>
						
					</div>
					
					{include file='postFormSettings' append=additionalSettings}
					{include file='messageFormTabs'}
					
				</fieldset>
				
				{include file='captcha'}
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.preview{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" tabindex="{counter name='tabindex'}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="idHash" value="{$idHash}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>