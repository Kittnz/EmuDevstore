{include file="documentHeader"}
<head>
	<title>{$folders.$folderID.folderName} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
	{include file='imageViewer'}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{include file='multiQuote' formURL="index.php?form=PMNew&action=new&pmID=$pmID&reply=1"|concat:SID_ARG_2ND_NOT_ENCODED}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

<p class="skipHeader hidden"><a href="#skipToContent" title="{lang}wcf.global.skipToContent{/lang}">{lang}wcf.global.skipToContent{/lang}</a></p><!-- support for disabled surfers -->

{* --- quick search controls --- *}
{assign var='searchFieldTitle' value='{lang}wcf.pm.search.query{/lang}'}
{capture assign=searchHiddenFields}
	<input type="hidden" name="types[]" value="pm" />
{/capture}
{* --- end --- *}
{include file='header' sandbox=false}

	<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{icon}pmEmptyS.png{/icon}" alt="" /> <span>{lang}wcf.pm.title{/lang}</span></a> &raquo;</li>
	</ul>
	
	<a href="#" id="skipToContent"></a><!-- support for disabled surfers -->
	
	<div class="mainHeadline">
		<img src="{icon}{$folders.$folderID.iconLarge}{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {$folders.$folderID.folderName}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{pages assign=pagesOutput link="index.php?page=PMList&folderID=$folderID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&filterBySender=$filterBySender"|concat:SID_ARG_2ND_NOT_ENCODED}
	<div class="pmView">
		{include file="pmList"}
	</div>
	
	<h2>{lang}wcf.pm.dialogue{/lang}</h2>
	<div class="contentHeader">
		{pages page=$pmPageNo pages=$pmPages assign=pmPagesOutput link="index.php?page=PMView&pmID=$pmID&pmPageNo=%d&folderID=$folderID&pageNo=$pageNo&sortField=$sortField&sortOrder=$sortOrder&filterBySender=$filterBySender"|concat:SID_ARG_2ND_NOT_ENCODED}
		
		<div class="largeButtons">
			<ul>
				<li><a href="index.php?form=PMNew&amp;action=new&amp;pmID={@$pmID}&amp;replyToAll=1{@SID_ARG_2ND}"><img src="{icon}pmReplyM.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.reply{/lang}</span></a></li>
			</ul>
		</div>
	</div>
	
	{foreach from=$privateMessages item=pm}
		{assign var="sidebar" value=$sidebarFactory->get('pm', $pm->pmID)}
		{assign var="author" value=$sidebar->getUser()}
		{assign var="messageID" value=$pm->pmID}
		{capture assign='messageClass'}message{if $this->getStyle()->getVariable('messages.framed')}Framed{/if}{@$this->getStyle()->getVariable('messages.sidebar.alignment')|ucfirst}{if $this->getStyle()->getVariable('messages.sidebar.divider.use')} dividers{/if}{/capture}
		
		<script type="text/javascript">
			//<![CDATA[
			quoteData.set('pm-{@$pm->pmID}', {
				objectID: {@$pm->pmID},
				objectType: 'pm',
				quotes: {@$pm->isQuoted()}
			});
			//]]>
		</script>
		
		<a id="pm{@$pm->pmID}"></a>
		
		<div class="message message{if $this->getStyle()->getVariable('messages.framed')}Framed{/if}{@$this->getStyle()->getVariable('messages.sidebar.alignment')|ucfirst}">
			<div class="messageInner {@$messageClass} container-{if $this->getStyle()->getVariable('messages.sidebar.color.cycle')}2{else}3{/if}{if !$sidebar->getUser()->userID} guestPost{/if}">
				{include file='messageSidebar'}
											
				<div class="messageContent">
					<div class="messageContentInner color-1">
						<div class="messageHeader">
							<div class="containerIcon">
								<img src="{icon}pmReadM.png{/icon}" alt="" />
							</div>
							<div class="containerContent">
								<p class="light smallFont">{@$pm->time|time}</p>
								{if $pm->getRecipients()|count > 0}
									{if $pm->getRecipients()|count > 1 || !$pm->recipientID}
										<p class="light smallFont">{lang}wcf.pm.recipientList{/lang}
										{implode from=$pm->getRecipients() item=recipient}<a href="index.php?page=User&amp;userID={@$recipient->recipientID}{@SID_ARG_1ST}">{$recipient->recipient}</a>{if !$pm->recipientID && $recipient->isViewed} ({@$recipient->isViewed|shorttime}){/if}{/implode}
										</p>
									{/if}
								{/if}
							</div>
						</div>
						
						<h3 class="messageTitle">{$pm->subject}</h3>
						
						<div class="messageBody" id="pmText{@$pm->pmID}">
							{@$pm->getFormattedMessage()}
						</div>
						
						{include file='attachmentsShow'}
						
						{if MODULE_USER_SIGNATURE == 1 && $pm->getSignature()}
							<div class="signature">
								{@$pm->getSignature()}
							</div>
						{/if}
						
						<div class="messageFooter{@$this->getStyle()->getVariable('messages.footer.alignment')|ucfirst}">
							<div class="smallButtons">
								{if $folderID >= 0 && $moveToOptions|count > 1}
									<div class="pmMove">
										<form action="index.php?page=PM" method="post">
											{htmlOptions options=$moveToOptions selected=$folderID name=folderID}
											<input type="hidden" name="action" value="moveTo" />
											<input type="hidden" name="pmID" value="{@$pmID}" />
											{@SID_INPUT_TAG}
											{@SECURITY_TOKEN_INPUT_TAG}
											<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
										</form>
									</div>
								{/if}
								<ul id="pmButtons{@$pm->pmID}">
									<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>

									<li><a href="index.php?form=PMNew&amp;action=new&amp;pmID={@$pm->pmID}&amp;forwarding=1{@SID_ARG_2ND}"><img src="{icon}pmForwardS.png{/icon}" alt="" /><span> {lang}wcf.pm.button.forward{/lang}</span></a></li>
									<li><a href="index.php?page=PM&amp;action=delete&amp;pmID={@$pm->pmID}&amp;folderID={@$folderID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.pm.delete.sure{/lang}');" title="{lang}wcf.global.button.delete{/lang}"><img src="{icon}deleteS.png{/icon}" alt="" /> <span>{lang}wcf.global.button.delete{/lang}</span></a></li>
									
									{if $pm->isDeleted == 1}
										<li><a href="index.php?page=PM&amp;action=recover&amp;pmID={@$pm->pmID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.pm.button.recover{/lang}"><img src="{icon}pmRecoverS.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.recover{/lang}</span></a></li>
									{/if}
									
									{if $pm->userID == $this->user->userID && !$pm->isViewedByOne && !$pm->isDraft}
										<li><a href="index.php?page=PM&amp;action=cancel&amp;pmID={@$pm->pmID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.pm.cancel.sure{/lang}');" title="{lang}wcf.pm.button.cancel{/lang}"><img src="{icon}cancelS.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.cancel{/lang}</span></a></li>
										<li><a href="index.php?page=PM&amp;action=edit&amp;pmID={@$pm->pmID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.pm.button.edit{/lang}"><img src="{icon}editS.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.edit{/lang}</span></a></li>
									{/if}
									
									{if $pm->userID == $this->user->userID && $pm->isDraft}
										<li><a href="index.php?form=PMNew&amp;pmID={@$pm->pmID}{@SID_ARG_2ND}" title="{lang}wcf.pm.button.edit{/lang}"><img src="{icon}editS.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.edit{/lang}</span></a></li>
									{/if}
									
									<li><a href="index.php?page=PM&amp;action=download&amp;pmID={@$pm->pmID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.pm.button.download{/lang}"><img src="{icon}pmDownloadS.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.download{/lang}</span></a></li>
									
									{if $additionalSmallButtons|isset}{@$additionalSmallButtons}{/if}
								</ul>
							</div>
						</div>
						<hr />
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	
	<div class="contentFooter">
		{@$pmPagesOutput}
		
		<div class="largeButtons">
			<ul>
				<li><a href="index.php?form=PMNew&amp;action=new&amp;pmID={@$pmID}&amp;replyToAll=1{@SID_ARG_2ND}"><img src="{icon}pmReplyM.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.reply{/lang}</span></a></li>
			</ul>
		</div>
	</div>
	
	<div class="pageOptions">
		{if $parentPmID != 0}
			<a href="index.php?action=PMDialogueDownload&amp;parentPmID={@$parentPmID}{@SID_ARG_2ND}"><img alt="" src="{icon}saveS.png{/icon}"/> <span>{lang}wcf.pm.button.downloadDialogue{/lang}</span></a>
		{/if}
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>
