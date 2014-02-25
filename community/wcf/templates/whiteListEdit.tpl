{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.whitelist.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $success|isset}
			<p class="success">
				{if $success == 'remove'}{lang}wcf.user.whitelist.remove.success{/lang}{/if}
				{if $success == 'add'}
					{if $addedUsers|count > 0}{lang}wcf.user.whitelist.add.success{/lang}{/if}
					{if $invitedUsers|count > 0}{lang}wcf.user.whitelist.offer.success{/lang}{/if}
				{/if}
				{if $success == 'decline'}{lang}wcf.user.whitelist.decline.success{/lang}{/if}
				{if $success == 'accept'}{lang}wcf.user.whitelist.accept.success{/lang}{/if}
				{if $success == 'cancel'}{lang}wcf.user.whitelist.cancel.success{/lang}{/if}
			</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=WhiteListEdit">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.whitelist.title{/lang}</h3>
					
				<div class="formElement{if $errorField == 'usernames'} formError{/if}">
					<div class="formFieldLabel">
						<label for="usernames">{lang}wcf.user.whitelist.username{/lang}</label>
					</div>
						
					<div class="formField">
						<input type="text" class="inputText" name="usernames" value="{$usernames}" id="usernames" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.setSource('index.php?page=PublicUserSuggest{@SID_ARG_2ND_NOT_ENCODED}');
							suggestion.init('usernames');
							//]]>
						</script>
						{if $errorField == 'usernames'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{if $error.type == 'notFound'}{lang username=$error.username}wcf.user.error.username.notFound{/lang}{/if}
											{if $error.type == 'canNotAddYourself'}{lang}wcf.user.whitelist.error.username.canNotAddYourself{/lang}{/if}
											{if $error.type == 'userDoesNotAllowFriendshipOfferings'}{lang}wcf.user.whitelist.error.username.userDoesNotAllowFriendshipOfferings{/lang}{/if}
										</p>
									{/foreach}
								{else}
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{/if}
							</div>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.whitelist.username.description{/lang}</p>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				
				{if $invitingMembers|count > 0}
					<fieldset>
						<legend>{lang}wcf.user.whitelist.invitingMembers{/lang}</legend>
						<ul class="memberList">
							{foreach from=$invitingMembers item=member}
								<li class="deletable">
									{if $member->isOnline()}
										<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.online{/lang}" class="memberListStatusIcon" />
									{else}
										<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.offline{/lang}" class="memberListStatusIcon" />
									{/if}
									<a href="index.php?page=User&amp;userID={@$member->userID}{@SID_ARG_2ND}" title="{lang username=$member}wcf.user.viewProfile{/lang}" class="memberName"><span>{$member}</span></a>
									<a href="index.php?form=WhiteListEdit&amp;accept={@$member->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.whitelist.accept{/lang}" class="memberRemove acceptButton deleteButton"><img src="{icon}checkS.png{/icon}" alt="" longdesc="{lang}wcf.user.whitelist.accept.sure{/lang}" /></a>
									<a href="index.php?form=WhiteListEdit&amp;decline={@$member->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.whitelist.decline{/lang}" class="memberRemove deleteButton"><img src="{icon}deleteS.png{/icon}" alt="" longdesc="{lang}wcf.user.whitelist.decline.sure{/lang}" /></a>
								</li>
							{/foreach}
						</ul>
					</fieldset>
					
					{if $invitingMembers|count > 1}
						<div class="contentFooter">
							<div class="largeButtons">
								<ul>
									<li><a href="index.php?action=WhiteListDeclineAllInvitations&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.user.whitelist.declineAll.sure{/lang}')" title="{lang}wcf.user.whitelist.declineAll{/lang}"><img src="{icon}deleteM.png{/icon}" alt="" /> <span>{lang}wcf.user.whitelist.declineAll{/lang}</span></a></li>
									<li><a href="index.php?action=WhiteListAcceptAllInvitations&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.user.whitelist.acceptAll.sure{/lang}')" title="{lang}wcf.user.whitelist.acceptAll{/lang}"><img src="{icon}checkM.png{/icon}" alt="" /> <span>{lang}wcf.user.whitelist.acceptAll{/lang}</span></a></li>
								</ul>
							</div>
						</div>
					{/if}
				{/if}
				
				{if $invitedMembers|count > 0}
					<fieldset>
						<legend>{lang}wcf.user.whitelist.invitedMembers{/lang}</legend>
						<ul class="memberList">
							{foreach from=$invitedMembers item=member}
								<li class="deletable">
									{if $member->isOnline()}
										<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.online{/lang}" class="memberListStatusIcon" />
									{else}
										<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.offline{/lang}" class="memberListStatusIcon" />
									{/if}
									<a href="index.php?page=User&amp;userID={@$member->userID}{@SID_ARG_2ND}" title="{lang username=$member}wcf.user.viewProfile{/lang}" class="memberName"><span>{$member}</span></a>
									<a href="index.php?form=WhiteListEdit&amp;cancel={@$member->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.whitelist.cancel{/lang}" class="memberRemove deleteButton"><img src="{icon}deleteS.png{/icon}" alt="" longdesc="{lang}wcf.user.whitelist.cancel.sure{/lang}" /></a>
								</li>
							{/foreach}
						</ul>
					</fieldset>
				{/if}
				
				{if $members|count > 0}
					<fieldset>
						<legend>{lang}wcf.user.whitelist.members{/lang}</legend>
						<ul class="memberList">
							{foreach from=$members item=member}
								<li class="deletable">
									{if $member->isOnline()}
										<img src="{icon}onlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.online{/lang}" class="memberListStatusIcon" />
									{else}
										<img src="{icon}offlineS.png{/icon}" alt="" title="{lang username=$member}wcf.user.offline{/lang}" class="memberListStatusIcon" />
									{/if}
									<a href="index.php?page=User&amp;userID={@$member->userID}{@SID_ARG_2ND}" title="{lang username=$member}wcf.user.viewProfile{/lang}" class="memberName"><span>{$member}</span></a>
									<a href="index.php?form=WhiteListEdit&amp;remove={@$member->userID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.whitelist.remove{/lang}" class="memberRemove deleteButton"><img src="{icon}deleteS.png{/icon}" alt="" longdesc="{lang}wcf.user.whitelist.remove.sure{/lang}" /></a>
								</li>
							{/foreach}
						</ul>
					</fieldset>
					
					<div class="contentFooter">
						<div class="largeButtons">
							<ul>
								<li><a href="index.php?action=WhiteListEmpty&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.user.whitelist.empty.sure{/lang}')" title="{lang}wcf.user.whitelist.empty{/lang}"><img src="{icon}deleteM.png{/icon}" alt="" /> <span>{lang}wcf.user.whitelist.empty{/lang}</span></a></li>
							</ul>
						</div>
					</div>
				{/if}
				
				{@SID_INPUT_TAG}
			</div>
		</div>
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>