{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/bbcodeL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.bbcode.view{/lang}</h2>
	</div>
</div>

{if $deletedBBCodeID}
	<p class="success">{lang}wcf.acp.bbcode.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=BBCodeList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.bbcode.canAddBBCode')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=BBCodeAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/bbcodeAddM.png" alt="" title="{lang}wcf.acp.bbcode.add{/lang}" /> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $bbcodes|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.bbcode.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnBBCodeID{if $sortField == 'bbcodeID'} active{/if}" colspan="2"><div><a href="index.php?page=BBCodeList&amp;pageNo={@$pageNo}&amp;sortField=bbcodeID&amp;sortOrder={if $sortField == 'bbcodeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.bbcode.bbcodeID{/lang}{if $sortField == 'bbcodeID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnBBCodeTag{if $sortField == 'bbcodeTag'} active{/if}"><div><a href="index.php?page=BBCodeList&amp;pageNo={@$pageNo}&amp;sortField=bbcodeTag&amp;sortOrder={if $sortField == 'bbcodeTag' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.bbcode.bbcodeTag{/lang}{if $sortField == 'bbcodeTag'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnBBCodeClassName{if $sortField == 'className'} active{/if}"><div><a href="index.php?page=BBCodeList&amp;pageNo={@$pageNo}&amp;sortField=className&amp;sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.bbcode.className{/lang}{if $sortField == 'className'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnBBCodeAttributes{if $sortField == 'attributeCount'} active{/if}"><div><a href="index.php?page=BBCodeList&amp;pageNo={@$pageNo}&amp;sortField=attributeCount&amp;sortOrder={if $sortField == 'attributeCount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.bbcode.attributes{/lang}{if $sortField == 'attributeCount'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$bbcodes item=bbcode}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.bbcode.canEditBBCode')}
							{if !$bbcode.disabled}
								<a href="index.php?action=BBCodeDisable&amp;bbcodeID={@$bbcode.bbcodeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.bbcode.disable{/lang}" /></a>
							{else}
								<a href="index.php?action=BBCodeEnable&amp;bbcodeID={@$bbcode.bbcodeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.bbcode.enable{/lang}" /></a>
							{/if}
							
							<a href="index.php?form=BBCodeEdit&amp;bbcodeID={@$bbcode.bbcodeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.bbcode.edit{/lang}" /></a>
						{else}
							{if !$bbcode.disabled}
								<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.bbcode.disable{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.bbcode.enable{/lang}" />
							{/if}
							
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.bbcode.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.bbcode.canDeleteBBCode')}
							<a onclick="return confirm('{lang}wcf.acp.bbcode.delete.sure{/lang}')" href="index.php?action=BBCodeDelete&amp;bbcodeID={@$bbcode.bbcodeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.bbcode.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.bbcode.delete{/lang}" />
						{/if}
						
						{if $bbcode.additionalButtons|isset}{@$bbcode.additionalButtons}{/if}
					</td>
					<td class="columnBBCodeID columnID">{@$bbcode.bbcodeID}</td>
					<td class="columnBBCodeTag columnText">
						{if $this->user->getPermission('admin.bbcode.canEditBBCode')}
							<a href="index.php?form=BBCodeEdit&amp;bbcodeID={@$bbcode.bbcodeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$bbcode.bbcodeTag}</a>
						{else}
							{$bbcode.bbcodeTag}
						{/if}
					</td>
					<td class="columnBBCodeClassName columnText">{$bbcode.className}</td>
					<td class="columnBBCodeAttributes columnNumbers">{#$bbcode.attributeCount}</td>
					
					{if $bbcode.additionalColumns|isset}{@$bbcode.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.bbcode.canAddBBCode')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=BBCodeAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/bbcodeAddM.png" alt="" title="{lang}wcf.acp.bbcode.add{/lang}" /> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{/if}

{include file='footer'}
