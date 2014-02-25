{include file="documentHeader"}
<head>
	<title>{$poll->question} - {lang}wcf.poll{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Poll.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	{if $specialBreadCrumbs|isset}
		{@$specialBreadCrumbs}
	{else}
		<ul class="breadCrumbs">
			<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		</ul>
	{/if}
	
	<div class="mainHeadline">
		<img src="{icon}pollL.png{/icon}" alt="" title="{lang}wcf.poll{/lang}" />
		<div class="headlineContainer">
			<h2><a href="index.php?page=PollOverview&amp;pollID={@$pollID}{@SID_ARG_2ND}">{$poll->question}</a></h2>
			<p>{lang}wcf.poll.votes{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="border">
		<div class="layout-{if $additionalSidebarContent|isset}2{else}1{/if}">
			<div class="columnContainer">
				<div class="container-1 column first">
					<div class="columnInner">
						<div class="contentBox">
							<h3 class="subHeadline">{$poll->question}</h3>
							
							<div class="poll" id="poll{@$pollID}">
								<div class="pollShowResults {if $poll->canVote() && !$poll->voted}hidden{/if}">
									<ul class="dataList">
										{cycle values='container-1,container-2' print=false advance=false}
										{cycle name='barColor' values='1,2,3,4,5,6,7,8,9,10' print=false advance=false reset=true}
										{foreach from=$poll->getSortedPollOptions() item=pollOption}
											<li class="{cycle}">
												<div class="containerIcon">
													<p class="smallFont">{@$pollOption->getPercent()|round:0}%</p>
												</div>
												<div class="containerContent">
													{if $pollOption->getBarWidth() > 0}<div class="pollOptionBar pollBarColor{cycle name='barColor'}" style="width: {@$pollOption->getBarWidth()}%"></div>{/if}
													<p class="pollOption">{$pollOption->pollOption} ({#$pollOption->votes})</p>
												</div>
											</li>
										{/foreach}
									</ul>
									
									{if $poll->canVote()}
										<div class="buttonBar container-1 pollResults">
											<input type="submit" class="pollChangeButton" value="{if $poll->voted}{lang}wcf.poll.button.change{/lang}{else}{lang}wcf.poll.button.vote{/lang}{/if}" />
										</div>
									{/if}
								</div>
								
								{if $poll->canVote()}
									<form method="post" action="index.php?page=PollOverview">
										<div class="pollShowForm {if $poll->voted}hidden{/if}">
											<div>
												{if $poll->choiceCount > 1 && $poll->choiceCount < $poll->getPollOptions()|count}
													<p class="smallFont">{lang}wcf.poll.vote.choiceCount{/lang}</p>
												{/if}
												
												{if $poll->endTime > 0}
													<p class="smallFont">{lang}wcf.poll.vote.endTime{/lang}</p>
												{/if}
												
												{if $poll->voted}
													<p class="smallFont">{lang}wcf.poll.vote.changeVote{/lang}</p>
												{/if}
												
												{if $errorField|isset && $errorField == 'pollOptionID'}
													<p class="innerError">
														{if $errorType == 'notValid'}{lang}wcf.poll.vote.error.notValid{/lang}{/if}
														{if $errorType == 'tooMuch'}{lang}wcf.poll.vote.error.tooMuch{/lang}{/if}
													</p>
												{/if}
											</div>
											
											<ul class="dataList">
											{foreach from=$poll->getPollOptions() item=pollOption}
												<li class="container-{cycle values="1,2"}">
													<div class="containerIcon">
														<label for="pollOption{@$pollOption->pollOptionID}"><input id="pollOption{@$pollOption->pollOptionID}" {if $poll->choiceCount > 1}type="checkbox" name="pollOptionID[]"{else}type="radio" name="pollOptionID"{/if} value="{@$pollOption->pollOptionID}" {if $pollOption->isChecked()}checked="checked" {/if}/></label>
													</div>
													<div class="containerContent">
														<label for="pollOption{@$pollOption->pollOptionID}">{$pollOption->pollOption}</label>
													</div>
												</li>
											{/foreach}
											</ul>
											
											<div class="buttonBar container-1 pollResults">
												<input type="submit" name="votePoll" value="{lang}wcf.global.button.submit{/lang}" />
												<input type="submit" class="pollShowButton" value="{lang}wcf.poll.button.result{/lang}" />
												{@SID_INPUT_TAG}
												<input type="hidden" name="pollID" value="{@$pollID}" />
											</div>					
										</div>
									</form>
								{/if}
							</div>
							
							<script type="text/javascript">
								//<![CDATA[
								new Poll({@$pollID}{if $poll->choiceCount > 1}, {
									'choiceCount' : {@$poll->choiceCount}, 
									'pollOptionIDs' : new Array({implode from=$poll->getPollOptions() item=pollOption}{@$pollOption->pollOptionID}{/implode})
									}{/if}
								);
								//]]>
							</script>
						</div>
						
						{if $votes|count > 0}
							<div class="contentBox">
								<h3 class="subHeadline">{lang}wcf.poll.voters{/lang}</h3>
								{foreach from=$votes item=pollOptionData}
									<fieldset>
										<legend>{$pollOptionData.pollOption->pollOption} ({#$pollOptionData.pollOption->votes})</legend>
										<p>{lang}wcf.poll.option.votes{/lang}</p>
										
										{if $pollOptionData.users|count}
											<ul class="memberList">
												{foreach from=$pollOptionData.users item=user}
													<li class="memberListNoDelete">
														{if $user->isOnline()}
															<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$user->username}wcf.user.online{/lang}" class="memberListStatusIcon" />
														{else}
															<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$user->username}wcf.user.offline{/lang}" class="memberListStatusIcon" />
														{/if}
														<a href="index.php?page=User&amp;userID={@$user->userID}{@SID_ARG_2ND}" title="{lang username=$user->username}wcf.user.viewProfile{/lang}" class="memberName"><span>{$user->username}</span></a>
													</li>
												{/foreach}
											</ul>
										{/if}
									</fieldset>
								{/foreach}
							</div>
						{/if}
					</div>
				</div>
				{if $additionalSidebarContent|isset}
					<div class="container-3 column second">
						<div class="columnInner">
							{@$additionalSidebarContent}
						</div>
					</div>				
				{/if}
			</div>
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>