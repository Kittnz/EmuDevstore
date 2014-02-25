{if !$enableFieldset|isset}{assign var=enableFieldset value=true}{/if}
{if $captchaID}
	{if $enableFieldset}<fieldset>
		<legend>{lang}wcf.captcha.captchaString{/lang}</legend>
	{/if}
		<div class="formElement{if $errorField == 'captchaString'} formError{/if}">
			<div class="formFieldLabel">
				<label for="captchaString">{lang}wcf.captcha.captchaString.title{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" name="captchaString" value="" id="captchaString" />
				{if $errorField == 'captchaString'}
					<p class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'false'}{lang}wcf.captcha.error.captchaString.false{/lang}{/if}
					</p>
				{/if}
			</div>
			<div class="formFieldDesc">
				<p>{lang}wcf.captcha.captchaString.description{/lang}</p>
				<img id="captchaImage" src="index.php?page=Captcha&amp;captchaID={@$captchaID}{@SID_ARG_2ND}" alt="" />
			</div>
			
			<input type="hidden" id="captchaID" name="captchaID" value="{@$captchaID}" />
			
			<script type="text/javascript">
				//<![CDATA[
				var captchaLanguage = new Object();
				captchaLanguage['wcf.captcha.reload'] = '{lang}wcf.captcha.reload{/lang}';
				captchaLanguage['wcf.captcha.minimize'] = '{lang}wcf.captcha.minimize{/lang}';
				captchaLanguage['wcf.captcha.maximize'] = '{lang}wcf.captcha.maximize{/lang}';
				//]]>
			</script>
			<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Captcha.class.js"></script>
		</div>
	{if $enableFieldset}</fieldset>{/if}
{/if}