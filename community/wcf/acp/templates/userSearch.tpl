{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	onloadEvents.push(function() { tabMenu.showSubTabMenu('profile') });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.search{/lang}</h2>
	</div>
</div>

{if $errorField == 'search'}
<p class="error">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/if}

{if $deletedUsers}
	<p class="success">{lang}wcf.acp.user.delete.success{/lang}</p>	
{elseif $deletedUsers === 0}
	<p class="error">{lang}wcf.acp.user.delete.error{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			{if $this->user->getPermission('admin.user.canAddUser')}
				<li><a href="index.php?form=UserAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userAddM.png" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="index.php?page=UserList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.user.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/usersM.png" alt="" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a>
			</li>
		</ul>
	</div>
</div>

<form method="post" action="index.php?form=UserSearch">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.search.conditions.general{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="username" name="staticParameters[username]" value="{if $staticParameters.username|isset}{$staticParameters.username}{/if}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.enableMultiple(false);
							suggestion.init('username');
							//]]>
						</script>
						<label><input type="checkbox" name="matchExactly[username]" value="1" {if $matchExactly.username|isset}checked="checked" {/if}/> {lang}wcf.global.search.matchesExactly{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="userID">{lang}wcf.user.userID{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" id="userID" name="staticParameters[userID]" value="{if $staticParameters.userID|isset}{$staticParameters.userID}{/if}" />
					</div>
				</div>
				
				{if $this->user->getPermission('admin.user.canEditMailAddress')}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="email">{lang}wcf.user.email{/lang}</label>
						</div>
						<div class="formField">	
							<input type="text" class="inputText" id="email" name="staticParameters[email]" value="{if $staticParameters.email|isset}{$staticParameters.email}{/if}" />
							<label><input type="checkbox" name="matchExactly[email]" value="1" {if $matchExactly.email|isset}checked="checked" {/if}/> {lang}wcf.global.search.matchesExactly{/lang}</label>
						</div>
					</div>
				{/if}
				
				{if $availableGroups|count}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.groups{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableGroups name='staticParameters[groupIDs]' selected=$staticParameters.groupIDs}
									
									<label style="margin-top: 10px"><input type="checkbox" name="invertGroupIDs" value="1" {if $invertGroupIDs == 1}checked="checked" {/if}/> {lang}wcf.acp.user.groups.invertSearch{/lang}</label>
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
				
				{if $availableLanguages|count > 1}
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.user.language{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.language{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$availableLanguages name='staticParameters[languageIDs]' disableEncoding=true}
								</div>
							</fieldset>
						</div>
					</div>
				{/if}
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
			
			<div class="tabMenu">
				<ul>
					{if $options|count}<li id="profile"><a onclick="tabMenu.showSubTabMenu('profile');"><span>{lang}wcf.acp.user.search.conditions.profile{/lang}</span></a></li>{/if}
					{if $additionalTabs|isset}{@$additionalTabs}{/if}
					<li id="resultOptions"><a onclick="tabMenu.showSubTabMenu('resultOptions');"><span>{lang}wcf.acp.user.search.display{/lang}</span></a></li>
				</ul>
			</div>
			<div class="subTabMenu">
				<div class="containerHead"><div> </div></div>
			</div>
			
			{if $options|count}
				<div class="border tabMenuContent hidden" id="profile-content">
					<div class="container-1">
						<h3 class="subHeadline">{lang}wcf.acp.user.search.conditions.profile{/lang}</h3>
						{include file='optionFieldList' langPrefix='wcf.user.option.'}
					</div>
				</div>
			{/if}
			
			{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
			
			<div class="border tabMenuContent hidden" id="resultOptions-content">
				<div class="container-1">
					<h3 class="subHeadline">{lang}wcf.acp.user.search.display{/lang}</h3>
					
					<fieldset>
						<legend>{lang}wcf.acp.user.search.display.general{/lang}</legend>
						
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="sortField">{lang}wcf.acp.user.search.display.sort{/lang}</label>
							</div>
							<div class="formField">
								<select name="sortField" id="sortField">
									<option value="userID"{if $sortField == 'userID'} selected="selected"{/if}>{lang}wcf.user.userID{/lang}</option>
									<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.user.username{/lang}</option>
									<option value="email"{if $sortField == 'email'} selected="selected"{/if}>{lang}wcf.user.email{/lang}</option>
									<option value="registrationDate"{if $sortField == 'registrationDate'} selected="selected"{/if}>{lang}wcf.user.registrationDate{/lang}</option>
									
									{if $additionalSortFields|isset}{@$additionalSortFields}{/if}
								</select>
								<select name="sortOrder" id="sortOrder">
									<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
									<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
								</select>
							</div>
						</div>
						
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="itemsPerPage">{lang}wcf.acp.user.search.display.itemsPerPage{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" id="itemsPerPage" name="itemsPerPage" value="{@$itemsPerPage}" />
							</div>
						</div>
					</fieldset>
					
					<fieldset>
						<legend>{lang}wcf.acp.user.search.display.columns{/lang}</legend>
						
						{if $options|count}
							<div class="formGroup">
								<div class="formGroupLabel">
									<label>{lang}wcf.acp.user.search.display.columns.profile{/lang}</label>
								</div>
								<div class="formGroupField">
									<fieldset>
										<legend>{lang}wcf.acp.user.search.display.columns.profile{/lang}</legend>
										
										<div class="formField">
											{foreach from=$options item=option}
												<label><input type="checkbox" name="columns[]" value="{$option.optionName}" {if $option.optionName|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.option.{$option.optionName}{/lang}</label>
											{/foreach}
										</div>
									</fieldset>
								</div>
							</div>
						{/if}
						
						<div class="formGroup">
							<div class="formGroupLabel">
								<label>{lang}wcf.acp.user.search.display.columns.other{/lang}</label>
							</div>
							<div class="formGroupField">
								<fieldset>
									<legend>{lang}wcf.acp.user.search.display.columns.other{/lang}</legend>
									
									<div class="formField">
										<label><input type="checkbox" name="columns[]" value="email" {if "email"|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.email{/lang}</label>
										<label><input type="checkbox" name="columns[]" value="registrationDate" {if "registrationDate"|in_array:$columns}checked="checked" {/if}/> {lang}wcf.user.registrationDate{/lang}</label>
										
										{if $additionalColumns|isset}{@$additionalColumns}{/if}
									</div>
								</fieldset>
							</div>
						</div>
						
						
					</fieldset>
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	document.getElementById('username').focus();
	//]]>
</script>

{include file='footer'}