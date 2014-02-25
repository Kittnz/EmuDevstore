{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.attachment{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	{include file='imageViewer'}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.attachment{/lang}</h3>
			<p>{lang}wcf.attachment.list.stats{/lang}</p>

			<form method="get" action="index.php">
				<fieldset>
					<legend>{lang}wcf.attachment.list.filter{/lang}</legend>

					<div class="formElement">
						<div class="formFieldLabel">
							<label for="filename">{lang}wcf.attachment.list.filter.filename.contains{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="filename" name="filename" value="{$filename}" />
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
										<a id="fromButton"><img src="{icon}datePickerOptionsM.png{/icon}" alt="" /></a>
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
										<a id="untilButton"><img src="{icon}datePickerOptionsM.png{/icon}" alt="" /></a>
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
					<input type="hidden" name="page" value="UserAttachmentList" />
					<input type="hidden" name="sortField" value="{@$sortField}" />
					<input type="hidden" name="sortOrder" value="{@$sortOrder}" />
					<input type="hidden" name="pageNo" value="{@$pageNo}" />
					{@SID_INPUT_TAG}
					<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
				</div>
			</form>
	
			{if $attachments|count}
				{assign var=parameterURLString value=''}
				{if $isImage == 1}{capture append=parameterURLString}&isImage=1{/capture}{/if}
				{if $showThumbnail == 1}{capture append=parameterURLString}&showThumbnail=1{/capture}{/if}
				{if $containerType}{capture append=parameterURLString}&containerType={@$containerType|rawurlencode}{/capture}{/if}
				{if $fileType}{capture append=parameterURLString}&fileType={@$fileType|rawurlencode}{/capture}{/if}
				{if $filename}{capture append=parameterURLString}&filename={@$filename|rawurlencode}{/capture}{/if}
				{if $greaterThan > 0}{capture append=parameterURLString}&greaterThan={@$greaterThan}{/capture}{/if}
				{if $fromYear}{capture append=parameterURLString}&fromYear={@$fromYear|rawurlencode}&fromMonth={@$fromMonth}&fromDay={@$fromDay}{/capture}{/if}
				{if $untilYear}{capture append=parameterURLString}&untilYear={@$untilYear|rawurlencode}&untilMonth={@$untilMonth}&untilDay={@$untilDay}{/capture}{/if}
				
				<div class="contentHeader">
					{pages print=true assign=pagesLinks link="index.php?page=UserAttachmentList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$parameterURLString"|concat:SID_ARG_2ND_NOT_ENCODED}
				</div>
				
				<div class="border titleBarPanel">
					<div class="containerHead">
						<h4>{lang}wcf.attachment.list.count{/lang}</h4>
					</div>
				</div>
				<div class="border borderMarginRemove">
					<table class="tableList">
						<thead>
							<tr class="tableHead">
								<th class="columnFileType{if $sortField == 'fileType'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=fileType&amp;sortOrder={if $sortField == 'fileType' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										<img src="{icon}attachmentS.png{/icon}" alt="{lang}wcf.attachment.fileType{/lang}" title="{lang}wcf.attachment.fileType{/lang}" />{if $sortField == 'fileType'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnContainerType{if $sortField == 'containerType'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=containerType&amp;sortOrder={if $sortField == 'containerType' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.containerType{/lang}{if $sortField == 'containerType'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnAttachmentName{if $sortField == 'attachmentName'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=attachmentName&amp;sortOrder={if $sortField == 'attachmentName' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.attachmentName{/lang}{if $sortField == 'attachmentName'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnUploadTime{if $sortField == 'uploadTime'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=uploadTime&amp;sortOrder={if $sortField == 'uploadTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.uploadTime{/lang}{if $sortField == 'uploadTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnAttachmentSize{if $sortField == 'attachmentSize'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=attachmentSize&amp;sortOrder={if $sortField == 'attachmentSize' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.attachmentSize{/lang}{if $sortField == 'attachmentSize'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnDownloads{if $sortField == 'downloads'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=downloads&amp;sortOrder={if $sortField == 'downloads' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.downloads{/lang}{if $sortField == 'downloads'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								<th class="columnLastDownloadTime{if $sortField == 'lastDownloadTime'} active{/if}">
									<div><a href="index.php?page=UserAttachmentList&amp;sortField=lastDownloadTime&amp;sortOrder={if $sortField == 'lastDownloadTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{$parameterURLString}{@SID_ARG_2ND}">
										{lang}wcf.attachment.lastDownloadTime{/lang}{if $sortField == 'lastDownloadTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								
								{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
							</tr>
						</thead>
						<tbody>
							{foreach from=$attachments item=attachment}
								<tr class="{cycle values='container-1,container-2'}">
									<td class="columnFileType columnIcon">
										{if $showThumbnail == 1 && $attachment->thumbnailType}
											<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}" class="enlargable"><img src="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;thumbnail=1&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}" style="width: {@$attachment->getThumbnailWidth()}px; height: {@$attachment->getThumbnailHeight()}px" alt="" /></a>
										{else}
											<img src="{@$attachment->getFileTypeIcon()}" alt="{$attachment->fileType}" title="{$attachment->fileType}" />
										{/if}
									</td>
									
									<td class="columnContainerType columnText">
										{if $attachment->getContainerURL()}
											<a href="{$attachment->getContainerURL()}">{lang}wcf.attachment.containerType.{$attachment->containerType}{/lang}</a>
										{else}
											{lang}wcf.attachment.containerType.{$attachment->containerType}{/lang}
										{/if}
									</td>
									
									<td class="columnAttachmentName columnText">
										<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}" title="{$attachment->attachmentName}">{$attachment->attachmentName|truncate:25}</a>
									</td>
									
									<td class="columnUploadTime columnText">
										{@$attachment->uploadTime|time}
									</td>
									
									<td class="columnAttachmentSize columnNumbers">
										{@$attachment->attachmentSize|filesize}
									</td>
									
									<td class="columnDownloads columnNumbers">
										{#$attachment->downloads}
									</td>
									
									<td class="columnLastDownloadTime columnText">
										{if $attachment->lastDownloadTime}{@$attachment->lastDownloadTime|time}{/if}
									</td>
									
									{if $additionalColumns.$attachment->attachmentID|isset}{@$additionalColumns.$attachment->attachmentID}{/if}
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				
				<div class="contentFooter">
					{@$pagesLinks}
				</div>
			{else}
				<p>{lang}wcf.attachment.list.noAttachments{/lang}</p>	
			{/if}
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>