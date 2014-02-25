{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/attachmentL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.attachment.view{/lang}</h2>
		<p>{lang}wcf.attachment.list.stats{/lang}</p>
	</div>
</div>

{if $deletedAttachmentID}
	<p class="success">{lang}wcf.acp.attachment.delete.success{/lang}</p>	
{/if}

<form method="get" action="index.php">
	<fieldset>
		<legend>{lang}wcf.attachment.list.filter{/lang}</legend>

		<div class="formElement">
			<div class="formFieldLabel">
				<label for="username">{lang}wcf.user.username{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" id="username" name="username" value="{$username}" />
				<script type="text/javascript">
					//<![CDATA[
					suggestion.enableMultiple(false);
					suggestion.init('username');
					//]]>
				</script>
			</div>
		</div>

		<div class="formElement">
			<div class="formFieldLabel">
				<label for="filename">{lang}wcf.attachment.list.filter.filename.contains{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" id="filename" name="filename" value="{$filename}" />
			</div>
		</div>
		
		<div class="formGroup">
			<div class="formGroupLabel">
				<label for="fromDay">{lang}wcf.attachment.list.filter.period{/lang}</label>
			</div>
			
			<div class="formGroupField">
				<fieldset id="searchPeriod">
				
					<legend><label for="fromDay">{lang}wcf.attachment.list.filter.period{/lang}</label></legend>
					
					<div class="floatedElement">
						<div class="floatedElement">
							<p> {lang}wcf.attachment.list.filter.period.start{/lang}</p>
						</div>
						
						<div class="floatedElement">
							<label for="fromDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$fromDay id=fromDay name=fromDay}
						</div>
						
						<div class="floatedElement">
							<label for="fromMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$fromMonth id=fromMonth name=fromMonth}
						</div>
						
						<div class="floatedElement">
							<label for="fromYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="fromYear" class="inputText fourDigitInput" type="text" name="fromYear" value="{@$fromYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="fromButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="fromCalendar" class="inlineCalendar"></div>
						</div>
					</div>
					
					<div class="floatedElement">
						<div class="floatedElement">
							<p> {lang}wcf.attachment.list.filter.period.end{/lang}</p>
						</div>
						
						<div class="floatedElement">
							<label for="untilDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$untilDay id=untilDay name=untilDay}
						</div>
						
						<div class="floatedElement">
							<label for="untilMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$untilMonth id=untilMonth name=untilMonth}
						</div>
						
						<div class="floatedElement">
							<label for="untilYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="untilYear" class="inputText fourDigitInput" type="text" name="untilYear" value="{@$untilYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="untilButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="untilCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('from');
								calendar.init('until');
								//]]>
							</script>
						</div>
					</div>
					
				</fieldset>
			</div>
		</div>
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="greaterThan">{lang}wcf.attachment.list.filter.greaterThan{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" id="greaterThan" name="greaterThan" value="{@$greaterThan}" />
			</div>
		</div>

		{if $availableContainerTypes|count > 1}
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="containerType">{lang}wcf.attachment.containerType{/lang}</label>
				</div>
				<div class="formField">
					<select name="containerType" id="containerType">
						<option value=""></option>
						{foreach from=$availableContainerTypes item=availableContainerType}
							<option value="{$availableContainerType.containerType}"{if $availableContainerType.containerType == $containerType} selected="selected"{/if}>{lang}wcf.attachment.containerType.{$availableContainerType.containerType}{/lang}</option>
						{/foreach}
					</select>
				</div>
			</div>
		{/if}
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="fileType">{lang}wcf.attachment.fileType{/lang}</label>
			</div>
			<div class="formField">
				{if $availableFileTypes|count > 1}
					<select name="fileType" id="fileType">
						<option value=""></option>
						{foreach from=$availableFileTypes item=availableFileType}
							<option value="{$availableFileType.fileType}"{if $availableFileType.fileType == $fileType} selected="selected"{/if}>{$availableFileType.fileType}</option>
						{/foreach}
					</select>
				{/if}
				<label><input type="checkbox" name="isImage" value="1"{if $isImage == 1} checked="checked"{/if}/> {lang}wcf.attachment.list.filter.isImage{/lang}</label>
				<label><input type="checkbox" name="showThumbnail" value="1"{if $showThumbnail == 1} checked="checked"{/if}/> {lang}wcf.attachment.list.filter.showThumbnail{/lang}</label>
			</div>
		</div>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}

	</fieldset>
	
	<div class="formSubmit">
		<input type="hidden" name="page" value="AdminAttachmentList" />
		<input type="hidden" name="sortField" value="{@$sortField}" />
		<input type="hidden" name="sortOrder" value="{@$sortOrder}" />
		<input type="hidden" name="pageNo" value="{@$pageNo}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
	</div>
</form>

{assign var=parameterURLString value=''}
{if $isImage == 1}{capture append=parameterURLString}&isImage=1{/capture}{/if}
{if $showThumbnail == 1}{capture append=parameterURLString}&showThumbnail=1{/capture}{/if}
{if $containerType}{capture append=parameterURLString}&containerType={@$containerType|rawurlencode}{/capture}{/if}
{if $fileType}{capture append=parameterURLString}&fileType={@$fileType|rawurlencode}{/capture}{/if}
{if $username}{capture append=parameterURLString}&username={@$username|rawurlencode}{/capture}{/if}
{if $filename}{capture append=parameterURLString}&filename={@$filename|rawurlencode}{/capture}{/if}
{if $greaterThan > 0}{capture append=parameterURLString}&greaterThan={@$greaterThan}{/capture}{/if}
{if $fromYear}{capture append=parameterURLString}&fromYear={@$fromYear|rawurlencode}&fromMonth={@$fromMonth}&fromDay={@$fromDay}{/capture}{/if}
{if $untilYear}{capture append=parameterURLString}&untilYear={@$untilYear|rawurlencode}&untilMonth={@$untilMonth}&untilDay={@$untilDay}{/capture}{/if}
{capture assign=url}index.php?page=AdminAttachmentList&sortField={@$sortField}&sortOrder={@$sortOrder}&pageNo={@$pageNo}&isImage={@$isImage}&showThumbnail={@$showThumbnail}&containerType={@$containerType}&fileType={@$fileType}&username={@$username}&packageID={@PACKAGE_ID}{/capture}
{assign var=url value=$url|rawurlencode}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=AdminAttachmentList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$parameterURLString&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $additionalLargeButtons|isset}
		<div class="largeButtons">
			<ul>
				{@$additionalLargeButtons}
			</ul>
		</div>
	{/if}
</div>

{if $attachments|count}
	<div class="border titleBarPanel">
		<div class="containerHead">
			<h4>{lang}wcf.attachment.list.count{/lang}</h4>
		</div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnAttachmentID{if $sortField == 'attachmentID'} active{/if}" colspan="2">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=attachmentID&amp;sortOrder={if $sortField == 'attachmentID' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.attachmentID{/lang}{if $sortField == 'attachmentID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnFileType{if $sortField == 'fileType'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=fileType&amp;sortOrder={if $sortField == 'fileType' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							<img src="{@RELATIVE_WCF_DIR}icon/attachmentS.png" alt="{lang}wcf.attachment.fileType{/lang}" title="{lang}wcf.attachment.fileType{/lang}" />{if $sortField == 'fileType'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnContainerType{if $sortField == 'containerType'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=containerType&amp;sortOrder={if $sortField == 'containerType' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.containerType{/lang}{if $sortField == 'containerType'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnAttachmentName{if $sortField == 'attachmentName'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=attachmentName&amp;sortOrder={if $sortField == 'attachmentName' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.attachmentName{/lang}{if $sortField == 'attachmentName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnUserID{if $sortField == 'userID'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=userID&amp;sortOrder={if $sortField == 'userID' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.user.username{/lang}{if $sortField == 'userID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnUploadTime{if $sortField == 'uploadTime'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=uploadTime&amp;sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.uploadTime{/lang}{if $sortField == 'uploadTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnAttachmentSize{if $sortField == 'attachmentSize'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=attachmentSize&amp;sortOrder={if $sortField == 'attachmentSize' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.attachmentSize{/lang}{if $sortField == 'attachmentSize'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnDownloads{if $sortField == 'downloads'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=downloads&amp;sortOrder={if $sortField == 'downloads' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.downloads{/lang}{if $sortField == 'downloads'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					<th class="columnLastDownloadTime{if $sortField == 'lastDownloadTime'} active{/if}">
						<div><a href="index.php?page=AdminAttachmentList&amp;sortField=lastDownloadTime&amp;sortOrder={if $sortField == 'lastDownloadTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
							{lang}wcf.attachment.lastDownloadTime{/lang}{if $sortField == 'lastDownloadTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}
						</a></div>
					</th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$attachments item=attachment}
					<tr class="{cycle values='container-1,container-2'}">
						<td class="columnIcon">
							{if $this->user->getPermission('admin.user.canEditUser')}
								<a onclick="return confirm('{lang}wcf.acp.attachment.delete.sure{/lang}')" href="index.php?action=AttachmentDelete&amp;attachmentID={@$attachment->attachmentID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}&url={$url}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.attachment.delete{/lang}" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.attachment.delete{/lang}" />
							{/if}
							
							{if $additionalButtons.$attachment->attachmentID|isset}{@$additionalButtons.$attachment->attachmentID}{/if}
						</td>
						<td class="columnAttachmentID columnID">{@$attachment->attachmentID}</td>
					
						<td class="columnFileType columnIcon">
							{if $showThumbnail == 1 && $attachment->thumbnailType}
								<img src="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;thumbnail=1{@SID_ARG_2ND}" style="width: {@$attachment->getThumbnailWidth()}px; height: {@$attachment->getThumbnailHeight()}px" alt="" />
							{else}
								<img src="{@$attachment->getFileTypeIcon()}" alt="{$attachment->fileType}" title="{$attachment->fileType}" />
							{/if}
						</td>
						
						<td class="columnContainerType columnText smallFont">
							{if $attachment->getContainerURL()}
								<a href="../{SID_ARG_2ND_NOT_ENCODED|str_replace:'':$attachment->getContainerURL()}">{lang}wcf.attachment.containerType.{$attachment->containerType}{/lang}</a>
							{else}
								{lang}wcf.attachment.containerType.{$attachment->containerType}{/lang}
							{/if}
						</td>
						
						<td class="columnAttachmentName columnText smallFont" title="{$attachment->attachmentName}">
							<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}{@SID_ARG_2ND}">{$attachment->attachmentName|truncate:25}</a>
						</td>
						
						<td class="columnUserID columnText smallFont">
							{if $attachment->userID && $this->user->getPermission('admin.user.canEditUser')}
								<a href="index.php?form=UserEdit&amp;userID={@$attachment->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$attachment->username}</a>
							{else}
								{$attachment->username}
							{/if}
						</td>
						
						<td class="columnUploadTime columnText smallFont">
							{@$attachment->uploadTime|shorttime}
						</td>
						
						<td class="columnAttachmentSize columnNumbers smallFont">
							{@$attachment->attachmentSize|filesize}
						</td>
						
						<td class="columnDownloads columnNumbers smallFont">
							{#$attachment->downloads}
						</td>
						
						<td class="columnLastDownloadTime columnText smallFont">
							{if $attachment->lastDownloadTime}{@$attachment->lastDownloadTime|shorttime}{/if}
						</td>
						
						{if $additionalColumns.$attachment->attachmentID|isset}{@$additionalColumns.$attachment->attachmentID}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $additionalLargeButtons|isset}
			<div class="largeButtons">
				<ul>
					{@$additionalLargeButtons}
				</ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.attachment.list.noAttachments{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
