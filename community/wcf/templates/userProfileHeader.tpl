<ul class="breadCrumbs">
	<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	<li>{if MODULE_MEMBERS_LIST}<a href="index.php?page=MembersList{@SID_ARG_2ND}"><img src="{icon}membersS.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.members{/lang}</span></a>{else}<img src="{icon}membersS.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.members{/lang}</span>{/if} &raquo;</li>
</ul>

{assign var=adminOptions value=''}
{if $this->user->getPermission('admin.user.canEditUser')}
	{capture append=adminOptions}<li><a href="acp/index.php?form=Login&amp;url=index.php%3Fform=UserEdit%26userID%3D{@$user->userID}%26packageID%3D{@PACKAGE_ID}">{lang}wcf.acp.user.edit{/lang}</a></li>{/capture}
	{if MODULE_AVATAR == 1}
		{if $user->disableAvatar == 1}
			{capture append=adminOptions}<li><a href="index.php?action=UserAvatarEnable&amp;userID={@$userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}">{lang}wcf.user.profile.avatar.enable{/lang}</a></li>{/capture}
		{else}
			{capture append=adminOptions}<li><a href="index.php?action=UserAvatarDisable&amp;userID={@$userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}">{lang}wcf.user.profile.avatar.disable{/lang}</a></li>{/capture}
		{/if}
	{/if}
{/if}

{if $additionalAdminOptions|isset}{append var=adminOptions value=$additionalAdminOptions}{/if}

<div class="mainHeadline">
	{if $adminOptions != ''}
		<img src="{icon}profileOptionsL.png{/icon}" alt="" id="adminOptions" class="pointer" />
	{else}
		<img id="userEdit{@$userID}" src="{icon}profileL.png{/icon}" alt="" />
	{/if}
	<div class="headlineContainer">
		<h2><a href="index.php?page=User&amp;userID={@$userID}{@SID_ARG_2ND}">{lang}wcf.user.profile.title{/lang}</a></h2>
		{if $user->getOldUsername()}
			<p>{lang}wcf.user.profile.oldUsername{/lang}</p>
		{/if}
	</div>
	{if $adminOptions != ''}
		<div class="hidden" id="adminOptionsMenu">
			<div class="pageMenu">
				<ul>
					{@$adminOptions}
				</ul>
			</div>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			popupMenuList.register("adminOptions");
			//]]>
		</script>
	{/if}
</div>

{if $userMessages|isset}{@$userMessages}{/if}

<div id="userCard" class="border">
	<div class="userCardInner container-1">
		<ul class="userCardList">
			<li id="userCardAvatar" style="width: {if $user->getAvatar() && ($user->userID == $this->user->userID || $this->user->getPermission('user.profile.avatar.canViewAvatar'))}{@$user->getAvatar()->getWidth()+49}{else}149{/if}px">
				<div class="userAvatar">
					<a href="index.php?page=User&amp;userID={@$userID}{@SID_ARG_2ND}">
					{if $user->getAvatar() && ($user->userID == $this->user->userID || $this->user->getPermission('user.profile.avatar.canViewAvatar'))}
						{@$user->getAvatar()}
					{else}
						<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" />
					{/if}
					</a>
				</div>
			</li>
			
			<li id="userCardCredits" style="margin-left: {if $user->getAvatar() && ($user->userID == $this->user->userID || $this->user->getPermission('user.profile.avatar.canViewAvatar'))}{@$user->getAvatar()->getWidth()+50}{else}150{/if}px">
				<div class="userCardCreditsInner">
					<div class="userPersonals">
						<p class="userName">
							{if $user->isOnline()}
								<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$user->username}wcf.user.online{/lang}" />
							{else}
								<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$user->username}wcf.user.offline{/lang}" />		
							{/if}
							<span>{$user->username}</span>
						</p>
						
						{if MODULE_USER_RANK}
							{if $user->getUserTitle()}
								<p class="userTitle smallFont">{@$user->getUserTitle()}</p>
							{/if}
							
							{if $user->getRank() && $user->getRank()->rankImage}
								<p class="userRank">{@$user->getRank()->getImage()}</p>
							{/if}
						{/if}
						
						{if $userSymbols|count > 0}
							<ul class="userStatus">
								{foreach from=$userSymbols item=userSymbol}
									<li>{@$userSymbol}</li>
								{/foreach}
							</ul>
						{/if}
					</div>
					
					<div class="smallButtons userCardOptions">
						<ul>
							{if $this->user->userID}
								{if $user->userID != $this->user->userID}
									{if $user->blackUserID}
										<li><a href="index.php?form=BlackListEdit&amp;remove={@$user->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.removeFromIgnoreList{/lang}"><img src="{icon}removeM.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.removeFromIgnoreList{/lang}</span></a></li>
									{/if}
									
									{if $user->whiteUserID || $user->invitedUserID}
										<li><a href="index.php?form=WhiteListEdit&amp;remove={@$user->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.removeFromBuddyList{/lang}"><img src="{icon}deleteM.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.removeFromBuddyList{/lang}</span></a></li>
									{else}	
										{if !$user->blackUserID}
											<li><a href="index.php?form=BlackListEdit&amp;add={@$user->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.addToIgnoreList{/lang}"><img src="{icon}removeM.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.addToIgnoreList{/lang}</span></a></li>
										{/if}
										<li><a href="index.php?form=WhiteListEdit&amp;add={@$user->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.addToBuddyList{/lang}"><img src="{icon}addM.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.addToBuddyList{/lang}</span></a></li>
									{/if}
								{else}
									<li><a href="index.php?form=UserProfileEdit{@SID_ARG_2ND}" title="{lang}wcf.user.profile.edit{/lang}"><img src="{icon}editM.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.button.edit{/lang}</span></a></li>
								{/if}
							{elseif !REGISTER_DISABLED}
								<li><a href="index.php?page=Register{@SID_ARG_2ND}" title="{lang}wcf.user.register.invitation{/lang}"><img src="{icon}registerM.png{/icon}" alt="" /> <span>{lang}wcf.user.register.invitation{/lang}</span></a></li>
							{/if}
							
							{if $additionalUserCardOptions|isset}{@$additionalUserCardOptions}{/if}
						</ul>
					</div>
				</div>
				
				{if $connection|count > 0}
					<div class="friendsConnection">
						<h3 class="light">{lang}wcf.user.profile.connection{/lang}</h3>
						<ul class="dynContainer dynItems{@$connection|count}">
						
							{foreach name='connection' from=$connection item=friend} 
								<li class="dynItem{if $tpl.foreach.connection.first} first{elseif $tpl.foreach.connection.last} last{/if}">
									<div class="dynBox">
										<div class="dynBoxInner">
											<a href="index.php?page=User&amp;userID={@$friend->userID}{@SID_ARG_2ND}" title="{lang username=$friend->username}wcf.user.viewProfile{/lang}">
												{if $friend->getAvatar()}
													{assign var=x value=$friend->getAvatar()->setMaxSize(48, 48)}
													<span class="avatarFrame" style="width: {@$friend->getAvatar()->getWidth()}px;">{@$friend->getAvatar()}</span>
												{else}
													<span class="avatarFrame" style="width: 48px;"><img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 48px; height: 48px" /></span>
												{/if}
												<span class="avatarCaption smallFont">{$friend->username}</span>
											</a>
										</div>
									</div>
								</li>
							{/foreach}
						</ul>
					</div>
				{elseif $network|count > 0}
					<div class="friendsNetwork">
						<h3 class="light">{lang}wcf.user.profile.network{/lang}</h3>
						<ul class="dynContainer dynItems3">
						
							{if $network.friends}
								<li class="dynItem first">
									<div class="dynBox network1">
										<div class="dynBoxInner">
											<span class="digitDisplay light">{#$network.friends}</span>
											<span class="digitCaption">{lang}wcf.user.profile.network.friends{/lang}</span>
										</div>
									</div>
								</li>
							{/if}
							
							{if $network.friendsOfFriends}
								<li class="dynItem">
									<div class="dynBox network2">
										<div class="dynBoxInner">
											<span class="digitDisplay light">{#$network.friendsOfFriends}</span>
											<span class="digitCaption">{lang}wcf.user.profile.network.friendsOfFriends{/lang}</span>
										</div>
									</div>
								</li>
							{/if}
							
							{if $network.friends3rdGrade}
								<li class="dynItem">
									<div class="dynBox network3">
										<div class="dynBoxInner">
											<span class="digitDisplay light">{#$network.friends3rdGrade}</span>
											<span class="digitCaption">{lang}wcf.user.profile.network.friends3rdGrade{/lang}</span>
										</div>
									</div>
								</li>
							{/if}
						</ul>
					</div>
				{else}
					<div class="friendsNone">
						<h3 class="light">{if $this->user->userID == $user->userID}{lang}wcf.user.profile.network.none{/lang}{elseif $this->user->userID != 0}{lang}wcf.user.profile.connection.none{/lang}{else}{lang}wcf.user.profile.connection.guest{/lang}{/if}</h3>
					</div>
				{/if}
				<!--[if IE]>
					<hr class="hidden" style="display: block; clear: both;" />
				<![endif]-->
			</li>
		</ul>
	</div>
</div>

{if $additionalMessages|isset}{@$additionalMessages}{/if}

{if !$showUserProfileMenu|isset}{assign var=showUserProfileMenu value=true}{/if}
{if $showUserProfileMenu && $this->getUserProfileMenu()->getMenuItems('')|count > 1}
	<div id="profileContent" class="tabMenu">
		<ul>
			{foreach from=$this->getUserProfileMenu()->getMenuItems('') item=item}
				<li{if $item.menuItem|in_array:$this->getUserProfileMenu()->getActiveMenuItems()} class="activeTabMenu"{/if}><a href="{$item.menuItemLink}">{if $item.menuItemIcon}<img src="{$item.menuItemIcon}" alt="" /> {/if}<span>{lang}{@$item.menuItem}{/lang}</span></a></li>
			{/foreach}
		</ul>
	</div>
	
	<div class="subTabMenu">
		<div class="containerHead">
			{assign var=activeMenuItem value=$this->getUserProfileMenu()->getActiveMenuItem()}
			{if $activeMenuItem && $this->getUserProfileMenu()->getMenuItems($activeMenuItem)|count}
				<ul>
					{foreach from=$this->getUserProfileMenu()->getMenuItems($activeMenuItem) item=item}
						<li{if $item.menuItem|in_array:$this->getUserProfileMenu()->getActiveMenuItems()} class="activeSubTabMenu"{/if}><a href="{$item.menuItemLink}">{if $item.menuItemIcon}<img src="{$item.menuItemIcon}" alt="" /> {/if}<span>{lang}{@$item.menuItem}{/lang}</span></a></li>
					{/foreach}
				</ul>
			{else}
				<div> </div>
			{/if}
		</div>
	</div>
{/if}
