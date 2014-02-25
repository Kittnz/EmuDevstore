{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/package{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{$packageName}</h2>
		<p>{$packageDescription}</p>
	</div>
</div>

{if $missingPackages > 0}
	<p class="error">{lang}wcf.acp.package.install.error{/lang}</p>
{/if}

{if $excludingPackages|count > 0}
	<div class="error">{lang}wcf.acp.package.install.error.excludingPackages{/lang}
		<ul>
		{foreach from=$excludingPackages item=excludingPackage}
			<li>{lang}wcf.acp.package.install.error.excludingPackages.excludingPackage{/lang}</li>
		{/foreach}
		</ul>
	</div>
{/if}

{if $excludedPackages|count > 0}
	<div class="error">{lang}wcf.acp.package.install.error.excludedPackages{/lang}
		<ul>
		{foreach from=$excludedPackages item=excludedPackage}
			<li>{lang}wcf.acp.package.install.error.excludedPackages.excludedPackage{/lang}</li>
		{/foreach}
		</ul>
	</div>
{/if}

<fieldset>
	<legend>{lang}wcf.acp.package.view.properties{/lang}</legend>

	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.identifier{/lang}</p>
		<p class="formField">{$archive->getPackageInfo('name')}</p>
	</div>

	{if $package}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.version{/lang}</p>
			<p class="formField">{$package->getVersion()}</p>
		</div>
	{/if}
	
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.install.version{/lang}</p>
		<p class="formField">{$packageVersion}</p>
	</div>

	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.date{/lang}</p>
		<p class="formField">{@$packageDate|date}</p>
	</div>

	{if $archive->getPackageInfo('packageURL') != ''}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.package.view.url{/lang}</p>
			<p class="formField"><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$archive->getPackageInfo('packageURL')|rawurlencode}" class="externalURL">{$archive->getPackageInfo('packageURL')}</a></p>
		</div>
	{/if}
	
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.package.view.author{/lang}</p>
		<p class="formField">{if $packageAuthorURL}<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$packageAuthorURL|rawurlencode}" class="externalURL">{$packageAuthor}</a>{else}{$packageAuthor}{/if}</p>
	</div>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

{if $updatableInstances|count > 0} 
	<p class="warning" style="margin: 20px 0 10px 0">{lang}wcf.acp.package.install.updatableInstances.warning{/lang}</p>
	
	<div class="border titleBarPanel">
		<div class="containerHead">
			<h3>{lang}wcf.acp.package.install.updatableInstances{/lang}</h3>
			<p class="smallFont light">{lang}wcf.acp.package.install.updatableInstances.description{/lang}</p>
		</div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$updatableInstances item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnText"><a href="index.php?page=Package&amp;action=install&amp;queueID={@$queueID}&amp;step=changeToUpdate&amp;updatePackageID={@$package.packageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$package.packageName}{if $package.instanceNo > 1 && $package.instanceName == ''} (#{#$package.instanceNo}){/if}</a></td>
					<td class="columnText">{$package.packageVersion}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

{if $requiredPackages|count > 0}
	<div class="border titleBarPanel">
		<div class="containerHead">
			<h3>{lang}wcf.acp.package.view.requiredPackages{/lang}</h3>
			<p class="smallFont light">{lang}wcf.acp.package.view.requiredPackages.description{/lang}</p>
		</div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.name{/lang}</span></div></th>
					<th><div><span class="emptyHead">{lang}wcf.acp.package.list.version{/lang}</span></div></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$requiredPackages item=$package}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnText">{lang}wcf.acp.package.install.packageName{/lang}</td>
					<td class="columnText">{if $package.minversion|isset}{$package.minversion}{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<form method="post" action="index.php?page=Package">
	<div class="formSubmit">
		<input type="button" accesskey="c" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step=cancel&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}')" />
		
		{if $missingPackages == 0 && $excludingPackages|count == 0 && $excludedPackages|count == 0}
			<input type="submit" accesskey="s" name="submitButton" value="{lang}wcf.global.button.next{/lang}" />
		{/if}
		
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	 	{@SID_INPUT_TAG}
	 	<input type="hidden" name="action" value="{@$action}" />
	 	<input type="hidden" name="step" value="installationFrame" />
	 	<input type="hidden" name="queueID" value="{@$queueID}" />
	</div>
</form>
{include file='footer'}