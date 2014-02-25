{include file="documentHeader"}
<head>
	<title>{if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if} - {lang}{$board->title}{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	
	{include file='imageViewer'}
	
	<!--[if IE]>
		<script type="text/javascript">
			//<![CDATA[
			{literal}
			onloadEvents.push(function() {
				if (document.referrer) {
					var postForm = document.referrer.search(/PostEdit|PostAdd/);
					if (postForm != -1) {
						var postID = (window.location + '').split('postID=');
						if (!isNaN(postID[1])) window.location.href = '#post' + postID[1];
					}
				}
				});
			{/literal}
			//]]>
		</script>
	<![endif]-->
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH};
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{if $polls|isset}<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Poll.class.js"></script>{/if}
	<script type="text/javascript">
		//<![CDATA[
		var language = new Object();
		var postData = new Hash();
		var url = 'index.php?page=Thread&threadID={@$thread->threadID}&pageNo={@$pageNo}{@SID_ARG_2ND_NOT_ENCODED}';
		//]]>
	</script>
	{if $thread->canReplyThread()}
		{include file='multiQuote' formURL="index.php?form=PostAdd&threadID=$threadID"|concat:SID_ARG_2ND_NOT_ENCODED}
	{else}
		{include file='multiQuote'}
	{/if}
	{if $permissions.canHandleThread || $permissions.canHandlePost}
		{include file='threadInlineEdit' pageType=thread}
		<script type="text/javascript">
			//<![CDATA[
			threadData.set({@$thread->threadID}, {
				'isMarked': {@$thread->isMarked()},
				'isDeleted': {@$thread->isDeleted},
				'isDisabled': {@$thread->isDisabled},
				'isClosed': {@$thread->isClosed},
				'isSticky': {@$thread->isSticky},
				'isAnnouncement': {@$thread->isAnnouncement},
				'isMoved': 0,
				'prefix': '{$thread->prefix|encodeJS}',
				'isDone': {@$thread->isDone}
			});
			//]]>
		</script>
	{/if}
	<link rel="alternate" type="application/rss+xml" href="index.php?page=PostsFeed&amp;format=rss2&amp;threadID={@$threadID}" title="{lang}wbb.thread.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=PostsFeed&amp;format=atom&amp;threadID={@$threadID}" title="{lang}wbb.thread.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

<p class="skipHeader hidden"><a href="#skipToContent" title="{lang}wcf.global.skipToContent{/lang}">{lang}wcf.global.skipToContent{/lang}</a></p><!-- support for disabled surfers -->

{* --- quick search controls --- *}
{assign var='searchFieldTitle' value='{lang}wbb.thread.search.query{/lang}'}
{capture assign=searchHiddenFields}
	<input type="hidden" name="threadID" value="{@$threadID}" />
	<input type="hidden" name="types[]" value="post" />
	<input type="hidden" name="findThreads" value="0" />
{/capture}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation" showBoard=true}
	
	<a href="#" id="skipToContent"></a><!-- support for disabled surfers -->
	
	<div class="mainHeadline">
		<img id="threadEdit{@$thread->threadID}" src="{icon}{@$thread->getIconName()}L.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2 id="threadTitle{@$thread->threadID}">
				<span id="threadPrefix{@$thread->threadID}" class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span>
				<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{$thread->topic}</a>
			</h2>
			<p>{if $enableRating}{@$thread->getRatingOutput()}{/if}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="contentHeader">
		{assign var=multiplePagesLink value="index.php?page=Thread&threadID=$threadID&pageNo=%d"}
		{if $highlight}
			{assign var=encodedHighlight value=$highlight|urlencode}
			{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&highlight=':$encodedHighlight}
		{/if}
		{pages print=true assign=pagesOutput link=$multiplePagesLink|concat:SID_ARG_2ND_NOT_ENCODED}
		<div class="largeButtons">
			{if $thread->canReplyThread() || $additionalLargeButtons|isset}
				<ul>
					{if $thread->canReplyThread()}<li><a href="index.php?form=PostAdd&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}" id="replyButton1" title="{lang}wbb.thread.button.reply{/lang}"><img src="{icon}messageAddM.png{/icon}" alt="" /> <span>{lang}wbb.thread.button.reply{/lang}</span></a></li>{/if}
					
					{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
				</ul>
			{/if}
		</div>
	</div>
	
	{* build message css classes *}
	{if $this->getStyle()->getVariable('messages.color.cycle')}
		{cycle name=messageCycle values='2,1' print=false}
	{else}
		{cycle name=messageCycle values='1' print=false}
	{/if}

	{if $this->getStyle()->getVariable('messages.sidebar.color.cycle')}
		{if $this->getStyle()->getVariable('messages.color.cycle')}
			{cycle name=postCycle values='1,2' print=false}
		{else}
			{cycle name=postCycle values='3,2' print=false}
		{/if}
	{else}
		{cycle name=postCycle values='3' print=false}
	{/if}
	
	{capture assign='messageClass'}message{if $this->getStyle()->getVariable('messages.framed')}Framed{/if}{@$this->getStyle()->getVariable('messages.sidebar.alignment')|ucfirst}{if $this->getStyle()->getVariable('messages.sidebar.divider.use')} dividers{/if}{/capture}
	{capture assign='messageFooterClass'}messageFooter{@$this->getStyle()->getVariable('messages.footer.alignment')|ucfirst}{/capture}
	
	{if $sortOrder == 'DESC'}{assign var=startIndex value=$items-$startIndex+1}{/if}
	{foreach from=$posts item=post}
		{assign var="sidebar" value=$sidebarFactory->get('post', $post->postID)}
		{assign var="author" value=$sidebar->getUser()}
		{assign var="pollID" value=$post->pollID}
		{assign var="messageID" value=$post->postID}
		
		<script type="text/javascript">
			//<![CDATA[
				
				quoteData.set('post-{@$post->postID}', {
					objectID: {@$post->postID},
					objectType: 'post',
					quotes: {@$post->isQuoted()}
				});
				
			{if $permissions.canHandleThread || $permissions.canHandlePost}
				postData.set({@$post->postID}, {
					'isMarked': {@$post->isMarked()},
					'isDeleted': {@$post->isDeleted},
					'isDisabled': {@$post->isDisabled},
					'isClosed': {@$post->isClosed}
				});
			{/if}
			//]]>
		</script>
		
		{assign var="hiddenDeletedPost" value=false}
		{assign var="hiddenIgnoredPost" value=false}
		{if $post->isDeleted && !$thread->isDeleted}
			{assign var="hiddenDeletedPost" value=true}
		{/if}
		{if $sidebar->getUser()->userID && $this->user->userID && $this->user->ignores($sidebar->getUser()->userID)}
			{assign var="hiddenIgnoredPost" value=true}
		{/if}
		
		{if !$post->isDeleted || $board->getModeratorPermission('canReadDeletedPost')}
			<div id="postRow{@$post->postID}" class="message{if THREAD_ENABLE_THREAD_AUTHOR && $sidebar->getUser()->userID && $sidebar->getUser()->userID == $thread->userID} threadStarterPost{/if}"{if $hiddenDeletedPost || $hiddenIgnoredPost} style="display: none"{/if}>
				<div class="messageInner {@$messageClass} container-{cycle name=postCycle}{if !$sidebar->getUser()->userID} guestPost{/if}">
					{if !$hiddenDeletedPost && !$hiddenIgnoredPost}<a id="post{@$post->postID}"></a>{/if}
					
					{include file='messageSidebar'}
					
					<div class="messageContent">
						<div class="messageContentInner color-{cycle name=messageCycle}">
							<div class="messageHeader">
								<p class="messageCount">
									<a href="index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}" title="{lang}wbb.thread.permalink{/lang}" class="messageNumber">{#$startIndex}</a>
									{if $permissions.canMarkPost}
										<span class="messageMarkCheckBox">
											<input id="postMark{@$post->postID}" type="checkbox" title="{lang}wbb.thread.post.mark{/lang}" />
										</span>
									{/if}
								</p>
								<div class="containerIcon">
									<img id="postEdit{@$post->postID}" src="{icon}{@$post->getIconName()}M.png{/icon}" alt="" />
								</div>
								<div class="containerContent">
									<p class="smallFont light">{@$post->time|time}</p>
								</div>
							</div>
							
							<h3 id="postTopic{@$post->postID}" class="messageTitle"><span>{$post->subject}</span></h3>
							
							<div class="messageBody">
								{include file='pollShow'}
								<div id="postText{@$post->postID}">
									{@$post->getFormattedMessage()}
								</div>
							</div>
							
							{include file='attachmentsShow'}
							
							{if MODULE_USER_SIGNATURE == 1 && $post->getSignature()}
								<div class="signature">
									{@$post->getSignature()}
								</div>
							{/if}
							
							{if $post->editCount > 0}
								<p class="editNote smallFont light">{lang}wbb.thread.post.editNote{/lang}</p>
							{/if}
							
							{if $post->isDeleted}
								<p class="deleteNote smallFont light">{lang}wbb.thread.post.deleteNote{/lang}</p>
							{/if}
							
							<div class="{@$messageFooterClass}">
								<div class="smallButtons">
									<ul id="postButtons{@$post->postID}">
										<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
										{if $thread->canReplyThread()}
											<li><a id="postQuote{@$post->postID}" href="index.php?form=PostAdd&amp;postID={@$post->postID}&amp;action=quote{@SID_ARG_2ND}" title="{lang}wcf.multiQuote.button.quote{/lang}"><img src="{icon}messageQuoteS.png{/icon}" alt="" /> <span>{lang}wcf.multiQuote.button.quote{/lang}</span></a></li>
										{/if}
										{if $post->canEditPost($board, $thread)}
											<li><a href="index.php?form=PostEdit&amp;postID={@$post->postID}{@SID_ARG_2ND}" title="{lang}wbb.thread.post.button.edit{/lang}"><img src="{icon}editS.png{/icon}" alt="" /> <span>{lang}wbb.thread.post.button.edit{/lang}</span></a></li>
										{/if}
										{if $this->user->userID}
											<li><a href="index.php?form=PostReport&amp;postID={@$post->postID}{@SID_ARG_2ND}" title="{lang}wbb.thread.post.button.report{/lang}"><img src="{icon}postReportS.png{/icon}" alt="" /> <span>{lang}wbb.thread.post.button.report{/lang}</span></a></li>
										{/if}
										{if MODULE_USER_INFRACTION == 1 && $post->userID && $this->user->getPermission('admin.user.infraction.canWarnUser')}
											<li><a href="index.php?form=UserWarn&amp;userID={@$post->userID}&amp;objectType=post&amp;objectID={@$post->postID}{@SID_ARG_2ND}" title="{lang}wcf.user.infraction.button.warn{/lang}"><img src="{icon}infractionWarningS.png{/icon}" alt="" /> <span>{lang}wcf.user.infraction.button.warn{/lang}</span></a></li>
										{/if}
										{if $additionalSmallButtons.$messageID|isset}{@$additionalSmallButtons.$messageID}{/if}
									</ul>
								</div>
							</div>
							<hr />
						</div>
					</div>
					
				</div>
			</div>
		{/if}
		
		{if $hiddenDeletedPost || $hiddenIgnoredPost}
			{cycle name=postCycle print=false}
			<div class="message messageMinimized" id="hiddenPostInfo{@$post->postID}">
				<div class="messageInner container-{cycle name=postCycle}">
					<a id="post{@$post->postID}"></a>
					{if $hiddenDeletedPost}
						<img src="{icon}postTrashM.png{/icon}" alt="" />
					{else}
						<img src="{icon}warningM.png{/icon}" alt="" />
					{/if}
					<p class="smallFont light">
						
						{if !$hiddenDeletedPost || $board->getModeratorPermission('canReadDeletedPost')}
							<a onclick="showContent('postRow{@$post->postID}', 'hiddenPostInfo{@$post->postID}')" title="{lang}wbb.thread.showPost{/lang}">
						{/if}
						
						<span>
							{if $hiddenDeletedPost}
								{lang}wbb.thread.deletedPost{/lang}
							{elseif $hiddenIgnoredPost}
								{lang}wbb.thread.ignoredPost{/lang}
							{/if}
						</span>
						
						{if !$hiddenDeletedPost || $board->getModeratorPermission('canReadDeletedPost')}
							</a>
						{/if}
					</p>
				</div>
			</div>
		{/if}
		
		{if $sortOrder == 'DESC'}
			{assign var="startIndex" value=$startIndex - 1}
		{else}
			{assign var="startIndex" value=$startIndex + 1}
		{/if}
	{/foreach}
	
	{if THREAD_ENABLE_QUICK_REPLY == 1 && $thread->canReplyThread() && $this->user->userID != 0}
		{include file='threadQuickReply'}
	{/if}
	
	<div class="contentFooter">
		{@$pagesOutput}
		
		<div id="threadEditMarked" class="optionButtons"></div>
		<div id="postEditMarked" class="optionButtons"></div>
		
		<div class="largeButtons">
			{if $thread->canReplyThread() || $additionalLargeButtons|isset}
				<ul>
					{if $thread->canReplyThread()}<li><a href="index.php?form=PostAdd&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}" id="replyButton2" title="{lang}wbb.thread.button.reply{/lang}"><img src="{icon}messageAddM.png{/icon}" alt="" /> <span>{lang}wbb.thread.button.reply{/lang}</span></a></li>{/if}
					
					{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
				</ul>
			{/if}
		</div>
	</div>
	
	{cycle values='class="container-2",class="container-1"' name='container' print=false}
	
	{if $usersOnlineTotal|isset || $similarThreads|count || $tags|count || $additionalBoxes|isset}
		<div class="border infoBox">
			{if $usersOnlineTotal|isset}
				<div {cycle name='container'}>
					<div class="containerIcon"><img src="{icon}membersM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{if $this->user->getPermission('user.usersOnline.canView')}<a href="index.php?page=UsersOnline&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{lang}wbb.thread.usersOnline{/lang}</a>{else}{lang}wbb.thread.usersOnline{/lang}{/if}</h3>
						<p class="smallFont">{lang}wbb.index.usersOnline.detail{/lang}</p>
						{if $usersOnline|count}
							<p class="smallFont">{implode from=$usersOnline item=userOnline}<a href="index.php?page=User&amp;userID={@$userOnline.userID}{@SID_ARG_2ND}">{@$userOnline.username}</a>{/implode}</p>
							{if INDEX_ENABLE_USERS_ONLINE_LEGEND && $usersOnlineMarkings|count}
								<p class="smallFont">
								{lang}wcf.usersOnline.marking.legend{/lang} {implode from=$usersOnlineMarkings item=usersOnlineMarking}{@$usersOnlineMarking}{/implode}
								</p>
							{/if}
						{/if}
					</div>
				</div>
			{/if}
			
			{if $similarThreads|count}
				<div {cycle name='container'}>
					<div class="containerIcon"><img src="{icon}similarThreadsM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wbb.thread.similarThreads{/lang}</h3>
						<ul class="similarThreads">
							{foreach from=$similarThreads item=similarThread}
								<li{if BOARD_THREADS_ENABLE_MESSAGE_PREVIEW} title="{$similarThread->firstPostPreview}"{/if}>
									<ul class="breadCrumbs">
										<li><a href="index.php?page=Board&amp;boardID={@$similarThread->boardID}{@SID_ARG_2ND}"><img src="{icon}boardS.png{/icon}" alt="" /> <span>{lang}{$similarThread->title}{/lang}</span></a> &raquo;</li>
										<li>
											<a href="index.php?page=Thread&amp;threadID={@$similarThread->threadID}{@SID_ARG_2ND}"><img src="{icon}threadS.png{/icon}" alt="" /> {if $similarThread->prefix}<span class="prefix"><strong>{lang}{$similarThread->prefix}{/lang}</strong></span> {/if}<span>{$similarThread->topic}</span></a>
											<span class="light">({@$similarThread->time|shorttime})</span>
										</li>
									</ul>
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}
			
			{if $tags|count}
				<div {cycle name='container'}>
					<div class="containerIcon"><img src="{icon}tagM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wcf.tagging.tags.used{/lang}</h3>
						<p class="smallFont">{implode from=$tags item=tag}<a href="index.php?page=TaggedObjects&amp;tagID={@$tag->getID()}{@SID_ARG_2ND}">{$tag->getName()}</a>{/implode}</p>
					</div>
				</div>
			{/if}

			{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
		</div>
	{/if}
	
	<div class="pageOptions">
		{if $additionalPageOptions|isset}{@$additionalPageOptions}{/if}

		{if MODULE_THREAD_MARKING_AS_DONE && $board->enableMarkingAsDone && $this->user->userID && $this->user->userID == $thread->userID && $board->getPermission('canMarkAsDoneOwnThread') && !$thread->isDone}
			<a href="index.php?action=ThreadMarkAsDone&amp;threadID={@$thread->threadID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}doneS.png{/icon}" alt="" /> <span>{lang}wbb.board.threads.button.done{/lang}</span></a>
		{/if}

		{if $this->user->userID}
			{if !$thread->subscribed}
				<a href="index.php?action=ThreadSubscribe&amp;threadID={@$thread->threadID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}subscribeS.png{/icon}" alt="" /> <span>{lang}wbb.thread.subscribe{/lang}</span></a>
			{else}
				<a href="index.php?action=ThreadUnsubscribe&amp;threadID={@$thread->threadID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}unsubscribeS.png{/icon}" alt="" /> <span>{lang}wbb.thread.unsubscribe{/lang}</span></a>
			{/if}
		{/if}
		
		{if $enableRating && $board->getPermission('canRateThread')}
			{if $thread->userRating === null || $thread->userRating > 0}
				<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/Rating.class.js"></script>
				<form method="post" action="index.php?page=Thread">
					<div>
						<input type="hidden" name="threadID" value="{@$thread->threadID}" />
						{@SID_INPUT_TAG}
						<input type="hidden" name="pageNo" value="{@$pageNo}" />
						<input type="hidden" id="threadRating" name="rating" value="0" />

						<span class="hidden" id="threadRatingSpan"></span>

						<span>{lang}wbb.thread.rate{/lang}</span>

						<noscript>
							<div>
								<select id="threadRatingSelect" name="rating">
									<option value="1"{if $thread->userRating == 1} selected="selected"{/if}>1</option>
									<option value="2"{if $thread->userRating == 2} selected="selected"{/if}>2</option>
									<option value="3"{if $thread->userRating == 3} selected="selected"{/if}>3</option>
									<option value="4"{if $thread->userRating == 4} selected="selected"{/if}>4</option>
									<option value="5"{if $thread->userRating == 5} selected="selected"{/if}>5</option>
								</select>
								<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
							</div>
						</noscript>
					</div>
				</form>
				
				<script type="text/javascript">
					//<![CDATA[
					new Rating('threadRating', {@$thread->userRating|intval});
					//]]>
				</script>
			{/if}
		{/if}
	</div>
	{include file='boardQuickJump'}
</div>

{include file='footer' sandbox=false}
</body>
</html>
