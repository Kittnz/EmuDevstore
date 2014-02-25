{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionEditL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.userSuspension.edit{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.infraction.userSuspension.edit.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UserSuspensionList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionM.png" alt="" title="{lang}wcf.acp.menu.link.user.infraction.suspension.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.infraction.userSuspension.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UserSuspensionEdit">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.user.infraction.warning.general{/lang}</legend>
				
				<div class="formElement">
					<p class="formFieldLabel">{lang}wcf.user.username{/lang}</p>
					<p class="formField"><a href="index.php?form=UserEdit&amp;userID={@$userSuspension->userID}{@SID_ARG_2ND}">{$userSuspension->username}</a></p>
				</div>
				
				<div class="formElement">
					<p class="formFieldLabel">{lang}wcf.acp.infraction.userSuspension.suspension{/lang}</p>
					<p class="formField">{$userSuspension->title}</a></p>
				</div>
				
				<div class="formElement">
					<p class="formFieldLabel">{lang}wcf.user.infraction.userWarning.time{/lang}</p>
					<p class="formField">{@$userSuspension->time|date}</a></p>
				</div>
				
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
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="userSuspensionID" value="{@$userSuspensionID}" />
	</div>
</form>

{include file='footer'}