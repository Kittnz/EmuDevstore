{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionSuspension{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.suspension.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.infraction.suspension.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=SuspensionList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionM.png" alt="" title="{lang}wcf.acp.menu.link.user.infraction.suspension.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.infraction.suspension.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=Suspension{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.user.infraction.warning.general{/lang}</legend>
				
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
					<div class="formFieldDesc hidden" id="titleHelpMessage">
						<p>{lang}wcf.acp.infraction.suspension.title.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formElement{if $errorField == 'points'} formError{/if}" id="pointsDiv">
					<div class="formFieldLabel">
						<label for="points">{lang}wcf.user.infraction.warning.points{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="points" id="points" value="{@$points}" />
						{if $errorField == 'points'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="pointsHelpMessage">
						<p>{lang}wcf.acp.infraction.suspension.points.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('points');
				//]]></script>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label for="fromDay">{lang}wcf.user.infraction.warning.expires{/lang}</label>
					</div>
					
					<div class="formGroupField">
						<fieldset>
							<legend><label for="expiresWeek">{lang}wcf.user.infraction.warn.expires{/lang}</label></legend>
							
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
							<p>{lang}wcf.acp.infraction.suspension.expires.description{/lang}</p>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.infraction.suspension.type{/lang}</legend>
				
				<div class="formElement{if $errorField == 'suspensionType'} formError{/if}" id="suspensionTypeDiv">
					<div class="formFieldLabel">
						<label for="suspensionType">{lang}wcf.acp.infraction.suspension.type{/lang}</label>
					</div>
					<div class="formField">
						<select name="suspensionType" id="suspensionType" onchange="this.form.submit();"{if $suspension|isset && $suspension->suspensions != 0} disabled="disabled"{/if}>
							<option value=""></option>
							{foreach from=$availableSuspensionTypes key=name item=availableSuspensionType}
								<option value="{$name}"{if $name == $suspensionType} selected="selected"{/if}>{lang}wcf.acp.infraction.suspension.type.{$name}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="suspensionTypeHelpMessage">
						<p>{lang}wcf.acp.infraction.suspension.type.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('suspensionType');
				//]]></script>
				
				{if $suspensionTypeObject && $suspensionTypeObject->getTemplateName()}
					{include file=$suspensionTypeObject->getTemplateName()}
				{/if}
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $suspensionID|isset}<input type="hidden" name="suspensionID" value="{@$suspensionID}" />{/if}
 	</div>
</form>

{include file='footer'}