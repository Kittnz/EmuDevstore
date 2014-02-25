{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/TemplateList.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var templateList = new TemplateList();
	onloadEvents.push(function() { templateList.init('templateID', 'quickSearch', 'editButton', 'deleteButton', true); });
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/templateSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.search.result{/lang}</h2>
	</div>
</div>

<p class="success">{lang}wcf.acp.template.search.success{/lang}</p>	

<form method="post" action="index.php?action=TemplateDelete" onsubmit="return false;">
	<div class="border content">
		<div class="container-1">
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
					<div class="formField">
						<select size="20" style="width: 99%" name="templateID[]" id="templateID">
							{foreach from=$templates item=template}
								<option value="{@$template.templateID}">{if $template.templatePackFolderName}{$template.templatePackFolderName}/{/if}{$template.templateName}{if $template.matches|isset} {lang}wcf.acp.template.search.result.matches{/lang}{/if}</option>
							{/foreach}
						</select>
						{if $this->user->getPermission('admin.template.canEditTemplate')}
							<input type="button" id="editButton" value="{lang}wcf.acp.template.edit{/lang}" onclick="templateList.edit();" />
						{/if}
						{if $this->user->getPermission('admin.template.canDeleteTemplate')}
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

{include file='footer'}