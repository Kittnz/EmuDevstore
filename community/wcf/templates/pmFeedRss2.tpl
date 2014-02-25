<?xml version="1.0" encoding="{@CHARSET}"?>
<rss version="2.0">
	<channel>
		<title>{lang}wcf.pm.feed.title{/lang}</title>
		<link>{@PAGE_URL}/</link>
		<description>{lang}wcf.pm.feed.description{/lang}</description>
		
		<pubDate>{@'r'|gmdate:TIME_NOW}</pubDate>
		<lastBuildDate>{@'r'|gmdate:TIME_NOW}</lastBuildDate>
		<generator>WoltLab Community Framework {@WCF_VERSION}</generator>
		<ttl>60</ttl>
		
		{foreach from=$messages item=$message}
			<item>
				<title>{$message->subject}</title>
				<author>{$message->username}</author>
				<link>{@PAGE_URL}/index.php?page=PMView&amp;pmID={@$message->pmID}#pm{@$message->pmID}</link>
				<guid>{@PAGE_URL}/index.php?page=PMView&amp;pmID={@$message->pmID}#pm{@$message->pmID}</guid>
				<pubDate>{@'r'|gmdate:$message->time}</pubDate>
				<description><![CDATA[{@$message->getFormattedMessage()}]]></description>
			</item>
		{/foreach}
	</channel>
</rss>