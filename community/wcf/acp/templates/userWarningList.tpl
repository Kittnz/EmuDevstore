{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Calendar.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionWarningL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.userWarning.view{/lang}</h2>
	</div>
</div>

{if $deletedUserWarningID}
	<p class="success">{lang}wcf.acp.infraction.userWarning.delete.success{/lang}</p>	
{/if}

<form method="get" action="index.php">
	<fieldset>
		<legend>{lang}wcf.acp.infraction.userWarning.filter{/lang}</legend>

		<div class="formElement">
			<div class="formFieldLabel">
				<label for="username">{lang}wcf.user.username{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" id="username" name="username" value="{$username}" />
				<script type="text/javascript">
					//<![CDATA[
					suggestion.enableMultiple(false);
					suggestion.init('username');
					//]]>
				</script>
			</div>
		</div>
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="judge">{lang}wcf.user.infraction.userWarning.jugde{/lang}</label>
			</div>
			<div class="formField">
				<input type="text" class="inputText" id="judge" name="judge" value="{$judge}" />
				<script type="text/javascript">
					//<![CDATA[
					suggestion.enableMultiple(false);
					suggestion.init('judge');
					//]]>
				</script>
			</div>
		</div>

		{if $availableWarnings|count > 0}
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="warningID">{lang}wcf.user.infraction.userWarning.warning{/lang}</label>
				</div>
				<div class="formField">
					<select name="warningID" id="warningID">
						<option value=""></option>
						{htmlOptions options=$availableWarnings selected=$warningID}
					</select>
				</div>
			</div>
		{/if}
		
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="status">{lang}wcf.acp.infraction.userWarning.status{/lang}</label>
			</div>
			<div class="formField">
				<select name="status" id="status">
					<option value=""></option>
					<option value="active"{if $status == 'active'} selected="selected"{/if}>{lang}wcf.acp.infraction.userWarning.status.active{/lang}</option>
					<option value="expired"{if $status == 'expired'} selected="selected"{/if}>{lang}wcf.acp.infraction.userWarning.status.expired{/lang}</option>
				</select>
			</div>
		</div>
		
		<div class="formGroup">
			<div class="formGroupLabel">
				<label for="fromDay">{lang}wcf.acp.infraction.userWarning.period{/lang}</label>
			</div>
			
			<div class="formGroupField">
				<fieldset>
					<legend><label for="fromDay">{lang}wcf.acp.infraction.userWarning.period{/lang}</label></legend>
					
					<div class="floatedElement">
						<div class="floatedElement">
							<p> {lang}wcf.acp.infraction.userWarning.period.start{/lang}</p>
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
							<p> {lang}wcf.acp.infraction.userWarning.period.end{/lang}</p>
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
		
		{if $additionalFields|isset}{@$additionalFields}{/if}

	</fieldset>
	
	<div class="formSubmit">
		<input type="hidden" name="page" value="UserWarningList" />
		<input type="hidden" name="sortField" value="{@$sortField}" />
		<input type="hidden" name="sortOrder" value="{@$sortOrder}" />
		<input type="hidden" name="pageNo" value="{@$pageNo}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
	</div>
</form>

{assign var=parameterURLString value=''}
{if $username}{capture append=parameterURLString}&username={@$username|rawurlencode}{/capture}{/if}
{if $judge}{capture append=parameterURLString}&judge={@$judge|rawurlencode}{/capture}{/if}
{if $warningID}{capture append=parameterURLString}&warningID={@$warningID}{/capture}{/if}
{if $status}{capture append=parameterURLString}&status={@$status|rawurlencode}{/capture}{/if}
{if $fromDay}{capture append=parameterURLString}&fromDay={@$fromDay}{/capture}{/if}
{if $fromMonth}{capture append=parameterURLString}&fromMonth={@$fromMonth}{/capture}{/if}
{if $fromYear}{capture append=parameterURLString}&fromYear={@$fromYear}{/capture}{/if}
{if $untilDay}{capture append=parameterURLString}&untilDay={@$untilDay}{/capture}{/if}
{if $untilMonth}{capture append=parameterURLString}&untilMonth={@$untilMonth}{/capture}{/if}
{if $untilYear}{capture append=parameterURLString}&untilYear={@$untilYear}{/capture}{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserWarningList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$parameterURLString&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
</div>

{if $userWarnings|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.infraction.userWarning.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnUserWarningID{if $sortField == 'userWarningID'} active{/if}" colspan="2"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=userWarningID&amp;sortOrder={if $sortField == 'userWarningID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.userWarning.userWarningID{/lang}{if $sortField == 'userWarningID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningTitle{if $sortField == 'title'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.title{/lang}{if $sortField == 'title'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningObject{if $sortField == 'objectID'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=objectID&amp;sortOrder={if $sortField == 'objectID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.userWarning.object{/lang}{if $sortField == 'objectID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningUser{if $sortField == 'userID'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=userID&amp;sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'userID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningJudge{if $sortField == 'judgeID'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=judgeID&amp;sortOrder={if $sortField == 'judgeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.userWarning.jugde{/lang}{if $sortField == 'judgeID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningTime{if $sortField == 'time'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.userWarning.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningPoints{if $sortField == 'points'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=points&amp;sortOrder={if $sortField == 'points' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.points{/lang}{if $sortField == 'points'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserWarningExpires{if $sortField == 'expires'} active{/if}"><div><a href="index.php?page=UserWarningList&amp;pageNo={@$pageNo}&amp;sortField=expires&amp;sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{$parameterURLString}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.expires{/lang}{if $sortField == 'expires'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$userWarnings item=userWarning}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
							<a href="index.php?form=UserWarningEdit&amp;userWarningID={@$userWarning->userWarningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.infraction.canDeleteWarning')}
							<a onclick="return confirm('{lang}wcf.acp.infraction.userWarning.delete.sure{/lang}')" href="index.php?action=UserWarningDelete&amp;userWarningID={@$userWarning->userWarningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.infraction.userWarning.delete{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.infraction.userWarning.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$userWarning->userWarningID|isset}{@$additionalButtons.$userWarning->userWarningID}{/if}
					</td>
					<td class="columnUserWarningID columnID">{@$userWarning->userWarningID}</td>
					<td class="columnUserWarningTitle columnText">
						{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
							<a href="index.php?form=UserWarningEdit&amp;userWarningID={@$userWarning->userWarningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}">{$userWarning->title}</a>
						{else}
							{$userWarning->title}
						{/if}
					</td>
					<td class="columnUserWarningObject columnText">
						{if $userWarning->object}
							<a href="../{$userWarning->object->getURL()}">{$userWarning->object->getTitle()}</a>
						{/if}
					</td>
					<td class="columnUserWarningUser columnText">
						{if $userWarning->userID && $this->user->getPermission('admin.user.canEditUser')}
							<a href="index.php?form=UserEdit&amp;userID={@$userWarning->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.user.edit{/lang}">{$userWarning->username}</a>
						{else}
							{$userWarning->username}
						{/if} 
					</td>
					<td class="columnUserWarningJudge columnText">
						{if $userWarning->judgeID && $this->user->getPermission('admin.user.canEditUser')}
							<a href="index.php?form=UserEdit&amp;userID={@$userWarning->judgeID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.user.edit{/lang}">{$userWarning->judgeUsername}</a>
						{else}
							{$userWarning->judgeUsername}
						{/if} 
					</td>
					<td class="columnUserWarningTime columnText">{@$userWarning->time|shorttime}</td>
					<td class="columnUserWarningPoints columnNumbers">{#$userWarning->points}</td>
					<td class="columnUserWarningExpires columnText">{if $userWarning->expires > 0}{@$userWarning->expires|shorttime}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if}</td>
					
					{if $additionalColumns.$userWarning->userWarningID|isset}{@$additionalColumns.$userWarning->userWarningID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.infraction.userWarning.view.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
