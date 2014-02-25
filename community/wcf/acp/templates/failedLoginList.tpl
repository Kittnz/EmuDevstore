{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/loginFailedL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.failedLogin.view{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=FailedLoginList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
</div>

{if $failedLogins|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.failedLogin.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnFailedLoginID{if $sortField == 'failedLoginID'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=failedLoginID&amp;sortOrder={if $sortField == 'failedLoginID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.failedLogin.failedLoginID{/lang}{if $sortField == 'failedLoginID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnEnvironment{if $sortField == 'environment'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=environment&amp;sortOrder={if $sortField == 'environment' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.failedLogin.environment{/lang}{if $sortField == 'environment'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUsername{if $sortField == 'username'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTime{if $sortField == 'time'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.failedLogin.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=ipAddress&amp;sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.failedLogin.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserAgent{if $sortField == 'userAgent'} active{/if}"><div><a href="index.php?page=FailedLoginList&amp;pageNo={@$pageNo}&amp;sortField=userAgent&amp;sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.failedLogin.userAgent{/lang}{if $sortField == 'userAgent'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$failedLogins item=failedLogin}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnFailedLoginID columnID">{@$failedLogin->failedLoginID}</td>
					<td class="columnEnvironment columnText">
						{lang}wcf.acp.failedLogin.environment.{$failedLogin->environment}{/lang}
					</td>
					<td class="columnUsername columnText">{$failedLogin->username}</td>
					<td class="columnTime columnText">{@$failedLogin->time|time}</td>
					<td class="columnIpAddress columnText">{$failedLogin->ipAddress}</td>
					<td class="columnUserAgent columnText smallFont">{$failedLogin->userAgent}</td>
					
					{if $additionalColumns.$failedLogin->failedLoginID|isset}{@$additionalColumns.$failedLogin->failedLoginID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.failedLogin.view.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
