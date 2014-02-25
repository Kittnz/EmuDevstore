{if $captchaID}
	<div{if $errorField == 'captchaString'} class="errorField"{/if}>
		<label for="captchaString">{lang}wcf.captcha.captchaString.title{/lang}</label>
		<input type="text" class="inputText" name="captchaString" value="" id="captchaString" />
		{if $errorField == 'captchaString'}
			<p>
				<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
				{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
				{if $errorType == 'false'}{lang}wcf.captcha.error.captchaString.false{/lang}{/if}
			</p>
		{/if}
		<p>{lang}wcf.captcha.captchaString.description{/lang}</p>
		<p><img id="captchaImage" src="index.php?page=ACPCaptcha&amp;captchaID={@$captchaID}{@SID_ARG_2ND}" alt="" /></p>
		<input type="hidden" id="captchaID" name="captchaID" value="{@$captchaID}" />
	</div>
	
	<script type="text/javascript">
		//<![CDATA[
		var captchaLanguage = new Object();
		captchaLanguage['wcf.captcha.reload'] = '{lang}wcf.captcha.reload{/lang}';
		captchaLanguage['wcf.captcha.minimize'] = '{lang}wcf.captcha.minimize{/lang}';
		captchaLanguage['wcf.captcha.maximize'] = '{lang}wcf.captcha.maximize{/lang}';
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Captcha.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		captcha.src = 'index.php?page=ACPCaptcha';
		//]]>
	</script>
{/if}