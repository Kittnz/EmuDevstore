{include file="documentHeader"}
<head>
	<title>{if $tagObj}{lang}wcf.tagging.title{/lang}{else}{lang}wcf.tagging.tags{/lang}{/if} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' sandbox=false}

<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php{@SID_ARG_1ST}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}tagL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{if $tagObj}{lang}wcf.tagging.title{/lang}{else}{lang}wcf.tagging.tags{/lang}{/if}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $tagObj}
		<div class="tabMenu">
			<ul>
				{if $availableTaggables|count > 1}
					<li{if !$taggableID} class="activeTabMenu"{/if}><a href="index.php?page=TaggedObjects&amp;tagID={@$tagID}{@SID_ARG_2ND}"><span>{lang}wcf.tagging.overview{/lang}</span></a></li>
				{/if}
				{foreach from=$availableTaggables item=availableTaggable}
					<li{if $availableTaggable->getTaggableID() == $taggableID} class="activeTabMenu"{/if}><a href="index.php?page=TaggedObjects&amp;tagID={@$tagID}&amp;taggableID={@$availableTaggable->getTaggableID()}{@SID_ARG_2ND}"><span>{lang}wcf.tagging.taggable.{$availableTaggable->getName()}{/lang}</span></a></li>
				{/foreach}
			</ul>
		</div>
		<div class="subTabMenu">
			<div class="containerHead"><div> </div></div>
		</div>
		
		<div class="border tabMenuContent">
			<div class="container-1">
				{if !$taggable}
					{* overview *}
					{foreach from=$availableTaggables item=availableTaggable}
						<div class="contentBox">
							{assign var=items value=$taggedObjects[$availableTaggable->getTaggableID()]}
							<h3 class="subHeadline"><a href="index.php?page=TaggedObjects&amp;tagID={@$tagID}&amp;taggableID={@$availableTaggable->getTaggableID()}{@SID_ARG_2ND}" title="{lang}wcf.tagging.taggable.{$availableTaggable->getName()}{/lang}">{lang}wcf.tagging.taggable.{$availableTaggable->getName()}{/lang}</a></h3>
							
							<ul class="dataList">
								{cycle name=className values='container-1,container-2' reset=true print=false}
								{foreach from=$items item=taggedObject}
									<li class="{cycle name=className}">
										<div class="containerIcon">
											<a href="{$taggedObject->getURL()}{@SID_ARG_2ND}"><img src="{@$taggedObject->getMediumSymbol()}" alt="" style="width: 24px;" /></a>
										</div>
										<div class="containerContent">
											<h4><a href="{$taggedObject->getURL()}{@SID_ARG_2ND}">{$taggedObject->getTitle()}</a></h4>
											<p class="firstPost smallFont light">{lang}wcf.tagging.by{/lang} {if $taggedObject->getUser()->userID}<a href="index.php?page=User&amp;userID={@$taggedObject->getUser()->userID}{@SID_ARG_2ND}">{$taggedObject->getUser()->username}</a>{else}{$taggedObject->getUser()->username}{/if} ({@$taggedObject->getDate()|time})</p>
										</div>
									</li>
								{/foreach}
							</ul>
							
							<div class="buttonBar">
								<div class="smallButtons">
									<ul>
										<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
										
										<li><a href="index.php?page=TaggedObjects&amp;tagID={@$tagID}&amp;taggableID={@$availableTaggable->getTaggableID()}{@SID_ARG_2ND}" title="{lang}wcf.tagging.taggable.{$availableTaggable->getName()}{/lang}"><img src="{@$availableTaggable->getSmallSymbol()}" alt="" /> <span>{lang}wcf.tagging.taggable.all.{$availableTaggable->getName()}{/lang}</span></a></li>
									</ul>
								</div>
							</div>
						</div>
					{/foreach}
				{else}
					{include file=$taggable->getResultTemplateName()}
				{/if}
				
				{if $taggable}
					<div class="contentFooter">
						{pages link="index.php?page=TaggedObjects&tagID=$tagID&pageNo=%d&taggableID=$taggableID"|concat:SID_ARG_2ND_NOT_ENCODED}
					</div>
				{/if}
			</div>
		</div>
	{/if}
	
	<div class="border content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.tagging.mostPopular{/lang}</h3>
			{include file="tagCloud"}
		</div>
	</div>
	
</div>

{include file='footer' sandbox=false}

</body>
</html>
