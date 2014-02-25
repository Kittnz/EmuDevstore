{capture append='specialStyles'}
<style type="text/css">
/*<![CDATA[*/
.statBar {
	text-align: left;
	padding: 1px;
	background-color: #fff;
	border: 1px solid #8da4b7;
	float: left;
	width: 400px;
}

.statBar div {
	font-size: 6px; /* needed for correct usage-bar display in IE-browsers */
	background-color: #0c0;
	border-bottom: 6px solid #0a0;
	height: 6px;
}

.statBarLabel {
	margin-left: 410px;
}			
/*]]>*/
</style>
{/capture}
{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>
	
<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/statsL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.stats{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $results|isset && !$results|count}
	<p class="error">{lang}wcf.acp.stats.noResults{/lang}</p>
{/if}

<form  method="post" action="index.php?form=Stats">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.stats.config{/lang}</legend>
				
				<div class="formElement{if $errorField == 'type'} formError{/if}">
					<div class="formFieldLabel">
						<label for="type">{lang}wcf.acp.stats.type{/lang}</label>
					</div>
					<div class="formField">
						<select name="type" id="type">
							{foreach from=$availableTypes item=availableType}
								<option value="{$availableType->typeName}"{if $type == $availableType->typeName} selected="selected"{/if}>{lang}wcf.acp.stats.type.{$availableType->typeName}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'type'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'username'} formError{/if}">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="username" name="username" value="{$username}" />
						{if $errorField == 'username'}
							<p class="innerError">
								{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
							</p>
						{/if}
						<script type="text/javascript">
							//<![CDATA[
							suggestion.enableMultiple(false);
							suggestion.init('username');
							//]]>
						</script>
					</div>
				</div>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="fromDay">{lang}wcf.acp.stats.period{/lang}</label>
					</div>
					
					<div class="formGroupField">
						<fieldset id="searchPeriod">
						
							<legend><label for="fromDay">{lang}wcf.acp.stats.period{/lang}</label></legend>
							
							<div class="floatedElement">
								<div class="floatedElement">
									<p> {lang}wcf.acp.stats.period.start{/lang}</p>
								</div>
								
								<div class="floatedElement">
									<label for="fromDay">{lang}wcf.global.date.day{/lang}</label>
									{htmlOptions options=$dayOptions selected=$fromDay id=fromDay name=fromDay}
								</div>
								
								<div class="floatedElement">
									<label for="fromMonth">{lang}wcf.global.date.month{/lang}</label>
									{htmlOptions options=$monthOptions selected=$fromMonth id=fromMonth name=fromMonth}
								</div>
								
								<div class="floatedElement">
									<label for="fromYear">{lang}wcf.global.date.year{/lang}</label>
									<input id="fromYear" class="inputText fourDigitInput" type="text" name="fromYear" value="{@$fromYear}" maxlength="4" />
								</div>
								
								<div class="floatedElement">
									<a id="fromButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
									<div id="fromCalendar" class="inlineCalendar"></div>
								</div>
							</div>
							
							<div class="floatedElement">
								<div class="floatedElement">
									<p> {lang}wcf.acp.stats.period.end{/lang}</p>
								</div>
								
								<div class="floatedElement">
									<label for="untilDay">{lang}wcf.global.date.day{/lang}</label>
									{htmlOptions options=$dayOptions selected=$untilDay id=untilDay name=untilDay}
								</div>
								
								<div class="floatedElement">
									<label for="untilMonth">{lang}wcf.global.date.month{/lang}</label>
									{htmlOptions options=$monthOptions selected=$untilMonth id=untilMonth name=untilMonth}
								</div>
								
								<div class="floatedElement">
									<label for="untilYear">{lang}wcf.global.date.year{/lang}</label>
									<input id="untilYear" class="inputText fourDigitInput" type="text" name="untilYear" value="{@$untilYear}" maxlength="4" />
								</div>
								
								<div class="floatedElement">
									<a id="untilButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
									<div id="untilCalendar" class="inlineCalendar"></div>
									<script type="text/javascript">
										//<![CDATA[
										calendar.init('from');
										calendar.init('until');
										//]]>
									</script>
								</div>
							</div>
							
						</fieldset>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="groupBy">{lang}wcf.acp.stats.groupBy{/lang}</label>
					</div>
					<div class="formField">
						<select name="groupBy" id="groupBy">
							<option value="day"{if $groupBy == 'day'} selected="selected"{/if}>{lang}wcf.acp.stats.groupBy.day{/lang}</option>
							<option value="week"{if $groupBy == 'week'} selected="selected"{/if}>{lang}wcf.acp.stats.groupBy.week{/lang}</option>
							<option value="month"{if $groupBy == 'month'} selected="selected"{/if}>{lang}wcf.acp.stats.groupBy.month{/lang}</option>
						</select>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="sortField">{lang}wcf.acp.stats.sortField{/lang}</label>
					</div>
					<div class="formField">
						<select name="sortField" id="sortField">
							<option value="date"{if $sortField == 'date'} selected="selected"{/if}>{lang}wcf.acp.stats.sortField.date{/lang}</option>
							<option value="count"{if $sortField == 'count'} selected="selected"{/if}>{lang}wcf.acp.stats.sortField.count{/lang}</option>
						</select>
						
						<select name="sortOrder" id="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

{if $results|isset && $results|count}
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.stats.results{/lang}</legend>
	
				{foreach from=$results item=result}
					<div class="formElement">
						<p class="formFieldLabel">{$result.date|date:$dateFormat:false}</p>
						<div class="formField"><div class="statBar"><div style="width: {$result.count/$max*100|round}%;"></div></div><p class="statBarLabel">{#$result.count}</p></div>
					</div>
				
				{/foreach}
			</fieldset>
		</div>
	</div>
{/if}

{include file='footer'}