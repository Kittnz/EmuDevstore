{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjobsLogL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.cronjobs.log{/lang}</h2>
		<p>{lang}wcf.acp.cronjobs.subtitle{/lang}</p>
	</div>
</div>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=CronjobsShowLog&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	<div class="largeButtons">
		<ul><li><a href="index.php?page=CronjobsList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjobs.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsM.png" alt="" /> <span>{lang}wcf.acp.menu.link.cronjobs.view{/lang}</span></a></li></ul>
	</div>
</div>

{if !$items}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.cronjobs.log.noEntries{/lang}</p>
		</div>
	</div>
{else}
	<form method="post" action="index.php?action=CronjobsLogDelete">
		<div class="border titleBarPanel">
			<div class="containerHead"><h3>{lang}wcf.acp.cronjobs.log.data{/lang}</h3></div>
		</div>
		<div class="border borderMarginRemove">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th class="columnCronjobID{if $sortField == 'cronjobID'} active{/if}"><div><a href="index.php?page=CronjobsShowLog&amp;pageNo={@$pageNo}&amp;sortField=cronjobID&amp;sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.cronjobID{/lang}{if $sortField == 'cronjobID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						<th class="columnClassPath{if $sortField == 'classPath'} active{/if}"><div><a href="index.php?page=CronjobsShowLog&amp;pageNo={@$pageNo}&amp;sortField=classPath&amp;sortOrder={if $sortField == 'classPath' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.classPath{/lang}{if $sortField == 'classPath'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						<th class="columnDescription{if $sortField == 'description'} active{/if}"><div><a href="index.php?page=CronjobsShowLog&amp;pageNo={@$pageNo}&amp;sortField=description&amp;sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.description{/lang}{if $sortField == 'description'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						<th class="columnExecTime{if $sortField == 'execTime'} active{/if}"><div><a href="index.php?page=CronjobsShowLog&amp;pageNo={@$pageNo}&amp;sortField=execTime&amp;sortOrder={if $sortField == 'execTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.log.execTime{/lang}{if $sortField == 'execTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						
						{if $additionalColumns|isset}{@$additionalColumns}{/if}
					</tr>
				</thead>
				<tbody>
				{foreach from=$logEntries item=logEntry}
					<tr class="{cycle values="container-1,container-2"}">
						<td class="columnCronjobID columnID">{@$logEntry.cronjobID}</td>
						<td class="columnClassPath columnText">{$logEntry.classPath}</td>
						<td class="columnDescription columnText">{$logEntry.description}</td>
						{if $logEntry.success}
							<td class="columnExecTime columnDate">{@$logEntry.execTime|shorttime} {lang}wcf.acp.cronjobs.log.success{/lang}</td>
						{elseif $logEntry.error}
							<td class="columnExecTime columnText">
								{@$logEntry.execTime|shorttime} {lang}wcf.acp.cronjobs.log.error{/lang}<br />
								{@$logEntry.error}
							</td>
						{else}
							<td class="columnExecTime columnText"></td>
						{/if}
						
						{if $logEntry.additionalColumns|isset}{@$logEntry.additionalColumns}{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
		
		<div class="formSubmit">
			<input type="hidden" name="packageID" value="{PACKAGE_ID}" />
			{@SID_INPUT_TAG}
			<input type="submit" accesskey="c" value="{lang}wcf.acp.cronjobs.log.clear{/lang}" onclick="return confirm('{lang}wcf.acp.cronjobs.log.clear.confirm{/lang}')" />
		</div>
	</form>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		<div class="largeButtons">
			<ul><li><a href="index.php?page=CronjobsList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjobs.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsM.png" alt="" /> <span>{lang}wcf.acp.menu.link.cronjobs.view{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{include file='footer'}