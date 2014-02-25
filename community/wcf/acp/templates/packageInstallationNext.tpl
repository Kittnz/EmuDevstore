{include file='setupWindowHeader'}

<h2>CurrentStep: {@$step}</h2>
<h2>NextStep: {@$nextStep}</h2>

<form method="post" action="index.php?page=Package&amp;step={@$nextStep}&amp;queueID={@$queueID}&amp;action={@$action}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
	<input type="submit" value="Go" onclick="parent.stopAnimating()" />
</form>

<script type="text/javascript">
	//<![CDATA[
	{if $progress|isset}parent.setProgress({@$progress});{/if}
	parent.showWindow(false);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	
	window.onload = function() {
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='setupWindowFooter'}