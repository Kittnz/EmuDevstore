{capture assign="actionTitle"}{lang}wcf.acp.package.{$action}{/lang}{/capture}
{capture assign="pageTitle"}{lang}wcf.acp.worker.progressBar{/lang} - {@$actionTitle}{/capture}
{include file='setupHeader'}

<script type="text/javascript">
	//<![CDATA[
	var text = '';
	var noText = htmlEntityDecode('&nbsp;');
	var showText = false;
	var titleAnimator = null;
	
	function showWindow(show) {
		if (show) { 
			document.getElementById('iframe').style.visibility = 'visible';
			if (document.title != noText) text = document.title;
			if (!Prototype.Browser.WebKit && !document.hasFocus()) {
				if (titleAnimator != null) titleAnimator.stop();
				titleAnimator = new PeriodicalExecuter(toggleTitle.bind(this), 1);
			}
		}
		else {
			xHeight('iframe', 0);
			document.getElementById('iframe').style.visibility = 'hidden';
		}
	}
	
	function toggleTitle(pe) {
		if (showText) {
			document.title = text;
			showText = false;
		}
		else {
			document.title = noText;
			showText = true;
		}
	}
	
	function htmlEntityDecode(str) {
		var ta=document.createElement("textarea");
		ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
		return ta.value;
	} 
	
	var observeWindow = document;
	if (Prototype.Browser.IE) observeWindow = window;
	
	Event.observe(observeWindow, 'focus', stopAnimating.bind(this));
	
	function stopAnimating() { 
		if (titleAnimator != null) {
			titleAnimator.stop();
			document.title = text;
		}
	}
	
	function setCurrentStep(title) {
		document.getElementById('currentStep').innerHTML = title;
	}
	
	function setProgress(progress) {
		document.getElementById('progressBar').style.width = Math.round(300 * progress / 100) + 'px';
		{literal}
		document.getElementById('progressText').innerHTML = document.getElementById('progressText').innerHTML.replace(/\d{1,3}%/, progress + '%');
		document.title = document.title.replace(/\d{1,3}%/, progress + '%');
		{/literal}
	}
	
	function rollback(button) {
		button.disabled = true;
		{if $cancelable && $action == 'install'}
			document.location.href=fixURL('index.php?page=Package&action={@$action}&queueID={@$queueID}&step=rollback&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}');
		{else}
			document.location.href=fixURL('index.php?page=Package&action={@$action}&queueID={@$queueID}&step=cancel&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}');	
		{/if}
	}
	
	function enableRollback(enable) {
		if (document.getElementById('rollbackButton')) {
			document.getElementById('rollbackButton').style.display = (enable ? 'block' : 'none');
		}
	}
	
	onloadEvents.push(function() {
		document.getElementById('installIcon').onclick = function(event) {
			if (!event) event = window.event;
			
			if (event.altKey) {
				showWindow(true);
				if (!xHeight('iframe')) {
					xHeight('iframe', 300);
				}
			}
		}
	});
	//]]>
</script>

<img id="installIcon" class="icon" src="{@RELATIVE_WCF_DIR}icon/package{@$action|ucfirst}XL.png" alt="" />

<h1><b>{lang}wcf.global.pageTitle{/lang}</b><br />{@$actionTitle}</h1>

<div class="progress">
	<div id="progressBar" class="progressBar" style="width: {@300*$progress/100|round:0}px"></div>
	<div id="progressText" class="progressText">{lang}wcf.acp.worker.progressBar{/lang}</div>
</div>

<hr />

<h2>{$packageName}</h2>

<p class="shortInfo">{lang}wcf.acp.package.shortPackageInfo{/lang}</p>

<p>{$packageDescription}</p>

<fieldset>
	<legend>{lang}wcf.acp.worker.currentStep{/lang}</legend>
	
	<div class="inner">
		<div><span id="currentStep"></span></div>
		
		<iframe id="iframe" frameborder="0" src="index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step={@$nextStep}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"></iframe>
	</div>
</fieldset>

{if $cancelable && $action == 'install'}
<div id="rollbackButton" class="nextButton">
	<input type="button" value="{lang}wcf.acp.package.install.cancel{/lang}" onclick="if (confirm('{lang}wcf.acp.package.install.cancel.sure{/lang}')) rollback(this);" />
</div>
{/if}

{include file='setupFooter'}