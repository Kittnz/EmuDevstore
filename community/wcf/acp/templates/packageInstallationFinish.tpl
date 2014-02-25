{include file='setupWindowHeader'}

<div class="nextButton">
	<input type="button" value="{lang}wcf.global.button.next{/lang}" onclick="parent.location.href='{if $installationType == 'setup'}index.php?{elseif $installationType == 'install'}index.php?form=PackageStartInstall&amp;action=install{else}index.php?page=PackageList{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}'; parent.stopAnimating();" />
</div>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	{if $progress|isset}parent.setProgress({@$progress});{/if}
	parent.enableRollback(false);
	parent.showWindow(true);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	//]]>
</script>

{include file='setupWindowFooter'}