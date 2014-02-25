{include file="documentHeader"}
<head>
	<title>{lang}wcf.search.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}

	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
	<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
	<script type="text/javascript">
		//<![CDATA[
		function showSearchForm(formID, show) {
			if (show) {
				document.getElementById(formID).style.display = 'block';
			}
			else {
				document.getElementById(formID).style.display = 'none';
			}
		}
		
		var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{assign var='searchShowExtendedLink' value=false}
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}searchL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.search.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	{if $errorMessage|isset}
		<p class="error">{@$errorMessage}</p>
	{/if}
	
	<form method="post" action="index.php?form=Search">
		<div class="border content">
			<div class="container-1">
			
				{if $additionalBoxes1|isset}{@$additionalBoxes1}{/if}
				
				<fieldset>
					<legend>{lang}wcf.search.general{/lang}</legend>
					<div class="formElement{if $errorField == 'q'} formError{/if}">
						<div class="formFieldLabel">
							<label for="searchTerm">{lang}wcf.search.query{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="searchTerm" name="q" value="{if $query}{$query}{else}{$defaultQuery}{/if}" maxlength="255" />
							{*<label><input type="checkbox" name="subjectOnly" value="1"{if $subjectOnly} checked="checked"{/if} /> {lang}wcf.search.subjectOnly{/lang}</label>*}
							{if $additionalQueryOptions|isset}{@$additionalQueryOptions}{/if}
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.search.query.description{/lang}</p>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="searchAuthor">{lang}wcf.search.author{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="searchAuthor" name="username" value="{$username}" maxlength="255" />
							<script type="text/javascript">
								//<![CDATA[
								suggestion.setSource('index.php?page=PublicUserSuggest{@SID_ARG_2ND_NOT_ENCODED}');
								suggestion.enableMultiple(false);
								suggestion.init('searchAuthor');
								//]]>
							</script>
							<label><input type="checkbox" name="nameExactly" value="1"{if $nameExactly} checked="checked"{/if} /> {lang}wcf.global.search.matchesExactly{/lang}</label>
							{if $additionalAuthorOptions|isset}{@$additionalAuthorOptions}{/if} 
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label for="fromDay">{lang}wcf.search.period{/lang}</label>
						</div>
						
						<div class="formGroupField">
							<fieldset id="searchPeriod">
							
								<legend><label for="fromDay">{lang}wcf.search.period{/lang}</label></legend>
								
								<div class="floatedElement">
									<div class="floatedElement">
										<p> {lang}wcf.search.period.start{/lang}</p>
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
										<a id="fromButton"><img src="{icon}datePickerOptionsM.png{/icon}" alt="" /></a>
										<div id="fromCalendar" class="inlineCalendar"></div>
									</div>
								</div>
								
								<div class="floatedElement">
									<div class="floatedElement">
										<p> {lang}wcf.search.period.end{/lang}</p>
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
										<a id="untilButton"><img src="{icon}datePickerOptionsM.png{/icon}" alt="" /></a>
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
					
					<div class="formGroup">
						<div class="formGroupLabel">
							<label for="sortField">{lang}wcf.search.results.display{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend><label for="sortField">{lang}wcf.search.results.display{/lang}</label></legend>
								<div class="floatContainer">
									<div class="floatedElement">
										<label for="sortField">{lang}wcf.search.sortBy{/lang}</label>
										<select id="sortField" name="sortField">
											<option value="relevance"{if $sortField == 'relevance'} selected="selected"{/if}>{lang}wcf.search.sortBy.relevance{/lang}</option>
											<option value="subject"{if $sortField == 'subject'} selected="selected"{/if}>{lang}wcf.search.sortBy.subject{/lang}</option>
											<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wcf.search.sortBy.creationDate{/lang}</option>
											<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.search.sortBy.author{/lang}</option>
										</select>
										
										<select name="sortOrder">
											<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
											<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
										</select>
									</div>
									{if $additionalDisplayOptions|isset}{@$additionalDisplayOptions}{/if}
								</div>
							</fieldset>
						</div>
					</div>
					
					<div class="formGroup">
						<div class="formGroupLabel">
							{lang}wcf.search.type{/lang}
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.search.type{/lang}</legend>
								<div class="formField">
									<ul class="formOptions">
									{foreach from=$types key=type item=typeObj}
										{if $typeObj->isAccessible()}
											<li><label><input id="{$type}" type="checkbox" name="types[]" value="{$type}"{if $type|in_array:$selectedTypes} checked="checked"{/if} {if $typeObj->getFormTemplateName()}onclick="showSearchForm('{$type}Form', this.checked)" {/if}/> {lang}wcf.search.type.{$type}{/lang}</label></li>
										{/if}
									{/foreach}
									</ul>
								</div>
							</fieldset>
						</div>
					</div>
				</fieldset>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				{include file='captcha'}
				
				{foreach from=$types key=type item=typeObj}
					{if $typeObj->isAccessible() && $typeObj->getFormTemplateName()}
						<fieldset id="{$type}Form">
							<legend>{lang}wcf.search.type.{$type}{/lang}</legend>
							
							<div>{include file=$typeObj->getFormTemplateName()}</div>
						
							{if !$type|in_array:$selectedTypes}
								<script type="text/javascript">
									//<![CDATA[
									showSearchForm('{$type}Form', false);
									//]]>
								</script>
							{/if}
						</fieldset>
					{/if}
				{/foreach}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
		</div>
	</form>
	
	{if $additionalBoxes2|isset}{@$additionalBoxes2}{/if}

</div>

{include file='footer' sandbox=false}
</body>
</html>