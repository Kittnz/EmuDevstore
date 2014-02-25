{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.membersList.membersSearch{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchScript' value='index.php?form=MembersSearch'}
{assign var='searchFieldName' value='staticParameters[username]'}
{if $staticParameters.username|isset}{assign var='searchFieldValue' value=$staticParameters.username}{/if}
{assign var='searchFieldTitle' value='{lang}wcf.user.membersList.search.query{/lang}'}
{assign var='searchShowExtendedLink' value=false}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	<div class="mainHeadline">
		<img src="{icon}membersL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {lang}wcf.user.membersList.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField == 'search'}
		<p class="error">{lang}wcf.user.membersList.membersSearch.error.noMatches{/lang}</p>
	{/if}
	
	<div class="tabMenu">
		<ul>
			<li><a href="index.php?page=MembersList{@SID_ARG_2ND}"><img src="{icon}membersM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.allMembers{/lang}</span></a></li>
			<li class="activeTabMenu"><a href="index.php?form=MembersSearch{@SID_ARG_2ND}"><img src="{icon}membersSearchM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.membersSearch{/lang}</span></a></li>
			{if $hasFriends}<li><a href="index.php?page=MyFriendsList{@SID_ARG_2ND}"><img src="{icon}friendsM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.myFriends{/lang}</span></a></li>{/if}
			{if $additionalTabs|isset}{@$additionalTabs}{/if}
		</ul>
	</div>
	<div class="subTabMenu">
		<div class="containerHead"><div> </div></div>
	</div>
	
	<form method="post" action="index.php?form=MembersSearch">
		<div class="border tabMenuContent">
			<div class="container-1">
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="username" name="staticParameters[username]" value="{if $staticParameters.username|isset}{$staticParameters.username}{/if}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.setSource('index.php?page=PublicUserSuggest{@SID_ARG_2ND_NOT_ENCODED}');
							suggestion.enableMultiple(false);
							suggestion.init('username');
							//]]>
						</script>
						<label><input type="checkbox" name="matchExactly[username]" value="1" {if $matchexactly.username|isset}checked="checked" {/if}/>
						{lang}wcf.global.search.matchesExactly{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="email">{lang}wcf.user.email{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="email" name="staticParameters[email]" value="{if $staticParameters.email|isset}{$staticParameters.email}{/if}" />
						<label><input type="checkbox" name="matchExactly[email]" value="1" {if $matchexactly.email|isset}checked="checked" {/if}/>
						{lang}wcf.global.search.matchesExactly{/lang}</label>
					</div>
				</div>
				
				{foreach from=$optionCategories item=category}
					<fieldset>
						<legend>{if $category.categoryIconM}<img src="{$category.categoryIconM}" alt="" /> {/if}{lang}wcf.user.option.category.{$category.categoryName}{/lang}</legend>
						
						{include file='userOptionFieldList' options=$category.options hideDescription=true}
						
					</fieldset>
				{/foreach}
				
				{if $options|count > 0}
					{include file='userOptionFieldList' hideDescription=true}
				{/if}
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		<div class="formSubmit">
			{@SID_INPUT_TAG}
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>