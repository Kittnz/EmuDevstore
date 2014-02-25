{include file="documentHeader"}
<head>
	<title>{lang}wbb.postAdd.title{/lang} - {if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic} - {lang}{$board->title}{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	
	{include file='imageViewer'}
	
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabbedPane.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{include file='multiQuote'}
	{if $canUseBBCodes}{include file="wysiwyg"}{/if}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation" showThread=true}
	
	<div class="mainHeadline">
		<img src="{icon}messageAddL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wbb.postAdd.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	{if $oldThreadWarning}
		<p class="warning">{lang}wbb.postAdd.oldThreadWarning{/lang}</p>
	{/if}

	{if MODULE_THREAD_MARKING_AS_DONE == 1 && $board->enableMarkingAsDone == 1 && $thread->isDone == 1}
		<p class="warning">{lang}wbb.postAdd.threadIsMarkedAsDone{/lang}</p>
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
	
	<form enctype="multipart/form-data" method="post" action="index.php?form=PostAdd&amp;threadID={@$thread->threadID}">
		<div class="border content">
			<div class="container-1">
			
				<fieldset>
					<legend>{lang}wbb.threadAdd.information{/lang}</legend>
					
					{if $this->user->userID == 0}
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
							<input type="text" class="inputText" id="subject" name="subject" value="{$subject}" tabindex="{counter name='tabindex'}" />
							{if $errorField == 'subject'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				
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
			<input type="hidden" name="postID" value="{@$postID}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="idHash" value="{$idHash}" />
		</div>
	</form>
	
	<h2>{lang}wbb.postAdd.posts{/lang}</h2>
	
	{assign var=startIndex value=$items}
	{foreach from=$posts item=post}
		{assign var="author" value=$post->getUser()}
		{assign var="messageID" value=$post->postID}
		
		<script type="text/javascript">
			//<![CDATA[
			quoteData.set('post-{@$post->postID}', {
				objectID: {@$post->postID},
				objectType: 'post',
				quotes: {@$post->isQuoted()},
				author: '{$post->username|encodeJS}',
				url: 'index.php?page=Thread&postID={@$post->postID}#post{@$post->postID}'
			});
			//]]>
		</script>
		
		<div class="message content">
			<div class="messageInner container-{cycle values='1,2'}">
				<div class="messageHeader">
					<p class="messageCount">
						<a href="index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}" title="{lang}wbb.thread.permalink{/lang}" class="messageNumber">{#$startIndex}</a>
					</p>
					
					<div class="containerIcon">
						{if $author->getAvatar()}
							{assign var=x value=$author->getAvatar()->setMaxSize(24, 24)}
							{if $author->userID}<a href="index.php?page=User&amp;userID={@$author->userID}{@SID_ARG_2ND}" title="{lang username=$author->username}wcf.user.viewProfile{/lang}">{/if}{@$author->getAvatar()}{if $author->userID}</a>{/if}
						{else}
							{if $author->userID}<a href="index.php?page=User&amp;userID={@$author->userID}{@SID_ARG_2ND}" title="{lang username=$author->username}wcf.user.viewProfile{/lang}">{/if}<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 24px; height: 24px" />{if $author->userID}</a>{/if}
						{/if}
					</div>
					<div class="containerContent">
						<p class="light smallFont">{@$post->time|time}</p>
						<p class="light smallFont">{lang}wbb.board.threads.postBy{/lang} {if $author->userID}<a href="index.php?page=User&amp;userID={@$author->userID}{@SID_ARG_2ND}">{$post->username}</a>{else}{$post->username}{/if}</p>
					</div>
				</div>
				{if $post->subject}
					<h3>{$post->subject}</h3>
				{/if}
				<div class="messageBody">
					<div id="postText{@$post->postID}">
						{@$post->getFormattedMessage()}
					</div>
				</div>
				{include file='attachmentsShow' attachments=$postAttachments}
				<div class="messageFooter">
					<div class="smallButtons">
						<ul id="postButtons{@$post->postID}">
							<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="" title="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
						</ul>
					</div>
				</div>
				<hr />
			</div>
		</div>
		{assign var=startIndex value=$startIndex-1}
	{/foreach}

	{if $insertQuotes == 1}
		<script type="text/javascript">
			//<![CDATA[
			document.observe("dom:loaded", function() {
				window.setTimeout(function() {
					multiQuoteManagerObj.insertParentQuotes('post', {@$thread->threadID});
				}, 500);
			});
			//]]>
		</script>
	{/if}
</div>

{include file='footer' sandbox=false}
</body>
</html>