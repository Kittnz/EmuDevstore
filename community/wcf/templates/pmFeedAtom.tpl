<?xml version="1.0" encoding="{@CHARSET}"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>{lang}wcf.pm.feed.title{/lang}</title>
	<id>{@PAGE_URL}/</id>
	<updated>{@'c'|gmdate:TIME_NOW}</updated>
	<link href="{@PAGE_URL}/" />
	<generator uri="http://www.woltlab.com/" version="{@WCF_VERSION}">
		WoltLab Community Framework
	</generator>
	<subtitle>{lang}wcf.pm.feed.description{/lang}</subtitle>
	
	{foreach from=$messages item=$message}
		<entry>
			<title>{$message->subject}</title>
			<id>{@PAGE_URL}/index.php?page=PMView&amp;pmID={@$message->pmID}#pm{@$message->pmID}</id>
			<updated>{@'c'|gmdate:$message->time}</updated>
			<author>
				<name>{$message->username}</name>
			</author>
			<content type="html"><![CDATA[{@$message->getFormattedMessage()}]]></content>
			<link href="{@PAGE_URL}/index.php?page=PMView&amp;pmID={@$message->pmID}#pm{@$message->pmID}" />
		</entry>
	{/foreach}
</feed>