{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/postMassProcessingL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.massProcessing.posts{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $affectedPosts|isset}
	<p class="success">{lang}wbb.acp.massProcessing.posts.success{/lang}</p>	
{/if}

<p class="warning">{lang}wbb.acp.massProcessing.posts.warning{/lang}</p>

<form method="post" action="index.php?form=PostsMassProcessing">
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
								<li><label><input type="radio" name="action" value="trash" {if $action == 'trash'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.trash{/lang}</label></li>
								<li><label><input type="radio" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.delete{/lang}</label></li>
								<li><label><input type="radio" name="action" value="restore" {if $action == 'restore'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.restore{/lang}</label></li>
								<li><label><input type="radio" name="action" value="disable" {if $action == 'disable'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.disable{/lang}</label></li>
								<li><label><input type="radio" name="action" value="enable" {if $action == 'enable'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.enable{/lang}</label></li>
								<li><label><input type="radio" name="action" value="close" {if $action == 'close'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.close{/lang}</label></li>
								<li><label><input type="radio" name="action" value="open" {if $action == 'open'}checked="checked" {/if}/> {lang}wbb.acp.massProcessing.action.open{/lang}</label></li>
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