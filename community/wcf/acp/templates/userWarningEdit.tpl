{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	{if $warningID != 0}onloadEvents.push(function() { disableOptions('title', 'points', 'expires') });{/if}
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionWarningEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.userWarning.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.infraction.userWarning.edit.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UserWarningList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionWarningM.png" alt="" title="{lang}wcf.acp.menu.link.user.infraction.warning.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.infraction.userWarning.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UserWarningEdit">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.user.infraction.warning.general{/lang}</legend>
				
				<div class="formElement">
					<p class="formFieldLabel">{lang}wcf.user.username{/lang}</p>
					<p class="formField"><a href="index.php?form=UserEdit&amp;userID={@$userWarning->userID}{@SID_ARG_2ND}">{$userWarning->username}</a></p>
				</div>
				
				{if $object}
					<div class="formElement">
						<p class="formFieldLabel">{lang}wcf.user.infraction.userWarning.object{/lang}</p>
						<p class="formField"><a href="../{$object->getURL()}">{$object->getTitle()}</a></p>
					</div>
				{/if}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.user.infraction.userWarning.warning{/lang}</legend>
				
				{if $warnings|count}
					<div class="formElement">
						<p class="formFieldLabel">{lang}wcf.user.infraction.userWarning.warning.predefined{/lang}</p>
						<div class="formField"><ul class="formOptionsLong">
							{foreach from=$warnings item=warning}
								<li><label><input onclick="disableOptions('title', 'points', 'expires')" type="radio" name="warningID" value="{@$warning->warningID}" {if $warning->warningID == $warningID}checked="checked" {/if}/> {$warning->title}</label></li>
							{/foreach}
						</ul></div>
					</div>
				{/if}
				
				<div class="formElement">
					<div class="formField">
						<label><input onclick="enableOptions('title', 'points', 'expires')" type="radio" name="warningID" value="0" {if $warningID == 0}checked="checked" {/if}/> {lang}wcf.user.infraction.userWarning.warning.individual{/lang}</label>
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'title'} formError{/if}" id="titleDiv">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.user.infraction.warning.title{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="title" id="title" value="{$title}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="titleHelpMessage">
						<p>{lang}wcf.user.infraction.warning.title.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formElement" id="pointsDiv">
					<div class="formFieldLabel">
						<label for="points">{lang}wcf.user.infraction.warning.points{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="points" id="points" value="{@$points}" />
					</div>
					<div class="formFieldDesc hidden" id="pointsHelpMessage">
						<p>{lang}wcf.user.infraction.warning.points.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('points');
				//]]></script>
				
				<div class="formGroup" id="expiresDiv">
					<div class="formGroupLabel">
						<label>{lang}wcf.user.infraction.warning.expires{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.user.infraction.warning.expires{/lang}</legend>
							
							<div class="floatedElement floatedElementContainer">
								<div class="floatedElement">
									<label for="expiresDay">{lang}wcf.global.date.day{/lang}</label>
									{htmlOptions options=$dayOptions selected=$expiresDay id=expiresDay name=expiresDay}
								</div>
								
								<div class="floatedElement">
									<label for="expiresMonth">{lang}wcf.global.date.month{/lang}</label>
									{htmlOptions options=$monthOptions selected=$expiresMonth id=expiresMonth name=expiresMonth}
								</div>
								
								<div class="floatedElement">
									<label for="expiresYear">{lang}wcf.global.date.year{/lang}</label>
									<input id="expiresYear" class="inputText fourDigitInput" type="text" name="expiresYear" value="{@$expiresYear}" maxlength="4" />
								</div>
								
								<div class="floatedElement noFullDay">
									<label for="expiresHour">{lang}wcf.global.date.hour{/lang}</label>
									{htmlOptions options=$hourOptions selected=$expiresHour id=expiresHour name=expiresHour} :
								</div>
								
								<div class="floatedElement noFullDay">
									<label for="expiresMinute">{lang}wcf.global.date.minutes{/lang}</label>
									{htmlOptions options=$minuteOptions selected=$expiresMinute id=expiresMinute name=expiresMinute}
								</div>
								
								<div class="floatedElement">
									<a id="expiresButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
									<div id="expiresCalendar" class="inlineCalendar"></div>
									<script type="text/javascript">
										//<![CDATA[
										calendar.init('expires');
										//]]>
									</script>
								</div>
							</div>
						</fieldset>
						<div class="formFieldDesc">
							<p>{lang}wcf.user.infraction.warning.expires.description{/lang}</p>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.user.infraction.userWarning.reason{/lang}</legend>
				
				<div class="formElement"  id="reasonDiv">
					<div class="formFieldLabel">
						<label for="reason">{lang}wcf.user.infraction.userWarning.reason{/lang}</label>
					</div>
					<div class="formField">
						<textarea name="reason" id="reason" rows="10" cols="40">{$reason}</textarea>
					</div>
					<div class="formFieldDesc" id="reasonHelpMessage">
						<p>{lang}wcf.user.infraction.userWarning.reason.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('reason');
				//]]></script>
			</fieldset>
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="userWarningID" value="{@$userWarningID}" />
	</div>
</form>

{include file='footer'}