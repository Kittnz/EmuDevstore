{if $this->session->userAgent|stripos:'MSIE' === false}<?xml version="1.0" encoding="{@CHARSET}"?>
{/if}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{lang}wcf.global.pageDirection{/lang}" xml:lang="{@LANGUAGE_CODE}">
	<head>
		<title>{if $pageTitle|isset}{@$pageTitle}{else}{lang}wcf.global.pageTitle{/lang}{/if} - {lang}wcf.acp{/lang}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={@CHARSET}" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<script type="text/javascript">
			//<![CDATA[
			var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
			var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
			var PACKAGE_ID = {@PACKAGE_ID};
			//]]>
		</script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/protoaculous.1.8.2.min.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/default.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/StringUtil.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/PopupMenuList.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/default.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/ACPMenu.class.js"></script>
		
		{if $specialStyles|isset}
			<!-- special styles -->
			{@$specialStyles}
		{/if}
		
		<style type="text/css">
			@import url("{@RELATIVE_WCF_DIR}acp/style/style-{@PAGE_DIRECTION}.css");
		</style>
		
		<!-- opera styles -->
		<script type="text/javascript">
			//<![CDATA[
			if (Prototype.Browser.Opera) {
				document.write('<style type="text/css">.columnContainer { border: 0; }</style>');
			}
			//]]>
		</script>
		
		<!--[if lt IE 7]>
			<style type="text/css">
				@import url("{@RELATIVE_WCF_DIR}style/extra/ie6-fix{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
				@import url("{@RELATIVE_WCF_DIR}acp/style/extra/ie6-fix{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
			</style>
		<![endif]-->
		
		<!--[if IE 7]>
			<style type="text/css">
				@import url("{@RELATIVE_WCF_DIR}style/extra/ie7-fix{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
				@import url("{@RELATIVE_WCF_DIR}acp/style/extra/ie7-fix{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
			</style>
		<![endif]-->
		
		<!--[if IE 8]>
			<style type="text/css">
				@import url("{@RELATIVE_WCF_DIR}style/extra/ie8-fix{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
			</style>
		<![endif]-->
		
		<script type="text/javascript">
			//<![CDATA[
			var menuItemData = new Array();
			{counter start=-1 print=false}
			{foreach from=$menu->getMenuItems() item=items}
				{foreach from=$items item=item}
					{capture assign='menuItemName'}{lang}{@$item.menuItem}{/lang}{/capture}
					menuItemData[{counter}] = ['{@$item.parentMenuItem|encodeJS}', '{@$item.menuItem|encodeJS}', '{@$menuItemName|encodeJS}', '{@$item.menuItemLink|encodeJS}', '{@$item.menuItemIcon|encodeJS}'];
				{/foreach}
			{/foreach}
			
			var activeMenuItems = new Array();
			{counter start=-1 print=false}
			{foreach from=$menu->getActiveMenuItems() item=menuItem}
				activeMenuItems[{counter}] = '{@$menuItem|encodeJS}';
			{/foreach}
			
			// acp menu
			acpMenu.init(menuItemData, activeMenuItems);
			//]]>
		</script>
		
		
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/InlineHelp.class.js"></script>
	</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div id="headerContainer">
	<a id="top"></a>
	
	<div id="userPanel" class="userPanel">
		<p id="date">
			<img src="{@RELATIVE_WCF_DIR}icon/dateS.png" alt="" /> <span>{@TIME_NOW|fulldate} UTC{if $timezone > 0}+{@$timezone}{else if $timezone < 0}{@$timezone}{/if}</span>
		</p>
		<div class="userPanelInner">
			<p id="userNote"> 
				{if $this->user->userID != 0}{lang}wcf.acp.user.userNote{/lang}{/if}
			</p>
			<div id="userMenu">
				<ul>
					<li><a href="index.php?action=Logout&amp;t={@SECURITY_TOKEN}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/logoutS.png" alt="" /> <span>{lang}wcf.user.logout{/lang}</span></a></li>
					<li><a id="sitemapButton" href="javascript:void(0);"><img src="{@RELATIVE_WCF_DIR}icon/sitemapS.png" alt="" /> <span>{lang}wcf.acp.sitemap{/lang}</span></a></li>
					<li><a id="menuPopupHelp" href="javascript:void(0);"><img src="{@RELATIVE_WCF_DIR}icon/helpOptionsS.png" alt="" /> <span>{lang}wcf.acp.help{/lang}</span></a>
						<div class="hidden" id="menuPopupHelpMenu">
							<ul>
								<li id="helpLinkDisable"><a onclick="inlineHelp.disableHelp();" href="javascript:void(0);"><span>{lang}wcf.acp.help.disable{/lang}</span></a></li>
								<li id="helpLinkComplete"><a onclick="inlineHelp.enableHelp();" href="javascript:void(0);"><span>{lang}wcf.acp.help.complete{/lang}</span></a></li>
								<li id="helpLinkInteractive"><a onclick="inlineHelp.enableInteractiveHelp();" href="javascript:void(0);"><span>{lang}wcf.acp.help.interactive{/lang}</span></a></li>
							</ul>
						</div>
	
						<script type="text/javascript">
							//<![CDATA[
							popupMenuList.register('menuPopupHelp');
							onloadEvents.push(function() { inlineHelp.setStatus('{$this->user->inlineHelpStatus}'); });
							//]]>
						</script>
					</li>
					{if $quickAccessPackages|count > 1}
						<li><a id="packageQuickAccess"><img src="{@RELATIVE_WCF_DIR}icon/packageOptionsS.png" alt="" /> <span>{lang}wcf.acp.packageQuickAccess{/lang}</span></a>
							<div class="hidden" id="packageQuickAccessMenu">
								<ul>
									{foreach from=$quickAccessPackages item=quickAccessPackage}
										<li{if PACKAGE_ID == $quickAccessPackage.packageID} class="active"{/if}><a href="{@RELATIVE_WCF_DIR}{$quickAccessPackage.packageDir}acp/index.php{@SID_ARG_1ST}"><span>{$quickAccessPackage.packageName}{if $quickAccessPackage.instanceNo > 1 && $quickAccessPackage.instanceName == ''} #{#$quickAccessPackage.instanceNo}{/if}</span></a></li>
									{/foreach}
								</ul>
							</div>
	
							<script type="text/javascript">
								//<![CDATA[
								popupMenuList.register('packageQuickAccess');
								//]]>
							</script>
						</li>
					{/if}
					{if $additionalHeaderButtons|isset}{@$additionalHeaderButtons}{/if}
					
				</ul>
			</div>
		</div>
	</div>
	
	<div id="header" class="border">
		<div id="logo">
			<div class="logoInner">
				<h1 class="pageTitle"><a href="index.php?packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp{/lang}</a></h1>			
				<a href="index.php?packageID={@PACKAGE_ID}{@SID_ARG_2ND}" class="pageLogo">
					<img src="images/acpLogo.png" title="" alt="" />
				</a>
			</div>
		</div>

		<div id="sidebar">
			<ul>
				{foreach from=$menu->getMenuItems('') item=item}
					<li id="tab{$item.menuItem}"><a onclick="acpMenu.showMenuBar('{$item.menuItem}')">{if $item.menuItemIcon != ''}<img src="{$item.menuItemIcon}" alt="" /> {/if}<span>{lang}{$item.menuItem}{/lang}</span></a></li>
				{/foreach}
			</ul>
		</div>
		
		<div class="mainMenu" id="menuBar"><div class="mainMenuInner"><ul></ul></div></div>
	</div>
</div>	
<div id="mainContainer">
	<div id="content">