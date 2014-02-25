<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/ThreadQuickReply.class.js"></script>
<form method="post" action="index.php?form=PostQuickAdd&amp;threadID={@$thread->threadID}">
	<div class="quickReply message content messageMinimized hidden" id="quickReplyContainer-{@$thread->threadID}">
		<div class="messageInner container-1">
			<img src="{icon}messageQuickReplyM.png{/icon}" alt="" />
			<h3><a id="quickReplyLink-{@$thread->threadID}" title="{lang}wbb.thread.quickReply.title{/lang}">{lang}wbb.thread.quickReply{/lang}</a></h3>
			
			<div class="hidden" id="quickReplyInput-{@$thread->threadID}">
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="text">{lang}wbb.threadAdd.text{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="text" id="text" rows="10" cols="40"></textarea>
					</div>
				</div>
				<div class="formSubmit hidden" id="quickReplyButtons-{@$thread->threadID}">
					<input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
					<input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.editor.jump{/lang}" />
					<input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
					{@SID_INPUT_TAG}
				</div>
			</div>
		</div>
	</div>
	
</form>
<script type="text/javascript">
	//<![CDATA[
	// init quick reply
	new ThreadQuickReply({@$thread->threadID});
	//]]>
</script>