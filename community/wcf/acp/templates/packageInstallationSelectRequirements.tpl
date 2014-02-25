{include file='setupWindowHeader'}

<form method="post" action="index.php?page=Package">
	<fieldset>
		<legend>{lang}wcf.acp.package.requirements{/lang}</legend>
		<div class="inner">
			<p>{lang}wcf.acp.package.requirements.description{/lang}</p>
			
			{foreach from=$selectableRequirements key=identifier item=packages}
				<div style="margin-bottom: 10px">
					<p style="font-weight: bold">{$identifier}</p>
				
					{foreach from=$packages item=package}
						<div class="select">
							<input type="radio" id="requiredPackage-{@$package.packageID}" name="selectedRequirements[{$identifier}]" value="{@$package.packageID}"{if $selectedRequirements.$identifier|isset && $selectedRequirements.$identifier == $package.packageID} checked="checked"{/if} />
							<label for="requiredPackage-{@$package.packageID}"><b>{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</b><br />
								{$package.packageDescription}
							</label>
						</div>
					{/foreach}
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