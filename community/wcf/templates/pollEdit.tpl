<div id="pollEdit">
	<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
	<script type="text/javascript">
		//<![CDATA[
		var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
		//]]>
	</script>
	
	<fieldset class="noJavaScript">
		<legend class="noJavaScript">{lang}wcf.poll{/lang}</legend>
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="pollQuestion">{lang}wcf.poll.question{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" name="pollQuestion" id="pollQuestion" value="{$pollQuestion}" />
			</div>
		</div>
		
		<div class="formElement{if $errorField == 'pollOptions'} formError{/if}">
			<div class="formFieldLabel">
				<label for="pollOptions">{lang}wcf.poll.options{/lang}</label>
			</div>
			<div class="formField">
				<textarea name="pollOptions" id="pollOptions" rows="5" cols="20">{$pollOptions}</textarea>
				{if $errorField == 'pollOptions'}
					<p class="innerError">
						{if $errorType == 'notEnoughOptions'}{lang}wcf.poll.error.notEnoughOptions{/lang}{/if}
						{if $errorType == 'tooMuch'}{lang}wcf.poll.error.tooMuchOptions{/lang}{/if}
					</p>
				{/if}
			</div>
			<div class="formFieldDesc">
				<p>{lang}wcf.poll.options.description{/lang}</p>
			</div>
		</div>
		<div class="formGroup{if $errorField == 'endTime'} formError{/if}">
			<div class="formGroupLabel">
				<label>{lang}wcf.poll.endTime{/lang}</label>
			</div>
			<div class="formGroupField">
				<fieldset>
					<legend><label>{lang}wcf.poll.endTime{/lang}</label></legend>
		
					<div class="formField">
						<div class="floatedElement">
							<label for="endTimeDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$endTimeDay id=endTimeDay name=endTimeDay}
						</div>
						
						<div class="floatedElement">
							<label for="endTimeMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$endTimeMonth id=endTimeMonth name=endTimeMonth}
						</div>
						
						<div class="floatedElement">
							<label for="endTimeYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="endTimeYear" class="inputText fourDigitInput" type="text" name="endTimeYear" value="{@$endTimeYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<label for="endTimeHour">{lang}wcf.global.date.hour{/lang}</label>
							{htmlOptions options=$hourOptions selected=$endTimeHour id=endTimeHour name=endTimeHour} :
						</div>
															
						<div class="floatedElement">
							<label for="endTimeMinutes">{lang}wcf.global.date.minutes{/lang}</label>
							{htmlOptions options=$minuteOptions selected=$endTimeMinutes id=endTimeMinutes name=endTimeMinutes}
						</div>
						
						<div class="floatedElement">
							<a id="endTimeButton"><img src="{icon}datePickerOptionsM.png{/icon}" alt="" /></a>
							<div id="endTimeCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('endTime');
								//]]>
							</script>
						</div>
						
						{if $errorField == 'endTime'}
							<p class="floatedElement innerError">
								{if $errorType == 'invalid'}{lang}wcf.poll.endTime.error.invalid{/lang}{/if}
							</p>
						{/if}
					</div>
				</fieldset>
			</div>
		</div>
		
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="pollAnswers">{lang}wcf.poll.choiceCount{/lang}</label>
			</div>
			<div class="formField{if $errorField == 'choiceCount'} formError{/if}">
				<input type="text" class="inputText" name="choiceCount" value="{$choiceCount}" id="pollAnswers" />
				{if $errorField == 'choiceCount'}
					<p class="innerError">
						{if $errorType == 'notValid'}{lang}wcf.poll.error.choiceCount.notValid{/lang}{/if}
						{if $errorType == 'tooMuch'}{lang}wcf.poll.error.choiceCount.tooMuch{/lang}{/if}
					</p>
				{/if}
			</div>
		</div>
		
		<div class="formElement">
			<div class="formField">
				<label><input type="checkbox" name="votesNotChangeable" value="1" {if $votesNotChangeable == 1}checked="checked" {/if}/> {lang}wcf.poll.votesNotChangeable{/lang}</label>
			</div>
		</div>
		
		<div class="formElement">
			<div class="formField">
				<label><input type="checkbox" name="sortByResult" value="1" {if $sortByResult == 1}checked="checked" {/if}/> {lang}wcf.poll.sortByResult{/lang}</label>
			</div>
		</div>
		
		{if $canStartPublicPoll}
			<div class="formElement">
				<div class="formField">
					<label><input type="checkbox" name="isPublic" value="1" {if $isPublic == 1}checked="checked" {/if}/> {lang}wcf.poll.isPublic{/lang}</label>
				</div>
			</div>
		{/if}
	</fieldset>
</div>

<script type="text/javascript">
	//<![CDATA[
	tabbedPane.addTab('pollEdit', {if $errorField == 'pollOptions' || $errorField == 'choiceCount'}true{else}false{/if});
	//]]>
</script>