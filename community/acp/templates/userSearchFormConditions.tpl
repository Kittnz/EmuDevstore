<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>
	
<div class="border tabMenuContent hidden" id="conditions-content">
	<div class="container-1">
		<h3 class="subHeadline">{lang}wbb.acp.user.search.conditions{/lang}</h3>
	
		<fieldset>
			<legend>{lang}wcf.user.registrationDate{/lang}</legend>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="registrationDateAfterDay">{lang}wbb.acp.user.search.conditions.date.after{/lang}</label>
				</div>
				<div class="formField">	
					<div class="floatedElement">
						<label for="registrationDateAfterDay">{lang}wcf.global.date.day{/lang}</label>
						{htmlOptions options=$dayOptions selected=$registrationDateAfterDay id=registrationDateAfterDay name=registrationDateAfterDay}
					</div>
					
					<div class="floatedElement">
						<label for="registrationDateAfterMonth">{lang}wcf.global.date.month{/lang}</label>
						{htmlOptions options=$monthOptions selected=$registrationDateAfterMonth id=registrationDateAfterMonth name=registrationDateAfterMonth}
					</div>
					
					<div class="floatedElement">
						<label for="registrationDateAfterYear">{lang}wcf.global.date.year{/lang}</label>
						<input id="registrationDateAfterYear" class="inputText fourDigitInput" type="text" name="registrationDateAfterYear" value="{@$registrationDateAfterYear}" maxlength="4" />
					</div>
					
					<div class="floatedElement">
						<a id="registrationDateAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
						<div id="registrationDateAfterCalendar" class="inlineCalendar"></div>
						<script type="text/javascript">
							//<![CDATA[
							calendar.init('registrationDateAfter');
							//]]>
						</script>
					</div>
				</div>
			</div>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="registrationDateBeforeDay">{lang}wbb.acp.user.search.conditions.date.before{/lang}</label>
				</div>
				<div class="formField">	
					<div class="floatedElement">
						<label for="registrationDateBeforeDay">{lang}wcf.global.date.day{/lang}</label>
						{htmlOptions options=$dayOptions selected=$registrationDateBeforeDay id=registrationDateBeforeDay name=registrationDateBeforeDay}
					</div>
					
					<div class="floatedElement">
						<label for="registrationDateBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
						{htmlOptions options=$monthOptions selected=$registrationDateBeforeMonth id=registrationDateBeforeMonth name=registrationDateBeforeMonth}
					</div>
					
					<div class="floatedElement">
						<label for="registrationDateBeforeYear">{lang}wcf.global.date.year{/lang}</label>
						<input id="registrationDateBeforeYear" class="inputText fourDigitInput" type="text" name="registrationDateBeforeYear" value="{@$registrationDateBeforeYear}" maxlength="4" />
					</div>
					
					<div class="floatedElement">
						<a id="registrationDateBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
						<div id="registrationDateBeforeCalendar" class="inlineCalendar"></div>
						<script type="text/javascript">
							//<![CDATA[
							calendar.init('registrationDateBefore');
							//]]>
						</script>
					</div>
				</div>
			</div>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.user.lastActivity{/lang}</legend>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="lastActivityAfterDay">{lang}wbb.acp.user.search.conditions.date.after{/lang}</label>
				</div>
				<div class="formField">	
					<div class="floatedElement">
						<label for="lastActivityAfterDay">{lang}wcf.global.date.day{/lang}</label>
						{htmlOptions options=$dayOptions selected=$lastActivityAfterDay id=lastActivityAfterDay name=lastActivityAfterDay}
					</div>
					
					<div class="floatedElement">
						<label for="lastActivityAfterMonth">{lang}wcf.global.date.month{/lang}</label>
						{htmlOptions options=$monthOptions selected=$lastActivityAfterMonth id=lastActivityAfterMonth name=lastActivityAfterMonth}
					</div>
					
					<div class="floatedElement">
						<label for="lastActivityAfterYear">{lang}wcf.global.date.year{/lang}</label>
						<input id="lastActivityAfterYear" class="inputText fourDigitInput" type="text" name="lastActivityAfterYear" value="{@$lastActivityAfterYear}" maxlength="4" />
					</div>
					
					<div class="floatedElement">
						<a id="lastActivityAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
						<div id="lastActivityAfterCalendar" class="inlineCalendar"></div>
						<script type="text/javascript">
							//<![CDATA[
							calendar.init('lastActivityAfter');
							//]]>
						</script>
					</div>
				</div>
			</div>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="lastActivityBeforeDay">{lang}wbb.acp.user.search.conditions.date.before{/lang}</label>
				</div>
				<div class="formField">	
					<div class="floatedElement">
						<label for="lastActivityBeforeDay">{lang}wcf.global.date.day{/lang}</label>
						{htmlOptions options=$dayOptions selected=$lastActivityBeforeDay id=lastActivityBeforeDay name=lastActivityBeforeDay}
					</div>
					
					<div class="floatedElement">
						<label for="lastActivityBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
						{htmlOptions options=$monthOptions selected=$lastActivityBeforeMonth id=lastActivityBeforeMonth name=lastActivityBeforeMonth}
					</div>
					
					<div class="floatedElement">
						<label for="lastActivityBeforeYear">{lang}wcf.global.date.year{/lang}</label>
						<input id="lastActivityBeforeYear" class="inputText fourDigitInput" type="text" name="lastActivityBeforeYear" value="{@$lastActivityBeforeYear}" maxlength="4" />
					</div>
					
					<div class="floatedElement">
						<a id="lastActivityBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
						<div id="lastActivityBeforeCalendar" class="inlineCalendar"></div>
						<script type="text/javascript">
							//<![CDATA[
							calendar.init('lastActivityBefore');
							//]]>
						</script>
					</div>
				</div>
			</div>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.user.posts{/lang}</legend>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="postsGreaterThan">{lang}wbb.acp.user.search.conditions.greaterThan{/lang}</label>
				</div>
				<div class="formField">	
					<input id="postsGreaterThan" class="inputText" type="text" name="postsGreaterThan" value="{@$postsGreaterThan}" />
				</div>
			</div>
			
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="postsLessThan">{lang}wbb.acp.user.search.conditions.lessThan{/lang}</label>
				</div>
				<div class="formField">	
					<input id="postsLessThan" class="inputText" type="text" name="postsLessThan" value="{@$postsLessThan}" />
				</div>
			</div>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wbb.acp.user.search.conditions.status{/lang}</legend>
		
			<div class="formGroup">
				<div class="formGroupLabel">
					<label>{lang}wbb.acp.user.search.conditions.status{/lang}</label>
				</div>
				<div class="formGroupField">
					<fieldset>
						<legend>{lang}wbb.acp.user.search.conditions.status{/lang}</legend>
						<div class="formField">
							<ul class="formOptionsLong">
								<li><label><input type="checkbox" name="notEnabled" value="1" {if $notEnabled == 1}checked="checked" {/if}/> {lang}wbb.acp.user.search.conditions.status.notEnabled{/lang}</label></li>
								<li><label><input type="checkbox" name="enabled" value="1" {if $enabled == 1}checked="checked" {/if}/> {lang}wbb.acp.user.search.conditions.status.enabled{/lang}</label></li>
								<li><label><input type="checkbox" name="banned" value="1" {if $banned == 1}checked="checked" {/if}/> {lang}wbb.acp.user.search.conditions.status.banned{/lang}</label></li>
								<li><label><input type="checkbox" name="notBanned" value="1" {if $notBanned == 1}checked="checked" {/if}/> {lang}wbb.acp.user.search.conditions.status.notBanned{/lang}</label></li>
								<li><label><input type="checkbox" name="hasSpecialPermissions" value="1" {if $hasSpecialPermissions == 1}checked="checked" {/if}/> {lang}wbb.acp.user.search.conditions.status.hasSpecialPermissions{/lang}</label></li>
							</ul>
						</div>
					</fieldset>
				</div>
			</div>
		</fieldset>
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="registrationIpAddress1">{lang}wcf.user.registrationIpAddress{/lang}</label>
			</div>
			<div class="formField">	
				<input id="registrationIpAddress1" class="inputText fourDigitInput" type="text" name="registrationIpAddress1" value="{@$registrationIpAddress1}" maxlength="3" />
				<span>.</span>
				<input id="registrationIpAddress2" class="inputText fourDigitInput" type="text" name="registrationIpAddress2" value="{@$registrationIpAddress2}" maxlength="3" />
				<span>.</span>
				<input id="registrationIpAddress3" class="inputText fourDigitInput" type="text" name="registrationIpAddress3" value="{@$registrationIpAddress3}" maxlength="3" />
				<span>.</span>
				<input id="registrationIpAddress4" class="inputText fourDigitInput" type="text" name="registrationIpAddress4" value="{@$registrationIpAddress4}" maxlength="3" />
			</div>
		</div>
	</div>
</div>