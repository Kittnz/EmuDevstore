{include file='setupWindowHeader'}

<form method="post" action="index.php?page=Package">
	<fieldset>
		<legend>{lang}wcf.acp.package.packageDir{/lang}</legend>
		<div class="inner">	
			<p>{lang}wcf.acp.package.packageDir.description{/lang}</p>
			
			{if $errorField}
				<p class="error">
					{if $errorType == 'wcfDirLocked'}{lang}wcf.acp.package.packageDir.error.wcfDirLocked{/lang}{/if}
					{if $errorType == 'alreadyInstalled'}{lang}wcf.acp.package.packageDir.error.alreadyInstalled{/lang}{/if}
					{if $errorType == 'notWritable'}{lang}wcf.acp.package.packageDir.error.notWritable{/lang}{/if}
				</p>
			{/if}
			
			<div{if $errorField} class="errorField"{/if}>
				<label for="packageDir">{lang}wcf.acp.package.packageDir.input{/lang}</label>
				<input type="text" class="inputText" id="packageDir" name="packageDir" value="{$packageDir}" />
			</div>
			
			<div>
				<label for="packageUrl">{lang}wcf.acp.package.packageDir.url{/lang}</label>
				<input type="text" class="inputText" id="packageUrl" name="packageUrl" value="" readonly="readonly" />
			</div>
			
			<input type="hidden" name="queueID" value="{@$queueID}" />
			<input type="hidden" name="action" value="{@$action}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="step" value="{@$step}" />
			<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
			<input type="hidden" name="send" value="send" />
		</div>
	</fieldset>
	
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
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	
	// data
	var domainName = '{@$domainName|encodeJS}';
	var wcfDir = '{@$wcfDir|encodeJS}';
	var wcfUrl = '{@$wcfUrl|encodeJS}';
	var invalidErrorMessage = '{lang}wcf.acp.package.packageDir.error.invalid{/lang}';
	
	// function
	function refreshPackageUrl() {
		// split paths
		var wcfDirs = wcfDir.split('/');
		var packageDirs = document.getElementById('packageDir').value.split('/');
		var wcfUrlDirs = wcfUrl.split('/');
		
		// remove empty elements
		for (var i = wcfDirs.length; i >= 0; i--) if (wcfDirs[i] == '' || wcfDirs[i] == '.') wcfDirs.splice(i, 1);
		for (var i = packageDirs.length; i >= 0; i--) if (packageDirs[i] == '' || packageDirs[i] == '.') packageDirs.splice(i, 1);
		for (var i = wcfUrlDirs.length; i >= 0; i--) if (wcfUrlDirs[i] == '') wcfUrlDirs.splice(i, 1);
		
		// get relative path
		var relativePathDirs = new Array();
		var max = (packageDirs.length > wcfDirs.length ? packageDirs.length : wcfDirs.length);
		for (var i = 0; i < max; i++) {
			if (i < wcfDirs.length && i < packageDirs.length) {
				if (wcfDirs[i] != packageDirs[i]) {
					packageDirs.splice(0, i);
					for (var j = 0; j < wcfDirs.length - i; j++) relativePathDirs.push('..');
					relativePathDirs = relativePathDirs.concat(packageDirs);
					break;
				}
			}	
			// go up one level
			else if (i < wcfDirs.length && i >= packageDirs.length) {
				relativePathDirs.push('..');
			}
			else {
				relativePathDirs.push(packageDirs[i]);
			}
		}
		
		// loop dirs
		for (var i = 0; i < relativePathDirs.length; i++) {
			if (relativePathDirs[i] == '..') {
				if (wcfUrlDirs.length < 1) {
					document.getElementById('packageUrl').value = invalidErrorMessage;
					return;
				}
				
				wcfUrlDirs.pop();
			}
			else {
				wcfUrlDirs.push(relativePathDirs[i]);
			}
		}
		
		// implode and show result
		var result = domainName;
		for (var i = 0; i < wcfUrlDirs.length; i++) result += '/' + wcfUrlDirs[i];
		document.getElementById('packageUrl').value = result;
	}
	
	// set onkeyup listener
	document.getElementById('packageDir').onkeyup = function() { refreshPackageUrl(); };
	
	// set onblur listener
	document.getElementById('packageDir').onblur = function() { refreshPackageUrl(); };
		
	// set default value
	refreshPackageUrl();
	//]]>
</script>

{include file='setupWindowFooter'}