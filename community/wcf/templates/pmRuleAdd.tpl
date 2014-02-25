{include file="documentHeader"}
<head>
	<title>{lang}wcf.pm.rule.{$action}{/lang} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/PMRuleEditor.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var availableConditionTypes = new Object();
		{foreach from=$availableRuleConditionTypes key=name item=availableRuleConditionType}
			availableConditionTypes['{@$name}'] = {
				name : '{lang}wcf.pm.rule.condition.type.{@$name}{/lang}',
				availableConditions : { {implode from=$availableRuleConditionType->getAvailableConditions() key=key item=string}{@$key} : '{@$string|encodeJS}'{/implode} },
				valueType : '{@$availableRuleConditionType->getValueType()}',
				availableValues : { {implode from=$availableRuleConditionType->getAvailableValues() key=key item=string}{@$key} : '{@$string|encodeJS}'{/implode} }
			}
		{/foreach}
		var availableActions = new Object();
		{foreach from=$availableRuleActions key=name item=availableRuleAction}
			{if $availableRuleAction->getDestinationType() != 'options' || $availableRuleAction->getAvailableDestinations()|count > 0}
				availableActions['{@$name}'] = {
					name : '{lang}wcf.pm.rule.action.{@$name}{/lang}',
					destinationType : '{@$availableRuleAction->getDestinationType()}',
					availableDestinations : { {implode from=$availableRuleAction->getAvailableDestinations() key=key item=string}{@$key} : '{@$string|encodeJS}'{/implode} }
				}
			{/if}
		{/foreach}
		var selectedConditions = new Array();
		{foreach name=ruleConditionsLoop from=$ruleConditions key=name item=ruleCondition}
			selectedConditions[{@$tpl.foreach.ruleConditionsLoop.iteration-1}] = {
				type : '{if $ruleCondition[type]|isset}{@$ruleCondition[type]|encodeJS}{/if}',
				condition : '{if $ruleCondition[condition]|isset}{@$ruleCondition[condition]|encodeJS}{/if}',
				value : '{if $ruleCondition[value]|isset}{@$ruleCondition[value]|encodeJS}{/if}'
			}
		{/foreach}
		
		onloadEvents.push(function() {
			new PMRuleEditor($('conditions'), {
				availableConditionTypes: 	availableConditionTypes, 
				selectedConditions: 		selectedConditions, 
				actionContainer: 		$('action'), 
				availableActions: 		availableActions, 
				selectedAction: 		'{@$ruleAction|encodeJS}', 
				selectedDestination: 		'{@$ruleDestination|encodeJS}',
				imgDeleteDisabledSrc:		'{icon}deleteDisabledS.png{/icon}',
				imgDeleteSrc:			'{icon}deleteS.png{/icon}',
				imgAddSrc: 			'{icon}addS.png{/icon}'
			});
		});
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{icon}pmEmptyS.png{/icon}" alt="" /> <span>{lang}wcf.pm.title{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMRuleList{@SID_ARG_2ND}"><img src="{icon}pmRuleS.png{/icon}" alt="" /> <span>{lang}wcf.pm.editRules{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}pmRule{$action|ucfirst}L.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.pm.rule.{$action}{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $success|isset}
		<p class="success">{lang}wcf.pm.rule.{@$action}.success{/lang}</p>	
	{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?page=PMRuleList{@SID_ARG_2ND}" title="{lang}wcf.pm.editRules{/lang}"><img src="{icon}pmRuleM.png{/icon}" alt="" /> <span>{lang}wcf.pm.editRules{/lang}</span></a></li></ul>
		</div>
	</div>
	
	<form method="post" action="index.php?form=PMRule{$action|ucfirst}">
		<div class="border content">
			<div class="container-1">
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="enabled" value="1" {if $enabled == 1}checked="checked" {/if}/> {lang}wcf.pm.rule.enable{/lang}</label>
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'title'} formError{/if}">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.pm.rule.title{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="title" id="title" value="{$title}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formGroup{if $errorField == 'ruleConditions'} formError{/if}">
					<div class="formGroupLabel">
						<label>{lang}wcf.pm.rule.conditions{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.pm.rule.conditions{/lang}</legend>
			
							<div id="conditions"></div>
							
							{if $errorField == 'ruleConditions'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</fieldset>
					</div>
				</div>
				
				<div class="formGroup{if $errorField == 'ruleAction' || $errorField == 'logicalOperator'} formError{/if}">
					<div class="formGroupLabel">
						<label>{lang}wcf.pm.rule.action{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.pm.rule.action{/lang}</legend>
			
							<div id="action" class="floatContainer"><div class="floatedElement">
								<select name="logicalOperator" id="logicalOperator">
									<option value="and"{if $logicalOperator == 'and'} selected="selected"{/if}>{lang}wcf.pm.rule.logicalOperator.and{/lang}</option>
									<option value="or"{if $logicalOperator == 'or'} selected="selected"{/if}>{lang}wcf.pm.rule.logicalOperator.or{/lang}</option>
									<option value="nor"{if $logicalOperator == 'nor'} selected="selected"{/if}>{lang}wcf.pm.rule.logicalOperator.nor{/lang}</option>
								</select>
								{if $errorField == 'logicalOperator'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									</p>
								{/if}
							</div></div>
							
							{if $errorField == 'ruleAction'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</fieldset>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
	 		{if $ruleID|isset}<input type="hidden" name="ruleID" value="{@$ruleID}" />{/if}
		</div>
	</form>

</div>
	
{include file='footer' sandbox=false}
</body>
</html>

