{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/moderatorPermissionsL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.board.{@$type}.permissions.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wbb.acp.board.{@$type}.permissions.edit.success{/lang}</p>	
{/if}

<script type="text/javascript" src="{@RELATIVE_WBB_DIR}acp/js/PermissionBoardList.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// define board structure
	var boardStructure = new Object();
	{foreach from=$boardStructure key=parentID item=children}
		boardStructure[{@$parentID}] = new Array();
		{foreach from=$children item=boardID}
			boardStructure[{@$parentID}][boardStructure[{@$parentID}].length] = {@$boardID};
		{/foreach}
	{/foreach}
	
	// define global permissions
	{if !$groupID|empty}
		var globalPermissions = new Object();
		{foreach from=$globalPermissions key=name item=value}
			globalPermissions['{@$name}'] = {@$value};
		{/foreach}
	{else}
		var globalPermissions = new Object();
		{foreach from=$globalPermissions key=boardID item=permissions}
			globalPermissions[{@$boardID}] = new Object();
			{foreach from=$permissions key=name item=value}
				globalPermissions[{@$boardID}]['{@$name}'] = {@$value};
			{/foreach}
		{/foreach}
	{/if}
	
	// define board permissions
	var boardPermissions = new Object();
	{foreach from=$boardPermissions key=boardID item=permissions}
		boardPermissions[{@$boardID}] = new Object();
		{foreach from=$permissions key=name item=value}
			boardPermissions[{@$boardID}]['{@$name}'] = {@$value};
		{/foreach}
	{/foreach}
	
	onloadEvents.push(function() {
		// moderators
		var list = new PermissionBoardList(boardStructure, globalPermissions, boardPermissions, '{if !$groupID|empty}group{else}user{/if}');
		{if $permissionName}list.setActivePermission('{$permissionName}');{/if}
		
		document.forms[0].onsubmit = function() { list.submit(this); };
	});
	
	function openCategory(boardID) {
		var element = document.getElementById('category'+boardID);
		var close = 0;
		if (element.style.display == 'none') {
			// open list
			element.style.display = '';
			var image = document.getElementById('category'+boardID+'Image');
			if (image) {
				image.src = image.src.replace(/plus/, 'minus');
			}
		}
		else {
			// close list
			element.style.display = 'none';
			close = 1;
			var image = document.getElementById('category'+boardID+'Image');
			if (image) {
				image.src = image.src.replace(/minus/, 'plus');
			}
		}
		
		// save status
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=BoardCategoryClose'+SID_ARG_2ND, 'boardID='+encodeURIComponent(boardID)+'&close='+encodeURIComponent(close));
	}
	//]]>
</script>

<form method="post" action="index.php?form={@$type|ucfirst}PermissionsEdit">
	<div class="border content">
		<div class="container-1">
			<div class="formElement">
				<p class="formFieldLabel">
					{if !$groupID|empty}<label for="groupID">{lang}wcf.acp.group.groupName{/lang}</label>{else}{lang}wcf.user.username{/lang}{/if}
					<img src="{@RELATIVE_WCF_DIR}icon/{if !$groupID|empty}group{else}user{/if}S.png" alt="" />
				</p>
				<p class="formField">
					{if !$groupID|empty}
						<select id="groupID" name="groupID" onchange="document.location.href=fixURL('index.php?form=GroupPermissionsEdit&amp;groupID='+this.options[this.selectedIndex].value+'&amp;permissionName='+document.getElementById('permissionName').options[document.getElementById('permissionName').selectedIndex].value+'&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}')">
							{htmlOptions options=$groups selected=$groupID}
						</select>
					{else}
						<a href="index.php?form=UserEdit&amp;userID={@$userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$user->username}</a>
					{/if}
				</p>
			</div>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="permissionName">{lang}wbb.acp.board.permission{/lang}</label>
				</div>
				<div class="formField">
					<select name="permissionName" id="permissionName">
						<option value=""></option>
						<option value="fullControl"{if $permissionName == 'fullControl'} selected="selected"{/if}>{lang}wbb.acp.board.permissions.fullControl{/lang}</option>
						{foreach from=$availablePermissions item=$availablePermission}
							<option value="{@$availablePermission}"{if $permissionName == $availablePermission} selected="selected"{/if}>{lang}wbb.acp.board.permissions.{@$availablePermission}{/lang}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	
	<div id="boardList" style="display:none">
		<div class="accessRightsHeader">
			<span class="deny">{lang}wbb.acp.board.permissions.deny{/lang}</span>
			<span class="allow">{lang}wbb.acp.board.permissions.allow{/lang}</span>
		</div>
		
		<div class="border content">
			<div class="container-1">
				<ol class="itemList">
					{foreach from=$boards item=child}
						{* define *}
						{assign var="board" value=$child.board}
						
						<li>
							<h3 class="itemListTitle{if $board->isCategory()} itemListCategory{/if}">
								<label class="deny"><input type="checkbox" id="deny{@$board->boardID}" value="" /></label>
								<label class="allow"><input type="checkbox" id="allow{@$board->boardID}" value="" /></label>
								
								{if $board->isCategory()}
									{if $child.open}
										<a onclick="openCategory({@$board->boardID})"><img id="category{@$board->boardID}Image" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" title="" /></a>
									{else}
										<a onclick="openCategory({@$board->boardID})"><img id="category{@$board->boardID}Image" src="{@RELATIVE_WCF_DIR}icon/plusS.png" alt="" title="" /></a>
									{/if}
								{else}
									<img src="{@RELATIVE_WBB_DIR}icon/{if $board->isBoard()}board{else}boardRedirect{/if}S.png" alt="" title="{lang}wbb.acp.board.boardType.{@$board->boardType}{/lang}" />
								{/if}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" id="status{@$board->boardID}" />
								ID-{@$board->boardID} <a href="index.php?form=BoardEdit&amp;boardID={@$board->boardID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}{$board->title}{/lang}</a>
							</h3>
						
						{if $child.hasChildren}<ol id="category{@$board->boardID}"{if !$child.open} style="display: none"{/if}>{else}</li>{/if}
						{if $child.openParents > 0}{@"</ol></li>"|str_repeat:$child.openParents}{/if}
					{/foreach}
				</ol>
			</div>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if !$userID|empty}<input type="hidden" name="userID" value="{@$userID}" />{/if}
 	</div>
</form>

{include file='footer'}