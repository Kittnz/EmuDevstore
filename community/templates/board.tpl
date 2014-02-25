{include file="documentHeader"}
<head>
	<title>{lang}{$board->title}{/lang} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<link rel="alternate" type="application/rss+xml" href="index.php?page=ThreadsFeed&amp;format=rss2&amp;boardID={@$boardID}" title="{lang}wbb.board.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=ThreadsFeed&amp;format=atom&amp;boardID={@$boardID}" title="{lang}wbb.board.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchFieldTitle' value='{lang}wbb.board.search.query{/lang}'}
{capture assign=searchHiddenFields}
	<input type="hidden" name="boardIDs[]" value="{@$boardID}" />
	<input type="hidden" name="types[]" value="post" />
{/capture}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation"}
	
	<div class="mainHeadline">
		<img src="{icon}{@$board->getIconName()}L.png{/icon}" alt="" {if $board->isBoard()}ondblclick="document.location.href=fixURL('index.php?action=BoardMarkAsRead&amp;boardID={@$boardID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}')" title="{lang}wbb.board.markAsReadByDoubleClick{/lang}" {/if}/>
		<div class="headlineContainer">
			<h2><a href="index.php?page=Board&amp;boardID={@$boardID}{@SID_ARG_2ND}">{lang}{$board->title}{/lang}</a></h2>
			<p>{lang}{if $board->allowDescriptionHtml}{@$board->description}{else}{$board->description}{/if}{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{include file="boardList"}
	
	{if $board->isBoard()}
		<div class="contentHeader">
			{assign var=encodedPrefix value=$prefix|urlencode}
			{assign var=multiplePagesLink value="index.php?page=Board&boardID=$boardID&pageNo=%d"}
			{if $sortField != $defaultSortField}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&sortField=':$sortField}{/if}
			{if $sortOrder != $defaultSortOrder}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&sortOrder=':$sortOrder}{/if}
			{if $daysPrune != $defaultDaysPrune}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&daysPrune=':$daysPrune}{/if}
			{if $status}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&status=':$status}{/if}
			{if $prefix}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&prefix=':$encodedPrefix}{/if}
			{if $languageID}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&languageID=':$languageID}{/if}
			{if $tagID}{assign var=multiplePagesLink value=$multiplePagesLink|concat:'&tagID=':$tagID}{/if}
			{pages print=true assign=pagesOutput link=$multiplePagesLink|concat:SID_ARG_2ND_NOT_ENCODED}
			{if $board->canStartThread() || $additionalLargeButtons|isset}
				<div class="largeButtons">
					<ul>
						{if $board->canStartThread()}<li><a href="index.php?form=ThreadAdd&amp;boardID={@$boardID}{@SID_ARG_2ND}" title="{lang}wbb.board.button.newThread{/lang}"><img src="{icon}threadNewM.png{/icon}" alt="" /> <span>{lang}wbb.board.button.newThread{/lang}</span></a></li>{/if}
						{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
					</ul>
				</div>
			{/if}
		</div>
	
		{if $permissions.canHandleThread || $permissions.canHandlePost}
			<script type="text/javascript">
				//<![CDATA[	
				var language = new Object();
				var postData = new Hash();
				var url = 'index.php?page=Board&boardID={@$boardID}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}&daysPrune={@$daysPrune}&status={@$status}&prefix={@$prefix|encodeJS}&languageID={@$languageID}{@SID_ARG_2ND_NOT_ENCODED}';
				//]]>
			</script>
			{include file='threadInlineEdit' pageType=board}
		{/if}
		
		{if $topThreads|count == 0 && $normalThreads|count == 0}
			<div class="border content">
				<div class="container-1">
					<p>{lang}wbb.board.noThreads{/lang}</p>
				</div>
			</div>
		{else}
			<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/ThreadMarkAsRead.class.js"></script>
			{if $topThreads|count > 0}
				{include file="boardThreads" title="{lang}wbb.board.threads.top{/lang}" threads=$topThreads listName=topThreadsStatus listStatus=$topThreadsStatus listHasNewThreads=$newTopThreads}
			{/if}
			
			{if $normalThreads|count > 0}
				{include file="boardThreads" title="{lang}wbb.board.threads.normal{/lang}" threads=$normalThreads listName=normalThreadsStatus listStatus=$normalThreadsStatus listHasNewThreads=$newNormalThreads}
			{/if}
		{/if}
	
		<div class="contentFooter">
			{@$pagesOutput}
			
			<div id="threadEditMarked" class="optionButtons"></div>
			<div id="postEditMarked" class="optionButtons"></div>
	
			{if $board->canStartThread() || $additionalLargeButtons|isset}
				<div class="largeButtons">
					<ul>
						{if $board->canStartThread()}<li><a href="index.php?form=ThreadAdd&amp;boardID={@$boardID}{@SID_ARG_2ND}" title="{lang}wbb.board.button.newThread{/lang}"><img src="{icon}threadNewM.png{/icon}" alt="" /> <span>{lang}wbb.board.button.newThread{/lang}</span></a></li>{/if}
						{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
					</ul>
				</div>
			{/if}
		</div>
	{/if}
	
	{if $board->isBoard() || $usersOnlineTotal|isset || $boardModerators|count || $additionalBoxes|isset || $tags|count}
		{cycle values='container-1,container-2' print=false advance=false}
		<div class="border infoBox">
			{if $board->isBoard()}
				<div class="{cycle} infoBoxSorting">
					<div class="containerIcon"><img src="{icon}sortM.png{/icon}" alt="" /> </div>
					<div class="containerContent">
						<h3>{lang}wbb.board.sorting{/lang}</h3>
						<form method="get" action="index.php">
							<div class="threadSort">
								<input type="hidden" name="page" value="Board" />
								<input type="hidden" name="boardID" value="{@$boardID}" />
								<input type="hidden" name="pageNo" value="{@$pageNo}" />
								<input type="hidden" name="tagID" value="{@$tagID}" />
								
								<div class="floatedElement">
									<label for="sortField">{lang}wbb.board.sortBy{/lang}</label>
									<select name="sortField" id="sortField">
										<option value="prefix"{if $sortField == 'prefix'} selected="selected"{/if}>{lang}wbb.board.sortBy.prefix{/lang}</option>
										<option value="topic"{if $sortField == 'topic'} selected="selected"{/if}>{lang}wbb.board.sortBy.topic{/lang}</option>
										{if MODULE_ATTACHMENT}<option value="attachments"{if $sortField == 'attachments'} selected="selected"{/if}>{lang}wbb.board.sortBy.attachments{/lang}</option>{/if}
										{if MODULE_POLL}<option value="polls"{if $sortField == 'polls'} selected="selected"{/if}>{lang}wbb.board.sortBy.polls{/lang}</option>{/if}
										<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wbb.board.sortBy.starter{/lang}</option>
										<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wbb.board.sortBy.startTime{/lang}</option>
										{if $enableRating}<option value="ratingResult"{if $sortField == 'ratingResult'} selected="selected"{/if}>{lang}wbb.board.sortBy.rating{/lang}</option>{/if}
										<option value="replies"{if $sortField == 'replies'} selected="selected"{/if}>{lang}wbb.board.sortBy.replies{/lang}</option>
										<option value="views"{if $sortField == 'views'} selected="selected"{/if}>{lang}wbb.board.sortBy.views{/lang}</option>
										<option value="lastPostTime"{if $sortField == 'lastPostTime'} selected="selected"{/if}>{lang}wbb.board.sortBy.lastPostTime{/lang}</option>
									</select>
									<select name="sortOrder">
										<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
										<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
									</select>
								</div>
								
								<div class="floatedElement">
									<label for="filterDate">{lang}wbb.board.filterByDate{/lang}</label>
									<select name="daysPrune" id="filterDate">
										<option value="1"{if $daysPrune == 1} selected="selected"{/if}>{lang}wbb.board.filterByDate.1{/lang}</option>
										<option value="3"{if $daysPrune == 3} selected="selected"{/if}>{lang}wbb.board.filterByDate.3{/lang}</option>
										<option value="7"{if $daysPrune == 7} selected="selected"{/if}>{lang}wbb.board.filterByDate.7{/lang}</option>
										<option value="14"{if $daysPrune == 14} selected="selected"{/if}>{lang}wbb.board.filterByDate.14{/lang}</option>
										<option value="30"{if $daysPrune == 30} selected="selected"{/if}>{lang}wbb.board.filterByDate.30{/lang}</option>
										<option value="60"{if $daysPrune == 60} selected="selected"{/if}>{lang}wbb.board.filterByDate.60{/lang}</option>
										<option value="100"{if $daysPrune == 100} selected="selected"{/if}>{lang}wbb.board.filterByDate.100{/lang}</option>
										<option value="365"{if $daysPrune == 365} selected="selected"{/if}>{lang}wbb.board.filterByDate.365{/lang}</option>
										<option value="1000"{if $daysPrune == 1000} selected="selected"{/if}>{lang}wbb.board.filterByDate.1000{/lang}</option>
									</select>
								</div>
								
								{if $board->hasPrefixes()}
									<div class="floatedElement">
										<label for="filterPrefix">{lang}wbb.board.filterByPrefix{/lang}</label>
										<select name="prefix" id="filterPrefix">
											<option value=""></option>
											<option value="empty"{if $prefix == 'empty'} selected="selected"{/if}>{lang}wbb.board.filterByPrefix.empty{/lang}</option>
											{htmlOptions options=$board->getPrefixOptions() selected=$prefix}
										</select>
									</div>
								{/if}
								
								<div class="floatedElement">
									<label for="filterByStatus">{lang}wbb.board.filterByStatus{/lang}</label>
									<select name="status" id="filterByStatus">
										<option value=""></option>
										{if $this->user->userID}
											<option value="read"{if $status == 'read'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.read{/lang}</option>
											<option value="unread"{if $status == 'unread'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.unread{/lang}</option>
										{/if}
										{if MODULE_THREAD_MARKING_AS_DONE && $board->enableMarkingAsDone}
											<option value="done"{if $status == 'done'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.done{/lang}</option>
											<option value="undone"{if $status == 'undone'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.undone{/lang}</option>
										{/if}
										<option value="closed"{if $status == 'closed'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.closed{/lang}</option>
										<option value="open"{if $status == 'open'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.open{/lang}</option>
										{if $board->getModeratorPermission('canDeleteThreadCompletely')}<option value="deleted"{if $status == 'deleted'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.deleted{/lang}</option>{/if}
										{if $board->getModeratorPermission('canEnableThread')}<option value="hidden"{if $status == 'hidden'} selected="selected"{/if}>{lang}wbb.board.filterByStatus.hidden{/lang}</option>{/if}
									</select>
								</div>
								
								{if $contentLanguages|count > 1}
									<div class="floatedElement">
										<label for="filterByLanguage">{lang}wbb.board.filterByLanguage{/lang}</label>
										<select name="languageID" id="filterByLanguage">
											<option value="0"></option>
											{htmlOptions options=$contentLanguages selected=$languageID disableEncoding=true}
										</select>
									</div>
								{/if}
								
								<div class="floatedElement">
									<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
								</div>
	
								{@SID_INPUT_TAG}
							</div>
						</form>
					</div>
				</div>
			{/if}
	
			{if $boardModerators|count}
				<div class="{cycle} infoBoxModerators">
					<div class="containerIcon"><img src="{icon}moderatorM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wbb.board.moderators{/lang}</h3>
						<p class="smallFont">{implode from=$boardModerators item=moderator}{if $moderator->userID}<a href="index.php?page=User&amp;userID={@$moderator->userID}{@SID_ARG_2ND}">{$moderator}</a>{else}{$moderator}{/if}{/implode}</p>
					</div>
				</div>
			{/if}
		
			{if $usersOnlineTotal|isset}
				<div class="{cycle} infoBoxUsersOnline">
					<div class="containerIcon"><img src="{icon}membersM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{if $this->user->getPermission('user.usersOnline.canView')}<a href="index.php?page=UsersOnline&amp;boardID={@$boardID}{@SID_ARG_2ND}">{lang}wbb.board.usersOnline{/lang}</a>{else}{lang}wbb.board.usersOnline{/lang}{/if}</h3> 
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
		
			{if $board->isBoard() && BOARD_ENABLE_STATS}
				<div class="{cycle} infoBoxStatistics">
					<div class="containerIcon"><img src="{icon}statisticsM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wbb.index.stats{/lang}</h3> 
						<p class="smallFont">{lang}wbb.board.stats.detail{/lang}</p>
					</div>
				</div>
			{/if}
			
			{if $tags|count}
				<div class="{cycle} infoBoxTags">
					<div class="containerIcon"><img src="{icon}tagM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wcf.tagging.filter{/lang}</h3>
						<ul class="tagCloud">
							{foreach from=$tags item=tag}
								<li><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField={@$sortField}&amp;sortOrder={@$sortOrder}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tag->getID()}{@SID_ARG_2ND}" style="font-size: {@$tag->getSize()}%">{$tag->getName()}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/if}
			
			{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
		</div>
	{/if}
	
	<div class="pageOptions">
		{if $board->isBoard()}
		
			{if $additionalPageOptions|isset}{@$additionalPageOptions}{/if}
			{if $this->user->userID}
				{if !$this->user->isBoardSubscription($boardID)}<a href="index.php?action=BoardSubscribe&amp;boardID={@$boardID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}subscribeS.png{/icon}" alt="" /> <span>{lang}wbb.board.subscribe{/lang}</span></a>
				{else}<a href="index.php?action=BoardUnsubscribe&amp;boardID={@$boardID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}unsubscribeS.png{/icon}" alt="" /> <span>{lang}wbb.board.unsubscribe{/lang}</span></a>
				{/if}
			{/if}
			<a href="index.php?action=BoardMarkAsRead&amp;boardID={@$boardID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}boardMarkAsReadS.png{/icon}" alt="" /> <span>{lang}wbb.board.markAsRead{/lang}</span></a>
		{/if}
	</div>
	
	{include file='boardQuickJump'}
</div>

{include file='footer' sandbox=false}
</body>
</html>