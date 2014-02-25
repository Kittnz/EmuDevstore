{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.msn.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	
	<object classid="clsid:B69003B3-C55E-4B48-836C-BC5946FC3B28" codetype="application/x-oleobject" id="MsgrObj" style="width: 0; height: 0"></object>
	<script type="text/javascript">
		//<![CDATA[
		function handleError(message, url, lineNo) {
			if (message.indexOf('8100031e') != -1) {
				alert("{lang}wcf.user.messenger.msn.error{/lang}");
				return true;
			}
			else {
				return false;
			}
		}
		
		window.onerror = handleError;
		
		if (IS_IE) {
			onloadEvents.push(function() { document.getElementById('messengerOptions').style.display = ''; });
		}
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.msn.name{/lang}</p>
			<h1>{$identifier}</h1>
		</div>
		
		<ul class="messengerOptions" id="messengerOptions" style="display: none">
			<li><a onclick="MsgrObj.InstantMessage('{$identifier}');">{lang}wcf.user.messenger.sendMessage{/lang}</a></li>
			<li><a onclick="MsgrObj.AddContact(0, '{$identifier}');">{lang}wcf.user.messenger.addToContactList{/lang}</a></li>
		</ul>
		
		<p class="smallFont light messengerInfo">{lang}wcf.user.messenger.msn.description{/lang}</p>
	</div>
</div>
</body>
</html>