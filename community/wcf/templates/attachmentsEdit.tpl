<div id="attachmentsEdit">
	<fieldset class="noJavaScript">
		<legend class="noJavaScript">{lang}wcf.attachment.attachments{/lang}</legend>

		{if $attachments|isset && $attachments|count > 0}
			<fieldset>
				<legend>{lang}wcf.attachment.upload.files{/lang}</legend>
				<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ItemListEditor.class.js"></script>
				<script type="text/javascript">
					//<![CDATA[
					function init() {
						new ItemListEditor('attachmentList');
					}
					
					// when the dom is fully loaded, execute these scripts
					document.observe("dom:loaded", init);
					
					//]]>
				</script>
				<ol class="itemList" id="attachmentList">
					{foreach name="attachments" from=$attachments.$containerID item=attachment}
						<li id="item_{@$attachment->attachmentID}">
							<div class="buttons">
								<a href="javascript:WysiwygInsert('attachment', {@$attachment->attachmentID});" title="{lang}wcf.message.addToPost{/lang}"><img src="{icon}messageS.png{/icon}" alt="" /></a><input onclick="return confirm('{lang}wcf.attachment.delete.sure{/lang}')" type="image" src="{icon}deleteS.png{/icon}" name="delete[{@$attachment->attachmentID}]" title="{lang}wcf.global.button.delete{/lang}" value="{lang}wcf.global.button.delete{/lang}" />
								{if $additionalAttachmentSmallButtons[$attachment->attachmentID]|isset}{@$additionalAttachmentSmallButtons[$attachment->attachmentID]}{/if}					
							</div>
							<p class="itemListTitle">
								<img src="{$attachment->getFileTypeIcon()}" alt="" />
								<select name="attachmentListPositions[{@$attachment->attachmentID}]">
									{section name='positions' loop=$attachments.$containerID|count}
										<option value="{@$positions+1}"{if $positions+1 == $tpl.foreach.attachments.iteration} selected="selected"{/if}>{@$positions+1}</option>
									{/section}
								</select>
								
								<a href="index.php?page=Attachment&amp;attachmentID={@$attachment->attachmentID}&amp;h={@$attachment->sha1Hash}{@SID_ARG_2ND}"{if $attachment->isImage} class="enlargable"{/if}>{$attachment->attachmentName}</a> <span class="smallFont">({@$attachment->attachmentSize|filesize})</span>
							</p>
						</li>
					{/foreach}
				</ol>
			</fieldset>
		{/if}
		
		{if $maxUploadFields > 0}
			<fieldset>
			
				<legend>{lang}wcf.attachment.add{/lang}</legend>
				
				<ol id="uploadFields" class="itemList">
					<li>
						<div class="buttons">
							<a href="#delete" title="{lang}wcf.global.button.delete{/lang}" class="hidden"><img src="{icon}deleteS.png{/icon}" longdesc="" alt="" /></a>
						</div>
						<div class="itemListTitle">
							<input type="file" size="50" name="upload[]" />
						</div>
					</li>
				</ol>
				
				{if $errorField == 'attachments'}
					<div class="innerError formError">
						{foreach from=$errorType item=error}
							<p>
								{if $error.errorType == 'fileSize'}{lang}wcf.attachment.upload.error.fileSize{/lang}{/if}
								{if $error.errorType == 'doubleUpload'}{lang}wcf.attachment.upload.error.doubleUpload{/lang}{/if}
								{if $error.errorType == 'illegalExtension'}{lang}wcf.attachment.upload.error.illegalExtension{/lang}{/if}
								{if $error.errorType == 'fileSizePHP'}{lang}wcf.attachment.upload.error.fileSizePHP{/lang}{/if}
								{if $error.errorType == 'badImage'}{lang}wcf.attachment.upload.error.badImage{/lang}{/if}
							</p>
						{/foreach}
					</div>
				{/if}
			</fieldset>
			<div class="attachmentsInputSubmit" id="attachmentsInputSubmit">
				{if $maxUploadFields > 1}
					<script type="text/javascript">
						//<![CDATA[
						var openUploads = {@$maxUploadFields} - 1;
						function addUploadField() {
							if (openUploads > 0) {
								var fileInput = new Element('input', { 'type': 'file', 'name': 'upload[]', 'size': 50 });
								var fileDiv = new Element('div').addClassName('itemListTitle');
								var deleteButton = new Element('a', { 'href': '#delete', 'title': '{lang}wcf.global.button.delete{/lang}' });
								deleteButton.addClassName('hidden');
								var deleteImg = new Element('img', { 'src': '{icon}deleteS.png{/icon}', 'longdesc': '' });
								var buttons = new Element('div').addClassName('buttons').insert(deleteButton.insert(deleteImg));
								
								$('uploadFields').insert(new Element('li').insert(buttons).insert(fileDiv.insert(fileInput)));
								deleteButton.observe('click', removeUploadField);
								fileInput.observe('change', uploadFieldChanged);
								openUploads--;
							}
						}
						
						function removeUploadField(evt) {
							var fileInput = evt.findElement().up('li').down('input');
							var emptyField = true;
							var counter = 0;
							$$('#uploadFields input[type=file]').each(function(input) { 
								if (input.value == '') {
									emptyField = true;
								}
								counter++;
							});
							if (emptyField && fileInput.value != '' && counter > 1) {
								fileInput.up('li').fade({ 
									'duration': '0.5', afterFinish: function() { fileInput.up('li').remove(); } 
								});
								openUploads++;
							}
							else {
								fileInput.value = '';
							}
							
							evt.stop();
						}
						
						function uploadFieldChanged(e) {
							if (!e) e = window.event;
								
							if (e.target) var inputField = e.target;
							else if (e.srcElement) var inputField = e.srcElement;
							
							var emptyField = false;
							$$('#uploadFields input[type=file]').each(function(input) {
								if (input.value == '') emptyField = true;
							});
							
							if (!emptyField && inputField.value != '' && inputField.value != inputField.oldValue) {
								inputField.oldValue = inputField.value;
								addUploadField();
							}
							
							if (inputField.value == '') {
								$(inputField).up('li').down('a[href*="#delete"]').addClassName('hidden');	
							}
							else {
								$(inputField).up('li').down('a[href*="#delete"]').removeClassName('hidden');	
							}
						}
						
						// add button
						document.observe('dom:loaded', function() { 
							$$('#uploadFields input[type=file]').invoke('observe', 'change', uploadFieldChanged);
							$$('#uploadFields a[href*="#delete"]').invoke('observe', 'click', removeUploadField);
						});
						//]]>
					</script>
				{/if}
				<input type="submit" name="upload" id="attachmentsInputSubmitButton" value="{lang}wcf.attachment.button.upload{/lang}" />
			</div>
			<p class="smallFont">{lang}wcf.attachment.upload.limits{/lang}</p>
		{/if}
		
	</fieldset>
</div>

<script type="text/javascript">
	//<![CDATA[
	tabbedPane.addTab('attachmentsEdit', {if $errorField == 'attachments'}true{else}false{/if});
	//]]>
</script>
