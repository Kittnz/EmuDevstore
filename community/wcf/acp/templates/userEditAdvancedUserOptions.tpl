{if $this->user->getPermission('admin.user.canEnableUser')}
	// enable / disable user
	count = additionalUserOptions.length;
	additionalUserOptions[count] = new Object();
	additionalUserOptions[count]['function'] = "document.location.href=fixURL('index.php?action={if $user->activationCode == 0}UserDisable{else}UserEnable{/if}&userID={@$user->userID}&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalUserOptions[count]['text'] = '{if $user->activationCode == 0}{lang}wcf.acp.user.button.disable{/lang}{else}{lang}wcf.acp.user.button.enable{/lang}{/if}';
{/if}

{if $this->user->getPermission('admin.user.canBanUser')}
	// ban / undo ban
	count = additionalUserOptions.length;
	additionalUserOptions[count] = new Object();
	additionalUserOptions[count]['function'] = "document.location.href=fixURL('index.php?{if $user->banned == 0}form=UserBan{else}action=UserUnban{/if}&userID={@$user->userID}&url='+encodeURIComponent('{@$url|encodeJS}')+'&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
	additionalUserOptions[count]['text'] = '{if $user->banned == 0}{lang}wcf.acp.user.button.ban{/lang}{else}{lang}wcf.acp.user.button.unban{/lang}{/if}';
{/if}
