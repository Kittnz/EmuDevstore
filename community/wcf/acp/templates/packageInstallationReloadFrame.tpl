{include file='setupWindowHeader'}

<script type="text/javascript">
	//<![CDATA[
	{if $progress|isset}parent.setProgress({@$progress});{/if}
	parent.showWindow(false);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	parent.location.href='index.php?page=Package&action=openQueue&processNo={@$processNo}&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}';
	//]]>
</script>

{include file='setupWindowFooter'}