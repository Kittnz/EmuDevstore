{if $attachments[$messageID]|isset}
	{if $attachments.$messageID.images|count > 0}
		<fieldset class="attachmentPreview">
			<legend>{lang}wcf.attachment.images{/lang}</legend>
			<ul class="floatContainer">
				{foreach from=$attachments.$messageID.images item=attachment}
					<li class="floatedElement container-4" style="width: {ATTACHMENT_THUMBNAIL_WIDTH + 2}px; height: {ATTACHMENT_THUMBNAIL_HEIGHT + 2}px;">
						{if $attachment->thumbnailType}
							<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}" style="width: {ATTACHMENT_THUMBNAIL_WIDTH}px; height: {ATTACHMENT_THUMBNAIL_HEIGHT}px;" class="enlargable" title="{$attachment->attachmentName}"><img src="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}&amp;thumbnail=1{@SID_ARG_2ND}" alt="{$attachment->attachmentName}" style="width: {@$attachment->getThumbnailWidth()}px; height: {@$attachment->getThumbnailHeight()}px; margin-top: -{@$attachment->getThumbnailHeight()/2}px" /><span></span></a>
						{else}
							<div style="width: {ATTACHMENT_THUMBNAIL_WIDTH}px; height: {ATTACHMENT_THUMBNAIL_HEIGHT}px;"><img src="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}" alt="{$attachment->attachmentName}" title="{$attachment->attachmentName}" style="width: {@$attachment->getWidth()}px; height: {@$attachment->getHeight()}px; margin-top: -{@$attachment->getHeight()/2}px" /></div>
						{/if}
					</li>
				{/foreach}
			</ul>
		</fieldset>
	{/if}
	
	{if $attachments.$messageID.files|count > 0}
		<fieldset class="attachmentFile">
			<legend>{lang}wcf.attachment.files{/lang}</legend>
			<ul>
				{foreach from=$attachments.$messageID.files item=attachment}
					<li>
						<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}"><img src="{$attachment->getFileTypeIcon()}" alt="" /></a>
						
						<div>
							<a{if $attachment->getContentPreview()} title="{$attachment->getContentPreview()}"{/if} href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}">{$attachment->attachmentName}</a>
							<span class="smallFont">{lang}wcf.attachment.info{/lang}</span>
						</div>
					</li>
				{/foreach}
			</ul>
		</fieldset>
	{/if}
{/if}