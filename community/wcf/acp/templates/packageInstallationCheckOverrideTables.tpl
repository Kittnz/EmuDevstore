{include file='setupWindowHeader'}
<script type="text/javascript">
//<![CDATA[
	var checkedAll = true;	
	function toggleDontAsk(checkboxID, form) {
		var element = document.getElementById(checkboxID);		
		if (element.disabled) {
			element.disabled = false;
			toggleCheckBoxes(false, form);
		}
		else {
			element.disabled = true;
			element.checked = false;	
			toggleCheckBoxes(true, form);
		}
	}
	
	function toggleCheckBoxes(disable, form) {
		var inputs = form.getElementsByTagName('input');			
		for(var i in inputs) {
			var input = inputs[i];
			if (input.type == 'checkbox' && !input.name.match(/dontAskAgain/)) {
				input.disabled = disable;
				input.checked = false;
				checkedAll = false;
			}
		}
	}
	
	function checkUncheckAll(form) {
		var inputs = form.getElementsByTagName('input');			
		for(var i in inputs) {
			var input = inputs[i];
			if (input.type == 'checkbox' && !input.name.match(/(dontAskAgain|checkUncheck)/)) {
				input.checked = checkedAll;
			}
		}
		checkedAll = (checkedAll) ? false : true;
	}
//]]>
</script>

<form method="post" action="index.php?page=Package">
	<fieldset>
		<legend>{lang}wcf.acp.package.installation.tables.conflict{/lang}</legend>
		<div class="warning" style="margin: 10px 10px 0 10px;">{lang}wcf.acp.package.installation.tables.warning{/lang}</div>
		<div style="padding:5px;">
			<input style="float:left;marging-left:15px;" type="checkbox" onclick="javscript:toggleDontAsk('dontAskAgainKeep', this.form)" id="dontAskAgainOverride" name="dontAskAgainOverride" />
			<label style="float:left;margin-left:10px;" for="dontAskAgainOverride">{lang}wcf.acp.package.installation.tables.dontAskAgainOverride{/lang}</label>
			<br style="clear:both;" />
			<input style="float:left;marging-left:15px;" type="checkbox" onclick="javscript:toggleDontAsk('dontAskAgainOverride', this.form)" name="dontAskAgainKeep" id="dontAskAgainKeep" />
			<label style="float:left;margin-left:10px;" for="dontAskAgainKeep">{lang}wcf.acp.package.installation.tables.dontAskAgainKeep{/lang}</label>
		</div>
		<hr style="margin-top:10px;clear:both;" />
		<table style="text-align:left;" cellpadding="4" cellspacing="0">
			<thead>
				<tr>
					<th>
						<label class="emptyHead">
							<input type="checkbox" name="checkUncheck" value="" onmouseup="javascript:checkUncheckAll(this.form);" />
						</label>
					</th>
					<th>
						{lang}wcf.acp.package.installation.tables.tableName{/lang}
					</th>
					<th>
						{lang}wcf.acp.package.installation.tables.solution{/lang}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$tables key="index" item="table"}
					<tr>
						<td><input type="checkbox" id="table{$index}" name="checkedTables[]" value="{$table.tableName}" /></td>
						<td><label for="table{$index}">{$table.tableName}</label></td>
						<!-- TODO: put sql statements in the override type -->
						<td>{implode from=$table.overrideTypes item="type"}{$type}{/implode}</td>	
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" onclick="parent.stopAnimating()" />
		<input type="hidden" name="overrideTables" value="1" />
		<input type="hidden" name="queueID" value="{@$queueID}" />
		<input type="hidden" name="action" value="{@$action}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="step" value="{@$step}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="hidden" name="send" value="send" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	//]]>
</script>

{include file='setupWindowFooter'}
