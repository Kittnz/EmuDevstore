{if $this->user->getPermission('admin.user.canEditPassword') && $this->user->getPermission('admin.user.canMailUser')}
	// send new password
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?action=UserSendNewPassword&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.sendNewPassword{/lang}';
	additionalOptions[count]['className'] = 'bottomSeparator';
{/if}

{if $this->user->getPermission('admin.user.canEnableUser')}
	// enable user
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?action=UserEnable&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.enable{/lang}';
	
	// disable user
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?action=UserDisable&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.disable{/lang}';
	
	{if REGISTER_ACTIVATION_METHOD == 1}
		// send activation mail
		count = additionalOptions.length;
		additionalOptions[count] = new Object();
		additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?action=UserSendActivationMail&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
		additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.sendActivationMail{/lang}';
	{/if}
	
	additionalOptions[count]['className'] = 'bottomSeparator';
{/if}

{if $this->user->getPermission('admin.user.canBanUser')}
	// ban user
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?form=UserBan&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.ban{/lang}';
	
	// undo ban
	count = additionalOptions.length;
	additionalOptions[count] = new Object();
	additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?action=UserUnban&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalOptions[count]['text'] = '{lang}wcf.acp.user.button.unban{/lang}';
	additionalOptions[count]['className'] = 'bottomSeparator';
{/if}
