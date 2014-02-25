{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/SmileyListEdit.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	
	document.observe("dom:loaded", function() {
		var smileyList = $('smileyList');
		if (smileyList) {
			smileyList.addClassName('dragable');
			var startValue = {@$itemsPerPage} * ({@$pageNo} - 1) + 1;
			var smileyRow = null;
			
			Sortable.create(smileyList, { 
				tag: 'tr',
				onChange: function(row) {
					smileyRow = row;	
				},
				onUpdate: function(list) {
					var rows = list.select('tr');
					var showOrder = 0;
					rows.each(function(row, i) {
						row.className = 'container-' + (i % 2 == 0 ? '1' : '2') + (row.hasClassName('marked') ? ' marked' : '');
						showOrder = row.select('.columnNumbers')[0];
						if (showOrder.innerHTML != (i + startValue)) {
							showOrder.update(i + startValue);
							new Ajax.Request('index.php?action=SmileySort&smileyID=' + row.identify().gsub('smileyRow_', '') + '&showOrder=' + showOrder.innerHTML + SID_ARG_2ND);
						}
					});	
				}
			});
		}	
	});
	
	// data array
	var smileyData = new Hash();
	
	// language
	var language = new Object();
	language['wcf.global.button.mark']		= '{lang}wcf.global.button.mark{/lang}';
	language['wcf.global.button.unmark']		= '{lang}wcf.global.button.unmark{/lang}';
	language['wcf.global.button.delete']		= '{lang}wcf.global.button.delete{/lang}';
	language['wcf.acp.smiley.button.moveTo']	= '{lang}wcf.acp.smiley.button.moveTo{/lang}';
	language['wcf.acp.smiley.delete.sure']		= '{lang}wcf.acp.smiley.delete.sure{/lang}';
	language['wcf.acp.smiley.deleteMarked.sure']	= '{lang}wcf.acp.smiley.deleteMarked.sure{/lang}';
	language['wcf.acp.smiley.markedSmileys']	= '{lang}wcf.acp.smiley.markedSmileys{/lang}';
	
	// permissions
	var permissions = new Object();
	permissions['canEditSmiley'] = {if $this->user->getPermission('admin.smiley.canEditSmiley')}1{else}0{/if};
	permissions['canDeleteSmiley'] = {if $this->user->getPermission('admin.smiley.canDeleteSmiley')}1{else}0{/if};
	
	// categories
	var categories = new Hash();
	categories.set(0, '{lang}wcf.smiley.category.default{/lang}');
	{foreach from=$smileyCategories item=smileyCategory}
		categories.set({@$smileyCategory->smileyCategoryID}, '{lang}{$smileyCategory->title}{/lang}');
	{/foreach}
	
	onloadEvents.push(function() { smileyListEdit = new SmileyListEdit(smileyData, {@$markedSmileys}, categories); });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/smileyL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.smiley.view{/lang}</h2>
		<p>{lang}wcf.acp.smiley.view.count{/lang}</p>
	</div>
</div>

{if $deletedSmileyID}
	<p class="success">{lang}wcf.acp.smiley.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=SmileyList&smileyCategoryID=$smileyCategoryID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.smiley.canAddSmiley')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=SmileyAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyAddM.png" alt="" title="{lang}wcf.acp.smiley.add{/lang}" /> <span>{lang}wcf.acp.smiley.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>


<div class="subTabMenu">
	<div class="containerHead">
		<ul>
			<li{if $smileyCategoryID == 0} class="activeSubTabMenu"{/if}><a href="index.php?page=SmileyList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><span>{lang}wcf.smiley.category.default{/lang} ({#$defaultSmileys})</span></a></li>
			{foreach from=$smileyCategories item=smileyCategory}
				<li{if $smileyCategoryID == $smileyCategory->smileyCategoryID} class="activeSubTabMenu"{/if}><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><span>{lang}{$smileyCategory->title}{/lang} ({#$smileyCategory->smileys})</span></a></li>
			{/foreach}
		</ul>
	</div>
</div>
{if $smilies|count}
	<div class="border">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnMark"><div><label class="emptyHead"><input name="smileyMarkAll" type="checkbox" /></label></div></th>
					<th class="columnSmileyID{if $sortField == 'smileyID'} active{/if}" colspan="2"><div><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategoryID}&amp;pageNo={@$pageNo}&amp;sortField=smileyID&amp;sortOrder={if $sortField == 'smileyID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.smileyID{/lang}{if $sortField == 'smileyID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmiley"><div><span class="emptyHead">{lang}wcf.acp.smiley{/lang}</span></div></th>
					<th class="columnSmileyPath{if $sortField == 'smileyPath'} active{/if}"><div><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategoryID}&amp;pageNo={@$pageNo}&amp;sortField=smileyPath&amp;sortOrder={if $sortField == 'smileyPath' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.path{/lang}{if $sortField == 'smileyPath'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmileyTitle{if $sortField == 'smileyTitle'} active{/if}"><div><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategoryID}&amp;pageNo={@$pageNo}&amp;sortField=smileyTitle&amp;sortOrder={if $sortField == 'smileyTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.title{/lang}{if $sortField == 'smileyTitle'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmileyCode{if $sortField == 'smileyCode'} active{/if}"><div><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategoryID}&amp;pageNo={@$pageNo}&amp;sortField=smileyCode&amp;sortOrder={if $sortField == 'smileyCode' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.code{/lang}{if $sortField == 'smileyCode'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmileyOrder{if $sortField == 'showOrder'} active{/if}"><div><a href="index.php?page=SmileyList&amp;smileyCategoryID={@$smileyCategoryID}&amp;pageNo={@$pageNo}&amp;sortField=showOrder&amp;sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.showOrder{/lang}{if $sortField == 'showOrder'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody id="smileyList">
			{foreach from=$smilies item=smiley}
				<tr class="{cycle values="container-1,container-2"}" id="smileyRow_{@$smiley->smileyID}">
					<td class="columnMark"><input name="smileyMark" id="smileyMark{@$smiley->smileyID}" type="checkbox" value="{@$smiley->smileyID}" /></td>
					<td class="columnIcon">
						<script type="text/javascript">
							//<![CDATA[
							smileyData.set({@$smiley->smileyID}, {
								'isMarked': {@$smiley->isMarked()}
							});
							//]]>
						</script>
						
						{if $this->user->getPermission('admin.smiley.canEditSmiley')}
							<a href="index.php?form=SmileyEdit&amp;smileyID={@$smiley->smileyID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.smiley.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.smiley.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.smiley.canDeleteSmiley')}
							<a onclick="return confirm('{lang}wcf.acp.smiley.delete.sure{/lang}')" href="index.php?action=SmileyDelete&amp;smileyID={@$smiley->smileyID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.smiley.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.smiley.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$smiley->smileyID|isset}{@$additionalButtons.$smiley->smileyID}{/if}
					</td>
					<td class="columnSmileyID columnID">{@$smiley->smileyID}</td>
					<td class="columnSmiley">
						{if $this->user->getPermission('admin.smiley.canEditSmiley')}
							<a href="index.php?form=SmileyEdit&amp;smileyID={@$smiley->smileyID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}{$smiley->smileyPath}" alt="{$smiley->smileyCode}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}{$smiley->smileyPath}" alt="{$smiley->smileyCode}" />
						{/if}
					</td>
					<td class="columnSmileyPath columnText">{$smiley->smileyPath}</td>
					<td class="columnSmileyTitle columnText">{$smiley->smileyTitle}</td>
					<td class="columnSmileyCode">{$smiley->smileyCode}</td>
					<td class="columnSmileyOrder columnNumbers">{@$smiley->showOrder}</td>
					
					{if $additionalColumns.$smiley->smileyID|isset}{@$additionalColumns.$smiley->smileyID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<div class="border content"><div class="container-1">{lang}wcf.acp.smiley.view.count{/lang}</div></div>
{/if}

<div class="contentFooter">
	{@$pagesLinks}
	<div id="smileyEditMarked" class="optionButtons"></div>
	
	{if $this->user->getPermission('admin.smiley.canAddSmiley')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=SmileyAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyAddM.png" alt="" title="{lang}wcf.acp.smiley.add{/lang}" /> <span>{lang}wcf.acp.smiley.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{include file='footer'}
