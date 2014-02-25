{include file="documentHeader"}
<head>
	<title>{if $parentHelpItem}{@$parentHelpItem->index} {lang}wcf.help.item.{$parentHelpItem->helpItem}{/lang} - {/if}{lang}wcf.help.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
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

<div id="main" class="help">

	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		
		{if $parentHelpItem}
			<li><a href="index.php?page=Help{@SID_ARG_2ND}"><img src="{icon}helpS.png{/icon}" alt="" /> <span>{lang}wcf.help.title{/lang}</span></a> &raquo;</li>
		{/if}
		
		{foreach from=$parentItems item=parentItem}
			<li><a href="index.php?page=Help&amp;item={$parentItem->helpItem}{@SID_ARG_2ND}"><img src="{icon}helpS.png{/icon}" alt="" /> <span>{@$parentItem->index} {lang}wcf.help.item.{$parentItem->helpItem}{/lang}</span></a> &raquo;</li>
		{/foreach}
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}helpL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{if $parentHelpItem}{@$parentHelpItem->index} {lang}wcf.help.item.{$parentHelpItem->helpItem}{/lang}{else}{lang}wcf.help.title{/lang}{/if}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}

	<div class="border">
		<div class="layout-3">
			<div class="columnContainer">
				<div class="column first container-3">
					<div class="columnInner">
						<div class="contentBox">
							<h3 class="subHeadline"><a href="index.php?page=Help{@SID_ARG_2ND}">{lang}wcf.help.toc{/lang}</a></h3>
							<div class="tocMenu">
								<ol>
									{foreach from=$toc item=$tocItem}
										<li><a {if $parentHelpItem && $tocItem[item]->helpItem == $parentHelpItem->helpItem}style="font-weight: bold;" {/if}href="index.php?page=Help&amp;item={$tocItem[item]->helpItem}{@SID_ARG_2ND}">{lang}wcf.help.item.{$tocItem[item]->helpItem}{/lang}</a>
										{if $tocItem.hasChildren}<ol>{else}</li>{/if}
										{if $tocItem.openParents > 0}{@"</ol></li>"|str_repeat:$tocItem.openParents}{/if}
									{/foreach}
								</ol>
							</div>
						</div>
					</div>
				</div>
			
			
				<div class="column second container-1">
					<div class="columnInner">
						<div class="contentBox">
							<h3 class="subHeadline">{if $parentHelpItem}{@$parentHelpItem->index} {lang}wcf.help.item.{$parentHelpItem->helpItem}{/lang}{else}{lang}wcf.help.title{/lang}{/if}</h3>
						
							{if $parentHelpItem}{lang}wcf.help.item.{$parentHelpItem->helpItem}.description{/lang}{else}{lang}wcf.help.description{/lang}{/if}
							
							{foreach from=$items item=$item}
								<h3 class="subHeadline">{@$item->index} <a href="index.php?page=Help&amp;item={$item->helpItem}{@SID_ARG_2ND}">{lang}wcf.help.item.{$item->helpItem}{/lang}</a></h3>
								<p>{@$item->getExcerpt()}</p>
							{/foreach}
							
							{if $previousItem || $nextItem}
								<div class="buttonBar">
									{if $previousItem}<p title="{lang}wcf.help.previous{/lang}" class="tocBack" >&laquo; {@$previousItem->index} <a href="index.php?page=Help&amp;item={$previousItem->helpItem}{@SID_ARG_2ND}">{lang}wcf.help.item.{$previousItem->helpItem}{/lang}</a></p>{/if}
									{if $nextItem}<p title="{lang}wcf.help.next{/lang}" class="tocForward">{@$nextItem->index} <a href="index.php?page=Help&amp;item={$nextItem->helpItem}{@SID_ARG_2ND}">{lang}wcf.help.item.{$nextItem->helpItem}{/lang}</a> &raquo;</p>{/if}
								</div>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

{include file='footer' sandbox=false}

</body>
</html>