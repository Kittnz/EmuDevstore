{include file="documentHeader"}
<head>
	<title>{lang}wcf.search.results{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH}; 
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}searchL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{if $query}<a href="index.php?form=Search&amp;q={$query|rawurlencode}{@SID_ARG_2ND}">{lang}wcf.search.results{/lang}</a>{else}{lang}wcf.search.results{/lang}{/if}</h2>
			<p>{lang}wcf.search.results.description{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{cycle print=false name="results" values="1,2" advance=false}
	
	<div class="contentHeader">
		{assign var=encodedHighlight value=$highlight|urlencode}
		{pages print=true assign=pagesOutput link="index.php?form=Search&pageNo=%d&searchID=$searchID&highlight=$encodedHighlight"|concat:SID_ARG_2ND_NOT_ENCODED}
		
		{if $alterable}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=Search&amp;searchID={@$searchID}&amp;modify=1{@SID_ARG_2ND}"><img src="{icon}searchM.png{/icon}" alt="" /> <span>{lang}wcf.search.results.change{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
	{assign var=i value=0}
	{assign var=length value=$messages|count}
	
	{foreach from=$messages item=item}
		{include file=$types[$item.type]->getResultTemplateName()}
		{assign var=i value=$i+1}
	{/foreach}
	
	<div class="contentFooter">
		{@$pagesOutput}
		
		{if $additionalContentFooterElements|isset}{@$additionalContentFooterElements}{/if}
		
		{if $alterable}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=Search&amp;searchID={@$searchID}&amp;modify=1{@SID_ARG_2ND}"><img src="{icon}searchM.png{/icon}" alt="" /> <span>{lang}wcf.search.results.change{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
	
	{if $alterable}
		<div class="border infoBox">
			<div class="container-1">
				<div class="containerIcon"><img src="{icon}sortM.png{/icon}" alt="" /> </div>
				<div class="containerContent">
					<h3>{lang}wcf.search.results.display{/lang}</h3>
					<form method="post" action="index.php">
						
						<div class="floatContainer">
							<input type="hidden" name="form" value="Search" />
							<input type="hidden" name="searchID" value="{@$searchID}" />
							<input type="hidden" name="pageNo" value="{@$pageNo}" />
							<input type="hidden" name="highlight" value="{$highlight}" />
							
							<div class="floatedElement">
								<label for="sortField">{lang}wcf.search.sortBy{/lang}</label>
								<select id="sortField" name="sortField">
									<option value="relevance"{if $sortField == 'relevance'} selected="selected"{/if}>{lang}wcf.search.sortBy.relevance{/lang}</option>
									<option value="subject"{if $sortField == 'subject'} selected="selected"{/if}>{lang}wcf.search.sortBy.subject{/lang}</option>
									<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wcf.search.sortBy.creationDate{/lang}</option>
									<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.search.sortBy.author{/lang}</option>
								</select>
							
								<select name="sortOrder">
									<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
									<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
								</select>
							</div>
							
							<div class="floatedElement">
							{if $additionalDisplayOptions|isset}{@$additionalDisplayOptions}{/if}						
							</div>
							<div class="floatedElement">
								<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
							</div>
	
							<input type="hidden" name="modify" value="1" />
							{@SID_INPUT_TAG}
						</div>
					</form>
				</div>
			</div>
			
			{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
		</div>
	{/if}
	{if $additionalOptions|isset}<div class="pageOptions">{@$additionalOptions}</div>{/if}
</div>

{include file='footer' sandbox=false}
</body>
</html>