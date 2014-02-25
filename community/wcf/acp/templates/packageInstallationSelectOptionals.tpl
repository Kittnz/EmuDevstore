{include file='setupWindowHeader'}

<form method="post" action="index.php?page=Package">
	<fieldset>
		<legend>{lang}wcf.acp.package.optionals{/lang}</legend>
		<div class="inner">
			<p>{lang}wcf.acp.package.optionals.description{/lang}</p>
			
			{foreach from=$availableOptionals item=package}
			<div class="select">
				<input type="checkbox" id="optionalPackage-{$package.name}" name="optionalPackages[]" value="{$package.name}"{if !$package.available} disabled="disabled"{/if} />
				<label {if !$package.available}class="disabled" {/if}for="optionalPackage-{$package.name}"><b>{$package.packageName}</b>{if !$package.available}{if $package.installed}{lang}wcf.acp.package.optionals.installed{/lang}{else}{lang}wcf.acp.package.optionals.openRequirements{/lang}{/if}{/if}<br />
					{$package.packageDescription}
				</label>
			</div>
			{/foreach}
			
			<input type="hidden" name="queueID" value="{@$queueID}" />
			<input type="hidden" name="action" value="{@$action}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="step" value="{@$step}" />
			<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
			<input type="hidden" name="send" value="send" />
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" onclick="parent.stopAnimating()" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	//]]>
</script>

{include file='setupWindowFooter'}