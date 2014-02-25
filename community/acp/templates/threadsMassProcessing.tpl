{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>
{if $action != 'move'}
	<script type="text/javascript">
		//<![CDATA[
		onloadEvents.push(function() { hideOptions('moveToDiv'); });
		//]]>
	</script>
{/if}
{if $action != 'changeLanguage'}
	<script type="text/javascript">
		//<![CDATA[
		onloadEvents.push(function() { hideOptions('changeLanguageDiv'); });
		//]]>
	</script>
{/if}
{if $action != 'changePrefix'}
	<script type="text/javascript">
		//<![CDATA[
		onloadEvents.push(function() { hideOptions('changePrefixDiv'); });
		//]]>
	</script>
{/if}

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/threadMassProcessingL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.massProcessing.threads{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $affectedThreads|isset}
	<p class="success">{lang}wbb.acp.massProcessing.threads.success{/lang}</p>	
{/if}

<p class="warning">{lang}wbb.acp.massProcessing.threads.warning{/lang}</p>

<form method="post" action="index.php?form=ThreadsMassProcessing">
	<div class="border content">
		<div class="container-1">
	
			<h3 class="subHeadline">{lang}wbb.acp.massProcessing.conditions{/lang}</h3>
				
			<fieldset>
				<legend>{lang}wbb.acp.massProcessing.conditions.time{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="timeAfterDay">{lang}wbb.acp.massProcessing.conditions.time.after{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="timeAfterDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$timeAfterDay id=timeAfterDay name=timeAfterDay}
						</div>
						
						<div class="floatedElement">
							<label for="timeAfterMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$timeAfterMonth id=timeAfterMonth name=timeAfterMonth}
						</div>
						
						<div class="floatedElement">
							<label for="timeAfterYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="timeAfterYear" class="inputText fourDigitInput" type="text" name="timeAfterYear" value="{@$timeAfterYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="timeAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="timeAfterCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('timeAfter');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="timeBeforeDay">{lang}wbb.acp.massProcessing.conditions.time.before{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="timeBeforeDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$timeBeforeDay id=timeBeforeDay name=timeBeforeDay}
						</div>
						
						<div class="floatedElement">
							<label for="timeBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$timeBeforeMonth id=timeBeforeMonth name=timeBeforeMonth}
						</div>
						
						<div class="floatedElement">
							<label for="timeBeforeYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="timeBeforeYear" class="inputText fourDigitInput" type="text" name="timeBeforeYear" value="{@$timeBeforeYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="timeBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="timeBeforeCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('timeBefore');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="lastPostTimeAfterDay">{lang}wbb.acp.massProcessing.conditions.lastPostTime.after{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="lastPostTimeAfterDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$lastPostTimeAfterDay id=lastPostTimeAfterDay name=lastPostTimeAfterDay}
						</div>
						
						<div class="floatedElement">
							<label for="lastPostTimeAfterMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$lastPostTimeAfterMonth id=lastPostTimeAfterMonth name=lastPostTimeAfterMonth}
						</div>
						
						<div class="floatedElement">
							<label for="lastPostTimeAfterYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="lastPostTimeAfterYear" class="inputText fourDigitInput" type="text" name="lastPostTimeAfterYear" value="{@$lastPostTimeAfterYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="lastPostTimeAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="lastPostTimeAfterCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('lastPostTimeAfter');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="lastPostTimeBeforeDay">{lang}wbb.acp.massProcessing.conditions.lastPostTime.before{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="lastPostTimeBeforeDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$lastPostTimeBeforeDay id=lastPostTimeBeforeDay name=lastPostTimeBeforeDay}
						</div>
						
						<div class="floatedElement">
							<label for="lastPostTimeBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$lastPostTimeBeforeMonth id=lastPostTimeBeforeMonth name=lastPostTimeBeforeMonth}
						</div>
						
						<div class="floatedElement">
							<label for="lastPostTimeBeforeYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="lastPostTimeBeforeYear" class="inputText fourDigitInput" type="text" name="lastPostTimeBeforeYear" value="{@$lastPostTimeBeforeYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="lastPostTimeBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="lastPostTimeBeforeCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('lastPostTimeBefore');
								//]]>
							</script>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wbb.acp.massProcessing.conditions.replies{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="repliesMoreThan">{lang}wbb.acp.massProcessing.conditions.replies.moreThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="repliesMoreThan" id="repliesMoreThan" value="{$repliesMoreThan}" />
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="repliesLessThan">{lang}wbb.acp.massProcessing.conditions.replies.lessThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="repliesLessThan" id="repliesLessThan" value="{$repliesLessThan}" />
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wbb.acp.massProcessing.conditions.username{/lang}</legend>
				<div class="formElement{if $errorField == 'createdBy'} formError{/if}">
					<div class="formFieldLabel">
						<label for="createdBy">{lang}wbb.acp.massProcessing.conditions.createdBy{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="createdBy" id="createdBy" value="{$createdBy}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.init('createdBy')
							//]]>
						</script>
						{if $errorField == 'createdBy'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{if $error.type == 'notFound'}{lang username=$error.username}wcf.user.error.username.notFound{/lang}{/if}
										</p>
									{/foreach}
								{/if}
							</div>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'postsBy'} formError{/if}">
					<div class="formFieldLabel">
						<label for="postsBy">{lang}wbb.acp.massProcessing.conditions.postsBy{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="postsBy" id="postsBy" value="{$postsBy}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.init('postsBy')
							//]]>
						</script>
						{if $errorField == 'postsBy'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{if $error.type == 'notFound'}{lang username=$error.username}wcf.user.error.username.notFound{/lang}{/if}
										</p>
									{/foreach}
								{/if}
							</div>
						{/if}
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wbb.acp.massProcessing.conditions.status{/lang}</legend>
			
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wbb.acp.massProcessing.conditions.status{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wbb.acp.massProcessing.conditions.status{/lang}</legend>
							<div class="formField">
								<ul class="formOptionsLong">
									<li><label><input type="checkbox" name="deleted" value="1" {if $deleted == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.deleted{/lang}</label></li>
									<li><label><input type="checkbox" name="notDeleted" value="1" {if $notDeleted == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.notDeleted{/lang}</label></li>
									<li><label><input type="checkbox" name="disabled" value="1" {if $disabled == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.disabled{/lang}</label></li>
									<li><label><input type="checkbox" name="notDisabled" value="1" {if $notDisabled == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.notDisabled{/lang}</label></li>
									<li><label><input type="checkbox" name="closed" value="1" {if $closed == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.closed{/lang}</label></li>
									<li><label><input type="checkbox" name="open" value="1" {if $open == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.open{/lang}</label></li>
									<li><label><input type="checkbox" name="redirect" value="1" {if $redirect == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.redirect{/lang}</label></li>
									<li><label><input type="checkbox" name="notRedirect" value="1" {if $notRedirect == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.notRedirect{/lang}</label></li>
									<li><label><input type="checkbox" name="announcement" value="1" {if $announcement == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.announcement{/lang}</label></li>
									<li><label><input type="checkbox" name="sticky" value="1" {if $sticky == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.sticky{/lang}</label></li>
									<li><label><input type="checkbox" name="normal" value="1" {if $normal == 1}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.conditions.status.normal{/lang}</label></li>
								</ul>
							</div>
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wbb.acp.massProcessing.conditions.other{/lang}</legend>
			
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="prefix">{lang}wbb.acp.massProcessing.conditions.prefix{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="prefix" id="prefix" value="{$prefix}" />
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="boardIDs">{lang}wbb.acp.massProcessing.conditions.boards{/lang}</label>
					</div>
					<div class="formField">
						<select name="boardIDs[]" id="boardIDs" size="{if $boardOptions|count > 10}10{else}{@$boardOptions|count}{/if}" multiple="multiple">
							{htmlOptions options=$boardOptions selected=$boardIDs disableEncoding=true}
						</select>
					</div>
					<p class="formFieldDesc">
						{lang}wcf.global.multiSelect{/lang}
					</p>
				</div>
				
				{if $languages|count > 0}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="languageIDs">{lang}wbb.threadAdd.language{/lang}</label>
						</div>
						<div class="formField">
							<select name="languageIDs[]" id="languageIDs" size="{if $languages|count > 5}5{else}{@$languages|count}{/if}" multiple="multiple">
								{htmlOptions options=$languages selected=$languageIDs disableEncoding=true}
							</select>
						</div>
						<p class="formFieldDesc">
							{lang}wcf.global.multiSelect{/lang}
						</p>
					</div>
				{/if}
			</fieldset>
			
			{if $additionalConditions|isset}{@$additionalConditions}{/if}
		</div>
	</div>
	<div class="border content">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wbb.acp.massProcessing.action{/lang}</h3>
				
			<div class="formGroup{if $errorField == 'action'} formError{/if}">
				<div class="formGroupLabel">
					<label>{lang}wbb.acp.massProcessing.action{/lang}</label>
				</div>
				<div class="formGroupField">
					<fieldset>
						<legend>{lang}wbb.acp.massProcessing.action{/lang}</legend>
						<div class="formField">
							<ul class="formOptionsLong">
								<li><label><input onclick="if (IS_SAFARI) showOptions('moveToDiv') + hideOptions('changeLanguageDiv', 'changePrefixDiv');" onfocus="showOptions('moveToDiv') + hideOptions('changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="move" {if $action == 'move'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.move{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="trash" {if $action == 'trash'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.trash{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.delete{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="restore" {if $action == 'restore'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.restore{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="disable" {if $action == 'disable'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.disable{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="enable" {if $action == 'enable'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.enable{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="close" {if $action == 'close'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.close{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="open" {if $action == 'open'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.open{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="deleteSubscriptions" {if $action == 'deleteSubscriptions'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.deleteSubscriptions{/lang}</label></li>
								<li><label><input onclick="if (IS_SAFARI) hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" onfocus="hideOptions('moveToDiv', 'changeLanguageDiv', 'changePrefixDiv');" type="radio" name="action" value="deleteLinks" {if $action == 'deleteLinks'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.deleteLinks{/lang}</label></li>
								{if $languages|count > 0}<li><label><input onclick="if (IS_SAFARI) showOptions('changeLanguageDiv') + hideOptions('moveToDiv', 'changePrefixDiv');" onfocus="showOptions('changeLanguageDiv') + hideOptions('moveToDiv', 'changePrefixDiv');" type="radio" name="action" value="changeLanguage" {if $action == 'changeLanguage'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.changeLanguage{/lang}</label></li>{/if}
								<li><label><input onclick="if (IS_SAFARI) showOptions('changePrefixDiv') + hideOptions('changeLanguageDiv', 'moveToDiv');" onfocus="showOptions('changePrefixDiv') + hideOptions('changeLanguageDiv', 'moveToDiv');" type="radio" name="action" value="changePrefix" {if $action == 'changePrefix'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.changePrefix{/lang}</label></li>
								{if $additionalActions|isset}{@$additionalActions}{/if}
							</ul>
						</div>
					{if $errorField == 'action'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
					</fieldset>
				</div>
			</div>
			<div class="formElement{if $errorField == 'moveTo'} formError{/if}" id="moveToDiv">
				<div class="formFieldLabel">
					<label for="moveTo">{lang}wbb.acp.massProcessing.action.moveTo{/lang}</label>
				</div>
				<div class="formField">
					<select name="moveTo" id="moveTo">
						{htmlOptions options=$boardOptions selected=$moveTo disableEncoding=true}
					</select>
					{if $errorField == 'moveTo'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			<div class="formElement{if $errorField == 'newLanguageID'} formError{/if}" id="changeLanguageDiv">
				<div class="formFieldLabel">
					<label for="newLanguageID">{lang}wbb.acp.massProcessing.action.newLanguage{/lang}</label>
				</div>
				<div class="formField">
					<select name="newLanguageID" id="newLanguageID">
						{htmlOptions options=$languages selected=$newLanguageID disableEncoding=true}
					</select>
					{if $errorField == 'newLanguageID'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
			<div class="formElement{if $errorField == 'newPrefix'} formError{/if}" id="changePrefixDiv">
				<div class="formFieldLabel">
					<label for="newPrefix">{lang}wbb.acp.massProcessing.action.newPrefix{/lang}</label>
				</div>
				<div class="formField">
					<input type="text" class="inputText" name="newPrefix" id="newPrefix" value="{$newPrefix}" />
					{if $errorField == 'newPrefix'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}