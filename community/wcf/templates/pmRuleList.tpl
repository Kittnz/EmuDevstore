{include file="documentHeader"}
<head>
	<title>{lang}wcf.pm.editRules{/lang} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{icon}pmEmptyS.png{/icon}" alt="" /> <span>{lang}wcf.pm.title{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}pmRuleL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.pm.editRules{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $deletedRuleID}
		<p class="success">{lang}wcf.pm.rule.delete.success{/lang}</p>	
	{/if}
	
	<div class="contentHeader">
		{pages print=true assign=pagesLinks link="index.php?page=PMRuleList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
		
		<div class="largeButtons">
			<ul><li><a href="index.php?form=PMRuleAdd{@SID_ARG_2ND}" title="{lang}wcf.pm.rule.add{/lang}"><img src="{icon}pmRuleAddM.png{/icon}" alt="" /> <span>{lang}wcf.pm.rule.add{/lang}</span></a></li></ul>
		</div>
	</div>
	
	{if $rules|count}
		<div class="border titleBarPanel">
			<div class="containerHead"><h3>{lang}wcf.pm.rule.view.count{/lang}</h3></div>
		</div>
		<div class="border borderMarginRemove">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th class="columnRuleTitle{if $sortField == 'title'} active{/if}" colspan="2"><div><a href="index.php?page=PMRuleList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.pm.rule.title{/lang}{if $sortField == 'title'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}</a></div></th>
						<th class="columnRuleConditions{if $sortField == 'conditions'} active{/if}"><div><a href="index.php?page=PMRuleList&amp;pageNo={@$pageNo}&amp;sortField=conditions&amp;sortOrder={if $sortField == 'conditions' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.pm.rule.conditions{/lang}{if $sortField == 'conditions'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}</a></div></th>
						
						{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
					</tr>
				</thead>
				<tbody>
				{foreach from=$rules item=rule}
					<tr class="{cycle values="container-1,container-2"}">
						<td class="columnIcon actionIcons">
							{if !$rule->disabled}
								<a href="index.php?action=PMRuleDisable&amp;ruleID={@$rule->ruleID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}enabledS.png{/icon}" alt="" title="{lang}wcf.pm.rule.disable{/lang}" /></a>
							{else}
								<a href="index.php?action=PMRuleEnable&amp;ruleID={@$rule->ruleID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}disabledS.png{/icon}" alt="" title="{lang}wcf.pm.rule.enable{/lang}" /></a>
							{/if}
							
							<a href="index.php?form=PMRuleEdit&amp;ruleID={@$rule->ruleID}{@SID_ARG_2ND}"><img src="{icon}editS.png{/icon}" alt="" title="{lang}wcf.pm.rule.edit{/lang}" /></a>
							<a onclick="return confirm('{lang}wcf.pm.rule.delete.sure{/lang}')" href="index.php?action=PMRuleDelete&amp;ruleID={@$rule->ruleID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}deleteS.png{/icon}" alt="" title="{lang}wcf.pm.rule.delete{/lang}" /></a>
							
							{if $additionalButtons.$rule->ruleID|isset}{@$additionalButtons.$rule->ruleID}{/if}
						</td>
						<td class="columnRuleTitle columnText">
							<a href="index.php?form=PMRuleEdit&amp;ruleID={@$rule->ruleID}{@SID_ARG_2ND}" title="{lang}wcf.pm.rule.edit{/lang}">{$rule->title}</a>
						</td>
						<td class="columnRuleCondtions columnNumbers">{#$rule->conditions}</td>
						
						{if $additionalColumns.$rule->ruleID|isset}{@$additionalColumns.$rule->ruleID}{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
		
		<div class="contentFooter">
			{@$pagesLinks}
			
			<div class="largeButtons">
				<ul><li><a href="index.php?form=PMRuleAdd{@SID_ARG_2ND}" title="{lang}wcf.pm.rule.add{/lang}"><img src="{icon}pmRuleAddM.png{/icon}" alt="" /> <span>{lang}wcf.pm.rule.add{/lang}</span></a></li></ul>
			</div>
		</div>
	{else}
		<div class="border content">
			<div class="container-1">
				<p>{lang}wcf.pm.rule.view.count.noEntries{/lang}</p>
			</div>
		</div>
		
		<div class="largeButtons">
			<ul><li><a href="index.php?form=PMRuleAdd{@SID_ARG_2ND}" title="{lang}wcf.pm.rule.add{/lang}"><img src="{icon}pmRuleAddM.png{/icon}" alt="" /> <span>{lang}wcf.pm.rule.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>
	
{include file='footer' sandbox=false}
</body>
</html>

