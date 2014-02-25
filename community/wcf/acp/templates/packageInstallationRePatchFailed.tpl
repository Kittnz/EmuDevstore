{include file='setupWindowHeader'}

{capture assign=errorReport}
	{foreach from=$failures item=$failure}
		<br />
		<b>error message:</b> {$failure.errorMessage}<br />
		<b>error code:</b> {@$failure.errorCode}<br />
		<b>affected template:</b> {$failure.templateName}<br />
		<b>patcher:</b> {$failure.packageName}
	{/foreach}
{/capture}

<form method="post" action="index.php?page=Package&amp;step={@$nextStep}">
	<input type="hidden" name="queueID" value="{@$queueID}" />
	<input type="hidden" name="action" value="{@$action}" />
	{@SID_INPUT_TAG}
	<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	<input type="hidden" name="send" value="send" />
	
	<p class="error">{lang}wcf.acp.package.templatePatch.repatch.failed{/lang}{@$errorReport}</p>
	
	<div class="nextButton">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" onclick="parent.stopAnimating();" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	//]]>
</script>

{include file='setupWindowFooter'}