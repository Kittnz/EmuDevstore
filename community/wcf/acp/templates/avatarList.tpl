{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/avatarL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.avatar.view{/lang}</h2>
	</div>
</div>

{if $deletedAvatarID}
	<p class="success">{lang}wcf.acp.avatar.delete.success{/lang}</p>	
{/if}

<form method="get" action="index.php">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.avatar.view.type{/lang}</legend>
				
				<div>
					<ul class="formOptions">
						<li><label><input type="radio" name="type" value="0" onclick="if (IS_SAFARI) window.setTimeout('document.forms[0].submit()', 100);" onfocus="window.setTimeout('document.forms[0].submit()', 100);" {if $type == 0}checked="checked" {/if}/> {lang}wcf.acp.avatar.view.defaultAvatars{/lang}</label></li>
						<li><label><input type="radio" name="type" value="1" onclick="if (IS_SAFARI) window.setTimeout('document.forms[0].submit()', 100);" onfocus="window.setTimeout('document.forms[0].submit()', 100);" {if $type == 1}checked="checked" {/if}/> {lang}wcf.acp.avatar.view.userAvatars{/lang}</label></li>
					</ul>
				</div>
			</fieldset>
		</div>
		
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="hidden" name="page" value="AvatarList" />
 		{@SID_INPUT_TAG}
	</div>
</form>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=AvatarList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&type=$type&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.avatar.canAddAvatar')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=AvatarAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.avatar.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarAddM.png" alt="" /> <span>{lang}wcf.acp.avatar.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>
	
{if $avatars|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.avatar.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnAvatarID{if $sortField == 'avatarID'} active{/if}" colspan="2"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=avatarID&amp;sortOrder={if $sortField == 'avatarID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.avatarID{/lang}{if $sortField == 'avatarID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatar"><div><span class="emptyHead">{lang}wcf.acp.avatar{/lang}</span></div></th>
					<th class="columnAvatarName{if $sortField == 'avatarName'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=avatarName&amp;sortOrder={if $sortField == 'avatarName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.name{/lang}{if $sortField == 'avatarName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatarExtension{if $sortField == 'avatarExtension'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=avatarExtension&amp;sortOrder={if $sortField == 'avatarExtension' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.extension{/lang}{if $sortField == 'avatarExtension'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatarWidth{if $sortField == 'width'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=width&amp;sortOrder={if $sortField == 'width' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.width{/lang}{if $sortField == 'width'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatarHeight{if $sortField == 'height'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=height&amp;sortOrder={if $sortField == 'height' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.height{/lang}{if $sortField == 'height'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					{if $type == 0}
						<th class="columnAvatarCategory{if $sortField == 'avatarCategoryTitle'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=avatarCategoryTitle&amp;sortOrder={if $sortField == 'avatarCategoryTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.category{/lang}{if $sortField == 'avatarCategoryTitle'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						<th class="columnAvatarGroup{if $sortField == 'groupName'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=groupName&amp;sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.group{/lang}{if $sortField == 'groupName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
						<th class="columnAvatarPoints{if $sortField == 'neededPoints'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=neededPoints&amp;sortOrder={if $sortField == 'neededPoints' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.neededPoints{/lang}{if $sortField == 'neededPoints'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					{else}
						<th class="columnAvatarUser{if $sortField == 'username'} active{/if}"><div><a href="index.php?page=AvatarList&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;type={@$type}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					{/if}
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$avatars item=avatar}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $type == 1}
							{if $this->user->getPermission('admin.avatar.canDisableAvatar')}
								{if !$avatar->disableAvatar}
									<a href="index.php?action=AvatarDisable&amp;userID={@$avatar->userID}&amp;pageNo={@$pageNo}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.avatar.disable{/lang}" /></a>
								{else}
									<a href="index.php?action=AvatarEnable&amp;userID={@$avatar->userID}&amp;pageNo={@$pageNo}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.avatar.enable{/lang}" /></a>
								{/if}
							{else}
								{if !$avatar->disableAvatar}
									<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.avatar.disable{/lang}" />
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.avatar.enable{/lang}" />
								{/if}
							{/if}
						{/if}
						{if $type == 0}
							{if $this->user->getPermission('admin.avatar.canEditAvatar')}
								<a href="index.php?form=AvatarEdit&amp;avatarID={@$avatar->avatarID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.avatar.edit{/lang}" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.avatar.edit{/lang}" />
							{/if}
						{/if}
						{if $this->user->getPermission('admin.avatar.canDeleteAvatar')}
							<a onclick="return confirm('{lang}wcf.acp.avatar.delete.sure{/lang}')" href="index.php?action=AvatarDelete&amp;avatarID={@$avatar->avatarID}&amp;type={@$type}&amp;pageNo={@$pageNo}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.avatar.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.avatar.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$avatar->avatarID|isset}{@$additionalButtons.$avatar->avatarID}{/if}
					</td>
					<td class="columnAvatarID columnID">{@$avatar->avatarID}</td>
					<td class="columnAvatar">
						{if $type == 0 && $this->user->getPermission('admin.avatar.canEditAvatar')}
							<a href="index.php?form=AvatarEdit&amp;avatarID={@$avatar->avatarID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-{@$avatar->avatarID}.{$avatar->avatarExtension}" alt="" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-{@$avatar->avatarID}.{$avatar->avatarExtension}" alt="" />
						{/if}
					</td>
					<td class="columnAvatarName columnText">{$avatar->avatarName}</td>
					<td class="columnAvatarExtension">{$avatar->avatarExtension}</td>
					<td class="columnAvatarWidth columnNumbers">{@$avatar->width}</td>
					<td class="columnAvatarHeight columnNumbers">{@$avatar->height}</td>
					{if $type == 0}
						<td class="columnAvatarCategory columnText">{lang}{$avatar->avatarCategoryTitle}{/lang}</td>
						<td class="columnAvatarGroup columnText">
							{if $this->user->getPermission('admin.user.canEditGroup')}
								<a href="index.php?form=GroupEdit&amp;groupID={@$avatar->groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}{$avatar->groupName}{/lang}</a>
							{else}
								{lang}{$avatar->groupName}{/lang}
							{/if}
						</td>
						<td class="columnAvatarPoints columnNumbers">{#$avatar->neededPoints}</td>
					{else}
						<td class="columnAvatarUser columnText">
							{if $this->user->getPermission('admin.user.canEditUser')}
								<a href="index.php?form=UserEdit&amp;userID={@$avatar->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$avatar->username}</a>
							{else}
								{$avatar->username}
							{/if}
						</td>
					{/if}
					
					{if $additionalColumns.$avatar->avatarID|isset}{@$additionalColumns.$avatar->avatarID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.avatar.canAddAvatar')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=AvatarAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.avatar.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarAddM.png" alt="" /> <span>{lang}wcf.acp.avatar.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.avatar.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
