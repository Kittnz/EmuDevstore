{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/dbManageL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.db.manage{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $results|isset}
	<div class="success">
		{lang}wcf.acp.db.manage.success{/lang}
		
		{if $results|count}
			<ul class="smallFont" style="margin: 0">
				{foreach from=$results item=result}
					<li>{$result.Table}: {$result.Msg_text}</li>
				{/foreach}
			</ul>
		{/if}
	</div>
{/if}

<form method="post" action="index.php?form=DatabaseOperations">
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.db.manage.tables{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnMark"><div><label class="emptyHead"><input onclick="checkUncheckAll(document.getElementById('tables'))" type="checkbox" /></label></div></th>
					<th class="columnTablename"><div><span class="emptyHead">{lang}wcf.acp.db.manage.table.name{/lang}</span></div></th>
					<th class="columnTableRows"><div><span class="emptyHead">{lang}wcf.acp.db.manage.table.rows{/lang}</span></div></th>
					<th class="columnTableSize"><div><span class="emptyHead">{lang}wcf.acp.db.manage.table.size{/lang}</span></div></th>
					<th class="columnTableIndexSize"><div><span class="emptyHead">{lang}wcf.acp.db.manage.table.index.size{/lang}</span></div></th>
					<th class="columnTableOverhead"><div><span class="emptyHead">{lang}wcf.acp.db.manage.table.overhead{/lang}</span></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody id="tables">
				{foreach from=$tables item=table}
					<tr class="{cycle values="container-1,container-2"}">
						<td class="columnMark"><input id="{$table.Name}" name="tablenameArray[]" type="checkbox" value="{$table.Name}" {if $table.Name|in_array:$tablenameArray}checked="checked" {/if}/></td>
						<td class="columnTablename columnText"><label for="{$table.Name}">{$table.Name}</label></td>
						<td class="columnTableRows columnNumbers">{#$table.Rows}</td>
						<td class="columnTableSize columnNumbers">{@$table.Data_length|filesize}</td>
						<td class="columnTableIndexSize columnNumbers">{@$table.Index_length|filesize}</td>
						<td class="columnTableOverhead columnNumbers"{if $table.Data_free > 0} style="color: #900"{/if}>{@$table.Data_free|filesize}</td>
						
						{if $table.additionalColumns|isset}{@$table.additionalColumns}{/if}
					</tr>
				{/foreach}
			
				<tr class="container-3">
					<td class="columnTablename columnText" colspan="2">{lang}wcf.acp.db.manage.tables.total{/lang}</td>
					<td class="columnTableRows columnNumbers">{#$totalRows}</td>
					<td class="columnTableSize columnNumbers">{@$totalDataLength|filesize}</td>
					<td class="columnTableIndexSize columnNumbers">{@$totalIndexLength|filesize}</td>
					<td class="columnTableOverhead columnNumbers"{if $totalDataFree > 0} style="color: #900"{/if}>{@$totalDataFree|filesize}</td>
					
					{if $additionalTotalColumns|isset}{@$additionalTotalColumns}{/if}
				</tr>
			</tbody>
		</table>
	</div>
	
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.db.manage.action{/lang}</legend>
				
				<div class="formElement{if $errorField == 'action'} formError{/if}" id="actionDiv">
					<div class="formFieldLabel">
						<label for="action">{lang}wcf.acp.db.manage.action{/lang}</label>
					</div>
					<div class="formField">
						<select name="action" id="action">
							<option value=""></option>
							{foreach from=$availableActions item=availableAction}
								<option value="{@$availableAction}"{if $availableAction == $action} selected="selected"{/if}>{lang}wcf.acp.db.manage.action.{@$availableAction}{/lang}</option>
							{/foreach}
						</select>
						{if $errorField == 'action'}
							<p class="innerError">
								{lang}wcf.global.error.empty{/lang}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="actionHelpMessage">
						{lang}wcf.acp.db.manage.action.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('action');
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
 	</div>
</form>

{include file='footer'}