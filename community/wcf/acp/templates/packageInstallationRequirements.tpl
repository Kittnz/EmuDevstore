{include file='setupWindowHeader'}

<form method="post" action="index.php?page=Package">
	<input type="hidden" name="parentQueueID" value="{@$queueID}" />
	<input type="hidden" name="action" value="openQueue" />
	{@SID_INPUT_TAG}
	<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	<input type="submit" value="Go" onclick="parent.stopAnimating()" />
</form>

<script type="text/javascript">
	//<![CDATA[
	parent.showWindow(false);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	
	window.onload = function() {
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='setupWindowFooter'}