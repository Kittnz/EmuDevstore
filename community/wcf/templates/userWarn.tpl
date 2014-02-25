{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.infraction.userWarning.add{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	{if $warningID != 0}
		<script type="text/javascript">
			//<![CDATA[
			onloadEvents.push(function() { disableOptions('title', 'points', 'expires') });
			//]]>
		</script>
	{/if}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}infractionWarningL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {lang}wcf.user.infraction.userWarning.add{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}

	<form method="post" action="index.php?form=UserWarn">
		<div class="border content">
			<div class="container-1">
				<fieldset>
					<legend>{lang}wcf.user.infraction.warning.general{/lang}</legend>
					
					<div class="formElement">
						<p class="formFieldLabel">{lang}wcf.user.username{/lang}</p>
						<p class="formField"><a href="index.php?page=User&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user->username}</a></p>
					</div>
					
					{if $object}
						<div class="formElement">
							<p class="formFieldLabel">{lang}wcf.user.infraction.userWarning.object{/lang}</p>
							<p class="formField"><a href="{$object->getURL()}{@SID_ARG_2ND}">{$object->getTitle()}</a></p>
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
									<li><label><input onclick="disableOptions('title', 'points', 'expires')" type="radio" name="warningID" value="{@$warning->warningID}" {if $warning->warningID == $warningID}checked="checked" {/if}/> {$warning->title} <span class="smallFont light">({lang}wcf.user.infraction.warning.points{/lang}: {#$warning->points}, {lang}wcf.user.infraction.warning.expires{/lang}: {if $warning->expires}{@$warning->expires+TIME_NOW|datediff}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if})</span></label></li>
								{/foreach}
							</ul></div>
						</div>
						
						{if $this->user->getPermission('admin.user.infraction.canWarnUserIndividual')}
							<div class="formElement">
								<div class="formField">
									<label><input onclick="enableOptions('title', 'points', 'expires')" type="radio" name="warningID" value="0" {if $warningID == 0}checked="checked" {/if}/> {lang}wcf.user.infraction.userWarning.warning.individual{/lang}</label>
								</div>
							</div>
						{/if}
					{/if}
					
					{if $this->user->getPermission('admin.user.infraction.canWarnUserIndividual')}
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
							<div class="formFieldDesc">
								<p>{lang}wcf.user.infraction.warning.title.description{/lang}</p>
							</div>
						</div>
						
						<div class="formElement" id="pointsDiv">
							<div class="formFieldLabel">
								<label for="points">{lang}wcf.user.infraction.warning.points{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="points" id="points" value="{@$points}" />
							</div>
							<div class="formFieldDesc">
								<p>{lang}wcf.user.infraction.warning.points.description{/lang}</p>
							</div>
						</div>
						
						<div class="formGroup" id="expiresDiv">
							<div class="formGroupLabel">
								<label for="expiresWeek">{lang}wcf.user.infraction.warning.expires{/lang}</label>
							</div>
							
							<div class="formGroupField">
								<fieldset>
									<legend><label for="expiresWeek">{lang}wcf.user.infraction.warning.expires{/lang}</label></legend>
									
									<div class="floatedElement">
										<div class="floatedElement">
											<label for="expiresWeek">{lang}wcf.global.date.weeks{/lang}</label>
											<input id="expiresWeek" class="inputText fourDigitInput" type="text" name="expiresWeek" value="{@$expiresWeek}" />
										</div>
										
										<div class="floatedElement">
											<label for="expiresDay">{lang}wcf.global.date.days{/lang}</label>
											<input id="expiresDay" class="inputText fourDigitInput" type="text" name="expiresDay" value="{@$expiresDay}" />
										</div>
										
										<div class="floatedElement">
											<label for="expiresHour">{lang}wcf.global.date.hours{/lang}</label>
											<input id="expiresHour" class="inputText fourDigitInput" type="text" name="expiresHour" value="{@$expiresHour}" />
										</div>
									</div>
								</fieldset>
								<div class="formFieldDesc">
									<p>{lang}wcf.user.infraction.warning.expires.description{/lang}</p>
								</div>
							</div>
							
						</div>
					{/if}
				</fieldset>
				
				<fieldset>
					<legend>{lang}wcf.user.infraction.userWarning.reason{/lang}</legend>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="reason">{lang}wcf.user.infraction.userWarning.reason{/lang}</label>
						</div>
						<div class="formField">
							<textarea name="reason" id="reason" rows="10" cols="40">{$reason}</textarea>
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.user.infraction.userWarning.reason.description{/lang}</p>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
			
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="userID" value="{@$userID}" />
			<input type="hidden" name="objectID" value="{@$objectID}" />
			<input type="hidden" name="objectType" value="{@$objectType}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>