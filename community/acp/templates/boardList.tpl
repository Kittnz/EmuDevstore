{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ItemListEditor.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	function init() {
		{if $boards|count > 0 && $boards|count < 100 && $this->user->getPermission('admin.board.canEditBoard')}
			new ItemListEditor('boardList', { itemTitleEdit: true, itemTitleEditURL: 'index.php?action=BoardRename&boardID=', tree: true, treeTag: 'ol' });
		{/if}
	}
	
	// when the dom is fully loaded, execute these scripts
	document.observe("dom:loaded", init);	
	
	function openCategory(boardID) {
		var element = $('parentItem_' + boardID);
		var close = 0;
		if (element.visible()) {
			// close list
			Effect.BlindUp(element, { duration: 0.2 });
			close = 1;
			var image = $('category' + boardID + 'Image');
			if (image) {
				image.src = image.src.replace(/minus/, 'plus');
			}
		}
		else {
			// open list
			Effect.BlindDown(element, { duration: 0.2 });
			var image = $('category' + boardID + 'Image');
			if (image) {
				image.src = image.src.replace(/plus/, 'minus');
			}
		}
		
		// save status
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=BoardCategoryClose' + SID_ARG_2ND, 'boardID=' + encodeURIComponent(boardID) + '&close=' + encodeURIComponent(close));
	}
	
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/boardL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.board.list{/lang}</h2>
	</div>
</div>

{if $deletedBoardID}
	<p class="success">{lang}wbb.acp.board.delete.success{/lang}</p>	
{/if}

{if $successfulSorting}
	<p class="success">{lang}wbb.acp.board.sort.success{/lang}</p>	
{/if}

{if $this->user->getPermission('admin.board.canAddBoard')}
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=BoardAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wbb.acp.board.add{/lang}"><img src="{@RELATIVE_WBB_DIR}icon/boardAddM.png" alt="" /> <span>{lang}wbb.acp.board.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{if $boards|count > 0}
	{if $this->user->getPermission('admin.board.canEditBoard')}
	<form method="post" action="index.php?action=BoardSort">
	{/if}
		<div class="border content">
			<div class="container-1">
				<ol class="itemList" id="boardList">
					{foreach from=$boards item=child}
						{* define *}
						{assign var="board" value=$child.board}
						
						<li id="item_{@$board->boardID}" class="deletable">
							<div class="buttons">
								{if $this->user->getPermission('admin.board.canEditBoard') || $this->user->getPermission('admin.board.canEditPermissions') || $this->user->getPermission('admin.board.canEditModerators')}
									<a href="index.php?form=BoardEdit&amp;boardID={@$board->boardID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wbb.acp.board.edit{/lang}" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wbb.acp.board.edit{/lang}" />
								{/if}
								{if $this->user->getPermission('admin.board.canAddBoard')}
									<a href="index.php?form=BoardAdd&amp;parentID={@$board->boardID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wbb.acp.board.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/addS.png" alt="" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/addDisabledS.png" alt="" title="{lang}wbb.acp.board.add{/lang}" />
								{/if}								
								{if $this->user->getPermission('admin.board.canDeleteBoard')}
									<a href="index.php?action=BoardDelete&amp;boardID={@$board->boardID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wbb.acp.board.delete{/lang}" class="deleteButton"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" longdesc="{lang}wbb.acp.board.delete.sure{/lang}"  /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wbb.acp.board.delete{/lang}" />
								{/if}
								
								{if $child.additionalButtons|isset}{@$child.additionalButtons}{/if}
							</div>
							
							<h3 class="itemListTitle{if $board->isCategory()} itemListCategory{/if}">
								{if $board->isCategory()}
									{if $child.open}
										<a onclick="openCategory({@$board->boardID})"><img id="category{@$board->boardID}Image" src="{@RELATIVE_WCF_DIR}icon/minusS.png" alt="" title="" /></a>
									{else}
										<a onclick="openCategory({@$board->boardID})"><img id="category{@$board->boardID}Image" src="{@RELATIVE_WCF_DIR}icon/plusS.png" alt="" title="" /></a>
									{/if}
								{else}
									<img src="{@RELATIVE_WBB_DIR}icon/{if $board->isBoard()}board{else}boardRedirect{/if}S.png" alt="" title="{lang}wbb.acp.board.boardType.{@$board->boardType}{/lang}" />
								{/if}
								
								{if $this->user->getPermission('admin.board.canEditBoard')}
									<select name="boardListPositions[{@$board->boardID}][{@$child.parentID}]">
										{section name='positions' loop=$child.maxPosition}
											<option value="{@$positions+1}"{if $positions+1 == $child.position} selected="selected"{/if}>{@$positions+1}</option>
										{/section}
									</select>
								{/if}
								
								ID-{@$board->boardID} <a href="index.php?form=BoardEdit&amp;boardID={@$board->boardID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" class="title">{lang}{$board->title}{/lang}</a>
							</h3>
						
						{if $child.hasChildren}<ol id="parentItem_{@$board->boardID}"{if !$child.open} style="display: none"{/if}>{else}<ol id="parentItem_{@$board->boardID}"></ol></li>{/if}
						{if $child.openParents > 0}{@"</ol></li>"|str_repeat:$child.openParents}{/if}
					{/foreach}
				</ol>
			</div>
		</div>
	{if $this->user->getPermission('admin.board.canEditBoard')}
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" id="reset" value="{lang}wcf.global.button.reset{/lang}" />
			<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	 		{@SID_INPUT_TAG}
	 	</div>
	</form>
	{/if}
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wbb.acp.board.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
