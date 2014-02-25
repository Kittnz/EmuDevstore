{include file="documentHeader"}
<head>
	<title>{lang}wcf.help.search.results{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/help{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchScript' value='index.php?form=HelpSearch'}
{assign var='searchFieldTitle' value='{lang}wcf.help.search{/lang}'}
{assign var='searchShowExtendedLink' value=false}
{assign var='searchFieldOptions' value=false}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}helpL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.help.search.results{/lang}</h2>
			<p>{lang}wcf.help.search.results.description{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="contentHeader">
		{assign var=encodedHighlight value=$highlight|urlencode}
		{pages print=true assign=pagesOutput link="index.php?page=HelpSearchResult&pageNo=%d&searchID=$searchID&highlight=$encodedHighlight"|concat:SID_ARG_2ND_NOT_ENCODED}
	</div>
	
	{foreach from=$helpItems item=item}
		<div class="message content">
			 <div class="messageInner container-{cycle name='results' values='1,2'}">
				  <h3><a href="index.php?page=Help&amp;item={$item.item}{@SID_ARG_2ND}">{@$item.title}</a></h3>
				  
				  <div class="messageBody">
					   {@$item.description}
				  </div>
				  <hr />
			 </div>
		</div>
	{/foreach}
	
	<div class="contentFooter">
		{@$pagesOutput}
	</div>
	
</div>

{include file='footer' sandbox=false}

</body>
</html>