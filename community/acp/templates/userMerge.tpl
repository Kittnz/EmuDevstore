{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/usersL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wbb.acp.user.merge{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserMerge">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wbb.acp.user.merge.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$user}</a>{/implode}
				</div>
			</fieldset>	
			
			<fieldset>
				<legend>{lang}wbb.acp.user.merge.primaryUser{/lang}</legend>
				
				<div class="formElement{if $errorField == 'userID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="userID">{lang}wbb.acp.user.merge.primaryUser{/lang}</label>
					</div>
					<div class="formField">
						<select name="userID" id="userID">
							<option value="0"></option>
							{foreach from=$users item=$user}
								<option value="{@$user->userID}"{if $userID == $user->userID} selected="selected"{/if}>{$user}</option>
							{/foreach}
						</select>
						{if $errorField == 'userID'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<p class="formFieldDesc">
						{lang}wbb.acp.user.merge.primaryUser.description{/lang}
					</p>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}