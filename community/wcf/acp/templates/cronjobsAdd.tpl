{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjobs{$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.cronjobs.{$action}{/lang}</h2>
		<p>{lang}wcf.acp.cronjobs.subtitle{/lang}</p>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.cronjobs.{$action}.success{/lang}</p>	
{/if}

<p class="info">{lang}wcf.acp.cronjobs.intro{/lang}</p>

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?page=CronjobsList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.cronjobs.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsM.png" alt="" /> <span>{lang}wcf.acp.menu.link.cronjobs.view{/lang}</span></a></li>
			{if $action == 'edit'}<li><a href="index.php?action=CronjobExecute&amp;cronjobID={@$cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjobs.execute{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteM.png" alt="" /> <span>{lang}wcf.acp.cronjobs.execute{/lang}</span></a></li>{/if}
		</ul>
	</div>
</div>
<form method="post" action="index.php?form=Cronjobs{$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.cronjobs.edit.data{/lang}</legend>
				
				<div class="formElement{if $errorField == 'classPath'} formError{/if}" id="classPathDiv">
					<div class="formFieldLabel">
						<label for="classPath">{lang}wcf.acp.cronjobs.classPath{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="classPath" name="classPath" value="{$classPath}" />
						{if $errorField == 'classPath'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.acp.cronjobs.error.empty{/lang}{/if}
								{if $errorType == 'doesNotExist'}{lang}wcf.acp.cronjobs.error.doesNotExist{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="classPathHelpMessage">
						<p>{lang}wcf.acp.cronjobs.classPath.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('classPath');
				//]]></script>
				
				{* The packageName field which tells us what package installed this cron job 
				is not to be edited because it is either set automatically at the time the 
				package itself is being installed, or, in the case of a manual install of 
				this cron job, it is set to the name of the package which is the current 
				acp package.
				In contrast, the description field is being set to a language variable 
				in case this cronjob has been installed by a package. This is remembered 
				by setting the installedBySystem field to True; the field is being evaluated 
				when calling this template, and if it is True, the description is not to be edited. *}
				
				
				<div class="formElement" id="descriptionDiv">
					<div class="formFieldLabel">
						<label for="description">{lang}wcf.acp.cronjobs.description{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="description" name="description" value="{$description}" />
					</div>
					<div class="formFieldDesc hidden" id="descriptionHelpMessage">
						<p>{lang}wcf.acp.cronjobs.description.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('description');
				//]]></script>
				
				<div class="formCheckBox formElement" id="execMultipleDiv">
					<div class="formField">
						<label><input type="checkbox" id="execMultiple" name="execMultiple" value="1" {if $execMultiple == 1}checked="checked" {/if}/> {lang}wcf.acp.cronjobs.execMultiple{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="execMultipleHelpMessage">
						<p>{lang}wcf.acp.cronjobs.execMultiple.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('execMultiple');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.cronjobs.edit.timing{/lang}</legend>
				<div class="formElement{if $errorField == 'startMinute'} formError{/if}" id="startMinuteDiv">
					<div class="formFieldLabel">
						<label for="startMinute">{lang}wcf.acp.cronjobs.startMinute{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startMinute" name="startMinute" value="{$startMinute}" />
						{if $errorField == 'startMinute'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjobs.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startMinuteHelpMessage">
						<p>{lang}wcf.acp.cronjobs.startMinute.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMinute');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startHour'} formError{/if}" id="startHourDiv">
					<div class="formFieldLabel">
						<label for="startHour">{lang}wcf.acp.cronjobs.startHour{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startHour" name="startHour" value="{$startHour}" />
						{if $errorField == 'startHour'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjobs.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startHourHelpMessage">
						<p>{lang}wcf.acp.cronjobs.startHour.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startHour');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startDom'} formError{/if}" id="startDomDiv">
					<div class="formFieldLabel">
						<label for="startDom">{lang}wcf.acp.cronjobs.startDom{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startDom" name="startDom" value="{$startDom}" />
						{if $errorField == 'startDom'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjobs.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startDomHelpMessage">
						<p>{lang}wcf.acp.cronjobs.startDom.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startDom');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startMonth'} formError{/if}" id="startMonthDiv">
					<div class="formFieldLabel">
						<label for="startMonth">{lang}wcf.acp.cronjobs.startMonth{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startMonth" name="startMonth" value="{$startMonth}" />
						{if $errorField == 'startMonth'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjobs.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startMonthHelpMessage">
						<p>{lang}wcf.acp.cronjobs.startMonth.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startMonth');
				//]]></script>
				
				<div class="formElement{if $errorField == 'startDow'} formError{/if}" id="startDowDiv">
					<div class="formFieldLabel">
						<label for="startDow">{lang}wcf.acp.cronjobs.startDow{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="startDow" name="startDow" value="{$startDow}" />
						{if $errorField == 'startDow'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.acp.cronjobs.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="startDowHelpMessage">
						<p>{lang}wcf.acp.cronjobs.startDow.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('startDow');
				//]]></script>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $cronjobID|isset}<input type="hidden" name="cronjobID" value="{@$cronjobID}" />{/if}
	</div>
</form>

{include file='footer'}