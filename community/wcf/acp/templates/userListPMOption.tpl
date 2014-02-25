{if $this->user->getPermission('admin.user.canPMUser')}
	// send pm
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?form=UserPM&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.sendPM{/lang}';
	additionalOptions[count]['className'] = 'bottomSeparator';
{/if}