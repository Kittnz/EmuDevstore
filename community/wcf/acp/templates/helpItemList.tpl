{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ItemListEditor.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	function init() {
		{if $helpItems|count > 0 && $this->user->getPermission('admin.help.canEditHelpItem')}
			new ItemListEditor('helpList', { itemTitleEdit: true, itemTitleEditURL: 'index.php?action=HelpItemRename&helpItemID=', tree: true, treeTag: 'ol' });
		{/if}
	}
	
	// when the dom is fully loaded, execute these scripts
	document.observe("dom:loaded", init);
	
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/helpItemL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.helpItem.view{/lang}</h2>
	</div>
</div>

{if $deletedHelpItemID}
	<p class="success">{lang}wcf.acp.helpItem.delete.success{/lang}</p>	
{/if}

{if $successfullSorting}
	<p class="success">{lang}wcf.acp.helpItem.sort.success{/lang}</p>	
{/if}

{if $this->user->getPermission('admin.help.canAddHelpItem')}
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=HelpItemAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/helpItemAddM.png" alt="" title="{lang}wcf.acp.menu.link.helpItem.add{/lang}" /> <span>{lang}wcf.acp.menu.link.helpItem.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{if $helpItems|count > 0}
	<form method="post" action="index.php?action=HelpItemSort">
		<div class="border content">
			<div class="container-1">
				<ol id="helpList" class="itemList">
					{foreach from=$helpItems item=child}
						{* define *}
						{assign var="helpItem" value=$child.helpItem}
						
						<li id="item_{@$helpItem->helpItemID}">
							
							<div class="buttons">
								{if $this->user->getPermission('admin.help.canEditHelpItem')}
									{if !$helpItem->isDisabled}
										<a href="index.php?action=HelpItemEnable&amp;helpItemID={@$helpItem->helpItemID}&amp;enable=0&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.helpItem.disable{/lang}" /></a>
									{else}
										<a href="index.php?action=HelpItemEnable&amp;helpItemID={@$helpItem->helpItemID}&amp;enable=1&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.helpItem.enable{/lang}" /></a>
									{/if}
									
									<a href="index.php?form=HelpItemEdit&amp;helpItemID={@$helpItem->helpItemID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.helpItem.edit{/lang}" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.helpItem.editDisabled{/lang}" />
								{/if}
								
								{if $this->user->getPermission('admin.help.canDeleteHelpItem')}
									<a onclick="return confirm('{lang}wcf.acp.helpItem.delete.sure{/lang}')" href="index.php?action=HelpItemDelete&amp;helpItemID={@$helpItem->helpItemID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.helpItem.delete{/lang}" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.helpItem.deleteDisabled{/lang}" />
								{/if}
								
								{if $child.additionalButtons|isset}{@$child.additionalButtons}{/if}
							</div>
						
							<h3 class="itemListTitle">
							
								{if $this->user->getPermission('admin.help.canEditHelpItem')}
									<select name="helpListPositions[{@$helpItem->helpItemID}][{@$helpItem->parentHelpItemID}]">
										{section name='positions' loop=$child.maxPosition}
											<option value="{@$positions+1}"{if $positions+1 == $child.position} selected="selected"{/if}>{@$positions+1}</option>
										{/section}
									</select>
								{/if}
								
								
								ID-{@$helpItem->helpItemID} <a href="index.php?form=HelpItemEdit&amp;helpItemID={@$helpItem->helpItemID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" class="title">{lang}wcf.help.item.{$helpItem->helpItem}{/lang}</a>
							</h3>
						
						{if $child.hasChildren}<ol id="parentItem_{@$helpItem->helpItemID}">{else}<ol id="parentItem_{@$helpItem->helpItemID}"></ol></li>{/if}
						{if $child.openParents > 0}{@"</ol></li>"|str_repeat:$child.openParents}{/if}
					{/foreach}
				</ol>
			</div>
		</div>
		
		{if $this->user->getPermission('admin.help.canEditHelpItem')}
			<div class="formSubmit">
				<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
				<input type="reset" id="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
				{@SID_INPUT_TAG}
			</div>
		{/if}
	</form>
{/if}


{include file='footer'}