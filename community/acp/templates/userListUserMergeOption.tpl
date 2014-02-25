// merge users
count = additionalOptions.length;
additionalOptions[count] = new Object();
additionalOptions[count]['function'] = "document.location.href=fixURL('index.php?form=UserMerge&packageID={@PACKAGE_ID}{@SID_ARG_2ND_NOT_ENCODED}')";
additionalOptions[count]['text'] = '{lang}wbb.acp.user.button.merge{/lang}';
additionalOptions[count]['className'] = 'bottomSeparator';