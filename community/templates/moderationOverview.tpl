{include file="documentHeader"}
<head>
	<title>{lang}wbb.moderation.overview.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wbb.moderation.overview.title{/lang}</h3>
			
			<ul class="moderationOverview">
				<li>
					<img src="{icon}postReportL.png{/icon}" alt="" />
					<ul>
						<li><a href="index.php?page=ModerationReports{@SID_ARG_2ND}">{lang}wbb.moderation.reports.count{/lang}</a></li>
					</ul>
				</li>
				
				{if $this->user->getPermission('mod.board.canEnablePost') || $this->user->getPermission('mod.board.canEnableThread')}
					<li>
						<img src="{icon}postHiddenL.png{/icon}" alt="" />
						<ul>
							{if $this->user->getPermission('mod.board.canEnablePost')}<li><a href="index.php?page=ModerationHiddenPosts{@SID_ARG_2ND}">{lang}wbb.moderation.hiddenPosts.count{/lang}</a></li>{/if}
							{if $this->user->getPermission('mod.board.canEnableThread')}<li><a href="index.php?page=ModerationHiddenThreads{@SID_ARG_2ND}">{lang}wbb.moderation.hiddenThreads.count{/lang}</a></li>{/if}
						</ul>
					</li>
				{/if}
				
				{if $this->user->getPermission('mod.board.canReadDeletedPost') || $this->user->getPermission('mod.board.canReadDeletedThread')}
					<li>
						<img src="{icon}postDeletedL.png{/icon}" alt="" />
						<ul>
							{if $this->user->getPermission('mod.board.canReadDeletedPost')}<li><a href="index.php?page=ModerationDeletedPosts{@SID_ARG_2ND}">{lang}wbb.moderation.deletedPosts.count{/lang}</a></li>{/if}
							{if $this->user->getPermission('mod.board.canReadDeletedThread')}<li><a href="index.php?page=ModerationDeletedThreads{@SID_ARG_2ND}">{lang}wbb.moderation.deletedThreads.count{/lang}</a></li>{/if}
						</ul>
					</li>
				{/if}
				
				<li>
					<img src="{icon}postMarkedL.png{/icon}" alt="" />
					<ul>
						<li><a href="index.php?page=ModerationMarkedPosts{@SID_ARG_2ND}">{lang}wbb.moderation.markedPosts.count{/lang}</a></li>
						<li><a href="index.php?page=ModerationMarkedThreads{@SID_ARG_2ND}">{lang}wbb.moderation.markedThreads.count{/lang}</a></li>
					</ul>
				</li>
				
				 {if $additionalContents|isset}{@$additionalContents}{/if}
			</ul>
		</div>
	</div>

</div>

{include file='footer' sandbox=false}

</body>
</html>