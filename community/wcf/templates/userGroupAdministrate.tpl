{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.userGroups.leader.administrate{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
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
				{lang}wcf.user.userGroups.leader.administrate.members.add.success{/lang}
			</p>
		{/if}
		
		{if $pmSuccess}
			<p class="success">
				{lang}wcf.user.userGroups.leader.administrate.pm.success{/lang}
			</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.userGroups.leader.administrate{/lang}</h3>
			
			<form method="post" action="index.php?form=UserGroupAdministrate">
				<fieldset>
					<legend>{lang}wcf.user.userGroups.leader.administrate.members.add{/lang}</legend>
				
					<div class="formElement{if $errorField == 'usernames'} formError{/if}">
						<div class="formFieldLabel">
							<label for="usernames">{lang}wcf.user.userGroups.leader.administrate.members.usernames{/lang}</label>
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
											</p>
										{/foreach}
									{else}
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{/if}
								</div>
							{/if}
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.user.userGroups.leader.administrate.members.usernames.description{/lang}</p>
						</div>
					</div>
					
				</fieldset>
				
				<div class="formSubmit">
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
					<input type="hidden" name="groupID" value="{@$groupID}" />
					<input type="hidden" name="pageNo" value="{@$pageNo}" />
					{@SID_INPUT_TAG}
				</div>
			</form>
			
			{if $members|count > 0}
				{if MODULE_PM}
					<form method="post" action="index.php?action=UserGroupPM">
						<fieldset>
							<legend>{lang}wcf.user.userGroups.leader.administrate.pm{/lang}</legend>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="subject">{lang}wcf.pm.subject{/lang}</label>
								</div>
									
								<div class="formField">
									<input type="text" class="inputText" name="subject" value="" id="subject" />
								</div>
							</div>
							
							<div class="formElement">
								<div class="formFieldLabel">
									<label for="text">{lang}wcf.pm.text{/lang}</label>
								</div>
									
								<div class="formField">
									<textarea rows="10" cols="40" name="text" id="text"></textarea>
								</div>
							</div>
						</fieldset>
						
						<div class="formSubmit">
							<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
							<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
							<input type="hidden" name="groupID" value="{@$groupID}" />
							{@SID_INPUT_TAG}
							{@SECURITY_TOKEN_INPUT_TAG}
						</div>
					</form>
				{/if}
				
				<form method="post" action="index.php?action=UserGroupMemberRemove">
					<fieldset>
						<legend>{lang}wcf.user.userGroups.leader.administrate.members{/lang}</legend>
						<ul class="formOptions">
							{foreach from=$members item=member}
								<li>
									<input type="checkbox" name="userIDs[]" value="{$member.userID}" />
									<a href="index.php?page=User&amp;userID={@$member.userID}{@SID_ARG_2ND}">{$member.username}</a>
								</li>
							{/foreach}
						</ul>
						
						<div class="contentFooter">
							{pages print=true assign=pagesOutput link="index.php?form=UserGroupAdministrate&pageNo=%d&groupID=$groupID"|concat:SID_ARG_2ND_NOT_ENCODED}
						</div>
					</fieldset>
					
					<div class="formSubmit">
						<input type="submit" accesskey="r" value="{lang}wcf.user.userGroups.leader.administrate.members.remove{/lang}" />
						<input type="hidden" name="groupID" value="{@$groupID}" />
						{@SID_INPUT_TAG}
						{@SECURITY_TOKEN_INPUT_TAG}
					</div>
				</form>
			{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>