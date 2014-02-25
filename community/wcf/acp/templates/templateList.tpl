{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/TemplateList.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var templateList = new TemplateList();
	onloadEvents.push(function() { templateList.init('templateID', 'quickSearch', 'editButton', 'deleteButton', {if $templatePackID}true{else}false{/if}); });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/templateL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.view{/lang}</h2>
	</div>
</div>

{if $deletedTemplates}
	<p class="success">{lang}wcf.acp.template.delete.success{/lang}</p>	
{/if}

{if !$templatePackID}
	<p class="warning">{lang}wcf.acp.template.warning.canNotEditDefaultTemplates{/lang}</p>
{/if}

<div class="contentHeader">
	{if $this->user->getPermission('admin.template.canAddTemplate')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=TemplateAdd&amp;templatePackID={@$templatePackID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templateAddM.png" alt="" title="{lang}wcf.acp.template.add{/lang}" /> <span>{lang}wcf.acp.template.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

<form method="post" action="index.php?action=TemplateDelete" onsubmit="return false;">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.template.pack{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="templatePackID">{lang}wcf.acp.template.pack{/lang}</label>
					</div>
					<div class="formField">
						<select name="templatePackID" id="templatePackID" onchange="document.location.href=fixURL('index.php?page=TemplateList&amp;templatePackID='+this.options[this.selectedIndex].value+'&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}')">
							<option value="0">{lang}wcf.acp.template.pack.default{/lang}</option>
							{htmlOptions options=$templatePacks selected=$templatePackID}
						</select>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.template.view.count{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="quickSearch">{lang}wcf.acp.template.view.quickSearch{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="quickSearch" id="quickSearch" value="" />
					</div>
				</div>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="templateID">{lang}wcf.acp.template.name{/lang}</label>
					</div>
					<div class="formField longSelect">
						<select size="20" name="templateID[]" id="templateID">
							{htmlOptions options=$templates}
						</select>
						{if $this->user->getPermission('admin.template.canEditTemplate')}
							<input type="button" id="editButton" value="{lang}wcf.acp.template.edit{/lang}" onclick="templateList.edit();" />
						{/if}
						{if $templatePackID && $this->user->getPermission('admin.template.canDeleteTemplate')}
							<input type="button" id="deleteButton" value="{lang}wcf.acp.template.delete{/lang}" onclick="if (confirm('{lang}wcf.acp.template.delete.sure{/lang}')) templateList.remove();" />
						{/if}
					</div>
				</div>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
		
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		{@SID_INPUT_TAG}
	</div>
</form>

<div class="contentFooter">
	{if $this->user->getPermission('admin.template.canAddTemplate')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=TemplateAdd&amp;templatePackID={@$templatePackID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templateAddM.png" alt="" title="{lang}wcf.acp.template.add{/lang}" /> <span>{lang}wcf.acp.template.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{include file='footer'}