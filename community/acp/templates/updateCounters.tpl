{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	var counters = new Object();
	{foreach from=$counters key=counterName item=defaultLimit}
		counters['{@$counterName}'] = '{@$defaultLimit}';
	{/foreach}
	
	function setDefaultLimit(counterName) {
		if (counterName != '') {
			document.getElementById('limit').value = counters[counterName];
		}
	}
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/updateCountersL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.updateCounters{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UpdateCounters">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wbb.acp.updateCounters{/lang}</legend>
				
				<div class="formElement{if $errorField == 'counter'} formError{/if}">
					<div class="formFieldLabel">
						<label for="counter">{lang}wbb.acp.updateCounters.counter{/lang}</label>
					</div>
					<div class="formField">
						<select name="counter" id="counter" onchange="setDefaultLimit(this.options[this.selectedIndex].value)">
							<option value=""></option>
							{foreach from=$counters key=counterName item=defaultLimit}
								<option value="{@$counterName}"{if $counterName == $counter} selected="selected"{/if}>{lang}wbb.acp.updateCounters.counter.{@$counterName}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'counter'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wbb.acp.updateCounters.counter.description{/lang}</p>
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'limit'} formError{/if}">
					<div class="formFieldLabel">
						<label for="limit">{lang}wbb.acp.updateCounters.limit{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="limit" id="limit" value="{$limit}" />
						{if $errorField == 'limit'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wbb.acp.updateCounters.limit.description{/lang}</p>
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
 	</div>
</form>

{include file='footer'}