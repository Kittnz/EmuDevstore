{include file="documentHeader"}
<head>
	<title>{lang}wcf.pm.newMessage{/lang} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
	{include file='imageViewer'}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabbedPane.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{include file='multiQuote'}
	{include file="wysiwyg"}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{icon}pmEmptyS.png{/icon}" alt="" /> <span>{lang}wcf.pm.title{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}pmNewL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {lang}wcf.pm.newMessage{/lang}</h2>
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
						{@$preview}
					</div>
				</div>
			</div>
		</div>
	{/if}
	
	<form enctype="multipart/form-data" method="post" action="index.php?form=PMNew">
		<div class="border content">
			<div class="container-1">
				<fieldset>
					<legend>{lang}wcf.pm.information{/lang}</legend>
					
					<div class="formElement{if $errorField == 'recipients'} formError{/if}">
						<div class="formFieldLabel">
							<label for="recipients">{lang}wcf.pm.visibleRecipients{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="recipients" name="recipients" value="{$recipients}" tabindex="{counter name='tabindex'}" />
							<script type="text/javascript">
								//<![CDATA[
								suggestion.setSource('index.php?page=PublicUserSuggest{@SID_ARG_2ND_NOT_ENCODED}');
								suggestion.init('recipients');
								//]]>
							</script>
							{if $errorField == 'recipients'}
								<p class="innerError">
									{if $errorType|is_array}
										{foreach from=$errorType item=error}
											{if $error.type == 'notFound'}{lang}wcf.pm.error.recipient.notFound{/lang}{/if}
											{if $error.type == 'canNotUsePm'}{lang}wcf.pm.error.recipient.canNotUsePm{/lang}{/if}
											{if $error.type == 'doesNotAcceptPm'}{lang}wcf.pm.error.recipient.doesNotAcceptPm{/lang}{/if}
											{if $error.type == 'ignoresYou'}{lang}wcf.pm.error.recipient.ignoresYou{/lang}{/if}
											{if $error.type == 'onlyAcceptsPmFromBuddies'}{lang}wcf.pm.error.recipient.onlyAcceptsPmFromBuddies{/lang}{/if}
											{if $error.type == 'recipientsMailboxIsFull'}{lang}wcf.pm.error.recipient.recipientsMailboxIsFull{/lang}{/if}
											<br />
										{/foreach}
									{else}
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'tooManyRecipients'}{lang}wcf.pm.error.recipient.tooManyRecipients{/lang}{/if}
									{/if}
								</p>
							{/if}
						</div>
					</div>
			
					<div class="formElement{if $errorField == 'blindCopies'} formError{/if}">
						<div class="formFieldLabel">
							<label for="blindCopies">{lang}wcf.pm.invisibleRecipients{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText"  id="blindCopies" name="blindCopies" value="{$blindCopies}" tabindex="{counter name='tabindex'}" />
							<script type="text/javascript">
								//<![CDATA[
								suggestion.init('blindCopies')
								//]]>
							</script>
							{if $errorField == 'blindCopies'}
								<p class="innerError">
									{if $errorType|is_array}
										{foreach from=$errorType item=error}
											{if $error.type == 'notFound'}{lang}wcf.pm.error.recipient.notFound{/lang}{/if}
											{if $error.type == 'canNotUsePm'}{lang}wcf.pm.error.recipient.canNotUsePm{/lang}{/if}
											{if $error.type == 'doesNotAcceptPm'}{lang}wcf.pm.error.recipient.doesNotAcceptPm{/lang}{/if}
											{if $error.type == 'ignoresYou'}{lang}wcf.pm.error.recipient.ignoresYou{/lang}{/if}
											{if $error.type == 'onlyAcceptsPmFromBuddies'}{lang}wcf.pm.error.recipient.onlyAcceptsPmFromBuddies{/lang}{/if}
											{if $error.type == 'recipientsMailboxIsFull'}{lang}wcf.pm.error.recipient.recipientsMailboxIsFull{/lang}{/if}
											<br />
										{/foreach}
									{else}
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'subject'} formError{/if}">
						<div class="formFieldLabel">
							<label for="subject">{lang}wcf.pm.subject{/lang}</label>
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
					
				</fieldset>
				
				<fieldset>
					<legend>{lang}wcf.pm.text{/lang}</legend>
					
					<div class="editorFrame formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
						<div class="formFieldLabel">
							<label for="text">{lang}wcf.pm.text{/lang}</label>
						</div>
						<div class="formField">
							<textarea name="text" id="text" rows="20" cols="40" tabindex="{counter name='tabindex'}">{$text}</textarea>
							{if $errorField == 'text'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'tooLong'}{lang}wcf.message.error.tooLong{/lang}{/if}
									{if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				
					{include file="messageFormTabs"}
					
				</fieldset>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="submit" name="draft" accesskey="d" value="{lang}wcf.pm.button.saveAsDraft{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.preview{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" tabindex="{counter name='tabindex'}" />
		</div>
		
		<div class="formSubmit">
			{@SID_INPUT_TAG}
			<input type="hidden" name="pmID" value="{@$pmID}" />
			<input type="hidden" name="forwarding" value="{@$forwarding}" />
			<input type="hidden" name="reply" value="{@$reply}" />
			<input type="hidden" name="replyToAll" value="{@$replyToAll}" />
			<input type="hidden" name="idHash" value="{$idHash}" />
		</div>
	</form>
	
	{if $privateMessages|isset && $privateMessages|count > 0}
		<h2>{lang}wcf.pm.originalMessage{/lang}</h2>
		
		{foreach from=$privateMessages item=pm}
			{assign var="author" value=$pm->getUser()}
			{assign var="messageID" value=$pm->pmID}
			
			<script type="text/javascript">
				//<![CDATA[
				quoteData.set('pm-{@$pm->pmID}', {
					objectID: {@$pm->pmID},
					objectType: 'pm',
					quotes: {@$pm->isQuoted()},
					author: '{$pm->username|encodeJS}',
					url: 'index.php?page=PMView&amp;pmID={@$pm->pmID}#pm{@$pm->pmID}'
				});
				//]]>
			</script>
			
			<div class="message content">
				<div class="messageInner {cycle values='container-1,container-2'}">
					<div class="messageHeader">
						<div class="containerIcon">
							{if $pm->getUser()->getAvatar()}
								{assign var=x value=$pm->getUser()->getAvatar()->setMaxSize(24, 24)}
								{if $pm->userID}<a href="index.php?page=User&amp;userID={@$pm->userID}{@SID_ARG_2ND}" title="{lang username=$pm->username}wcf.user.viewProfile{/lang}">{/if}{@$pm->getUser()->getAvatar()}{if $pm->userID}</a>{/if}
							{else}
								{if $pm->userID}<a href="index.php?page=User&amp;userID={@$pm->userID}{@SID_ARG_2ND}" title="{lang username=$pm->username}wcf.user.viewProfile{/lang}">{/if}<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 24px; height: 24px" />{if $pm->userID}</a>{/if}
							{/if}
						</div>
						<div class="containerContent">
							<p class="light smallFont">{@$pm->time|time}</p>
							<p class="light smallFont">{lang}wcf.pm.from{/lang} {if $pm->userID}<a href="index.php?page=User&amp;userID={@$pm->userID}{@SID_ARG_2ND}">{$pm->username}</a>{else}{$pm->username}{/if}</p>
						</div>
					</div>
					
					<h3>{$pm->subject}</h3>
					
					<div class="messageBody" id="pmText{@$pm->pmID}">
						{@$pm->getFormattedMessage()}
					</div>
					{include file='attachmentsShow' attachments=$pmAttachments}
					<div class="messageFooter">
						<div class="smallButtons">
							<ul id="pmButtons{@$pm->pmID}">
								<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="" title="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
							</ul>
						</div>
					</div>
					<hr />
				</div>
			</div>
		{/foreach}
	{/if}

	{if $parentPmID != 0 && $insertQuotes == 1}
		<script type="text/javascript">
			//<![CDATA[
			document.observe("dom:loaded", function() {
				window.setTimeout(function() {
					multiQuoteManagerObj.insertParentQuotes('pm', {@$parentPmID});
				}, 500);
			});
			//]]>
		</script>
	{/if}
</div>

{include file='footer' sandbox=false}
</body>
</html>