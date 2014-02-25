{include file="documentHeader"}
<head>
	<title>{lang}wcf.pm.editFolders{/lang} - {lang}wcf.pm.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{capture append=specialStyles}
		<link rel="stylesheet" type="text/css" media="screen" href="{@RELATIVE_WCF_DIR}style/extra/privateMessages{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css" />
	{/capture}
	{include file='headInclude' sandbox=false}
	<script type="text/javascript">
		//<![CDATA[
			var foldersByColor = new Hash();
			foldersByColor.set('', '{icon}pmFolderYellowM.png{/icon}');
			{foreach from=$availableColors item=availableColor}
				foldersByColor.set('{@$availableColor}', '{icon}pmFolder{@$availableColor|ucfirst}M.png{/icon}');
			{/foreach}
			changeColor = function(evt) {
				var select = evt.findElement();
				select.adjacent('img')[0].src = foldersByColor.get($F(select));
			}
		
			init = function() {
				$$('.color').invoke('observe', 'change', changeColor).invoke('observe', 'keyup', changeColor);	
			}
		
			document.observe("dom:loaded", init);
		//]]>
	</script>

</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
		<li><a href="index.php?page=PMList{@SID_ARG_2ND}"><img src="{icon}pmEmptyS.png{/icon}" alt="" /> <span>{lang}wcf.pm.title{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}pmFolderEditL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.pm.editFolders{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	{if $success|isset}
		<p class="success">
			{if $success == 'add'}{lang}wcf.pm.addFolder.success{/lang}{/if}
			{if $success == 'rename'}{lang}wcf.pm.renameFolders.success{/lang}{/if}
			{if $success == 'delete'}{lang}wcf.pm.deleteFolder.success{/lang}{/if}
		</p>
	{/if}
	
	{if $folders|count > 0}
		<form method="post" action="index.php?form=PMFolderEdit">
			<div class="border content">
				<div class="container-1">
					<div class="formGroup">
						<div class="formGroupLabel">
							<label>{lang}wcf.pm.existingFolders{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.pm.existingFolders{/lang}</legend>
								<ol class="itemList">
									{foreach from=$folders item=$folder}
										<li>
											<div class="buttons">
													<a href="index.php?form=PMFolderEdit&amp;delete={@$folder.folderID}{@SID_ARG_2ND}" onclick="return confirm('{lang}wcf.pm.deleteFolder.sure{/lang}')"><img src="{icon}deleteS.png{/icon}" alt="{lang}wcf.pm.deleteFolder{/lang}" title="{lang}wcf.pm.deleteFolder{/lang}" /></a>
											</div>
											<div class="itemListTitle {if $errorField == 'folderName'|concat:$folder.folderID} formError{/if}">
												<img src="{icon}pmFolder{$folder.color|ucfirst}M.png{/icon}" alt="" />
												<select name="colors[{@$folder.folderID}]" class="color">
													<option value="">{lang}wcf.pm.addFolder.color{/lang}</option>
													{foreach from=$availableColors item=availableColor}
														<option value="{@$availableColor}"{if $folder.color == $availableColor} selected="selected"{/if}>{lang}wcf.pm.addFolder.color.{@$availableColor}{/lang}</option>
													{/foreach}
												</select>
												<input type="text" class="inputText" name="folderNames[{@$folder.folderID}]" value="{if $folderNames[$folder.folderID]|isset}{$folderNames[$folder.folderID]}{else}{$folder.folderName}{/if}" />
												
												{if $errorField == 'folderName'|concat:$folder.folderID}
													<p class="innerError">
														{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
													</p>
												{/if}
											</div>
										</li>
									{/foreach}
								</ol>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
	
			<div class="formSubmit">
				<input type="submit" name="rename" value="{lang}wcf.global.button.submit{/lang}" />
				<input type="reset" value="{lang}wcf.global.button.reset{/lang}" />
				{@SID_INPUT_TAG}
			</div>
		</form>
	{/if}
		
	{if $folders|count < $this->user->getPermission('user.pm.maxFolders')}
		<form method="post" action="index.php?form=PMFolderEdit">
			<div class="border content">
				<div class="container-1">
					<div class="formGroup{if $errorField == 'folderName'} formError{/if}">
						<div class="formGroupLabel">
							<label for="folderName">{lang}wcf.pm.addFolder{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.pm.addFolder{/lang}</legend>
								<div class="floatContainer">
									<div class="floatedElement">
										<img src="{icon}pmFolderYellowM.png{/icon}" alt="" />
										<select name="color" class="color">
											<option value="">{lang}wcf.pm.addFolder.color{/lang}</option>
											{foreach from=$availableColors item=availableColor}
												<option value="{@$availableColor}"{if $color == $availableColor} selected="selected"{/if}>{lang}wcf.pm.addFolder.color.{@$availableColor}{/lang}</option>
											{/foreach}
										</select>
									</div>
									<div class="floatedElement{if $errorField == 'folderName'} formError{/if}">	
										<input type="text" class="inputText" name="folderName" id="folderName" value="{$folderName}" />
									</div>
								</div>
								{if $errorField == 'folderName'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'tooManyFolders'}{lang}wcf.pm.error.addFolder.tooManyFolders{/lang}{/if}
									</p>
								{/if}
							</fieldset>
						</div>
					</div>
				</div>
			</div>	
		
			<div class="formSubmit">
				<input type="submit" name="add" value="{lang}wcf.global.button.submit{/lang}" />
				<input type="reset" value="{lang}wcf.global.button.reset{/lang}" />
				{@SID_INPUT_TAG}
			</div>
		</form>
	{/if}

</div>
	
{include file='footer' sandbox=false}
</body>
</html>