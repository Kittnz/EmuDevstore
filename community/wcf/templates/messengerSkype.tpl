{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.skype.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.skype.name{/lang}</p>
			<h1>{$identifier}</h1>
		</div>
		<p class="messengerStatus"><img src="http://mystatus.skype.com/{$identifier}" alt="" /></p>
		<ul class="messengerOptions">			
			<li><a href="skype:{$identifier}?add" onclick="return skypeCheck();">{lang}wcf.user.messenger.addToContactList{/lang}</a></li>
			<li><a href="skype:{$identifier}?userinfo" onclick="return skypeCheck();">{lang}wcf.user.messenger.viewProfile{/lang}</a></li>
			<li><a href="skype:{$identifier}?call" onclick="return skypeCheck();">{lang}wcf.user.messenger.call{/lang}</a></li>
			<li><a href="skype:{$identifier}?chat" onclick="return skypeCheck();">{lang}wcf.user.messenger.sendMessage{/lang}</a></li>
			<li><a href="skype:{$identifier}?voicemail" onclick="return skypeCheck();">{lang}wcf.user.messenger.voicemail{/lang}</a></li>
			<li><a href="skype:{$identifier}?sendfile" onclick="return skypeCheck();">{lang}wcf.user.messenger.sendFile{/lang}</a></li>
			
		</ul>
		<p class="smallFont light messengerInfo">{lang}wcf.user.messenger.skype.description{/lang}</p>
	</div>
</div>
</body>
</html>