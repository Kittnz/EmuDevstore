<div class="contentHeader">
	{@$pagesOutput}
	
	{capture assign=largeButtons}
		<div class="largeButtons">
			<ul>
				<li><a href="index.php?form=PMNew{@SID_ARG_2ND}"><img src="{icon}pmNewM.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.newMessage{/lang}</span></a></li>
				{if $messages|count && $pages > 1}<li><a href="index.php?action=PMMarkAll&amp;folderID={@$folderID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}pmMarkAllM.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.markAll{/lang}</span></a></li>{/if}
				{if $folderID == -3 && $messages|count}<li><a href="index.php?page=PM&amp;action=emptyRecycleBin&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.pm.emptyRecycleBin.sure{/lang}');"><img src="{icon}pmEmptyRecycleBinM.png{/icon}" alt="" /> <span>{lang}wcf.pm.button.emptyRecycleBin{/lang}</span></a></li>{/if}
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</div>
	{/capture}
	{@$largeButtons}
</div>

<div class="pmIndex">
	<div class="border pmFolders">
		<script type="text/javascript">
			//<![CDATA[
			var pmFolders = new Hash();
			//]]>
		</script>
		<div class="pageMenu">
			<ul>
				{foreach from=$folders item=folder}
					<li{if $folderID == $folder.folderID} class="active"{/if}>
						<a{if $folder.unreadMessages > 0} class="new"{/if} href="index.php?page=PMList&amp;folderID={@$folder.folderID}{@SID_ARG_2ND}">
							<img src="{icon}{$folder.icon}{/icon}" alt="" /> 
							<span>{$folder.folderName} ({if $folder.unreadMessages > 0}{#$folder.unreadMessages}/{/if}{#$folder.messages})
							{if $folder.folderID >= 0}
								<script type="text/javascript">
									//<![CDATA[
									pmFolders.set({@$folder.folderID}, '{$folder.folderName|encodeJS}');
									//]]>
								</script>
							{/if}
							</span>
						</a>
					</li>
				{/foreach}
			</ul>
		</div>
		<div class="container-3">
			<div class="pmUsage">
				<div class="pmUsageBar">
					<div{if $usage > 0.9} class="{if $usage > 0.99}storageError{else}storageWarning{/if}"{/if} style="width: {@$usage*100|floor}%;" title="{lang}wcf.pm.usage{/lang}"></div>
				</div>
				<p>{lang}wcf.pm.usage{/lang}</p>
			</div>
		</div>
		<div class="pageMenu">
			<ul>
				<li><a href="index.php?form=PMFolderEdit{@SID_ARG_2ND}"><img src="{icon}pmFolderM.png{/icon}" alt="" /> <span>{lang}wcf.pm.editFolders{/lang}</span></a></li>
				<li><a href="index.php?page=PMRuleList{@SID_ARG_2ND}"><img src="{icon}pmRuleM.png{/icon}" alt="" /> <span>{lang}wcf.pm.editRules{/lang}</span></a></li>
			</ul>
		</div>
	</div>
	
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/PMListEdit.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/StringUtil.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		// data array
		var pmData = new Hash();
		var folderID = {@$folderID};
		
		// language
		var language = new Object();
		language['wcf.global.button.mark']		= '{lang}wcf.global.button.mark{/lang}';
		language['wcf.global.button.unmark'] 		= '{lang}wcf.global.button.unmark{/lang}';
		language['wcf.global.button.delete'] 		= '{lang}wcf.global.button.delete{/lang}';
		language['wcf.pm.button.download'] 		= '{lang}wcf.pm.button.download{/lang}';
		language['wcf.pm.button.recover'] 		= '{lang}wcf.pm.button.recover{/lang}';
		language['wcf.pm.button.cancel'] 		= '{lang}wcf.pm.button.cancel{/lang}';
		language['wcf.pm.button.edit'] 			= '{lang}wcf.pm.button.edit{/lang}';
		language['wcf.pm.button.moveTo'] 		= '{lang}wcf.pm.button.moveTo{/lang}';
		language['wcf.pm.deleteMarked.sure'] 		= '{lang}wcf.pm.deleteMarked.sure{/lang}';
		language['wcf.pm.delete.sure'] 			= '{lang}wcf.pm.delete.sure{/lang}';
		language['wcf.pm.markedMessages'] 		= '{lang}wcf.pm.markedMessages{/lang}';
		language['wcf.pm.button.reply'] 		= '{lang}wcf.pm.button.reply{/lang}';
		language['wcf.pm.button.forward'] 		= '{lang}wcf.pm.button.forward{/lang}';
		language['wcf.pm.button.markAsUnread'] 		= '{lang}wcf.pm.button.markAsUnread{/lang}';
		language['wcf.pm.button.markAsRead'] 		= '{lang}wcf.pm.button.markAsRead{/lang}';
		language['wcf.pm.cancelMarked.sure'] 		= '{lang}wcf.pm.cancelMarked.sure{/lang}';
		language['wcf.pm.cancel.sure'] 			= '{lang}wcf.pm.cancel.sure{/lang}';
		
		onloadEvents.push(function() { pmListEdit = new PMListEdit(pmData, {@$markedMessages}, pmFolders); });
		//]]>
	</script>
	<div class="pmMessages">
		{if $messages|count}
		<div class="border">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th class="columnMark">
							<div>
								<label class="emptyHead"><input name="pmMarkAll" type="checkbox" /></label>
							</div>
						</th>
						{if $showIconColumn}
							<th class="columnIcon{if $sortField == 'isViewed'} active{/if}">
								<div>
									<p>
										<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=isViewed&amp;sortOrder={if $sortField == 'isViewed' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}"><img src="{icon}pmReadS.png{/icon}" alt="" />
											{if $sortField == 'isViewed'}
												<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
											{/if}
										</a>
									</p>
								</div>
							</th>
						{/if}
						{if $showViewedColumn}
							<th class="columnIcon{if $sortField == 'isViewedByAll'} active{/if}">
								<div>
									<p>
										<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=isViewedByAll&amp;sortOrder={if $sortField == 'isViewedByAll' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}"><img src="{icon}pmReadS.png{/icon}" alt="" />
											{if $sortField == 'isViewedByAll'}
												<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
											{/if}
										</a>
									</p>
								</div>
							</th>
						{/if}
						<th{if $sortField == 'subject'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=subject&amp;sortOrder={if $sortField == 'subject' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}">
										{lang}wcf.pm.subject{/lang} 
										{if $sortField == 'subject'}
											<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
						{if $showRecipientColumn}
							<th{if $sortField == 'recipients'} class="active"{/if}>
								<div>
									<p>
										<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=recipients&amp;sortOrder={if $sortField == 'recipients' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}">
											{lang}wcf.pm.recipient{/lang} 
											{if $sortField == 'recipients'}
												<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
											{/if}
										</a>
									</p>
								</div>
							</th>
						{else}
							<th{if $sortField == 'username'} class="active"{/if}>
								<div>
									<p>
										<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}">
											{lang}wcf.pm.author{/lang} 
											{if $sortField == 'username'}
												<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
											{/if}
										</a>
									</p>
								</div>
							</th>
						{/if}
						<th{if $sortField == 'time'} class="active"{/if}>
							<div>
								<p>
									<a href="index.php?page=PMList&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}">
										{lang}wcf.pm.date{/lang} 
										{if $sortField == 'time'}
											<img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />
										{/if}
									</a>
								</p>
							</div>
						</th>
					</tr>
				</thead>
				
				<tbody>
					{cycle values='container-1,container-2' name='className' print=false}
					{foreach from=$messages item=message}
						
						<tr class="{cycle name='className'}{if $pmID == $message->pmID} activeContainer{/if}" id="pmRow{@$message->pmID}">
							<td class="columnMark">
								<label><input name="pmMark" id="pmMark{@$message->pmID}" type="checkbox" value="{@$message->pmID}" /></label>
							</td>
							{if $showIconColumn}
								<td class="columnIcon">
									<img id="pmEdit{@$message->pmID}" src="{icon}pm{if !$message->isViewed()}Unread{else}Read{/if}{if $message->isReplied}Reply{/if}{if $message->isForwarded}Forward{/if}M.png{/icon}" alt="" {if !$message->isViewed()}title="{lang}wcf.pm.markAsReadByDoubleClick{/lang}" {/if}/>
								</td>
							{/if}
							{if $showViewedColumn}
								<td class="columnIcon">
									<img id="pmEdit{@$message->pmID}" src="{icon}pm{if !$message->isViewed()}Unread{else}Read{/if}M.png{/icon}" alt="" />
								</td>
							{/if}
							<td id="pmColumn{@$message->pmID}" class="columnTitle{if !$message->isViewed()} new{/if}" title="{$message->getMessagePreview()}">
								<p>
									{if MODULE_ATTACHMENT && $message->attachments}
										<img src="{icon}attachmentS.png{/icon}" alt="" title="" class="pmAttachmentIcon" />
									{/if}
									{if $pmID != $message->pmID}
										<a href="index.php?page=PMView&amp;pmID={@$message->pmID}&amp;folderID={@$folderID}&amp;pageNo={@$pageNo}&amp;sortField={@$sortField}&amp;sortOrder={@$sortOrder}&amp;filterBySender={@$filterBySender}{@SID_ARG_2ND}#pm{@$message->pmID}">{$message->subject}</a>
									{else}
										<span>{$message->subject}</span>
									{/if}
								</p>
							</td>
								
							{if $showRecipientColumn}
								{assign var="recipients" value=$message->getRecipients()}
								<td class="columnAuthor">
									<p>
										{if $recipients|count}<a href="index.php?page=User&amp;userID={@$recipients[0]->recipientID}{@SID_ARG_2ND}">{$recipients[0]->recipient}</a>{if $recipients|count > 1}, &hellip;{/if}{/if}
									</p>
								</td>
							{else}
								<td class="columnAuthor">
									<p>
										{if $message->userID}
											<a href="index.php?page=User&amp;userID={@$message->userID}{@SID_ARG_2ND}">{$message->username}</a>
										{elseif $message->username}
											{$message->username}
										{else}
											{lang}wcf.pm.author.system{/lang}
										{/if}
									</p>
								</td>
							{/if}
							<td class="columnDate smallFont">
								<p>{@$message->time|shorttime}
									{cycle name="className" print=false}
									<script type="text/javascript">
										//<![CDATA[
										pmData.set({@$message->pmID}, {
											'isMarked': {@$message->isMarked()},
											'isMoveable': {if $folderID >= 0}1{else}0{/if},
											'isRecoverable': {if $folderID == -3}1{else}0{/if},
											'isCancelable': {if $folderID == -1 && !$message->isViewedByOne}1{else}0{/if},
											'isEditable': {if $folderID == -2}1{else}0{/if},
											'isViewed': {if $message->isViewed()}1{else}0{/if},
											'canReply': {if $message->userID}1{else}0{/if},
											'className': '{cycle name="className"}{if $pmID == $message->pmID} activeContainer{/if}'
										});
										//]]>
									</script>
								</p>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		{else}
			<div class="border content">
				<div class="container-1">
					<p>{lang}wcf.pm.noMessages{/lang}</p>
				</div>
			</div>
		{/if}
		
		<div class="contentFooter">
			{@$pagesOutput} <div id="pmEditMarked" class="optionButtons"></div>
			{@$largeButtons}
		</div>
		
		<div class="border infoBox">
			<div class="container-1 infoBoxSorting">
				<div class="containerIcon"><img src="{icon}sortM.png{/icon}" alt="" /> </div>
				<div class="containerContent">
					<h3>{lang}wcf.pm.sorting{/lang}</h3>
					<form method="get" action="index.php">
						<div class="floatContainer">
							<input type="hidden" name="page" value="PMList" />
							<input type="hidden" name="folderID" value="{@$folderID}" />
							<input type="hidden" name="pageNo" value="{@$pageNo}" />
							
							<div class="floatedElement">
								<label for="sortField">{lang}wcf.pm.sortBy{/lang}</label>
								<select name="sortField" id="sortField">
									<option value="subject"{if $sortField == 'subject'} selected="selected"{/if}>{lang}wcf.pm.sortBy.subject{/lang}</option>
									<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wcf.pm.sortBy.time{/lang}</option>
									{if $showRecipientColumn}
										<option value="recipients"{if $sortField == 'recipients'} selected="selected"{/if}>{lang}wcf.pm.sortBy.recipients{/lang}</option>
									{else}
										<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.pm.sortBy.username{/lang}</option>
									{/if}
									{if $showIconColumn}<option value="isViewed"{if $sortField == 'isViewed'} selected="selected"{/if}>{lang}wcf.pm.sortBy.isViewed{/lang}</option>{/if}
									{if $showViewedColumn}<option value="isViewedByAll"{if $sortField == 'isViewedByAll'} selected="selected"{/if}>{lang}wcf.pm.sortBy.isViewedByAll{/lang}</option>{/if}
								</select>
								<select name="sortOrder">
									<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
									<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
								</select>
							</div>
							
							{if $availableSenders|count > 1}
								<div class="floatedElement">
									<label for="filterBySender">{lang}wcf.pm.filterBySender{/lang}</label>
									
									<select name="filterBySender" id="filterBySender">
										<option value=""></option>
										{htmlOptions options=$availableSenders selected=$filterBySender}
									</select>
								</div>
							{/if}
							
							<div class="floatedElement">
								<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
							</div>
		
							{@SID_INPUT_TAG}
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>