{include file="documentHeader"}
<head>
	<title>{lang}wbb.moderation.reports.title{/lang} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	
	{include file='imageViewer'}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wbb.moderation.{@$action}.title{/lang}</h3>
			{if $posts|count == 0}
				<p>{lang}wbb.moderation.reports.noPosts{/lang}</p>
			{else}
				<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
				<script type="text/javascript">
					//<![CDATA[
					var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
					//]]>
				</script>
				<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
				
				<div class="contentHeader">
					{pages print=true assign=pagesOutput link='index.php?page=ModerationReports&pageNo=%d'|concat:SID_ARG_2ND_NOT_ENCODED}
					
					<div class="optionButtons">
						<ul>
							<li><a><label><input name="postMarkAll" type="checkbox" /> <span>{lang}wbb.moderation.posts.markAll{/lang}</span></label></a></li>
						</ul>
					</div>
				</div>
				
				<script type="text/javascript">
					//<![CDATA[
					var language = new Object();
					var postData = new Hash();
					var url = 'index.php?page=ModerationReports&pageNo={@$pageNo}{@SID_ARG_2ND_NOT_ENCODED}';
					//]]>
				</script>
				
				{include file='threadInlineEdit' pageType=reports}
				
				{cycle name='container' values='1,2' print=false advance=false}
				{foreach from=$posts item=post}
					{assign var="author" value=$post->getUser()}
					{assign var="messageID" value=$post->postID}
					<script type="text/javascript">
						//<![CDATA[
						postData.set({@$post->postID}, {
							'isMarked': {@$post->isMarked()},
							'isDeleted': {@$post->isDeleted},
							'isDisabled': {@$post->isDisabled},
							'isClosed': {@$post->isClosed}
						});
						//]]>
					</script>
					
					<div id="postRow{@$post->postID}" class="message content">
						<div class="messageInner container-{cycle name='container'}">
							<div class="messageHeader">
								<p class="messageCount">
									<a href="index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}{@SID_ARG_2ND}" class="messageNumber">{#$startIndex}</a>
									<span class="messageMarkCheckBox">
										<input id="postMark{@$post->postID}" type="checkbox" />
									</span>
								</p>
								<div class="containerIcon">
									<img id="postEdit{@$post->postID}" src="{icon}{@$post->getIconName()}M.png{/icon}" alt="" />
								</div>
								<div class="containerContent">
									<p class="smallFont light">{@$post->time|time}</p>
									<p class="smallFont light">{lang}wbb.board.threads.postBy{/lang} {if $post->userID}<a href="index.php?page=User&amp;userID={@$post->userID}{@SID_ARG_2ND}">{$post->username}</a>{else}{$post->username}{/if}</p>
								</div>
							</div>
							
							<h4 id="postTopic{@$post->postID}" class="messageHeading"><span>{$post->subject}</span></h4>
							
							<div class="messageBody">
								<div id="postText{@$post->postID}">
									{@$post->getFormattedMessage()}
								</div>
							</div>
							{include file='attachmentsShow'}
							
							<p class="editNote">{lang}wbb.moderation.reports.reportedBy{/lang} {if $post->reporterID}<a href="index.php?page=User&amp;userID={@$post->reporterID}{@SID_ARG_2ND}">{$post->reporter}</a>{else}{$post->reporter}{/if} ({@$post->reportTime|time})</p>
							<p>{$post->report}</p>
							<div class="messageFooter">
								<div class="smallButtons">
									<ul>
										<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="" title="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
									</ul>
								</div>
								<ul class="breadCrumbs light">
									<li> <img src="{icon}boardS.png{/icon}" alt="" /> <a href="index.php?page=Board&amp;boardID={@$post->boardID}{@SID_ARG_2ND}">{lang}{$post->title}{/lang}</a> &raquo; </li>
									<li> <img src="{icon}threadS.png{/icon}" alt="" /> {if $post->prefix}<span class="prefix"><strong>{lang}{$post->prefix}{/lang}</strong></span> {/if}<a href="index.php?page=Thread&amp;threadID={@$post->threadID}{@SID_ARG_2ND}">{$post->topic}</a> </li>
								</ul>
							</div>
							<hr />
						</div>
					</div>
					{assign var="startIndex" value=$startIndex + 1}
				{/foreach}
				
				<div class="contentFooter">
					{@$pagesOutput}
					
					<div id="postEditMarked" class="optionButtons"></div>
				</div>
			{/if}
		</div>
	</div>
</div>

{include file='footer' sandbox=false}

</body>
</html>