{include file='header'}

{if $action == 'add'}{assign var=dataPermission value="admin.board.canAddBoard"}
{else}{assign var=dataPermission value="admin.board.canEditBoard"}
{/if}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabMenu.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var tabMenu = new TabMenu();
	onloadEvents.push(function() { tabMenu.showSubTabMenu("{$activeTabMenuItem}") });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WBB_DIR}icon/board{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.board.{@$action}{/lang}</h2>
		{if $boardID|isset}<p>{lang}{$board->title}{/lang}</p>{/if}
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{if $action == 'add'}{lang}wbb.acp.board.add.success{/lang}{else}{lang}wbb.acp.board.edit.success{/lang}{/if}</p>	
{/if}

<script type="text/javascript" src="{@RELATIVE_WBB_DIR}acp/js/PermissionList.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var language = new Object();
	
	{literal}
	// static
	language['wbb.acp.board.permissions.permissionsFor'] = '{lang}wbb.acp.board.permissions.permissionsFor{/lang}';
	language['wbb.acp.board.permissions.fullControl'] = '{lang}wbb.acp.board.permissions.fullControl{/lang}';
	{/literal}
	
	// dynamic
	{foreach from=$moderatorSettings item=moderatorSetting}
		language['wbb.acp.board.permissions.{@$moderatorSetting}'] = '{lang}wbb.acp.board.permissions.{@$moderatorSetting}{/lang}';
	{/foreach}
	{foreach from=$permissionSettings item=permissionSetting}
		language['wbb.acp.board.permissions.{@$permissionSetting}'] = '{lang}wbb.acp.board.permissions.{@$permissionSetting}{/lang}';
	{/foreach}
	
	function setBoardType(newType) {
		switch (newType) {
			case 0:
				showOptions('prefix', 'filter', 'style', 'settings');
				hideOptions('externalURLDiv');
				break;
			case 1:
				showOptions('style');
				hideOptions('externalURLDiv', 'prefix', 'filter', 'settings');
				break;
			case 2:
				showOptions('externalURLDiv');
				hideOptions('prefix', 'filter', 'style', 'settings');
				break;
		}
	}
	onloadEvents.push(function() { setBoardType({@$boardType}); });
	
	var permissions = new Array();
	{assign var=i value=0}
	{foreach from=$permissions item=permission}
		permissions[{@$i}] = new Object();
		permissions[{@$i}]['name'] = '{@$permission.name|encodeJS}';
		permissions[{@$i}]['type'] = '{@$permission.type}';
		permissions[{@$i}]['id'] = '{@$permission.id}';
		permissions[{@$i}]['settings'] = new Object();
		permissions[{@$i}]['settings']['fullControl'] = -1;
		
		{foreach from=$permission.settings key=setting item=value}
			{if $setting != 'name' && $setting != 'type' && $setting != 'id'}
				permissions[{@$i}]['settings']['{@$setting}'] = {@$value};
			{/if}
		{/foreach}
		{assign var=i value=$i+1}
	{/foreach}
	
	var moderators = new Array();
	{assign var=i value=0}
	{foreach from=$moderators item=moderator}
		moderators[{@$i}] = new Object();
		moderators[{@$i}]['name'] = '{@$moderator.name|encodeJS}';
		moderators[{@$i}]['type'] = '{@$moderator.type}';
		moderators[{@$i}]['id'] = '{@$moderator.id}';
		moderators[{@$i}]['settings'] = new Object();
		moderators[{@$i}]['settings']['fullControl'] = -1;
		
		{foreach from=$moderator.settings key=setting item=value}
			{if $setting != 'name' && $setting != 'type' && $setting != 'id'}
				moderators[{@$i}]['settings']['{@$setting}'] = {@$value};
			{/if}
		{/foreach}
		{assign var=i value=$i+1}
	{/foreach}
	
	var moderatorSettings = new Array({implode from=$moderatorSettings item=moderatorSetting}'{@$moderatorSetting}'{/implode});
	var permissionSettings = new Array({implode from=$permissionSettings item=permissionSetting}'{@$permissionSetting}'{/implode});	
	
	onloadEvents.push(function() {
		// moderators
		var list1 = new PermissionList('moderator', moderators, moderatorSettings);
		// user/group permissions
		var list2 = new PermissionList('permission', permissions, permissionSettings);
		// add onsubmit event
		document.getElementById('boardAddForm').onsubmit = function() { 
			if (suggestion.selectedIndex != -1) return false;
			if (list1.inputHasFocus || list2.inputHasFocus) return false;
			list1.submit(this); list2.submit(this);
		};
	});
	
	//]]>
</script>
<div class="contentHeader">
	
	<div class="largeButtons">
		<ul><li><a href="index.php?page=BoardList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wbb.acp.menu.link.content.board.view{/lang}"><img src="{@RELATIVE_WBB_DIR}icon/boardM.png" alt="" /> <span>{lang}wbb.acp.menu.link.content.board.view{/lang}</span></a></li></ul>
	</div>
</div>

<form method="post" action="index.php?form=Board{@$action|ucfirst}" id="boardAddForm">
	{if $boardID|isset && $boardQuickJumpOptions|count > 1}
		<fieldset>
			<legend>{lang}wbb.acp.board.edit{/lang}</legend>
			<div class="formElement">
				<div class="formFieldLabel">
					<label for="boardChange">{lang}wbb.acp.board.edit{/lang}</label>
				</div>
				<div class="formField">
					<select id="boardChange" onchange="document.location.href=fixURL('index.php?form=BoardEdit&amp;boardID='+this.options[this.selectedIndex].value+'&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}')">
						{htmloptions options=$boardQuickJumpOptions selected=$boardID disableEncoding=true}
					</select>
				</div>
			</div>
		</fieldset>
	{/if}
	
	<div class="tabMenu">
		<ul>
			{if $this->user->getPermission($dataPermission)}<li id="data"><a onclick="tabMenu.showSubTabMenu('data');"><span>{lang}wbb.acp.board.data{/lang}</span></a></li>{/if}
			{if $this->user->getPermission('admin.board.canEditPermissions')}<li id="permissions"><a onclick="tabMenu.showSubTabMenu('permissions');"><span>{lang}wbb.acp.board.permissions{/lang}</span></a></li>{/if}
			{if $this->user->getPermission('admin.board.canEditModerators')}<li id="moderators"><a onclick="tabMenu.showSubTabMenu('moderators');"><span>{lang}wbb.acp.board.moderators{/lang}</span></a></li>{/if}
			{if $additionalTabs|isset}{@$additionalTabs}{/if}
		</ul>
	</div>
	<div class="subTabMenu">
		<div class="containerHead"><div> </div></div>
	</div>
	
	{if $this->user->getPermission($dataPermission)}
		<div class="border tabMenuContent hidden" id="data-content">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.acp.board.data{/lang}</h3>
				
				<fieldset>
					<legend>{lang}wbb.acp.board.boardType{/lang}</legend>
					<div class="formElement{if $errorField == 'boardType'} formError{/if}">
						<ul class="formOptions">
							<li><label><input onclick="if (IS_SAFARI) setBoardType(0)" onfocus="setBoardType(0)" type="radio" name="boardType" value="0" {if $boardType == 0}checked="checked" {/if}/> {lang}wbb.acp.board.boardType.0{/lang}</label></li>
							<li><label><input onclick="if (IS_SAFARI) setBoardType(1)" onfocus="setBoardType(1)" type="radio" name="boardType" value="1" {if $boardType == 1}checked="checked" {/if}/> {lang}wbb.acp.board.boardType.1{/lang}</label></li>
							<li><label><input onclick="if (IS_SAFARI) setBoardType(2)" onfocus="setBoardType(2)" type="radio" name="boardType" value="2" {if $boardType == 2}checked="checked" {/if}/> {lang}wbb.acp.board.boardType.2{/lang}</label></li>
						</ul>
						{if $errorField == 'boardType'}
							<p class="innerError">
								{if $errorType == 'invalid'}{lang}wbb.acp.board.error.boardType.invalid{/lang}{/if}
							</p>
						{/if}
					</div>
				</fieldset>
				
				<fieldset>
					<legend>{lang}wbb.acp.board.data.general{/lang}</legend>
					
					<div class="formElement{if $errorField == 'title'} formError{/if}">
						<div class="formFieldLabel">
							<label for="title">{lang}wbb.acp.board.title{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="title" name="title" value="{$title}" />
							{if $errorField == 'title'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
			
					<div id="descriptionDiv" class="formElement">
						<div class="formFieldLabel">
							<label for="description">{lang}wbb.acp.board.description{/lang}</label>
						</div>
						<div class="formField">
							<textarea id="description" name="description" cols="40" rows="10">{$description}</textarea>
							<label><input type="checkbox" name="allowDescriptionHtml" value="1" {if $allowDescriptionHtml}checked="checked" {/if}/> {lang}wbb.acp.board.allowDescriptionHtml{/lang}</label>
						</div>
					</div>
					
					<div id="externalURLDiv" class="formElement{if $errorField == 'externalURL'} formError{/if}">
						<div class="formFieldLabel">
							<label for="externalURL">{lang}wbb.acp.board.externalURL{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="externalURL" name="externalURL" value="{$externalURL}" />
							{if $errorField == 'externalURL'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					{if $additionalGeneralFields|isset}{@$additionalGeneralFields}{/if}
				</fieldset>
				
				<fieldset>
					<legend>{lang}wbb.acp.board.data.position{/lang}</legend>
					
					{if $boardOptions|count > 0}
						<div class="formElement{if $errorField == 'parentID'} formError{/if}" id="parentIDDiv">
							<div class="formFieldLabel">
								<label for="parentID">{lang}wbb.acp.board.parentID{/lang}</label>
							</div>
							<div class="formField">
								<select name="parentID" id="parentID">
									<option value="0"></option>
									{htmlOptions options=$boardOptions disableEncoding=true selected=$parentID}
								</select>
								{if $errorField == 'parentID'}
									<p class="innerError">
										{if $errorType == 'invalid'}{lang}wbb.acp.board.error.parentID.invalid{/lang}{/if}
									</p>
								{/if}
							</div>
							<div class="formFieldDesc hidden" id="parentIDHelpMessage">
								<p>{lang}wbb.acp.board.parentID.description{/lang}</p>
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('parentID');
						//]]></script>
					{/if}
			
					<div class="formElement{if $errorField == 'position'} formError{/if}" id="positionDiv">
						<div class="formFieldLabel">
							<label for="position">{lang}wbb.acp.board.position{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="position" name="position" value="{@$position}" />
							{if $errorField == 'position'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="positionHelpMessage">
							<p>{lang}wbb.acp.board.position.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('position');
					//]]></script>
					
					<div class="formElement" id="invisibleDiv">
						<div class="formField">
							<label id="invisible"><input type="checkbox" name="invisible" value="1" {if $invisible}checked="checked" {/if}/> {lang}wbb.acp.board.invisible{/lang}</label>
						</div>
						<div class="formFieldDesc hidden" id="invisibleHelpMessage">
							<p>{lang}wbb.acp.board.invisible.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('invisible');
					//]]></script>
					
					{if $additionalPositionFields|isset}{@$additionalPositionFields}{/if}
				</fieldset>
				
				<fieldset id="settings">
					<legend>{lang}wbb.acp.board.data.settings{/lang}</legend>
					
					<div class="formElement">
						<div class="formField">
							<label id="closed"><input type="checkbox" name="closed" value="1" {if $closed}checked="checked" {/if}/> {lang}wbb.acp.board.closed{/lang}</label>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label id="countUserPosts"><input type="checkbox" name="countUserPosts" value="1" {if $countUserPosts}checked="checked" {/if}/> {lang}wbb.acp.board.countUserPosts{/lang}</label>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label id="showSubBoards"><input type="checkbox" name="showSubBoards" value="1" {if $showSubBoards}checked="checked" {/if}/> {lang}wbb.acp.board.showSubBoards{/lang}</label>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label id="searchable"><input type="checkbox" name="searchable" value="1" {if $searchable}checked="checked" {/if}/> {lang}wbb.acp.board.searchable{/lang}</label>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label id="searchableForSimilarThreads"><input type="checkbox" name="searchableForSimilarThreads" value="1" {if $searchableForSimilarThreads}checked="checked" {/if}/> {lang}wbb.acp.board.searchableForSimilarThreads{/lang}</label>
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label id="ignorable"><input type="checkbox" name="ignorable" value="1" {if $ignorable}checked="checked" {/if}/> {lang}wbb.acp.board.ignorable{/lang}</label>
						</div>
					</div>
					
					{if MODULE_THREAD_MARKING_AS_DONE}
						<div class="formElement">
							<div class="formField">
								<label><input type="checkbox" name="enableMarkingAsDone" value="1" {if $enableMarkingAsDone == 1}checked="checked" {/if}/> {lang}wbb.acp.board.enableMarkingAsDone{/lang}</label>
							</div>
						</div>
					{/if}
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="enableRating">{lang}wbb.acp.board.rating{/lang}</label>
						</div>
						<div class="formField">
							<select name="enableRating" id="enableRating">
								<option value="-1"></option>
								<option value="1"{if $enableRating == 1} selected="selected"{/if}>{lang}wbb.acp.board.rating.enable{/lang}</option>
								<option value="0"{if $enableRating == 0} selected="selected"{/if}>{lang}wbb.acp.board.rating.disable{/lang}</option>
							</select>
						</div>
					</div>
					
					{if $additionalSettings|isset}{@$additionalSettings}{/if}
				</fieldset>
				
				<fieldset id="prefix">
					<legend>{lang}wbb.acp.board.data.prefixes{/lang}</legend>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="prefixMode">{lang}wbb.acp.board.prefixMode{/lang}</label>
						</div>
						<div class="formField">
							<select name="prefixMode" id="prefixMode">
								<option value="0"{if $prefixMode == 0} selected="selected"{/if}>{lang}wbb.acp.board.prefixMode.off{/lang}</option>
								<option value="1"{if $prefixMode == 1} selected="selected"{/if}>{lang}wbb.acp.board.prefixMode.global{/lang}</option>
								<option value="2"{if $prefixMode == 2} selected="selected"{/if}>{lang}wbb.acp.board.prefixMode.board{/lang}</option>
								<option value="3"{if $prefixMode == 3} selected="selected"{/if}>{lang}wbb.acp.board.prefixMode.combination{/lang}</option>
							</select>
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'prefixes'} formError{/if}">
						<div class="formFieldLabel">
							<label for="prefixes">{lang}wbb.acp.board.prefixes{/lang}</label>
						</div>
						<div class="formField">
							<textarea id="prefixes" name="prefixes" cols="40" rows="5">{$prefixes}</textarea>
							<label><input type="checkbox" name="prefixRequired" value="1" {if $prefixRequired}checked="checked" {/if}/> {lang}wbb.acp.board.prefixRequired{/lang}</label>
							{if $errorField == 'prefixes'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					{if $additionalPrefixFields|isset}{@$additionalPrefixFields}{/if}
				</fieldset>
				
				<fieldset id="filter">
					<legend>{lang}wbb.acp.board.data.filter{/lang}</legend>
					
					<div class="formElement{if $errorField == 'daysPrune'} formError{/if}">
						<div class="formFieldLabel">
							<label for="daysPrune">{lang}wbb.acp.board.daysPrune{/lang}</label>
						</div>
						<div class="formField">
							<select name="daysPrune" id="daysPrune">
								<option value=""></option>
								<option value="1"{if $daysPrune == 1} selected="selected"{/if}>{lang}wbb.board.filterByDate.1{/lang}</option>
								<option value="3"{if $daysPrune == 3} selected="selected"{/if}>{lang}wbb.board.filterByDate.3{/lang}</option>
								<option value="7"{if $daysPrune == 7} selected="selected"{/if}>{lang}wbb.board.filterByDate.7{/lang}</option>
								<option value="14"{if $daysPrune == 14} selected="selected"{/if}>{lang}wbb.board.filterByDate.14{/lang}</option>
								<option value="30"{if $daysPrune == 30} selected="selected"{/if}>{lang}wbb.board.filterByDate.30{/lang}</option>
								<option value="60"{if $daysPrune == 60} selected="selected"{/if}>{lang}wbb.board.filterByDate.60{/lang}</option>
								<option value="100"{if $daysPrune == 100} selected="selected"{/if}>{lang}wbb.board.filterByDate.100{/lang}</option>
								<option value="365"{if $daysPrune == 365} selected="selected"{/if}>{lang}wbb.board.filterByDate.365{/lang}</option>
								<option value="1000"{if $daysPrune == 1000} selected="selected"{/if}>{lang}wbb.board.filterByDate.1000{/lang}</option>
							</select>
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'sortField'} formError{/if}">
						<div class="formFieldLabel">
							<label for="sortField">{lang}wbb.acp.board.sortField{/lang}</label>
						</div>
						<div class="formField">
							<select name="sortField" id="sortField">
								<option value=""></option>
								<option value="prefix"{if $sortField == 'prefix'} selected="selected"{/if}>{lang}wbb.board.sortBy.prefix{/lang}</option>
								<option value="topic"{if $sortField == 'topic'} selected="selected"{/if}>{lang}wbb.board.sortBy.topic{/lang}</option>
								{if MODULE_ATTACHMENT}<option value="attachments"{if $sortField == 'attachments'} selected="selected"{/if}>{lang}wbb.board.sortBy.attachments{/lang}</option>{/if}
								{if MODULE_POLL}<option value="polls"{if $sortField == 'polls'} selected="selected"{/if}>{lang}wbb.board.sortBy.polls{/lang}</option>{/if}
								<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wbb.board.sortBy.starter{/lang}</option>
								<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wbb.board.sortBy.startTime{/lang}</option>
								<option value="ratingResult"{if $sortField == 'ratingResult'} selected="selected"{/if}>{lang}wbb.board.sortBy.rating{/lang}</option>
								<option value="replies"{if $sortField == 'replies'} selected="selected"{/if}>{lang}wbb.board.sortBy.replies{/lang}</option>
								<option value="views"{if $sortField == 'views'} selected="selected"{/if}>{lang}wbb.board.sortBy.views{/lang}</option>
								<option value="lastPostTime"{if $sortField == 'lastPostTime'} selected="selected"{/if}>{lang}wbb.board.sortBy.lastPostTime{/lang}</option>
							</select>
							{if $errorField == 'sortField'}
								<p class="innerError">
									{if $errorType == 'invalid'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'sortOrder'} formError{/if}">
						<div class="formFieldLabel">
							<label for="sortOrder">{lang}wbb.acp.board.sortOrder{/lang}</label>
						</div>
						<div class="formField">
							<select name="sortOrder" id="sortOrder">
								<option value=""></option>
								<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
								<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
							</select>
							{if $errorField == 'sortOrder'}
								<p class="innerError">
									{if $errorType == 'invalid'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'postSortOrder'} formError{/if}">
						<div class="formFieldLabel">
							<label for="postSortOrder">{lang}wbb.acp.board.postSortOrder{/lang}</label>
						</div>
						<div class="formField">
							<select name="postSortOrder" id="postSortOrder">
								<option value=""></option>
								<option value="ASC"{if $postSortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
								<option value="DESC"{if $postSortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
							</select>
							{if $errorField == 'postSortOrder'}
								<p class="innerError">
									{if $errorType == 'invalid'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="threadsPerPage">{lang}wbb.acp.board.threadsPerPage{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="threadsPerPage" name="threadsPerPage" value="{@$threadsPerPage}" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="postsPerPage">{lang}wbb.acp.board.postsPerPage{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="postsPerPage" name="postsPerPage" value="{@$postsPerPage}" />
						</div>
					</div>
					
					{if $additionalFilterFields|isset}{@$additionalFilterFields}{/if}
				</fieldset>
				
				<fieldset id="style">
					<legend>{lang}wbb.acp.board.data.style{/lang}</legend>

					{if $availableStyles|count > 1}
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="styleID">{lang}wbb.acp.board.styleID{/lang}</label>
							</div>
							<div class="formField">
								<select name="styleID" id="styleID">
									<option value="0"></option>
									{htmlOptions options=$availableStyles selected=$styleID}
								</select>
								<label><input type="checkbox" name="enforceStyle" value="1" {if $enforceStyle}checked="checked" {/if}/> {lang}wbb.acp.board.enforceStyle{/lang}</label>
							</div>
						</div>
					{/if}
					
					<div class="formElement" id="imageDiv">
						<div class="formFieldLabel">
							<label for="image">{lang}wbb.acp.board.image{/lang}</label>
						</div>
						<div class="formField">	
							<input type="text" class="inputText" id="image" name="image" value="{$image}" />
						</div>
						<div class="formFieldDesc hidden" id="imageHelpMessage">
							<p>{lang}wbb.acp.board.image.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">
						//<![CDATA[
						inlineHelp.register('image');
						//]]>
					</script>
					
					<div class="formElement" id="imageNewDiv">
						<div class="formFieldLabel">
							<label for="imageNew">{lang}wbb.acp.board.imageNew{/lang}</label>
						</div>
						<div class="formField">	
							<input type="text" class="inputText" id="imageNew" name="imageNew" value="{$imageNew}" />
						</div>
						<div class="formFieldDesc hidden" id="imageNewHelpMessage">
							<p>{lang}wbb.acp.board.imageNew.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">
						//<![CDATA[
						inlineHelp.register('imageNew');
						//]]>
					</script>
					
					<div class="formElement" id="imageShowAsBackgroundDiv">
						<div class="formField">
							<label><input type="checkbox" name="imageShowAsBackground" value="1" {if $imageShowAsBackground == 1}checked="checked" {/if}/> {lang}wbb.acp.board.imageShowAsBackground{/lang}</label>
						</div>
						<div class="formFieldDesc hidden" id="imageShowAsBackgroundHelpMessage">
							<p>{lang}wbb.acp.board.imageShowAsBackground.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">
						//<![CDATA[
						inlineHelp.register('imageShowAsBackground');
						//]]>
					</script>
					
					<div class="formElement" id="imageBackgroundRepeatDiv">
						<div class="formFieldLabel">
							<label for="imageBackgroundRepeat">{lang}wbb.acp.board.imageBackgroundRepeat{/lang}</label>
						</div>
						<div class="formField">
							<select name="imageBackgroundRepeat" id="imageBackgroundRepeat">
								<option value="no-repeat"{if $imageBackgroundRepeat == 'no-repeat'} selected="selected"{/if}>{lang}wbb.acp.board.imageBackgroundRepeat.no{/lang}</option>
								<option value="repeat-y"{if $imageBackgroundRepeat == 'repeat-y'} selected="selected"{/if}>{lang}wbb.acp.board.imageBackgroundRepeat.vertical{/lang}</option>
								<option value="repeat-x"{if $imageBackgroundRepeat == 'repeat-x'} selected="selected"{/if}>{lang}wbb.acp.board.imageBackgroundRepeat.horizontal{/lang}</option>
								<option value="repeat"{if $imageBackgroundRepeat == 'repeat'} selected="selected"{/if}>{lang}wbb.acp.board.imageBackgroundRepeat.both{/lang}</option>
							</select>
						</div>
						<div class="formFieldDesc hidden" id="imageBackgroundRepeatHelpMessage">
							<p>{lang}wbb.acp.board.imageBackgroundRepeat.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">
						//<![CDATA[
						inlineHelp.register('imageBackgroundRepeat');
						//]]>
					</script>
					
					{if $additionalStyleFields|isset}{@$additionalStyleFields}{/if}
				</fieldset>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
	{/if}
	
	{if $this->user->getPermission('admin.board.canEditPermissions')}
		<div class="border tabMenuContent hidden" id="permissions-content">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.acp.board.permissions{/lang}</h3>
		
				<div class="formElement">
					<div class="formFieldLabel" id="permissionTitle">
						{lang}wbb.acp.board.permissions.title{/lang}
					</div>
					<div class="formField"><div id="permission" class="accessRights container-4"></div></div>
				</div>
				<div class="formElement">
					<div class="formField">	
						<input id="permissionAddInput" type="text" name="" value="" class="inputText accessRightsInput" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.setSource('index.php?page=BoardPermissionsObjectsSuggest{@SID_ARG_2ND_NOT_ENCODED}');
							suggestion.enableIcon(true);
							suggestion.init('permissionAddInput');
							//]]>
						</script>
						<input id="permissionAddButton" type="button" value="{lang}wbb.acp.board.permissions.add{/lang}" />
					</div>
				</div>
				
				<div class="formElement" style="display: none;">
					<div class="formFieldLabel">
						<div id="permissionSettingsTitle" class="accessRightsTitle"></div>
					</div>
					<div class="formField">
						<div id="permissionHeader" class="accessRightsHeader">
							<span class="deny">{lang}wbb.acp.board.permissions.deny{/lang}</span>
							<span class="allow">{lang}wbb.acp.board.permissions.allow{/lang}</span>
						</div>
						<div id="permissionSettings" class="accessRights container-4"></div>
					</div>
				</div>
			</div>
		</div>
	{/if}
	
	{if $this->user->getPermission('admin.board.canEditModerators')}
		<div class="border tabMenuContent hidden" id="moderators-content">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.acp.board.moderators{/lang}</h3>
				
				<div class="formElement">
					<div class="formFieldLabel" id="moderatorTitle">
						{lang}wbb.acp.board.permissions.title{/lang}
					</div>
					<div class="formField"><div id="moderator" class="accessRights container-4"></div></div>
				</div>
				<div class="formElement">
					<div class="formField">	
						<input id="moderatorAddInput" type="text" name="" value="" class="inputText accessRightsInput" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.init('moderatorAddInput');
							//]]>
						</script>
						<input id="moderatorAddButton" type="button" value="{lang}wbb.acp.board.permissions.add{/lang}" />
					</div>
				</div>
				
				<div class="formElement" style="display: none;">
					<div class="formFieldLabel">
						<div id="moderatorSettingsTitle" class="accessRightsTitle"></div>
					</div>
					<div class="formField">
						<div id="moderatorHeader" class="accessRightsHeader">
							<span class="deny">{lang}wbb.acp.board.permissions.deny{/lang}</span>
							<span class="allow">{lang}wbb.acp.board.permissions.allow{/lang}</span>
						</div>
						<div id="moderatorSettings" class="accessRights container-4"></div>
					</div>
				</div>
			</div>
		</div>
	{/if}
	
	{if $additionalTabContents|isset}{@$additionalTabContents}{/if}
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $boardID|isset}<input type="hidden" name="boardID" value="{@$boardID}" />{/if}
 		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
 	</div>
</form>

{include file='footer'}