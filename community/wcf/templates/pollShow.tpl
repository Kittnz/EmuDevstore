{if $polls|isset}
	{assign var="poll" value=$polls->getPoll($pollID)}

	{if $poll}
		<div class="poll" id="poll{@$pollID}">
			<div class="border pollShowResults{if $poll->canVote() && !$poll->voted} hidden{/if}">
				<div class="container-3 pollQuestion">
					<div class="containerIcon"><img src="{icon}pollM.png{/icon}" alt="" title="{lang}wcf.poll{/lang}" /></div>
					<div class="containerContent">
						<h4><a href="index.php?page=PollOverview&amp;pollID={@$poll->pollID}{@SID_ARG_2ND}">{$poll->question}</a></h4>
						<p class="smallFont">{lang}wcf.poll.votes{/lang}</p>
					</div>
				</div>
				{cycle name='barColor' values='1,2,3,4,5,6,7,8,9,10' print=false advance=false reset=true}
				{foreach from=$poll->getSortedPollOptions() item=pollOption}
					<div class="container-{cycle values="1,2"}{if $pollOption->isChecked()} pollOptionChecked{/if}">
						<div class="containerIcon">
							<p class="smallFont">{@$pollOption->getPercent()|round:0}%</p>
						</div>
						<div class="containerContent">
							{if $pollOption->getBarWidth() > 0}<div class="pollOptionBar pollBarColor{cycle name='barColor'}" style="width: {@$pollOption->getBarWidth()}%"></div>{/if}
							<p class="smallFont pollOption">{$pollOption->pollOption}{if $pollOption->votes} ({#$pollOption->votes}){/if}</p>
						</div>
					</div>
				{/foreach}
				
				<div class="container-3 pollResults">
					{if $poll->canVote()}
						<input type="submit" class="pollChangeButton" value="{if $poll->voted}{lang}wcf.poll.button.change{/lang}{else}{lang}wcf.poll.button.vote{/lang}{/if}" />
					{/if}
					<input type="button" value="{lang}wcf.poll.button.detail{/lang}" onclick="document.location.href=fixURL('index.php?page=PollOverview&amp;pollID={@$poll->pollID}{@SID_ARG_2ND}')" />
				</div>
			</div>
		
			{if $poll->canVote()}
				<form method="post">
					<div class="border pollShowForm{if $poll->voted} hidden{/if}">
						<div class="container-3 pollQuestion">
							<div class="containerIcon"><img src="{icon}pollM.png{/icon}" alt="" title="{lang}wcf.poll{/lang}" /></div>
							<div class="containerContent">
								<h4><a href="index.php?page=PollOverview&amp;pollID={@$poll->pollID}{@SID_ARG_2ND}">{$poll->question}</a></h4>
								
								{if $poll->choiceCount > 1 && $poll->choiceCount < $poll->getPollOptions()|count}
									<p class="smallFont">{lang}wcf.poll.vote.choiceCount{/lang}</p>
								{/if}
								
								{if $poll->endTime > 0}
									<p class="smallFont">{lang}wcf.poll.vote.endTime{/lang}</p>
								{/if}
								
								{if $poll->voted}
									<p class="smallFont">{lang}wcf.poll.vote.changeVote{/lang}</p>
								{/if}
								{if $activePollID|isset && $activePollID == $pollID && $errorField == 'pollOptionID'}
									<p class="innerError smallFont">
										{if $errorType == 'notValid'}{lang}wcf.poll.vote.error.notValid{/lang}{/if}
										{if $errorType == 'tooMuch'}{lang}wcf.poll.vote.error.tooMuch{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
							
						{foreach from=$poll->getPollOptions() item=pollOption}
							<div class="container-{cycle values="1,2"}">
								<div class="containerIcon">
									<input id="pollOption{@$pollOption->pollOptionID}" {if $poll->choiceCount > 1}type="checkbox" name="pollOptionID[]"{else}type="radio" name="pollOptionID"{/if} value="{@$pollOption->pollOptionID}" {if $pollOption->isChecked()}checked="checked" {/if}/>
								</div>
								<div class="containerContent"><label for="pollOption{@$pollOption->pollOptionID}" class="smallFont">{$pollOption->pollOption}</label></div>
							</div>
						{/foreach}
						
						<div class="container-3 pollResults">
							<input type="submit" name="votePoll" value="{lang}wcf.global.button.submit{/lang}" />
							<input type="submit" class="pollShowButton" value="{lang}wcf.poll.button.result{/lang}" />
							<input type="button" value="{lang}wcf.poll.button.detail{/lang}" onclick="document.location.href=fixURL('index.php?page=PollOverview&amp;pollID={@$poll->pollID}{@SID_ARG_2ND}')" />
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
	{/if}
{/if}