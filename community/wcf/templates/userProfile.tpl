{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.profile.title{/lang} - {lang}wcf.user.profile.members{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchFieldTitle' value='{lang}wcf.user.profile.search.query{/lang}'}
{capture assign=searchHiddenFields}
	<input type="hidden" name="userID" value="{@$user->userID}" />
{/capture}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	{include file="userProfileHeader"}
	
	<div class="border">
		<div class="layout-2">
			<div class="columnContainer">	
				<div class="container-1 column first">
					<div class="columnInner">
						
						{if $additionalContent1|isset}{@$additionalContent1}{/if}
					
						{cycle values='container-1,container-2' print=false advance=false}

						{foreach from=$categories item=category}
							<div class="contentBox">
								<h3 class="subHeadline">{lang}wcf.user.option.category.{$category.categoryName}{/lang}</h3>
								
								<ul class="dataList">
									{foreach from=$category.options item=option}
										{if $option.optionName == 'aboutMe'}
											<li class="{cycle} messageBody">
												{@$option.optionValue}
											</li>
										{else}
											<li class="{cycle} formElement">
												<p class="formFieldLabel">{lang}wcf.user.option.{$option.optionName}{/lang}</p>
												<p class="formField">{@$option.optionValue}</p>
											</li>
										{/if}
									{/foreach}
								</ul>
								
								{if $category.categoryName == 'profile.aboutMe'}
									{if $additionalAboutMeContent|isset}{@$additionalAboutMeContent}{/if}
								{/if}
								
								<div class="buttonBar">
									<div class="smallButtons">
										<ul><li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li></ul>
									</div>
								</div>
							</div>
						{foreachelse}
							<div class="contentBox">
								<h3 class="subHeadline">{lang}wcf.user.option.category.profile.aboutMe{/lang}</h3>
								
								<div class="messageBody">{lang}wcf.user.option.aboutMe.empty{/lang}</div>
								
								{if $additionalAboutMeContent|isset}{@$additionalAboutMeContent}{/if}
								
								<div class="buttonBar">
									<div class="smallButtons">
										<ul><li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li></ul>
									</div>
								</div>						
							</div>	
						{/foreach}
						
						{if $additionalContent2|isset}{@$additionalContent2}{/if}
						
						{if $friends|count > 0}
							<div class="contentBox">
								<h3 class="subHeadline"><a href="index.php?page=UserFriendList&amp;userID={@$userID}{@SID_ARG_2ND}">{lang}wcf.user.profile.friends{/lang}</a> <span>({#$user->friends})</span></h3>
								
								<ul class="dataList thumbnailView floatContainer container-1">
									{foreach name='friends' from=$friends item=friend}
										<li class="floatedElement smallFont{if $tpl.foreach.friends.iteration == 5} last{/if}">
											<a href="index.php?page=User&amp;userID={@$friend->userID}{@SID_ARG_2ND}" title="{lang username=$friend->username}wcf.user.viewProfile{/lang}">
												{if $friend->getAvatar()}
													{assign var=x value=$friend->getAvatar()->setMaxSize(48, 48)}
													<span class="thumbnail" style="width: {@$friend->getAvatar()->getWidth()}px;">{@$friend->getAvatar()}</span>
												{else}
													<span class="thumbnail" style="width: 48px;"><img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 48px; height: 48px" /></span>
												{/if}
												<span class="avatarCaption">{$friend->username}</span>
											</a>
										</li>
									{/foreach}
								</ul>
								<div class="buttonBar">
									<div class="smallButtons">
										<ul>
											<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
											<li><a href="index.php?page=UserFriendList&amp;userID={@$userID}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.friends.allFriends{/lang}"><img src="{icon}friendsS.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.friends.allFriends{/lang}</span></a></li>
										</ul>
									</div>
								</div>
							</div>
						{/if}
						
						{if $additionalContent3|isset}{@$additionalContent3}{/if}
					</div>
				</div>
					
				<div class="container-3 column second sidebar profileSidebar">
					<div class="columnInner">
					
						{if $additionalBoxes1|isset}{@$additionalBoxes1}{/if}
					
						{if $contactInformation|count > 0}
							<div class="contentBox">
								<div class="border"> 
									<div class="containerHead"> 
										<h3>{lang}wcf.user.profile.contact{/lang}</h3> 
									</div> 
									<div class="pageMenu"> 
										<ul class="twoRows">
											{foreach from=$contactInformation item=contact}
												<li class="{cycle values='container-1,container-2'}">
													<a{if $contact.url} href="{@$contact.url}"{/if}>{if $contact.icon}<img src="{@$contact.icon}" alt="" /> {/if}<label class="smallFont">{@$contact.title}</label> <span>{@$contact.value}</span></a>
												</li>
											{/foreach}
										</ul>
									</div> 
								</div>
							</div>
						{/if}
						
						{if $generalInformation|count > 0}
							<div class="contentBox">
								<div class="border">
									<div class="containerHead">
										<h3>{lang}wcf.user.profile.general{/lang}</h3>
									</div>
									
									<ul class="dataList">
										{foreach from=$generalInformation item=general}
											<li class="{cycle values='container-1,container-2'}">
												<div class="containerIcon">
													{if !$general.icon|empty}<img src="{@$general.icon}" alt="" title="{@$general.title}" />{/if}
												</div>
												<div class="containerContent">
													<h4 class="smallFont">{@$general.title}</h4>
													<p>{@$general.value}</p>
												</div>
											</li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
						
						{if $additionalBoxes2|isset}{@$additionalBoxes2}{/if}
					
						{if $profileVisitors|count > 0}
							<div class="contentBox">
								<div class="border">
									<div class="containerHead">
										<h3>{lang}wcf.user.profile.visitors{/lang}</h3>
									</div>
									
									<ul class="dataList">
										{foreach from=$profileVisitors item=profileVisitor}
											<li class="{cycle values='container-1,container-2'}">
												<div class="containerIcon">
													<a href="index.php?page=User&amp;userID={@$profileVisitor->userID}{@SID_ARG_2ND}" title="{lang username=$profileVisitor->username}wcf.user.viewProfile{/lang}">
														{if $profileVisitor->getAvatar()}
															{assign var=x value=$profileVisitor->getAvatar()->setMaxSize(24, 24)}
															{@$profileVisitor->getAvatar()}
														{else}
															<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 24px; height: 24px" />
														{/if}
													</a>
												</div>
												<div class="containerContent">
													<h4><a href="index.php?page=User&amp;userID={@$profileVisitor->userID}{@SID_ARG_2ND}" title="{lang username=$profileVisitor->username}wcf.user.viewProfile{/lang}">{$profileVisitor->username}</a></h4>
													<p class="light smallFont">{@$profileVisitor->time|time}</p>
												</div>
											</li>
										{/foreach}
									</ul>
								</div>
							</div>
						{/if}
						
						{if $additionalBoxes3|isset}{@$additionalBoxes3}{/if}
		
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>
