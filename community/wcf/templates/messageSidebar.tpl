{if $this->getStyle()->getVariable('messages.sidebar.alignment') == 'top' && $sidebar->getUser()->getAvatar()}{assign var=dummy value=$sidebar->getUser()->getAvatar()->setMaxSize(76, 76)}{/if}
<div class="messageSidebar"{if $this->getStyle()->getVariable('messages.sidebar.alignment') == 'top'} style="min-height: {if $sidebar->getUser()->getAvatar()}{@$sidebar->getUser()->getAvatar()->getHeight()+14}px{else}90px{/if}"{/if}>
	<p class="skipSidebar hidden"><a href="#skipPoint{@$sidebar->getMessageID()}" title="{lang}wcf.message.sidebar.skip{/lang}">{lang}wcf.message.sidebar.skip{/lang}</a></p><!-- support for disabled surfers -->
	{if $sidebar->getUser()->userID}
		<div class="messageAuthor">
			<p class="userName">
				{if MESSAGE_SIDEBAR_ENABLE_ONLINE_STATUS}
					{if $sidebar->getUser()->isOnline()}
						<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$sidebar->getUser()->username}wcf.user.online{/lang}" />
					{else}
						<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$sidebar->getUser()->username}wcf.user.offline{/lang}" />		
					{/if}
				{/if}
			
				<a href="index.php?page=User&amp;userID={@$sidebar->getUser()->userID}{@SID_ARG_2ND}" title="{lang username=$sidebar->getUser()->username}wcf.user.viewProfile{/lang}">
					<span>{@$sidebar->getStyledUsername()}</span>
				</a>
				
				{if $additionalSidebarUsernameInformation[$sidebar->getMessageID()]|isset}{@$additionalSidebarUsernameInformation[$sidebar->getMessageID()]}{/if}
			</p>

			{if MODULE_USER_RANK && MESSAGE_SIDEBAR_ENABLE_RANK}
				{if $sidebar->getUser()->getUserTitle()}
					<p class="userTitle smallFont">{@$sidebar->getUser()->getUserTitle()}</p>
				{/if}
				{if $sidebar->getUser()->getRank() && $sidebar->getUser()->getRank()->rankImage}
					<p class="userRank">{@$sidebar->getUser()->getRank()->getImage()}</p>
				{/if}
			{/if}
			
			{if $additionalSidebarAuthorInformation[$sidebar->getMessageID()]|isset}{@$additionalSidebarAuthorInformation[$sidebar->getMessageID()]}{/if}
		</div>
		
		{if MESSAGE_SIDEBAR_ENABLE_AVATAR}
			{if $sidebar->getUser()->getAvatar()}
				<div class="userAvatar{if $this->getStyle()->getVariable('messages.sidebar.avatar.framed')}Framed{/if}">
					<a href="index.php?page=User&amp;userID={@$sidebar->getUser()->userID}{@SID_ARG_2ND}" title="{lang username=$sidebar->getUser()->username}wcf.user.viewProfile{/lang}"><img src="{$sidebar->getUser()->getAvatar()->getURL()}" alt=""
						style="width: {@$sidebar->getUser()->getAvatar()->getWidth()}px; height: {@$sidebar->getUser()->getAvatar()->getHeight()}px;{if $this->getStyle()->getVariable('messages.sidebar.avatar.framed')} margin-top: -{@$sidebar->getUser()->getAvatar()->getHeight()/2|intval}px; margin-left: -{@$sidebar->getUser()->getAvatar()->getWidth()/2|intval}px{/if}" /></a>
				</div>
			{elseif $this->getStyle()->getVariable('messages.sidebar.alignment') == 'top'}
				<div class="userAvatar{if $this->getStyle()->getVariable('messages.sidebar.avatar.framed')}Framed{/if}">
					<a href="index.php?page=User&amp;userID={@$sidebar->getUser()->userID}{@SID_ARG_2ND}" title="{lang username=$sidebar->getUser()->username}wcf.user.viewProfile{/lang}"><img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt=""
						style="width: 76px; height: 76px;{if $this->getStyle()->getVariable('messages.sidebar.avatar.framed')} margin-top: -38px; margin-left: -38px{/if}" /></a>
				</div>
			{/if}
		{/if}
		
		{if $sidebar->getUserSymbols()|count > 0 || $additionalSidebarUserSymbols[$sidebar->getMessageID()]|isset}
			<div class="userSymbols">
				<ul>
					{foreach from=$sidebar->getUserSymbols() item=$userSymbol}
						<li>{@$userSymbol.value}</li>
					{/foreach}
					
					{if $additionalSidebarUserSymbols[$sidebar->getMessageID()]|isset}{@$additionalSidebarUserSymbols[$sidebar->getMessageID()]}{/if}
				</ul>
			</div>
		{/if}
		
		{if $sidebar->getUserCredits()|count > 0 || $additionalSidebarUserCredits[$sidebar->getMessageID()]|isset}
			<div class="userCredits">
				{foreach from=$sidebar->getUserCredits() item=$userCredit}
					<p>{if $userCredit.url}<a href="{@$userCredit.url}">{@$userCredit.name}: {@$userCredit.value}</a>{else}{@$userCredit.name}: {@$userCredit.value}{/if}</p>
				{/foreach}
				
				{if $additionalSidebarUserCredits[$sidebar->getMessageID()]|isset}{@$additionalSidebarUserCredits[$sidebar->getMessageID()]}{/if}
			</div>
		{/if}
		
		{if $sidebar->getUserContacts()|count > 0 || $additionalSidebarUserContacts[$sidebar->getMessageID()]|isset}
			<div class="userMessenger">
				<ul>
					{foreach from=$sidebar->getUserContacts() item=$userContact}
						<li>{@$userContact.value}</li>
					{/foreach}
					
					{if $additionalSidebarUserContacts[$sidebar->getMessageID()]|isset}{@$additionalSidebarUserContacts[$sidebar->getMessageID()]}{/if}
				</ul>
			</div>
		{/if}
	{else}
		<div class="messageAuthor">
			<p class="userName">{@$sidebar->getStyledUsername()}</p>
			<p class="userTitle smallFont">{lang}wcf.user.guest{/lang}</p>
		</div>
		
		{if $sidebar->getUserSymbols()|count > 0 || $additionalSidebarUserSymbols[$sidebar->getMessageID()]|isset}
			<div class="userSymbols">
				<ul>
					{foreach from=$sidebar->getUserSymbols() item=$userSymbol}
						<li>{@$userSymbol.value}</li>
					{/foreach}
					
					{if $additionalSidebarUserSymbols[$sidebar->getMessageID()]|isset}{@$additionalSidebarUserSymbols[$sidebar->getMessageID()]}{/if}
				</ul>
			</div>
		{/if}
		
		{if $sidebar->getUserContacts()|count > 0 || $additionalSidebarUserContacts[$sidebar->getMessageID()]|isset}
			<div class="userMessenger">
				<ul>
					{foreach from=$sidebar->getUserContacts() item=$userContact}
						<li>{@$userContact.value}</li>
					{/foreach}
					
					{if $additionalSidebarUserContacts[$sidebar->getMessageID()]|isset}{@$additionalSidebarUserContacts[$sidebar->getMessageID()]}{/if}
				</ul>
			</div>
		{/if}
	{/if}
	
	{if $additionalSidebarContents[$sidebar->getMessageID()]|isset}{@$additionalSidebarContents[$sidebar->getMessageID()]}{/if}
	
	<a id="skipPoint{@$sidebar->getMessageID()}"></a><!-- support for disabled surfers -->
</div>