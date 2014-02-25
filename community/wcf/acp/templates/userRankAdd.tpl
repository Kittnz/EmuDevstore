{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userRank{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.rank.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.rank.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UserRankList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userRankM.png" alt="" title="{lang}wcf.acp.menu.link.user.rank.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.rank.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UserRank{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.rank.data{/lang}</legend>
				<div class="formElement{if $errorField == 'title'} formError{/if}" id="titleDiv">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.acp.rank.title{/lang}</label>
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
						{lang}wcf.acp.rank.title.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formGroup">
					<div class="formGroupLabel">
						<label>{lang}wcf.acp.rank.image{/lang}</label>
					</div>
					<div class="formGroupField">
						<fieldset>
							<legend>{lang}wcf.acp.rank.image{/lang}</legend>
			
							<div class="formElement" id="imageDiv">
								<div class="formFieldLabel">
									<label for="image">{lang}wcf.acp.rank.image{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="image" id="image" value="{$image}" />
								</div>
								<div class="formFieldDesc hidden" id="imageHelpMessage">
									{lang}wcf.acp.rank.image.description{/lang}
								</div>
							</div>
							<script type="text/javascript">//<![CDATA[
								inlineHelp.register('image');
							//]]></script>
							
							<div class="formElement{if $errorField == 'repeatImage'} formError{/if}" id="repeatImageDiv">
								<div class="formFieldLabel">
									<label for="repeatImage">{lang}wcf.acp.rank.image.repeat{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="repeatImage" id="repeatImage" value="{@$repeatImage}" />
									{if $errorField == 'repeatImage'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										</p>
									{/if}
								</div>
								<div class="formFieldDesc hidden" id="repeatImageHelpMessage">
									{lang}wcf.acp.rank.image.repeat.description{/lang}
								</div>
							</div>
							<script type="text/javascript">//<![CDATA[
								inlineHelp.register('repeatImage');
							//]]></script>
							
							{if $rank|isset && $rank->rankImage}
								<div class="formElement">
									<div class="formFieldLabel">
										<label>{lang}wcf.acp.rank.image.current{/lang}</label>
									</div>
									<div class="formField">
										{@$rank->getImage()}
									</div>
								</div>
							{/if}
						</fieldset>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.rank.requirements{/lang}</legend>
				<div class="formElement{if $errorField == 'groupID'} formError{/if}" id="groupIDDiv">
					<div class="formFieldLabel">
						<label for="groupID">{lang}wcf.acp.rank.group{/lang}</label>
					</div>
					<div class="formField">
						<select name="groupID" id="groupID">
							{foreach from=$groups key=groupKey item=groupName}
								<option value="{@$groupKey}"{if $groupID == $groupKey} selected="selected"{/if}>{lang}{$groupName}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'groupID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="groupIDHelpMessage">
						{lang}wcf.acp.rank.group.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('groupID');
				//]]></script>
				
				<div class="formElement" id="neededPointsDiv">
					<div class="formFieldLabel">
						<label for="neededPoints">{lang}wcf.acp.rank.neededPoints{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" name="neededPoints" id="neededPoints" value="{@$neededPoints}" />
					</div>
					<div class="formFieldDesc hidden" id="neededPointsHelpMessage">
						{lang}wcf.acp.rank.neededPoints.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('neededPoints');
				//]]></script>
				
				<div class="formElement{if $errorField == 'gender'} formError{/if}" id="genderDiv">
					<div class="formFieldLabel">
						<label for="gender">{lang}wcf.acp.rank.gender{/lang}</label>
					</div>
					<div class="formField">
						<select name="gender" id="gender">
							<option value="0"></option>
							<option value="1"{if $gender == 1} selected="selected"{/if}>{lang}wcf.user.gender.male{/lang}</option>
							<option value="2"{if $gender == 2} selected="selected"{/if}>{lang}wcf.user.gender.female{/lang}</option>
						</select>
						{if $errorField == 'gender'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="genderHelpMessage">
						{lang}wcf.acp.rank.gender.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('gender');
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
 		{if $rankID|isset}<input type="hidden" name="rankID" value="{@$rankID}" />{/if}
 	</div>
</form>

{include file='footer'}