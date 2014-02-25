<input type="submit" name="send" value="{lang}wcf.global.button.submit{/lang}" class="hidden" />
<div class="hidden" id="tabMenu">
	<ul id="tabMenuList">
		{if MODULE_SMILEY && $showSmilies && $canUseSmilies}<li id="smiliesTab"><a onclick="tabbedPane.openTab('smilies');"><span>{lang}wcf.smiley.smilies{/lang}</span></a></li>{/if}
		{if $showSettings}<li id="settingsTab"><a onclick="tabbedPane.openTab('settings');"><span>{lang}wcf.message.settings{/lang}</span></a></li>{/if}
		{if MODULE_ATTACHMENT && $showAttachments}<li id="attachmentsEditTab"><a onclick="tabbedPane.openTab('attachmentsEdit');"><span>{lang}wcf.attachment.attachments{/lang}</span></a></li>{/if}
		{if MODULE_POLL && $showPoll}<li id="pollEditTab"><a onclick="tabbedPane.openTab('pollEdit');"><span>{lang}wcf.poll{/lang}</span></a></li>{/if}
		{if $additionalTabs|isset}{@$additionalTabs}{/if}
	</ul>
</div>
<div class="hidden" id="subTabMenu"><div class="containerHead"><div> </div></div></div>
<div id="tabContent">
	{if MODULE_SMILEY && $showSmilies && $canUseSmilies}{include file="messageFormSmileys"}{/if}
	{if $showSettings}{include file="messageFormSettings"}{/if}
	{if MODULE_ATTACHMENT && $showAttachments}{include file="attachmentsEdit"}{/if}
	{if MODULE_POLL && $showPoll}{include file="pollEdit"}{/if}
	{if $additionalSubTabs|isset}{@$additionalSubTabs}{/if}
</div>
<input id="activeTab" type="hidden" name="activeTab" value="{$activeTab}" />
<script type="text/javascript">
	//<![CDATA[
	tabbedPane.init('{$activeTab|encodeJS}');
	//]]>
</script>