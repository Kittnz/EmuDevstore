{include file="documentHeader"}
<head>
	<title>{$folders.$folderID.folderName} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<link rel="alternate" type="application/rss+xml" href="index.php?page=PMFeed&amp;format=rss2" title="{lang}wcf.pm.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=PMFeed&amp;format=atom" title="{lang}wcf.pm.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
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
	
	<div class="mainHeadline">
		<img src="{icon}{$folders.$folderID.iconLarge}{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {$folders.$folderID.folderName}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{pages assign=pagesOutput link="index.php?page=PMList&folderID=$folderID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&filterBySender=$filterBySender"|concat:SID_ARG_2ND_NOT_ENCODED}
	{include file="pmList"}

</div>

{include file='footer' sandbox=false}
</body>
</html>