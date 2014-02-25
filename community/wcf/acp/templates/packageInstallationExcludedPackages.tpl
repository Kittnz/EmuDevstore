{include file='setupWindowHeader'}

<div class="error">
	{lang}wcf.acp.package.install.error.excludedPackages{/lang}
	<ul>
		{foreach from=$excludedPackages item=excludedPackage}
			<li>{lang}wcf.acp.package.install.error.excludedPackages.excludedPackage{/lang}</li>
		{/foreach}
	</ul>
</div>

<div id="rollbackButton" class="nextButton">
	<input name="rollbackButton" type="button" value="{lang}wcf.acp.package.{if $action != 'install'}uninstall.{/if}{if $cancelable && $action == 'install'}rollback{else}cancel{/if}{/lang}" onclick="{if $cancelable && $action == 'install'}if (confirm('{lang}wcf.acp.package.install.rollback.sure{/lang}')) {/if}if (parent && parent.rollback) parent.rollback(this); else document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step={if $cancelable && $action == 'install'}rollback{else}cancel{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}');parent.stopAnimating();" />
</div>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	//]]>
</script>

{include file='setupWindowFooter'}